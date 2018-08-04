<?php
require 'post_query_generators.php';
require 'utils.php';
require '../db/config.php';
header("Content-type: application/json");

// keep track of relavent operations to be printed if we're in debug mode.
if (!isset($GLOBALS['DEBUG_INFO'])) {
    $GLOBALS['DEBUG_INFO'] = ["executed_sql" => []];
}

// below is the block for receiving POST request from the frontend
try {
    $GLOBALS["conn"] = new PDO(
        "mysql:host=$servername;dbname=$database",
        $username,
        $password
    );

    $params = handle_request();
    $params = santitize_arguments($params);

    // store input as debug informtion
    $GLOBALS['DEBUG_INFO']["params"] = $params;

    // Decode the JSON body of the request
    $data = json_decode($params['post_body'], true);
    // The first dicision point of the API is the `what` parameter
    switch ($params['what']) {
        case 'surveys':
            // If the user wants to set the `viewable_by_others` state,
            // the desiered state will be included in $params
            if (isset($params["viewable_by_others"])) {
                handle_viewable_by_others(
                    $params['survey_id'],
                    as_bool($params['viewable_by_others'])
                );
            }
            handle_survey_setting(
                $params["survey_id"],
                $params["level"],
                $params["user_id"],
                $params["action"],
                $data
            );

            break;
        case 'user_info':
            handle_user_info($data["user_list"], $params["action"]);
            break;
        case 'course_pairings':
            // Determine if the user wants to update the user_associations or the
            // courses/sections of the term
            switch ($params["mode"]) {
                case "user_associations":
                    handle_user_association(
                        $data["association_list"],
                        $params["action"]
                    );
                    break;
                case "courses_sections":
                    handle_courses_sections(
                        $data["association_list"],
                        $params["action"]
                    );
                    break;
                default:
                    throw new Exception(
                        "'mode' '" . $params["mode"] . "' not valid."
                    );
                    break;
            }
        default:
            throw new Exception("'what' attribute '$what' not valid!");
            break;
    }
    // handling the exception
} catch (Exception $e) {
    do_error(400, $e);
    exit();
}

/**
 * Process `$args` for obvious deviations from the Post API
 */
function santitize_arguments(
    $args,
    $ensure = ["what", "mode", "action", "level"]
) {
    $POSSIBLE_ARGS = [
        "what" => ["surveys", "user_info", "course_pairings", "launch_survey"],
        "mode" => ["user_associations", "courses_sections"],
        "level" => ["dept", "course", "section"],
        "action" => ["add_or_update", "branch", "delete"]
    ];
    // verify each arg in possible args conforms to the API
    foreach ($POSSIBLE_ARGS as $key => $value) {
        if (isset($args[$key]) && !in_array($args[$key], $value)) {
            throw new Exception(
                "'$key' must be one of ['" .
                    implode("', '", $value) .
                    "'], not '" .
                    $args[$key] .
                    "'"
            );
        }
    }
    // make sure there is somethign set for each `$ensure` argument
    foreach ($ensure as $key) {
        if (!isset($args[$key])) {
            $args[$key] = null;
        }
    }
    return $args;
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
    $GLOBALS['DEBUG_INFO']["executed_sql"][] = [
        "query" => $query_string,
        "bindings" => $bind_variables
    ];

    // Attempt to execute sql command and print response in json format.
    // If an sql error occurs, JSON error object.
    if ($query_string == null) {
        return null;
    }
    // set the PDO error mode to exception
    $GLOBALS["conn"]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $GLOBALS["conn"]->prepare($query_string);
    foreach ($bind_variables as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    // When the $operation variable suggests that this sql is a 'select' statement,
    // We need to return the fetched result from the database
    if ($operation == "select") {
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $fetched;
    }
    // If it is an insert, update or delete statement, we just return a "success"
    // message
    return "success";
}

/* ---------- Encapsulate logic for the operations -------------- */

/**
 * helper function for executing the SQL statement of updating user_info
 * @param user_list:array An array contain a list of user information with
 *                  user_id, name,
 */
function handle_user_info($user_list, $action)
{
    try {
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
                $temp = array("TYPE" => "success", "DATA" => null);
                array_push($return_data, $temp);
            } else {
                $temp = array();
                $temp["TYPE"] = "error";
                $temp["DATA"] = $status;
                array_push($return_data, $temp);
            }
        }
        // Encapsulate the data into a php object
        $return_json = array();
        $return_json["TYPE"] = "success";
        $return_json["DATA"] = $return_data;
        do_result($return_json);
        exit();
    } catch (Exception $e) {
        do_error(500, $e);
        exit();
    }
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
            $temp = array("TYPE" => "success", "DATA" => null);
            array_push($return_data, $temp);
        } else {
            $temp = array("TYPE" => "error", "DATA" => $status);
            array_push($return_data, $temp);
        }
    }
    // Encapsulate the data into a php object
    $return_json = array();
    $return_json["TYPE"] = "success";
    $return_json["DATA"] = $return_data;
    do_result($return_json);
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
            $bind_variables[0] = array(
                "course_code" => $course_code,
                "course_title" => $course_title,
                "department_name" => $department_name
            );
            $bind_variables[1] = array(
                "course_code" => $course_code,
                "term" => $term,
                "section_code" => $section["section_code"]
            );
            if ($section["section_id"] != null) {
                $bind_variables[1]["section_id"] = $section["section_id"];
            }
            $bind_variables[2] = null;
        }
        // generate the SQL statement using the information provided
        $sql_update_courses_sections_array = gen_query_update_courses_sections(
            $action,
            $section
        );
        // Use a variable to mark the success status
        $var_success_or_not = true;
        for ($i = 0; $i < count($sql_update_courses_sections_array); $i++) {
            $status = null;
            if ($action == "add_or_update") {
                $status = execute_sql(
                    $sql_update_courses_sections_array[$i],
                    $bind_variables[$i],
                    null
                );
            } else {
                $status = execute_sql(
                    $sql_update_courses_sections_array[$i],
                    $bind_variables,
                    null
                );
            }
            if ($status && $status != "success") {
                $var_success_or_not = false;
            }
        }
        // Determine the status and then add the status object in the returned array
        if ($var_success_or_not == true) {
            $temp = array("TYPE" => "success", "DATA" => null);
            array_push($return_data, $temp);
        } else {
            $temp = array();
            $temp["TYPE"] = "error";
            $temp["DATA"] = $status;
            array_push($return_data, $temp);
        }
    }
    // Encapsulate the data into a php object
    $return_json = array();
    $return_json["TYPE"] = "success";
    $return_json["DATA"] = $return_data;
    do_result($return_json);
    exit();
}

