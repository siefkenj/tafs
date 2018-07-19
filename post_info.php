<?php
require 'post_query_generators.php';
require 'db/config.php';
// below is the block for receiving POST request from the frontend
try {
    $GLOBALS["conn"] = new PDO(
        "mysql:host=$servername;dbname=$database",
        $username,
        $password
    );
    // Get the operations that the user wants to perform
    $action = $_REQUEST['action'];
    // Check the validation of $action
    if (
        $action != "add_or_update" &&
        $action != "branch" &&
        $action != "delete"
    ) {
        throw new Exception("'action' attribute '$action' not valid!");
    }
    // Decode the body of the request
    $data = file_get_contents('php://input');
    $data = json_decode($data, true);
    // Determine the objects that the user wants to update
    switch ($_REQUEST['what']) {
        case 'surveys':
            // Get the id of the survey
            $survey_id = $_REQUEST['survey_id'];
            // Get the level of the survey setting, which could be "dept", "course", "section"
            $level = $_REQUEST['level'];
            // Check the validation of $level
            if ($level != "dept" && $level != "course" && $level != "section") {
                throw new Exception("'level' attribute '$level' not valid!");
            }
            // Get the utorid of the user who sets this survey
            $user_id = $_REQUEST['user_id'];
            // Call the function handle_survey_setting to deal with different situations
            handle_survey_setting($survey_id, $level, $user_id, $action, $data);

            break;
        case 'user_info':
            // Get the list of user info that the user wants to update
            $user_list = $data["user_list"];
            // Call the function handle_user_info
            handle_user_info($user_list, $action);
            break;
        case 'course_pairings':
            // Determine if the user wants to update the user_associations or the
            // courses/sections of the term
            if ($_REQUEST['mode'] == "user_associations") {
                // Get the user association list from the request body data
                $association_list = $data["association_list"];
                // Call the function handle_user_association
                handle_user_association($association_list, $action);
            } else {
                // If the mode == "courses_sections"
                // Get the user association list from the request body data
                $association_list = $data["association_list"];
                // Call the function handle_courses_sections
                handle_courses_sections($association_list, $action);
            }
            break;
        default:
            throw new Exception("'what' attribute '$what' not valid!");
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
    $return_data = array();
    $return_data["TYPE"] = "error";
    $return_data["data"] = $e->getMessage();
    $return_json = json_encode($return_data);
    echo $return_json;
    exit();
}

/**
 * Function executes sql query and logs any sql error.
 *
 * @param query_string sql query string
 * @param bind_variables an associative array of variables to bind to sql query
 *
 */
function execute_sql($query_string, $bind_variables, $operation)
{
    // Attempt to execute sql command and print response in json format.
    // If an sql error occurs, JSON error object.
    try {
        if ($query_string == null) {
            return null;
        }
        // set the PDO error mode to exception
        $GLOBALS["conn"]->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
        $stmt = $GLOBALS["conn"]->prepare($query_string);
        foreach ($bind_variables as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        // When the $operation variable suggests that this sql is a 'select' statement,
        // We need to return the fetched result from the database
        if ($operation == "select") {
            // fetch all results
            $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $fetched;
        }
        // If it is an insert, update or delete statement, we just return a "success"
        // message
        return "success";
    } catch (PDOException $e) {
        $result = set_http_response(500);
        date_default_timezone_set('America/Toronto');
        error_log(
            date("Y-m-d h:i:sa") . " : " . $e->getMessage() . "\n",
            3,
            "errors.log"
        );
        return $e->getMessage();
    }
}

/* ---------- Encapsulate logic for the operations -------------- */

/**
 * helper function for executing the SQL statement of updating user_info
 * @param user_list:array An array contain a list of user information with
 *                  user_id, name,
 */
function handle_user_info($user_list, $action)
{
    // Initialize a success status array
    $return_data = array();
    for ($i = 0; $i < count($user_list); $i++) {
        $user_id = $user_list[$i]["user_id"];
        $name = $user_list[$i]["name"];
        $photo = $user_list[$i]["photo"];
        $bind_variables = array("user_id" => $user_id);
        if ($action == "add_or_update") {
            $bind_variables["name"] = $name;
            $bind_variables["photo"] = $photo;
        }
        $sql = gen_query_update_user_info($action);
        // Operate the database according to the sql generated
        $status = execute_sql($sql, $bind_variables, null);
        // Determine the status and then add the status object in the returned array
        if ($status == "success") {
            $temp = array('TYPE' => 'success', 'data' => null);
            array_push($return_data, $temp);
        } else {
            $temp = array();
            $temp["TYPE"] = "error";
            $temp["data"] = $status;
            array_push($return_data, $temp);
        }
    }
    // Encapsulate the data into a php object
    $return_json = array();
    $return_json["TYPE"] = "success";
    $return_json["data"] = $return_data;
    echo json_encode($return_json);
    exit();
}

/**
 * helper function for executing the SQL statement of updating profs/tas for one
 * course/section
 * @param association_list:array a list of user associations
 * @param action:string the actions that the user wants to perform
 */
function handle_user_association($association_list, $action)
{
    // Initialize a success status array
    $return_data = array();
    // go through each user association in the list and make seperate SQL calls
    for ($i = 0; $i < count($association_list); $i++) {
        $course_code = $association_list[$i]["course"]["course_code"];
        $section_id = $association_list[$i]["section"]["section_id"];
        $user_id = $association_list[$i]["user_id"];
        $bind_variables = array(
            "course_code" => $course_code,
            "section_id" => $section_id,
            "user_id" => $user_id
        );
        // generate the SQL statement using the information provided
        $sql = gen_query_update_user_association($action);
        $status = execute_sql($sql, $bind_variables, null);
        // Determine the status and then add the status object in the returned array
        if ($status == "success") {
            $temp = array('TYPE' => 'success', 'data' => null);
            array_push($return_data, $temp);
        } else {
            $temp = array("TYPE" => "error", "data" => $status);
            array_push($return_data, $temp);
        }
    }
    // Encapsulate the data into a php object
    $return_json = array();
    $return_json["TYPE"] = "success";
    $return_json["data"] = $return_data;
    echo json_encode($return_json);
    exit();
}

/**
 * helper function for executing the SQL statement of updating courses/sections
 * for one specific term
 * @param association_list:array a list of user associations
 * @param action:string the actions that the user wants to perform
 */
function handle_courses_sections($association_list, $action)
{
    // Initialize a success status array
    $return_data = array();
    // go through each user association in the list and make seperate SQL calls
    for ($i = 0; $i < count($association_list); $i++) {
        $course = $association_list[$i]["course"];
        $section = $association_list[$i]["section"];
        $user_id = $association_list[$i]["user_id"];
        $course_code = $course["course_code"];
        $course_title = $course["title"];
        $department_name = $course["department_name"];
        $term = $course["term"];
        // Initialize $bind_variables
        $bind_variables = array();
        if ($action == "delete") {
            if ($section != null) {
                $bind_variables["section_id"] = $section["section_id"];
            } else {
                $bind_variables["course_code"] = $course_code;
            }
        } elseif ($action == "add_or_update") {
            $bind_variables["course_code"] = $course_code;
            $bind_variables["course_title"] = $course_title;
            $bind_variables["department_name"] = $department_name;
            $bind_variables["section_code"] = $section["section_code"];
            $bind_variables["term"] = $term;
            if ($section["section_id"] != null) {
                $bind_variables["section_id"] = $section["section_id"];
            }
        }
        // generate the SQL statement using the information provided
        $sql = gen_query_update_courses_sections($action, $section);
        $status = execute_sql($sql, $bind_variables, null);
        // Determine the status and then add the status object in the returned array
        if ($status == "success") {
            $temp = array('TYPE' => 'success', 'data' => null);
            array_push($return_data, $temp);
        } else {
            $temp = array();
            $temp["TYPE"] = "error";
            $temp["data"] = $status;
            array_push($return_data, $temp);
        }
    }
    // Encapsulate the data into a php object
    $return_json = array();
    $return_json["TYPE"] = "success";
    $return_json["data"] = $return_data;
    echo json_encode($return_json);
    exit();
}

/**
 * helper function for executing the SQL statement of updating survey settings
 * @param survey_id:int The id of the survey
 * @param level:string dept, course, section
 * @param user_id:string The utorid of the user
 * @param action:string add_or_update, delete, branch
 * @param data:object the body of the request
 */
function handle_survey_setting($survey_id, $level, $user_id, $action, $data)
{
    // Initialize the return data
    $return_data = array("TYPE" => "success", "data" => null);
    // declare the variable "department_name", "course_code", "section_id"
    $department_name = null;
    $course_code = null;
    $section_id = null;
    // If the user wants to update a survey
    if ($action == "add_or_update") {
        // call the function handle_survey_update
        handle_survey_update($survey_id, $level, $action, $return_data, $data);
    } elseif ($action == "branch") {
        // If the user wants to branch a new survey, call the function handle_survey_branching
        handle_survey_branching(
            $survey_id,
            $level,
            $user_id,
            $action,
            $data,
            $return_data,
            $course_code,
            $department_name,
            $section_id
        );
    } else {
        // Call the function handle_survey_delete
        handle_survey_delete(
            $survey_id,
            $level,
            $user_id,
            $action,
            $return_data
        );
    }
}

/**
 * function for handling survey update
 * @param survey_id:number Id of the survey
 * @param level:string dept, course, section
 * @param action:string add_or_update, branch, delete
 * @param data:object The data package from user
 * @param return_data:array
 */
function handle_survey_update($survey_id, $level, $action, $return_data, $data)
{
    $sql_array = gen_query_update_survey($level, $action);
    $sql_update_survey = $sql_array[0];
    // 1. Update the settings in the existing surveys
    $bind_variables = array();
    $bind_variables["name"] = $data["name"];
    $bind_variables["term"] = $data["term"];
    $bind_variables["default_survey_open"] = $data["default_survey_open"];
    $bind_variables["default_survey_close"] = $data["default_survey_close"];
    $bind_variables["survey_id"] = $survey_id;
    $status = execute_sql($sql_array[0], $bind_variables, null);
    if ($status != "success" && $status != null) {
        $return_data["TYPE"] = "error";
        $return_data["data"] = $status;
        echo json_encode($return_data);
        exit();
    }
    // 2. Get the choices_id back from the database by executing the SQL
    $bind_variables = array();
    $bind_variables["survey_id"] = $survey_id;
    $survey_choice_id = execute_sql($sql_array[1], $bind_variables, "select");
    // 3. Get the choice_id
    $bind_variables = array();
    if ($level == "dept") {
        $bind_variables["dept_survey_choice_id"] = $survey_choice_id[0][
            "dept_survey_choice_id"
        ];
    } elseif ($level == "course") {
        $bind_variables["course_survey_choice_id"] = $survey_choice_id[0][
            "course_survey_choice_id"
        ];
    } else {
        $bind_variables["ta_survey_choice_id"] = $survey_choice_id[0][
            "ta_survey_choice_id"
        ];
    }
    $sql_get_choice_id = gen_query_get_choices_id($level);
    $choices_id = null;
    if ($level == "dept") {
        $choices_id = execute_sql($sql_get_choice_id[0], $bind_variables, "select");
    } elseif ($level == "course") {
        $choices_id = execute_sql($sql_get_choice_id[1], $bind_variables, "select");
    } else {
        $choices_id = execute_sql($sql_get_choice_id[2], $bind_variables, "select");
    }
    // 4. Update the choice instance according to the user's preferrence
    $bind_variables = array();
    $bind_variables["choices_id"] = (int) $choices_id[0]["choices_id"];
    $choice_array = null;
    if ($level == "dept") {
        $choice_array = $data["dept_survey_choices"]["choices"];
    } elseif ($level == "course") {
        $choice_array = $data["course_survey_choices"]["choices"];
    } elseif ($level == "section") {
        $choice_array = $data["ta_survey_choices"]["choices"];
    }
    // Use a loop to bind variables for choice 1 to 6
    for ($i = 0; $i < 6; $i++) {
        $choice_number = $i + 1;
        $choice_name = "choice" . $choice_number;
        $bind_variables[$choice_name] = (int) $choice_array[$i];
    }
    $sql_query_update_choice = gen_query_update_choice();
    $status = execute_sql($sql_query_update_choice, $bind_variables, null);
    if ($status != "success" && $status != null) {
        $return_data["TYPE"] = "error";
        $return_data["data"] = $status;
        echo json_encode($return_data);
        exit();
    } else {
        echo json_encode($return_data);
        exit();
    }
}

/**
 * handle survey branching
 * @param survey_id:int The id of the survey
 * @param level:string dept, course, section
 * @param user_id:string The id of the user
 * @param action:string add_or_update, branch, delete
 * @param data:object The object sent from the front end
 * @param return_data:array
 * @param course_code:string The code of the course
 * @param department_name:string The name of the department
 * @param section_id:number The id of the section
 */
function handle_survey_branching(
    $survey_id,
    $level,
    $user_id,
    $action,
    $data,
    $return_data,
    $course_code,
    $department_name,
    $section_id
) {
    // If the user wants to branch a new survey
    /* 1. Get the information of the original survey on a survey level */
    $sql = gen_query_update_survey($level, $action);
    // Create a nested associative array to store in the information of the original array
    $original_survey = array();
    $bind_variables = array("survey_id" => $survey_id);
    $info_array = execute_sql($sql, $bind_variables, "select");
    $original_survey_info_array = $info_array[0];

    /* 2. Get the id of the choices of the survey_choice that we want to branch from */
    $temp_array = get_choices_id(
        $level,
        $original_survey_info_array,
        $department_name,
        $course_code,
        $section_id
    );
    $original_choices_id_array = $temp_array["original_choices_id_array"];
    $department_name = $temp_array["department_name"];
    $course_code = $temp_array["course_code"];
    $section_id = $temp_array["section_id"];

    /* 3. Get the actual choices of the choice instance in the survey that we want
     to branch from */
    $sql_original_choices = gen_query_get_choices(
        $original_survey_info_array["dept_survey_choice_id"],
        $original_survey_info_array["course_survey_choice_id"],
        $original_survey_info_array["ta_survey_choice_id"]
    );
    // "choices_array" will store all the choices of the survey that we want to branch from
    $choices_array = array();
    // Initialize $bind_variables again
    $bind_variables = array();
    if ($original_choices_id_array[0]) {
        $bind_variables["choice_id_dept"] = $original_choices_id_array[0];
    }
    if ($original_choices_id_array[1]) {
        $bind_variables["choice_id_course"] = $original_choices_id_array[1];
    }
    if ($original_choices_id_array[2]) {
        $bind_variables["choice_id_section"] = $original_choices_id_array[2];
    }
    // Use a loop to go through the SQL statement iteratively
    for ($i = 0; $i < count($sql_original_choices); $i++) {
        $choices = execute_sql(
            $sql_original_choices[$i],
            $bind_variables,
            "select"
        );
        if ($choices) {
            array_push($choices_array, $choices[0]);
        } else {
            array_push($choices_array, null);
        }
    }

    /* 4. Create new choices instance */
    $temp_array = set_new_choices($choices_array);
    $new_choices_id_array = $temp_array["new_choices_id_array"];
    $sql_get_new_choices = $temp_array["sql_get_new_choices"];

    /* 5. Set up the new survey_choices instance */
    $new_survey_choices_id_array = set_new_survey_choices(
        $choices_array,
        $new_choices_id_array,
        $department_name,
        $course_code,
        $section_id,
        $user_id,
        $sql_get_new_choices
    );

    /* 6. Last step, set a new survey */
    $sql_set_new_survey = gen_query_set_new_survey();
    $bind_variables = array();
    $bind_variables["dept_survey_choice_id"] = $new_survey_choices_id_array[0];
    $bind_variables[
        "course_survey_choice_id"
    ] = $new_survey_choices_id_array[1];
    $bind_variables["ta_survey_choice_id"] = $new_survey_choices_id_array[2];
    $bind_variables["name"] = $data["name"];
    $bind_variables["term"] = $data["term"];
    $bind_variables["default_survey_open"] = $original_survey_info_array[
        "default_survey_open"
    ];
    $bind_variables["default_survey_close"] = $original_survey_info_array[
        "default_survey_close"
    ];
    $status = execute_sql($sql_set_new_survey, $bind_variables, null);
    if ($status != "success" && $status != null) {
        $return_data["TYPE"] = "error";
        $return_data["data"] = $status;
        echo json_encode($return_data);
        exit();
    } else {
        echo json_encode($return_data);
        exit();
    }
}

/**
 *  return the according department_name,course_code, section_id
 *  and the original_choices_id_array
 * @param level:string "dept", "course", "section"
 * @param original_survey_info_array:array An associative array that includes
 *        the information of the original survey
 * @return array associative array that includes all the necessary information for branching
 */
function get_choices_id(
    $level,
    $original_survey_info_array,
    $department_name,
    $course_code,
    $section_id
) {
    $sql_choice_id = gen_query_get_choices_id($level);
    $original_choices_id_array = array();
    $survey_choice_id_label = array(
        0 => "dept_survey_choice_id",
        1 => "course_survey_choice_id",
        2 => "ta_survey_choice_id"
    );
    for ($i = 0; $i < count($sql_choice_id); $i++) {
        $bind_variables = array();
        if ($original_survey_info_array[$survey_choice_id_label[$i]]) {
            $bind_variables[
                $survey_choice_id_label[$i]
            ] = $original_survey_info_array[$survey_choice_id_label[$i]];
        }

        $id = execute_sql($sql_choice_id[$i], $bind_variables, "select");
        if ($id) {
            array_push($original_choices_id_array, (int) $id[0]["choices_id"]);
            // assign value to one of department_name, course_code, section_id
            if ($i == 0) {
                $department_name = $id[0]["department_name"];
            } elseif ($i == 1) {
                $course_code = $id[1]["course_code"];
            } else {
                $section_id = (int) $id[2]["section_id"];
            }
        } else {
            array_push($original_choices_id_array, null);
        }
    }
    return array(
        "original_choices_id_array" => $original_choices_id_array,
        "department_name" => $department_name,
        "course_code" => $course_code,
        "section_id" => $section_id
    );
}

/**
 * return an array containing new choices id
 * @param choices_array:array Array containing choices
 * @return array Array containing new choice id
 */
function set_new_choices($choices_array)
{
    $sql_set_new_choices = gen_query_create_new_choices(
        $choices_array[0],
        $choices_array[1],
        $choices_array[2]
    );
    $new_choices_id_array = array();

    $sql_get_new_choices = gen_query_get_new_choices_id(
        $choices_array[0],
        $choices_array[1],
        $choices_array[2]
    );
    $bind_variables1 = array();

    $choice_label = array(0 => "dept", 1 => "course", 2 => "section");

    for ($i = 0; $i < count($sql_set_new_choices); $i++) {
        $bind_variables = array();
        // If it is the first iteration and dept_survey_choices are not null,
        // bind according variables

        if ($choices_array[$i]) {
            /*
             * Use a loop to bind variables for department choice
             *
             * For example
             * $bind_variables = [
             *    "dept_choice1" => 5,
             *    "dept_choice2" => 1,
             *    ...,
             *    "dept_chioce6" => 9
             * ]
             */

            for ($j = 1; $j <= 6; $j++) {
                $choice_name = $choice_label[$i] . "_choice" . $j;
                $choice_number = "choice$j";
                $bind_variables[$choice_name] = intval($choices_array["$i"][
                    $choice_number
                ]);
            }
        }

        $status = execute_sql($sql_set_new_choices[$i], $bind_variables, null);
        if ($status && $status != "success") {
            $return_data["TYPE"] = "error";
            $return_data["data"] = $status;
            echo json_encode($return_data);
            exit();
        }
        $id = execute_sql($sql_get_new_choices[$i], $bind_variables1, "select");
        array_push($new_choices_id_array, $id[0]["LAST_INSERT_ID()"]);
    }
    return array(
        "new_choices_id_array" => $new_choices_id_array,
        "sql_get_new_choices" => $sql_get_new_choices
    );
}

/**
 * set new survey choices and get the id back
 * @param choices_array:array
 * @return array The array containing the new survey choice id
 */
function set_new_survey_choices(
    $choices_array,
    $new_choices_id_array,
    $department_name,
    $course_code,
    $section_id,
    $user_id,
    $sql_get_new_choices
) {
    $sql_set_new_survey_choice = gen_query_set_survey_choice(
        $choices_array[0],
        $choices_array[1],
        $choices_array[2]
    );
    $new_survey_choices_id_array = array();
    // Use a for loop to go through and execute the according SQL statements
    // in $sql_set_new_survey_choice
    for ($i = 0; $i < count($sql_set_new_survey_choice); $i++) {
        $bind_variables = array();
        $choice_id_label = array(
            0 => "dept_choices_id",
            1 => "course_choices_id",
            2 => "ta_choices_id"
        );
        $level_label = array(
            0 => "department_name",
            1 => "course_code",
            2 => "section_id"
        );
        $level_variable = array(
            0 => $department_name,
            1 => $course_code,
            2 => $section_id
        );
        // If it is the according choices are not null,
        // bind according variables
        if ($choices_array[$i]) {
            $bind_variables[$choice_id_label[$i]] = $new_choices_id_array[$i];
            $bind_variables[$level_label[$i]] = $level_variable[$i];
            $bind_variables["user_id"] = $user_id;
        }

        $status = execute_sql(
            $sql_set_new_survey_choice[$i],
            $bind_variables,
            null
        );
        // If the status is not "success" or not null, directly return an error
        if ($status != "success" && $status != null) {
            $return_data["TYPE"] = "error";
            $return_data["data"] = $status;
            echo json_encode($return_data);
            exit();
        }
        $bind_variables1 = array();
        $id = execute_sql($sql_get_new_choices[$i], $bind_variables1, "select");
        array_push($new_survey_choices_id_array, $id[0]["LAST_INSERT_ID()"]);
    }
    return $new_survey_choices_id_array;
}

/**
 * @param survey_id:number The id of the survey
 * @param level:string dept, course, section
 * @param user_id:string The id of the user
 * @param action: add_or_update, branch, delete
 * @param return_data
 */
function handle_survey_delete(
    $survey_id,
    $level,
    $user_id,
    $action,
    $return_data
) {
    // If the user wants to delete the survey
    $bind_variables = array("survey_id" => $survey_id);
    $sql_survey_deletion = gen_query_update_survey($level, $action);
    $sql_get_survey_instance_id = $sql_survey_deletion[0];
    $sql_delete_response = $sql_survey_deletion[1];
    $sql_delete_survey_instance = $sql_survey_deletion[2];
    $sql_delete_survey = $sql_survey_deletion[3];
    $survey_instance_array = execute_sql(
        $sql_get_survey_instance_id,
        $bind_variables,
        "select"
    );
    // Use a loop to go through every survey_instance_id,
    // for each survey_instance_id, remove the according response

    for ($i = 0; $i < count($survey_instance_array); $i++) {
        $survey_instance_id = $survey_instance_array[$i]["survey_instance_id"];
        $bind_variables = array(
            "survey_instance_id" => (int) $survey_instance_id
        );
        $status = execute_sql($sql_delete_response, $bind_variables, null);
        if ($status != "success" && $status != null) {
            $return_data["TYPE"] = "error";
            $return_data["data"] = $status;
            echo json_encode($return_data);
            exit();
        }
    }
    // delete the survey_instances related to this survey
    $bind_variables = array("survey_id" => $survey_id);
    $status = execute_sql($sql_delete_survey_instance, $bind_variables, null);
    if ($status != "success" && $status != null) {
        $return_data["TYPE"] = "error";
        $return_data["data"] = $status;
        echo json_encode($return_data);
        exit();
    }
    // delete the survey
    $status = execute_sql($sql_delete_survey, $bind_variables, null);
    // If the status is not "success" or not null, directly return an error
    if ($status != "success" && $status != null) {
        $return_data["TYPE"] = "error";
        $return_data["data"] = $status;
        echo json_encode($return_data);
        exit();
    } else {
        echo json_encode($return_data);
        exit();
    }
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

    return array('CODE' => $num, 'ERROR' => $http[$num]);
}
?>
