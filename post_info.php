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
function execute_sql($query_string, $bind_variables, $operation = null)
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
                $survey_id = handle_survey_branching($survey_id, $level);
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
            handle_survey_branching($survey_id, $level);
            do_result($return_data);
            exit();
            break;

        default:
            // Call the function handle_survey_delete
            handle_survey_delete($survey_id, $return_data);
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
 * Update data in a survey. This function assumes that the
 * survey with `$survey_id` exists.
 *
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
    // 1. Get the information of the original survey on a survey level
    $sql = gen_query_survey_get_all();
    $query_result = execute_sql($sql, ["survey_id" => $survey_id], "select");
    $orig_survey_info = $query_result[0];

    // 2. Update the original survey's columns

    // figure out which columns need to be updated.
    // if the incoming data is `null`/undefined for that column,
    // it won't be updated.
    $update_cols = [];
    foreach (
        ["name", "term", "default_survey_open", "default_survey_close"]
        as $col
    ) {
        if (isset($data[$col]) && $data[$col] != null) {
            $update_cols[] = $col;
        }
    }
    // update the basic columns we've been given data on
    $sql = gen_query_update_survey_col($update_cols);
    $bind_variables = [];
    foreach ($update_cols as $col) {
        $bind_variables[$col] = $data[$col];
    }
    $bind_variables["survey_id"] = $survey_id;
    execute_sql($sql, $bind_variables);

    // 3. Next we update the the choices by first getting the choice_id and then
    // setting the choices. If the choice_id is null, we need to create a new
    // entry in the choices table.

    switch ($level) {
        case "dept":
            $table = "dept_survey_choices";
            $ref_id = $data[$table]["department_name"];
            $level_choices = "dept_survey_choice_id";
            break;
        case "course":
            $table = "course_survey_choices";
            $ref_id = $data[$table]["course_code"];
            $level_choices = "course_survey_choice_id";
            break;
        case "section":
        case "ta":
            $table = "ta_survey_choices";
            $ref_id = $data[$table]["section_id"];
            $level_choices = "ta_survey_choice_id";
            break;
    }
    $level_choices_id = $orig_survey_info[$level_choices];
    $choices = $data[$table]["choices"];

    if ($level_choices_id == null) {
        // in this case the choices were null. We need to first create
        // the a row in the `choices` table and then make a row
        // in the `*_survey_choices` table.
        $sql = gen_query_insert_new_choices();
        execute_sql($sql, [
            "choice1" => $choices[0],
            "choice2" => $choices[1],
            "choice3" => $choices[2],
            "choice4" => $choices[3],
            "choice5" => $choices[4],
            "choice6" => $choices[5]
        ]);
        // get the most recently inserted ID
        $query_result = execute_sql(gen_query_get_last(), [], "select");
        $choices_id = $query_result[0]["LAST_INSERT_ID()"];

        // Create a new entry in the appropriate table.
        $sql = gen_query_insert_new_choices($level);
        $bind_variables = [
            "choices_id" => $choices_id,
            "user_id" => $user_id,
            "ref_id" => $ref_id
        ];
        execute_sql($sql, $bind_variables);
        // Grab the id of the inserted row.
        $query_result = execute_sql(gen_query_get_last(), [], "select");
        $level_choices_id = $query_result[0]["LAST_INSERT_ID()"];
        // Update the appropriate reference.
        $sql = gen_query_update_survey_col([$level_choices]);
        execute_sql($sql, [
            $level_choices => $level_choices_id,
            "survey_id" => $survey_id
        ]);
    } else {
        // in this case, the choices reference already exists, so we
        // just need to update it.
        $sql = gen_query_update_choice();
        execute_sql($sql, [
            "choice1" => $choices[0],
            "choice2" => $choices[1],
            "choice3" => $choices[2],
            "choice4" => $choices[3],
            "choice5" => $choices[4],
            "choice6" => $choices[5],
            "choices_id" => $level_choices_id
        ]);
    }
    do_result($return_data);
    exit();
}

