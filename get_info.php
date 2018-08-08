<?php
require 'utils.php';
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
function handle_get($parameters)
{
    $bind_variables = [];
    $role = [];
    if (isset($parameters['user_id']) && $parameters['user_id'] != "null") {
        $bind_variables[':user_id'] = $parameters['user_id'];
        if ($parameters["what"] != "user_info") {
            $role = get_query_result(gen_query_user_role(), $bind_variables);
            if (empty($role)) {
                throw new Exception(
                    "User '" . $parameters['user_id'] . "' does not exist"
                );
            }
        }
    }
    if (isset($parameters['survey_id']) && $parameters['survey_id'] != "null") {
        $bind_variables[':survey_id'] = $parameters['survey_id'];
    }
    if (isset($parameters['target_ta']) && $parameters['target_ta'] != "null") {
        $bind_variables[':target_ta'] = $parameters['target_ta'];
    }
    if (isset($parameters["term"]) && $parameters['term'] != "null") {
        $bind_variables[':term'] = $parameters["term"];
    }
    if (
        isset($parameters["course_code"]) &&
        $parameters['course_code'] != "null"
    ) {
        $bind_variables[':course_code'] = $parameters["course_code"];
    }
    $survey_id = false;
    if (isset($parameters["survey_id"]) && $parameters['survey_id'] != "null") {
        $survey_id = $parameters["survey_id"];
    }
    switch ($parameters["what"]) {
        case "tas":
            $tas = get_query_result(
                set_parameters($parameters),
                $bind_variables
            );

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
                set_parameters($parameters),
                $bind_variables
            );
            $course_pairings_package = array(
                'TYPE' => "course_pairings_package",
                'DATA' => $course_pairings
            );
            return $course_pairings_package;
        case "user_info":
            $include_photo = true;
            if (isset($parameters["include_photo"])) {
                if ($parameters["include_photo"] == 'false') {
                    $include_photo = false;
                }
            }
            $list_of_users = is_list_of_users($parameters["user_id"]);
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
            $survey_package = get_list_of_surveys(
                $role[0],
                $survey_id,
                $bind_variables,
                true
            );
            return $survey_package;
        default:
            throw new Exception("InvalidPage");
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
 *                        questionChoice: [{
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

    //thsese are not needed for now
    unset($temp_variables[':target_ta']);
    unset($temp_variables[':course_code']);
    $survey_choices = null;
    
    //get list of surveys from a user;
    if($is_instance){
        $survey_choices =  get_query_result(gen_query_list_of_survey_instances(),
        [":user_id" => $bind_variables[":user_id"]]
        );
    }else{
        unset($temp_variables[':survey_id']);
        $survey_choices = get_query_result(
          gen_query_surveys($role, $term, $course),
          $temp_variables
        );
    }
    //get list of questions
    $list_of_quesitons = get_query_result(gen_query_questions(), []);
    //survey result to be returned
    $result = [];
    if (sizeof($survey_choices) <= 0) {
        $result = null;
    } elseif ($survey_id) {

        //for instances $valid_surveys is survey_instance_ids here
        //so we must find the according survey_ids
        if($is_instance){
            $survey_id = get_query_result(gen_query_get_survey_id(),[':survey_instance_id' => $survey_id])[0]["survey_id"];
        }
        //setup for next query
        unset($temp_variables[':user_id']);
        $temp_variables[":survey_id"] = $survey_id;
        //get all surveys selected
        $survey_data = get_query_result(gen_query_get_survey_data($term),
            $temp_variables
        );

        //setup for next query
        unset($temp_variables[':term']);
        //foreach survey_data combine the choices to create one choices attribute
        foreach ($survey_data as $index => $value) {
            // get 3 sets of survey choices
            $choices = get_query_result(gen_query_get_survey_choices(),
                $temp_variables
            )[0];
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
                //match quesiton_id to their responses if survey is given
                if($is_instance){
                    foreach ($responses as $i => $val) {
                        if($val["question_id"] == $value){
                            //split answers into a list
                            $q["responses"] = explode("----", $val['answers']);
                        }
                    }
                }else{
                    $q["responses"] = null;
                }
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
