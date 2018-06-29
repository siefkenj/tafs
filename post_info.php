<?php
require 'post_query_generators.php';
// header("Content-type: application/json");
// below is the block for receiving POST request from the frontend
try {
    // Get the operations that the user wants to perform
    $action = $_REQUEST['action'];
    // Decode the body of the request
    $data = json_decode(file_get_contents('php://input'));
    // echo $data_process["user_list"];
    // Determine the objects that the user wants to update
    switch ($_REQUEST['what']) {
        case 'surveys':
            // Get the id of the survey
            $survey_id = $_REQUEST['survey_id'];
            break;
        case 'user_info':
            // Get the list of user info that the user wants to update
            $user_list = $data->user_list;
            // Call the function handle_user_info
            handle_user_info($user_list);
            // $sql = "insert into users (user_id, name, photo) values ('admin2', 'admin2', null);";
            break;
        case 'course_pairings':
            // Determine if the user wants to update the user_associations or the
            // courses/sections of the term
            if ($_REQUEST['mode'] == "user_associations") {
                // Get the user association list from the request body data
                $association_list = $data->association_list;
                // Call the function handle_user_association
                handle_user_association($association_list, $action);
            } elseif ($_REQUEST['mode'] == "courses_sections") {
                // Get the user association list from the request body data
                $association_list = $data->association_list;
                // Call the function handle_courses_sections
                handle_courses_sections($association_list, $action);
            } else {

            }
            break;
        default:

            break;
    }
    // handling the exception
} catch (Exception $e) {
    $result = set_http_response(400);
    date_default_timezone_set('America/Toronto');
    error_log(
        date("Y-m-d h:i:sa") . " : " . $e->getMessage() . "\n",
        3,
        "errors.log"
    );
    $return_data->TYPE = "error";
    $return_data->data = $e->getMessage();
    $return_json = json_encode($return_data);
    echo $return_json;
    exit();
}

// below is the function for updating the information in the SQL database

/**
 * This function is for operating the database accrording to the SQL statement
 */
function execute_sql ($sql) {
    require './db/config.php';

    try {
        // connect to the mysql database
        $conn = new PDO(
            "mysql:host=$servername;dbname=$database",
            $username,
            $password
        );
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // use database 'tafs'
        $conn->exec("use tafs;");
        // Execute the sql statement
        $conn->exec($sql);

        // Close the connection
        $conn = null;

        // return a 'success' status
        return "success!";
    } catch (PDOException $e) {
        // $result = set_http_response(500);
        // date_default_timezone_set('America/Toronto');
        // error_log(
        //     date("Y-m-d h:i:sa") . " : " . $e->getMessage() . "\n",
        //     3,
        //     "errors.log"
        // );
        // print json_encode($result, JSON_PRETTY_PRINT);
        return $e->getMessage();
        exit();
    }
}

/* ---------- Encapsulate logic for the operations -------------- */

/**
 * helper function for executing the SQL statement of updating user_info
 * @param user_list:array An array contain a list of user information with
 *                  user_id, name,
 */
function handle_user_info ($user_list) {
    // Initialize a success status array
    $return_data = Array();
    // print_r($data);
    //print_r($data["user_list"]);
    //var_dump($data_process[0]["user_list"]);
    for ($i = 0; $i < count($user_list); $i++) {
        $user_id = $user_list[$i]->user_id;
        $name = $user_list[$i]->name;
        $photo = $user_list[$i]->photo;
        $sql = query_update_user_info($action, $user_id, $name, $photo);
        // Operate the database according to the sql generated
        $status = execute_sql($sql);
        // Determine the status and then add the status object in the returned array
        if ($status == "success!") {
            $temp = array('TYPE' => 'success', 'data' => null);
            //$temp->TYPE = 'success';
            //$temp->data = null;
            array_push($return_data, $temp);
        } else {
            $temp = new \stdClass();
            $temp->TYPE = "error";
            $temp->data = $status;
            array_push($return_data, $temp);
        }
    }
    // Encapsulate the data into a php object
    $return_json = new \stdClass();
    $return_json->TYPE = "success";
    $return_json->data = $return_data;
    echo json_encode($return_json);
}

/**
 * helper function for executing the SQL statement of updating profs/tas for one
 * course/section
 * @param association_list:array a list of user associations
 * @param action:string the actions that the user wants to perform
 */
function handle_user_association (
    $association_list,
    $action
) {
    // Initialize a success status array
    $return_data = Array();
    // go through each user association in the list and make seperate SQL calls
    for ($i = 0; $i < count($association_list); $i++) {
        $course_code = $association_list[$i]->course->course_code;
        $section_id = $association_list[$i]->section->section_id;
        $user_id = $association_list[$i]->user_id;
        // generate the SQL statement using the information provided
        $sql = query_update_user_association($action, $course_code, $section_id, $user_id);
        echo $sql . "\n";
        $status = execute_sql($sql);
        // Determine the status and then add the status object in the returned array
        if ($status == "success!") {
            $temp = array('TYPE' => 'success', 'data' => null);
            //$temp->TYPE = 'success';
            //$temp->data = null;
            array_push($return_data, $temp);
        } else {
            $temp = new \stdClass();
            $temp->TYPE = "error";
            $temp->data = $status;
            array_push($return_data, $temp);
        }
    }
    // Encapsulate the data into a php object
    $return_json = new \stdClass();
    $return_json->TYPE = "success";
    $return_json->data = $return_data;
    echo json_encode($return_json);

}

/**
 * helper function for executing the SQL statement of updating courses/sections
 * for one specific term
 * @param association_list:array a list of user associations
 * @param action:string the actions that the user wants to perform
 */
function handle_courses_sections (
    $association_list,
    $action
) {
    echo "goes here!\n";
    // Initialize a success status array
    $return_data = Array();
    // go through each user association in the list and make seperate SQL calls
    for ($i = 0; $i < count($association_list); $i++) {
        $course = $association_list[$i]->course;
        $section = $association_list[$i]->section;
        $user_id = $association_list[$i]->user_id;
        // generate the SQL statement using the information provided
        $sql = query_update_courses_sections($action, $course, $section, $user_id);
        echo $sql . "\n";
        $status = execute_sql($sql);
        // Determine the status and then add the status object in the returned array
        if ($status == "success!") {
            $temp = array('TYPE' => 'success', 'data' => null);
            array_push($return_data, $temp);
        } else {
            $temp = new \stdClass();
            $temp->TYPE = "error";
            $temp->data = $status;
            array_push($return_data, $temp);
        }
    }
    // Encapsulate the data into a php object
    $return_json = new \stdClass();
    $return_json->TYPE = "success";
    $return_json->data = $return_data;
    echo json_encode($return_json);
}

/**
 * helper function for executing the SQL statement of updating survey settings
 */
function handle_survey_setting () {

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

    // header($http[$num]);

    return array('CODE' => $num, 'ERROR' => $http[$num]);
}
?>
