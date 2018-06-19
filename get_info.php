<?php
require 'get_query_generators.php';
header("Content-type: application/json");
try {
    $method = "";
    if (isset($_SERVER['REQUEST_METHOD'])) {
        $query = "";
        $method = $_SERVER['REQUEST_METHOD'];
        // Request stored as associative array
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case "GET":
                print json_encode(handle_get($_GET));
                break;

            default:
                throw new Exception("Invalid Request");
                break;
        }
    } else {
        $error = 'No Request Method Found';
        throw new Exception($error);
    }
} catch (Exception $e) {
    $result = set_http_response(400);
    date_default_timezone_set('America/Toronto');
    error_log(
        date("Y-m-d h:i:sa") . " : " . $e->getMessage() . "\n",
        3,
        "errors.log"
    );
    print json_encode($result, JSON_PRETTY_PRINT);
    exit();
}

function get_query_result($query_string, $bind_variables)
{
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
        $result = set_http_response(500);
        date_default_timezone_set('America/Toronto');
        error_log(
            date("Y-m-d h:i:sa") . " : " . $e->getMessage() . "\n",
            3,
            "errors.log"
        );
        print json_encode($result, JSON_PRETTY_PRINT);
        exit();
    }
}

/**
 * This function handles GET requests and returns a sql query that gets the
 * required data for the what according to the API documentation.
 *
 * @throws InvalidPage The requested what does not exist
 * @return sql query to the corresponding what parameter
 */
function handle_get($parameters)
{
    $bind_variables = [];
    $role = [];
    if (isset($parameters['user_id'])) {
        $bind_variables[':user_id'] = $parameters['user_id'];
        $role = get_query_result(gen_query_user_role(), $bind_variables);
    }
    if (isset($parameters['survey_id'])) {
        $bind_variables[':survey_id'] = $parameters['survey_id'];
    }
    if (isset($parameters["term"])) {
        $bind_variables[':term'] = $parameters["term"];
    }
    if (isset($parameters["course_code"])) {
        $bind_variables[':course_code'] = $parameters["course_code"];
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
            $user_package = array(
                'TYPE' => "user package",
                'DATA' => $user_info
            );
            return $user_package;
        case "surveys":
            $provided_survey = false;
            if (isset($parameters["survey_id"])) {
                $provided_survey = $parameters["survey_id"];
            }
            $survey_package = get_list_of_surveys(
                $role[0],
                $provided_survey,
                $bind_variables,
                false
            );

            return $survey_package;
        case "survey_results":
            $provided_survey = false;
            if (isset($parameters["survey_id"])) {
                $provided_survey = $parameters["survey_id"];
            }
            $survey_package = get_list_of_surveys(
                $role[0],
                $provided_survey,
                $bind_variables,
                true
            );
            return $survey_package;
        default:
            throw new Exception("InvalidPage");
            break;
    }
}

/**
 * Returns true if user_id is a list of users.
 * @param user_id Value of user_id parameters
 * @return TRUE if user_id is a list
 */
function get_list_of_surveys($role, $survey_id, $bind_variables, $is_instance)
{
    $course = false;
    if (isset($bind_variables[":course_code"])) {
        $course = true;
    }
    $term = false;
    if (isset($bind_variables[":term"])) {
        $term = true;
    }
    $survey_choices = get_query_result(
        gen_query_surveys($role, $survey_id, $is_instance, $term, $course),
        $bind_variables
    );
    $list_of_quesitons = get_query_result(gen_query_questions(), []);
    if (sizeof($survey_choices) <= 0) {
        $survey_data = null;
    } elseif ($survey_id) {
        $survey_data = $survey_choices[0];
        $survey_data["questions"] = [];
        if (!isset($survey_data["course_number_locked"])) {
            $survey_data["course_number_locked"] = null;
        }
        $responses = [];
        if ($is_instance) {
            $responses = get_query_result(gen_query_survey_responses(), [
                ":survey_id" => $bind_variables[":survey_id"]
            ]);
        }
        for ($i = 1; $i <= 6; $i++) {
            $question = $list_of_quesitons[$survey_data["choice" . $i] - 1];
            $question["responses"] = isset(
                $responses[$survey_data["choice" . $i] - 1]
            )
                ? $responses[$survey_data["choice" . $i] - 1]["answers"]
                : null;
            $question['position'] = $i;
            array_push($survey_data["questions"], $question);
            unset($survey_data["choice" . $i]);
        }
    } else {
        $survey_data = $survey_choices;
    }
    $survey_package = array('TYPE' => "survey_package", 'DATA' => $survey_data);
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
 * This functions sets parameters for gen_query_course_associations based on
 * what parameter. Function returns the result of gen_query_course_associations.
 *
 * @param num The HTTP status code
 * @return array containing the HTTP status of request
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
    if (isset($parameters["course_code"])) {
        $course_code = true;
    }

    $term = false;
    if (isset($parameters["term"])) {
        $term = true;
    }

    if (isset($parameters["column"])) {
        if ($parameters["column"] == "sections") {
            return gen_query_course_pairings_section($course_code, $term);
        }
    }
    return gen_query_course_associations($course_code, $term, $is_ta);
}

/**
 * This function returns an HTTP status corresponding to the result of the
 * current request
 *
 * @param num The HTTP status code
 * @return array containing the HTTP status of request
 */
function set_http_response($num)
{
    $http = array(
        200 => 'HTTP/1.1 200 OK',
        202 => 'HTTP/1.1 202 Accepted',
        400 => 'HTTP/1.1 400 Bad Request',
        500 => 'HTTP/1.1 500 Internal Server Error'
    );

    header($http[$num]);

    return array('CODE' => $num, 'ERROR' => $http[$num]);
}
