<?php
require 'survey_query_generators.php';
require 'handle_request.php';
header("Content-type: application/json");
try {
    $method = "";
    $REQUEST_data = handle_request();
    if (isset($REQUEST_data['REQUEST_METHOD'])) {
        $method = $REQUEST_data['REQUEST_METHOD'];
        switch ($method) {
            case "GET":
                print json_encode(handle_get($REQUEST_data));
                exit();
            case "POST":
                print json_encode(handle_post($REQUEST_data['post_body']));
                exit();
            default:
                throw new Exception("Invalid Request");
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
/**
 * Function executes sql query and logs any sql error.
 *
 * @param query_string sql query string
 * @param bind_variables an associative array of variables to bind to sql query
 *
 */
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
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $fetched;
        } else {
            return array("TYPE" => "success");
        }
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
    }

    if (
        isset($parameters["override_token"]) &&
        $parameters["override_token"] != "null"
    ) {
        $bind_variables[':override_token'] = $parameters['override_token'];
    }

    switch ($parameters["what"]) {
        case "get_surveys":
            $survey_package = get_survey_questions($bind_variables);
            return $survey_package;
        case "get_ta":
            $ta_package = get_ta_info($bind_variables);
            return $ta_package;
        default:
            throw new Exception("InvalidPage");
    }
}

/**
 * Function returns survey question package for the select survey.
 *
 * @return package array containing question package for selected survey.
 */
function get_survey_questions($bind_variables)
{
    $survey_instances_bind[':override_token'] = $bind_variables[
        ':override_token'
    ];
    $survey_instances = get_query_result(
        gen_query_survey_instance(),
        $survey_instances_bind
    );

    $choices_id_bind["choices_id"] = $survey_instances[0]["choices_id"];

    $choices = get_query_result(gen_query_choices(), $choices_id_bind);

    $survey_id_bind["survey_id"] = $survey_instances[0]["survey_id"];

    $name = get_query_result(gen_query_surveys(), $survey_id_bind)[0]["name"];

    $list_of_quesitons = get_query_result(gen_query_questions(), []);
    $select_questions = select_questions($choices, $list_of_quesitons);
    $result = array(
        "survey_instance_id" => $survey_instances[0]["survey_instance_id"],
        "name" => $name,
        "timedate_open" => $survey_instances[0]["survey_open"],
        "timedate_close" => $survey_instances[0]["survey_close"],
        "questions" => $select_questions
    );

    $survey_package = array('TYPE' => "survey_package", 'DATA' => $result);
    return $survey_package;
}

/**
 * Returns list of questions for the specified choice id.
 *
 * @return array questions for the specified choice id.
 */
function select_questions($choices, $list_of_quesitons)
{
    $select_questions = array();
    $position = 1;
    foreach ($choices as $choice) {
        foreach ($choice as $key => $value) {
            if ($key != "choices_id") {
                foreach ($list_of_quesitons as $question) {
                    if ($question["question_id"] == $value) {
                        $question["position"] = $position++;
                        array_push($select_questions, $question);
                    }
                }
            }
        }
    }
    return $select_questions;
}

/**
 * Function returns ta data according to API documentation
 *
 * @param bind_variables Associative array containing key value pairs of URL parameters
 * @return array Package containing ta data according to URL.
 */
function get_ta_info($bind_variables)
{
    $survey_instances_bind[':override_token'] = $bind_variables[
        ':override_token'
    ];
    $survey_instances = get_query_result(
        gen_query_survey_instance(),
        $survey_instances_bind
    );

    $user_association_bind[":user_association_id"] = $survey_instances[0][
        "user_association_id"
    ];
    $user_association = get_query_result(
        gen_query_user_association(),
        $user_association_bind
    );

    $user_info_bind[":user_id"] = $user_association[0]["user_id"];
    $user_info = get_query_result(gen_query_user_info(), $user_info_bind);

    $section_bind[":section_id"] = $survey_instances[0]["survey_id"];
    $section = get_query_result(gen_query_section(), $section_bind);

    $data = array(
        "user_id" => $user_association[0]["user_id"],
        "name" => $user_info[0]["name"],
        "photo" => $user_info[0]["photo"],
        "section" => $section[0]["section_code"],
        "course_code" => $user_association[0]["course_code"]
    );

    $ta_package = array('TYPE' => "ta_package", 'DATA' => $data);
    return $ta_package;
}

/**
 * This function handles POST requests and inserts the specified information
 * and returns a package according to the API documentation.
 *
 * @throws InvalidPage The requested what does not exist.
 * @return package An associative array with type and data to sepcify result of insert.
 */
function handle_post($body)
{
    $body = json_decode($body, true);
    $bind_variables = [];
    if (isset($body['user_id']) && $body['user_id'] != "null") {
        $bind_variables[':user_id'] = $body['user_id'];
    }

    if (
        isset($body['survey_instance_id']) &&
        $body['survey_instance_id'] != "null"
    ) {
        $bind_variables[':survey_instance_id'] = $body['survey_instance_id'];
    }

    switch ($body["what"]) {
        case "post_surveys":
            $survey_package = post_survey_results($body, $bind_variables);
            return $survey_package;
        default:
            throw new Exception("Invalid Request");
    }
}

/**
 * Insert responses into response table.
 *
 * @return array An associative array with type and data to sepcify result of insert.
 */
function post_survey_results($body, $bind_variables)
{
    $mathces = [];
    if (isset($body['question_responses']) && $body['question_responses']) {
        $question_responses = $body['question_responses'];
    }

    $return_val_data = array();
    // Inserting each response
    foreach ($question_responses as $response) {
        $bind_variables[":question_id"] = $response["question_id"];
        $bind_variables[":answer"] = $response["response"];

        array_push(
            $return_val_data,
            get_query_result(gen_query_submit_responses(), $bind_variables)
        );
    }
    return array("TYPE" => "success", "DATA" => $return_val_data);
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
