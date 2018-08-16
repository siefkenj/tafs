<?php
require 'survey_query_generators.php';
require 'query_utils.php';
header("Content-type: application/json");
try {
    $method = "";
    $REQUEST_data = handle_request();
    if (isset($REQUEST_data['REQUEST_METHOD'])) {
        $method = $REQUEST_data['REQUEST_METHOD'];
        verify_user_id($REQUEST_data);
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
function get_query_result($query_string, $bind_variables, $post_select = false)
{
    require 'db_config.php';
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
        if ($_SERVER['REQUEST_METHOD'] == 'GET' || $post_select) {
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
            // Find the survey_instance_id based on the token,
            // and then get the survey package.
            $sql =
                "SELECT survey_instance_id FROM survey_instances WHERE override_token = :override_token";
            $bound = ["override_token" => $parameters["override_token"]];
            $res = do_select_query($sql, $bound);
            if (!$res->result || count($res->result) == 0) {
                throw new Exception(
                    "No survey with override token " .
                        $parameters["override_token"] .
                        " found"
                );
            }
            $conn = $res->conn;
            $survey_instance_id = $res->result[0]["survey_instance_id"];

            $survey_package = get_survey_package(
                null,
                $survey_instance_id,
                $conn,
                true
            );
            $ret = ["TYPE" => "survey_package", "DATA" => $survey_package];
            return $ret;
        case "get_ta":
            $ta_package = get_ta_info($bind_variables);
            return $ta_package;
        case "get_auth_info":
            $user_info_package = get_auth_info($parameters);
            return $user_info_package;
        default:
            throw new Exception("InvalidPage");
    }
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
    $existing_response_bind[":user_id"] = $bind_variables[':user_id'];
    $existing_response_bind[":survey_instance_id"] = $survey_instances[0][
        "survey_instance_id"
    ];
    $existing_response = get_query_result(
        gen_query_existing_response(),
        $existing_response_bind
    );
    $data = array(
        "user_id" => $user_association[0]["user_id"],
        "name" => $user_info[0]["name"],
        "photo" => $user_info[0]["photo"],
        "section" => $section[0]["section_code"],
        "course_code" => $user_association[0]["course_code"],
        "existing_response_id" => isset($existing_response[0])
    );
    $ta_package = array('TYPE' => "ta_package", 'DATA' => $data);
    return $ta_package;
}
/**
 * This function returns shibboleth sign on data. Specifically, it returns the
 * utorid, email and unscoped-affiliation
 *
 * @param parameters GET request $parameters
 * @return array containing shibboleth environment variables
 */
function get_auth_info($parameters)
{
    return array("TYPE" => "auth_info", "DATA" => [$parameters]);
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
    // Delete any existing responses
    $existing_response_bind[":user_id"] = $bind_variables[":user_id"];
    $existing_response_bind[":survey_instance_id"] = $bind_variables[
        ":survey_instance_id"
    ];
    $existing_response = get_query_result(
        gen_query_existing_response(),
        $existing_response_bind,
        true
    );
    foreach ($existing_response as $response_id) {
        $delete_bind[":response_id"] = $response_id["response_id"];
        get_query_result(gen_query_delete_response(), $delete_bind);
    }
    // Insert new responses
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
