<?php
require 'query_utils.php';
require 'get_query_generators.php';

header("Content-type: application/json");

// keep track of relavent operations to be printed if we're in debug mode.
if (!isset($GLOBALS['DEBUG_INFO'])) {
    $GLOBALS['DEBUG_INFO'] = ["executed_sql" => []];
}

try {
    $params = handle_request();

    // store input as debug informtion
    $GLOBALS['DEBUG_INFO']["params"] = $params;

    do_result(handle_get($params));
    exit();
} catch (Exception $e) {
    do_error(400, $e);
    exit();
}

/**
 * Function executes sql query and logs any sql error.
 *
 * @param query_string sql query string
 * @param bind_variables an associative array of variables to bind to sql query
 *
 */
function get_query_result($query_string, $bind_variables)
{
    $GLOBALS['DEBUG_INFO']["executed_sql"][] = [
        "query" => $query_string,
        "bindings" => $bind_variables
    ];

    require '../db/config.php';
    // Attempt to execute sql command and print response in json format.
    // If a sql error occurs, JSON error object.
    try {
        // connect to the mysql database
        $conn = new PDO(
            "mysql:host=$servername;dbname=$database",
            $username,
            $password
        );
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare($query_string);
        foreach ($bind_variables as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        //fetch all results
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $fetched;
    } catch (PDOException $e) {
        do_error(500, $e);
        exit();
    }
}

/**
 * This function handles GET requests and returns a data package that gets the
 * required data for the what according to the API documentation.
 *
 * @throws InvalidPage The requested what does not exist
 * @return package An associative array with type and data requested
 */
function handle_get($params)
{
    $bind_variables = [];
    $role = [];
    if (isset($params['user_id']) && $params['user_id'] != "null") {
        $bind_variables[':user_id'] = $params['user_id'];
        if ($params["what"] != "user_info") {
            $role = get_query_result(gen_query_user_role(), $bind_variables);
            if (empty($role)) {
                throw new Exception(
                    "User '" . $params['user_id'] . "' does not exist"
                );
            }
        }
    }
    if (isset($params['survey_id']) && $params['survey_id'] != "null") {
        $bind_variables[':survey_id'] = $params['survey_id'];
    }
    if (isset($params['target_ta']) && $params['target_ta'] != "null") {
        $bind_variables[':target_ta'] = $params['target_ta'];
    }
    if (isset($params["term"]) && $params['term'] != "null") {
        $bind_variables[':term'] = $params["term"];
    }
    if (isset($params["course_code"]) && $params['course_code'] != "null") {
        $bind_variables[':course_code'] = $params["course_code"];
    }
    $survey_id = false;
    if (isset($params["survey_id"]) && $params['survey_id'] != "null") {
        $survey_id = $params["survey_id"];
    }
    switch ($params["what"]) {
        case "tas":
            $tas = get_query_result(set_parameters($params), $bind_variables);

            $ta_package = array('TYPE' => "ta_package", 'DATA' => $tas);
            return $ta_package;
        case "questions":
            $questions = get_query_result(
                gen_query_questions(),
                $bind_variables
            );
            $question_package = array(
                'TYPE' => "questions_package",
                'DATA' => $questions
            );
            return $question_package;
        case "course_pairings":
            $course_pairings = get_query_result(
                set_parameters($params),
                $bind_variables
            );
            $course_pairings_package = array(
                'TYPE' => "course_pairings_package",
                'DATA' => $course_pairings
            );
            return $course_pairings_package;
        case "user_info":
            $include_photo = true;
            if (isset($params["include_photo"])) {
                if ($params["include_photo"] == 'false') {
                    $include_photo = false;
                }
            }
            $list_of_users = is_list_of_users($params["user_id"]);
            $user_info = get_query_result(
                gen_query_user_info($include_photo, $list_of_users),
                $bind_variables
            );
            // set photo to null if don't want to include
            if (!$include_photo) {
                foreach ($user_info as $key => $value) {
                    $user_info[$key]["photo"] = null;
                }
            }
            $user_package = array(
                'TYPE' => "user_package",
                'DATA' => $user_info
            );
            return $user_package;
        case "surveys":
            $survey_package = get_list_of_surveys(
                $role[0],
                $survey_id,
                $bind_variables,
                false
            );
            return $survey_package;
        case "survey_results":
            // in this case $params['survey_id'] referrs to `survey_instance_id`s

            $survey_ids = [];
            // if we specified $params['survey_id'], it takes precidence
            if (isset($params["survey_id"])) {
                $survey_ids = explode(",", $params["survey_id"]);
            }
            $user_id = $params["user_id"];
            $target_ta = $params["target_ta"];
            $course_code = array_get($params, "course_code");
            $term = array_get($params, "term");
            $other_survey_ids = get_associated_survey_instances(
                $target_ta,
                $course_code,
                $term
            );

            // merge together our list of instance ids
            $survey_ids = array_merge($survey_ids, $other_survey_ids);
            $survey_ids = array_unique($survey_ids);

            $data = [];
            foreach ($survey_ids as $surv_id) {
                if (can_view_survey_instance($surv_id, $user_id)) {
                    $survey_package = get_survey_package(null, $surv_id);
                    $data[] = $survey_package;
                }
            }
            $ret = ["TYPE" => "survey_package", "DATA" => $data];
            do_result($ret);
            exit();
        default:
            throw new Exception(
                "'Unknown what' action '" . $params["what"] . "'."
            );
    }
}

/**
 * Returns survey data
 *
 * @param role associative array to check role of user.
 * @param survey_id True if we want a list of surveys
 * @param bind_variables associative array for PDO bindValue function
 * @param is_instance True if we're getting survey_instances
 * @return Survey_package associative array that contains all the information
 * of the survey_package requested
 * Return JSON: {TYPE: "Survey_Package",
 *                DATA: [{
 *                        timedate_open: str,
 *                        timedate_close: str,
 *                        name: str,
 *                        survey_id: int | null,
 *                        survey_instance_id: int | null,
 *                        questions: [{
 *                           position: int,
 *                           question: {
 *                               contents: <survey.js structure>,
 *                               type: str,
 *                               question_id: int,
 *                               responses: str | null
 *                           }
 *                        }]
 *                 }
 *               }]
 */
function get_list_of_surveys($role, $survey_id, $bind_variables, $is_instance)
{
    //default_survey_choices
    $default_choices = [1, 2, 3, 4, 5, 6];

    //dealing with optional params term and course_code
    $course = false;
    if (isset($bind_variables[":course_code"])) {
        $course = true;
    }
    $term = false;
    if (isset($bind_variables[":term"])) {
        $term = true;
    }
    $temp_variables = $bind_variables;
    if ($survey_id) {
        unset($temp_variables[':survey_id']);
    }
    //get list of surveys from a user;
    $survey_choices = get_query_result(
        gen_query_surveys($role, $term, $course, $is_instance),
        $temp_variables
    );
    //get list of questions
    $list_of_quesitons = get_query_result(gen_query_questions(), []);
    //survey result to be returned
    $result = [];
    if (sizeof($survey_choices) <= 0) {
        $result = null;
    } elseif ($survey_id) {
        //invalid survey_id check
        $valid_surveys = filter_surveys(
            $survey_choices,
            $bind_variables[':survey_id'],
            $is_instance
        );

        //get all surveys selected
        $survey_data = get_query_result(gen_query_get_survey_data(), [
            ":survey_id" => $valid_surveys
        ]);

        //foreach survey_data combine the choices to create one choices attribute
        foreach ($survey_data as $index => $value) {
            // get 3 sets of survey choices

            $choices = get_query_result(gen_query_get_survey_choices(), [
                ":survey_id" => $value["survey_id"]
            ])[0];
            $all_choices = [
                $choices["dept_choices_id"],
                $choices["course_choices_id"],
                $choices["ta_choices_id"]
            ];
            $survey_data[$index]["questions"] = [];

            //get survey responses if the survey results are requested
            $responses = null;
            if ($is_instance) {
                //get list of responses when survey_results is requested
                $responses = get_query_result(gen_query_survey_responses(), [
                    ":survey_id" => $bind_variables[":survey_id"]
                ]);
            }
            //default set of choices + 3 set of choices for the provided survey
            $choice_set = $default_choices;
            for ($i = 0; $i < 3; $i++) {
                $choices = get_query_result(gen_query_get_choices(), [
                    ":choices_id" => $all_choices[$i]
                ]);

                //override the 2 choices for each role if the choice exist
                if (!sizeof($choices) == 0) {
                    //Assume data is proper
                    //  default_choices = [1,    2,    3,    4,   5,    6]
                    //  dept_choices =    [3,    8,    5,    5,   4,    8]
                    //  course_choices =  [NULL, NULL, 3,    5,   4,    7]
                    //  ta_choices =      [NULL, NULL, NULL, NULL,5,    9]
                    //  result =          [3,    8,    3,    5,   5,    9]
                    //note: choice set is initialized with default_choices
                    $choice_set[$i * 2] = $choices[0]["choice" . ($i * 2 + 1)];
                    $choice_set[2 * $i + 1] = $choices[0][
                        "choice" . ($i * 2 + 2)
                    ];
                }
            }
            //join choice id with question id to get question data for each choices
            foreach ($choice_set as $key => $value) {
                $q = $list_of_quesitons[$value - 1];
                $q['position'] = ($key + 1);
                $q["responses"] = $responses
                    ? explode(",", $responses[$value - 1]['answers'])
                    : null;
                array_push($survey_data[$index]["questions"], $q);
            }
            array_push($result, $survey_data[$index]);
        }
    } else {
        //list of surveys
        $result = $survey_choices;
    }

    $survey_package = array('TYPE' => "survey_package", 'DATA' => $result);
    return $survey_package;
}
/**
 * Returns true if user_id is a list of users.
 * @param user_id Value of user_id parameters
 * @return TRUE if user_id is a list
 */
function is_list_of_users($user_id)
{
    $user_id_list = explode(",", $user_id);

    $list_of_users = false;
    if (sizeof($user_id_list) > 1) {
        $list_of_users = true;
    }
    return $list_of_users;
}

/**
 * This functions sets parameters for gen_query_course_pairings and
 * gen_query_course_pairings_section according to API design.
 * Function returns the result of gen_query_course_pairings.
 *
 * @param parameters Associative array of parameter and values passed from url
 * @return sql sql that gets CourseAssociations object according to UML diagram
 */
function set_parameters($parameters)
{
    $is_ta = true;
    if ($parameters["what"] == "course_pairings") {
        if ($parameters["column"] == "instructor") {
            $is_ta = false;
        }
    }

    $course_code = false;
    if (
        isset($parameters["course_code"]) &&
        $parameters['course_code'] != "null"
    ) {
        $course_code = true;
    }

    $term = false;
    if (isset($parameters["term"]) && $parameters['term'] != "null") {
        $term = true;
    }

    // column is a required parameter for course_pairings
    if (isset($parameters["column"]) && $parameters['column'] != "null") {
        if ($parameters["column"] == "sections") {
            return gen_query_course_pairings_section($course_code, $term);
        }
    }
    return gen_query_course_pairings($course_code, $term, $is_ta);
}

/**
 * Filters out the surveys not related to current user, throws exception when no surveys are related to current user
 *
 * @param survey_choices array of surveys related to current user
 * @param requested_surveys parameter passed in survey_id as comma separated string ("33,3")
 */
function filter_surveys($survey_choices, $requested_surveys, $is_instance)
{
    //set list of valid_surveys
    $list_of_surveys = [];

    foreach ($survey_choices as $key => $value) {
        if ($is_instance) {
            array_push($list_of_surveys, $value["survey_instance_id"]);
        } else {
            array_push($list_of_surveys, $value["survey_id"]);
        }
    }
    //split the requested survey string into an array
    $rs = explode(',', $requested_surveys);

    //unset each element that is not valid
    foreach ($rs as $key => $value) {
        if (!in_array($value, $list_of_surveys)) {
            unset($rs[$key]);
        }
    }
    //if no valid surveys requestedd
    if (sizeof($rs) == 0) {
        throw new Exception("No Valid Surveys Requested");
    }
    //return the valid surveys as one string
    $valid_surveys = implode(",", $rs);
    return $valid_surveys;
}