/**
 * update the setting of "viewable_by_others" in the "survey_instances" table
 * @param survey_id:int The id of the survey
 * @param viewable_by_others:bool true or false
 */
function handle_viewable_by_others($survey_id, $viewable_by_others)
{
    $sql_set =
        "UPDATE survey_instances SET viewable_by_others = :viewable_by_others WHERE survey_id = :survey_id;";
    $bind_variables = array(
        "survey_id" => (int) $survey_id,
        "viewable_by_others" => (int) $viewable_by_others
    );
    $status = execute_sql($sql_set, $bind_variables, null);
    // if the sql statement is not successfully executed, return the error information in JSON format to users
    if ($status != "success") {
        do_result(array("TYPE" => "error", "DATA" => $status));
        exit();
    }
    // Get the "viewable_by_others" value back from the database and send it back to the user
    $sql_get =
        "SELECT viewable_by_others FROM survey_instances WHERE survey_id = :survey_id;";
    $bind_variables = array("survey_id" => (int) $survey_id);
    $fetched_data = execute_sql($sql_get, $bind_variables, "select");
    $fetched_viewable_by_others = boolval(
        $fetched_data[0]["viewable_by_others"]
    );
    // Construct the return data
    $return_data_array = array();
    array_push($return_data_array, array(
        "viewable_by_others" => $fetched_viewable_by_others,
        "survey_id" => $survey_id
    ));
    do_result(array("TYPE" => "success", "DATA" => $return_data_array));
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
    $return_data = array("TYPE" => "success", "DATA" => null);
    // declare the variable "department_name", "course_code", "section_id"
    $department_name = null;
    $course_code = null;
    $section_id = null;
    // If the user wants to update a survey
    switch ($action) {
        case 'add_or_update':
            // Use preprocessing to decide what to do, to be more precise:
            //     a. If the survey_choice at the level of the user is null,
            //        we branch first and then update
            //     b. If the survey_choice at the level of the user is not null,
            //        we directly update this survey
            $decision = determine_action_on_add_or_update($survey_id, $level);
            if ($decision == "branch_and_update") {
                $survey_id = handle_survey_branching(
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
            }
            // call the function handle_survey_update
            handle_survey_update(
                $survey_id,
                $level,
                $action,
                $return_data,
                $data,
                $user_id
            );
            break;

        case 'branch':
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
            do_result($return_data);
            exit();
            break;

        default:
            // Call the function handle_survey_delete
            handle_survey_delete(
                $survey_id,
                $level,
                $user_id,
                $action,
                $return_data
            );
            break;
    }
}

function determine_action_on_add_or_update($survey_id, $level)
{
    $survey_choice = null;
    $bind_variables = array("survey_id" => $survey_id);
    $sql = null;
    switch ($level) {
        case "dept":
            $sql =
                "SELECT dept_survey_choice_id FROM surveys WHERE survey_id = :survey_id";
            $return_data = execute_sql($sql, $bind_variables, "select");
            $survey_choice = $return_data[0]["dept_survey_choice_id"];
            break;
        case "course":
            $sql =
                "SELECT course_survey_choice_id FROM surveys WHERE survey_id = :survey_id";
            $return_data = execute_sql($sql, $bind_variables, "select");
            $survey_choice = $return_data[0]["course_survey_choice_id"];
            break;
        default:
            $sql =
                "SELECT ta_survey_choice_id FROM surveys WHERE survey_id = :survey_id";
            $return_data = execute_sql($sql, $bind_variables, "select");
            $survey_choice = $return_data[0]["ta_survey_choice_id"];
            break;
    }
    if ($survey_choice) {
        return "only_update";
    } else {
        return "branch_and_update";
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
function handle_survey_update(
    $survey_id,
    $level,
    $action,
    $return_data,
    $data,
    $user_id
) {
    /* 1. Get the information of the original survey on a survey level */
    $sql = gen_query_update_survey($level, "branch");
    // Create a nested associative array to store in the information of the original array
    $original_survey = array();
    $bind_variables = array("survey_id" => $survey_id);
    $info_array = execute_sql($sql, $bind_variables, "select");
    $original_survey_info_array = $info_array[0];

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
        $return_data["DATA"] = $status;
        do_result($return_data);
        exit();
    }
    // 2. Get the choices_id back from the database by executing the SQL
    $bind_variables = array();
    $bind_variables["survey_id"] = $survey_id;
    $survey_choice_id = execute_sql($sql_array[1], $bind_variables, "select");
    // 3. Get the choice_id
    $bind_variables = array();
    if ($level == "dept") {
        if (!$survey_choice_id[0]["dept_survey_choice_id"]) {
            add_new_survey_choice($survey_id, $level, $data, $user_id);
        }
        $bind_variables["dept_survey_choice_id"] = $survey_choice_id[0][
            "dept_survey_choice_id"
        ];
    } elseif ($level == "course") {
        if (!$survey_choice_id[0]["course_survey_choice_id"]) {
            add_new_survey_choice($survey_id, $level, $data, $user_id);
        }
        $bind_variables["course_survey_choice_id"] = $survey_choice_id[0][
            "course_survey_choice_id"
        ];
    } else {
        if (!$survey_choice_id[0]["ta_survey_choice_id"]) {
            add_new_survey_choice($survey_id, $level, $data, $user_id);
        }
        $bind_variables["ta_survey_choice_id"] = $survey_choice_id[0][
            "ta_survey_choice_id"
        ];
    }
    $sql_get_choice_id = gen_query_get_choices_id(
        $level,
        $original_survey_info_array
    );
    $choices_id = null;
    if ($level == "dept") {
        $choices_id = execute_sql(
            $sql_get_choice_id[0],
            $bind_variables,
            "select"
        );
    } elseif ($level == "course") {
        $choices_id = execute_sql(
            $sql_get_choice_id[1],
            $bind_variables,
            "select"
        );
    } else {
        $choices_id = execute_sql(
            $sql_get_choice_id[2],
            $bind_variables,
            "select"
        );
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
        $return_data["DATA"] = $status;
        do_result($return_data);
        exit();
    } else {
        do_result($return_data);
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
    $sql = gen_query_update_survey($level, "branch");
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
    $choice_label_array = array(
        0 => "choice_id_dept",
        1 => "choice_id_course",
        2 => "choice_id_section"
    );
    // Use a loop to go through the SQL statement iteratively
    for ($i = 0; $i < count($sql_original_choices); $i++) {
        // Initialize $bind_variables again
        $bind_variables = array();
        if ($original_choices_id_array[$i]) {
            $bind_variables[
                $choice_label_array[$i]
            ] = $original_choices_id_array[$i];
        }
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
        $return_data["DATA"] = $status;
        do_result($return_json);
        exit();
    }
    // No exit here if we want to continue to retrieve the LAST_INSERT_ID outside
    // of this function
    // get back the id of the latest inserted survey
    $new_id_object = execute_sql("SELECT LAST_INSERT_ID();", array(), "select");
    return (int) $new_id_object[0]["LAST_INSERT_ID()"];
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
    $sql_choice_id = gen_query_get_choices_id(
        $level,
        $original_survey_info_array
    );
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
                $course_code = $id[0]["course_code"];
            } else {
                $section_id = (int) $id[0]["section_id"];
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

function add_new_survey_choice($survey_id, $level, $data, $user_id)
{
    // Use a loop to bind variables for choice 1 to 6
    $choices_object = array();
    $choices_array = array();
    $department_name = null;
    $course_code = null;
    $section_id = null;

    $temp_label = $level;
    if ($level == "section") {
        $temp_label = "ta";
    }
    // Use an array to store the choice on the "$level"
    for ($i = 0; $i < 6; $i++) {
        $choice_number = $i + 1;
        $choice_name = "choice" . $choice_number;
        $choices_object[$choice_name] = (int) $data[
            $temp_label . "_survey_choices"
        ]["choices"][$i];
    }
    switch ($level) {
        case "dept":
            array_push($choices_array, $choices_object, null, null);
            $department_name = $data["dept_survey_choices"]["department_name"];
            break;
        case "course":
            array_push($choices_array, null, $choices_object, null);
            $course_code = $data["course_survey_choices"]["course_code"];
            break;
        default:
            array_push($choices_array, null, null, $choices_object);
            $section_id = $data["ta_survey_choices"]["section_id"];
            break;
    }
    // execute the "set_new_choices" function and
    // get back the "new_choices_id_array" and the "sql_get_new_choices"
    $set_new_choice_return_object = set_new_choices($choices_array);
    $new_choices_id_array = $set_new_choice_return_object[
        "new_choices_id_array"
    ];
    $sql_get_new_choices = $set_new_choice_return_object["sql_get_new_choices"];
    $new_survey_choices_id_array = set_new_survey_choices(
        $choices_array,
        $new_choices_id_array,
        $department_name,
        $course_code,
        $section_id,
        $user_id,
        $sql_get_new_choices
    );
    $bind_variables = array("survey_id" => $survey_id);

    $new_survey_choices_id = null;
    // get the new survey choice id
    switch ($level) {
        case "dept":
            $new_survey_choices_id = (int) $new_survey_choices_id_array[0];
            break;
        case "course":
            $new_survey_choices_id = (int) $new_survey_choices_id_array[1];
            break;
        default:
            $new_survey_choices_id = (int) $new_survey_choices_id_array[2];
            break;
    }
    $bind_variables[$temp_label . "_survey_choice_id"] = $new_survey_choices_id;
    $sql =
        "UPDATE surveys SET " .
        $temp_label .
        "_survey_choice_id = :" .
        $temp_label .
        "_survey_choice_id WHERE survey_id = :survey_id";
    $status = execute_sql($sql, $bind_variables, null);

    if ($status != "success") {
        do_result(array("TYPE" => "error", "DATA" => $status));
        exit();
    } else {
        do_result(array("TYPE" => "success", "DATA" => null));
        exit();
    }
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
                $choice_number = "choice" . $j;
                if ($choices_array[$i][$choice_number] != null) {
                    $bind_variables[$choice_name] = (int) $choices_array[$i][
                        $choice_number
                    ];
                } else {
                    $bind_variables[$choice_name] = null;
                }
            }
        }
        $status = execute_sql($sql_set_new_choices[$i], $bind_variables, null);
        if ($status && $status != "success") {
            $return_data["TYPE"] = "error";
            $return_data["DATA"] = $status;
            do_result($return_data);
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
            $return_data["DATA"] = $status;
            do_result($return_data);
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
            $return_data["DATA"] = $status;
            do_result($return_data);
            exit();
        }
    }
    // delete the survey_instances related to this survey
    $bind_variables = array("survey_id" => $survey_id);
    $status = execute_sql($sql_delete_survey_instance, $bind_variables, null);
    if ($status != "success" && $status != null) {
        $return_data["TYPE"] = "error";
        $return_data["DATA"] = $status;
        do_result($return_data);
        exit();
    }
    // delete the survey
    $status = execute_sql($sql_delete_survey, $bind_variables, null);
    // If the status is not "success" or not null, directly return an error
    if ($status != "success" && $status != null) {
        $return_data["TYPE"] = "error";
        $return_data["DATA"] = $status;
        do_result($return_data);
        exit();
    } else {
        do_result($return_data);
        exit();
    }
}