/**
 * Handle survey branching. When a survey is branched, a clone is
 * created. Every choices reference above the current level is
 * kept, every choices reference below the current level is kept,
 * and the choices reference at the current level is cloned.
 *
 * For example, at the course level, after a "branch", the survey
 * {
 *    dept_survey_choice_id: 5,
 *    course_survey_choice_id: 9,
 *    ta_survey_choice_id: 7
 * }
 *
 * would become
 * {
 *    dept_survey_choice_id: 5,
 *    course_survey_choice_id: <new num>,
 *    ta_survey_choice_id: null
 * }
 *
 * where the course_survey_choice referenced by <new num> would
 * be a clone of course_survey_choice with id 9.
 *
 *
 * @param survey_id:int The id of the survey
 * @param level:string dept, course, section
 */
function handle_survey_branching($survey_id, $level)
{
    // get all data associated with the current survey
    $sql = gen_query_survey_get_all();
    $orig_survey_info = execute_sql(
        $sql,
        ["survey_id" => $survey_id],
        "select"
    )[0];

    // create a duplicate of this survey and get a reference to it
    $sql = gen_query_set_new_survey();
    // remove the `survey_id` from `$orig_survey_info` so we can use
    // it as bind parameters.
    unset($orig_survey_info["survey_id"]);
    execute_sql($sql, $orig_survey_info);
    $query_result = execute_sql(gen_query_get_last(), [], "select");
    $new_survey_id = $query_result[0]["LAST_INSERT_ID()"];

    // set null all columns below the current level
    switch ($level) {
        case "dept":
            $active_column = "dept_survey_choice_id";
            $sql = gen_query_update_survey_col([
                "course_survey_choice_id",
                "ta_survey_choice_id"
            ]);
            execute_sql($sql, [
                "course_survey_choice_id" => null,
                "ta_survey_choice_id" => null
            ]);
            break;
        case "course":
            $active_column = "course_survey_choice_id";
            $sql = gen_query_update_survey_col(["ta_survey_choice_id"]);
            execute_sql($sql, ["ta_survey_choice_id" => null]);
            break;
        case "section":
        case "ta":
            $active_column = "ta_survey_choice_id";
            // nothing to nullify here
            break;
    }

    // create a clone of the choices at the appropriate level,
    // but only if it is non-null
    if ($orig_survey_info[$active_column] != null) {
        $sql = gen_query_clone_choices($level);
        execute_sql($sql, ["id" => $orig_survey_info[$active_column]]);
        // grab the new id so we can replace the `*_survey_choices` reference
        $query_result = execute_sql(gen_query_get_last(), [], "select");
        $level_survey_choices_id = $query_result[0]["LAST_INSERT_ID()"];

        // get the `choices_id` for the new clone
        $sql = gen_query_get_choices_id_by_level($level);
        $choices_id = execute_sql(
            $sql,
            ["id" => $level_survey_choices_id],
            "select"
        )[0]["id"];

        // if there is no reference to `choices`, then
        // we don't need to clone it. Otherwise we do.
        $new_choices_id = null;
        if ($choices_id != null) {
            // called with no arguments it clones
            // a row from the `choices` table.
            $sql = gen_query_clone_choices();
            execute_sql($sql, ["id" => $choices_id]);

            // grab a reference to the new choices
            $query_result = execute_sql(gen_query_get_last(), [], "select");
            $new_choices_id = $query_result[0]["LAST_INSERT_ID()"];
        }
    }

    return $new_survey_id;
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
 * Delete a survey and any associated instances.
 * @param survey_id:number The id of the survey
 * @param return_data
 */
function handle_survey_delete($survey_id, $return_data)
{
    // `$sql` will be a list of query commands. We should
    // execute them in sequence.
    $sql = gen_query_delete_survey();

    // Delete all the responses for the survey
    $query_result = execute_sql($sql[0], ["survey_id" => $survey_id]);
    // Delete all associated survey instances
    $query_result = execute_sql($sql[1], ["survey_id" => $survey_id]);
    // Delete the survey
    $query_result = execute_sql($sql[2], ["survey_id" => $survey_id]);

    do_result($return_data);
    exit();
}
