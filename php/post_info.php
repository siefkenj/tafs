<?php
require 'query_utils.php';
require 'db_config.php';
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
    verify_user_id($params);

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
        case 'launch_survey':
            handle_launch_survey($params["survey_id"], $params["user_id"]);

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
 * Returns the user_association_id for the given `$user_id`,
 * `$course_code` and `$section_id`. If no such user association
 * exists, one is created. If the course or section don't exist,
 * those are created too. A new user is NOT created.
 */
function ensure_association(
    $user_id,
    $course_code,
    $section_code,
    $term = null,
    $section_id = null
) {
    if ($term == null) {
        // if the term isn't set, assume it is the current term.
        $term = normalize_term($term);
    }
    if ($section_id == null) {
        // look up the section_id
        $sql =
            "SELECT section_id FROM sections WHERE course_code = :course_code AND term = :term AND section_code = :section_code;";
        $bound = [
            "course_code" => $course_code,
            "term" => $term,
            "section_code" => $section_code
        ];
        $query_result = execute_sql($sql, $bound, "select");
        if (count($query_result) > 0) {
            $section_id = $query_result[0]["section_id"];
        } else {
            // If a corresponding section cannot be found,
            // set the section_id to something that is guaranteed
            // not to exist, so the next query will return no results.
            $section_id = -1;
        }
    }
    // in this case we don't need to look up the section_id
    $sql =
        "SELECT user_association_id FROM user_associations WHERE user_id = :user_id AND course_code = :course_code AND section_id = :section_id;";
    $bound = [
        "user_id" => $user_id,
        "course_code" => $course_code,
        "section_id" => $section_id
    ];
    $query_result = execute_sql($sql, $bound, "select");
    if (count($query_result) > 0) {
        return $query_result[0]["user_association_id"];
    }

    // Either the course_code or the section_id is missing, so ensure they
    // both exist and then insert a new user_association.
    $course_code = ensure_course($course_code);
    $section_id = ensure_section($section_code, $course_code, $term);

    $sql =
        "INSERT INTO user_associations (user_id, course_code, section_id) VALUES (:user_id, :course_code, :section_id);";
    $bound = [
        "user_id" => $user_id,
        "course_code" => $course_code,
        "section_id" => $section_id
    ];
    execute_sql($sql, $bound);

    $query_result = execute_sql(gen_query_get_last(), [], "select");
    return $query_result[0]["LAST_INSERT_ID()"];
}

/**
 * Return the `course_code` of a course, createing it if it doesn't exist.
 */
function ensure_course($course_code, $title = "", $department_name = "")
{
    $sql = "SELECT course_code FROM courses WHERE course_code = :course_code;";
    $query_result = execute_sql(
        $sql,
        ["course_code" => $course_code],
        "select"
    );
    if (count($query_result) > 0) {
        return $course_code;
    }

    // department_name is a foreign key reference, so make
    // sure it's there.
    $department_name = ensure_department($department_name);

    $sql =
        "INSERT INTO courses (course_code, title, department_name) VALUES (:course_code, :title, :department_name);";
    execute_sql($sql, [
        "course_code" => $course_code,
        "title" => $title,
        "department_name" => $department_name
    ]);
    return $course_code;
}

/**
 * Return the `section_id` of a section, creating it if it doesn't exist.
 */
function ensure_section(
    $section_code,
    $course_code,
    $term = null,
    $meeting_time = null,
    $room = null
) {
    $sql =
        "SELECT section_id FROM sections WHERE section_code = :section_code;";
    $query_result = execute_sql(
        $sql,
        ["section_code" => $section_code],
        "select"
    );
    if (count($query_result) > 0) {
        return $query_result[0]["section_id"];
    }

    // We have to have a term. If we didn't specify one,
    // make up a term based on the current date.
    $term = normalize_term($term);

    $course_code = ensure_course($course_code);
    $sql =
        "INSERT INTO sections (section_code, course_code, term, meeting_time, room) " .
        "VALUES (:section_code, :course_code, :term, :meeting_time, :room);";
    execute_sql($sql, [
        "section_code" => $section_code,
        "course_code" => $course_code,
        "term" => $term,
        "meeting_time" => $meeting_time,
        "room" => $room
    ]);
    $query_result = execute_sql(gen_query_get_last(), [], "select");
    return $query_result[0]["LAST_INSERT_ID()"];
}

/**
 * Returns the department_name of `$department_name` and
 * creates it if it doesn't exist.
 */
function ensure_department($department_name)
{
    $sql =
        "INSERT INTO departments (department_name) VALUES (:department_name) ON DUPLICATE KEY UPDATE department_name = :department_name;";
    $bound = ["department_name" => $department_name];
    execute_sql($sql, $bound);
    return $department_name;
}

/**
 * Returns an override token that is guaranteed to be unique.
 */
function get_unique_override_token()
{
    $override_token = gen_override_token();
    $sql = gen_query_survey_instace_by_token();
    for ($i = 0; ; $i++) {
        if ($i > 100) {
            // don't get stuck in an infinite loop.
            // This should never happen...
            throw new Exception(
                "Too many collisions when looking for unique override token. Last tried '$override_token'"
            );
        }
        $res = execute_sql(
            $sql,
            ["override_token" => $override_token],
            "select"
        );
        if (count($res) == 0) {
            // We found a unique token!
            break;
        }
        $override_token = gen_override_token();
    }
    return $override_token;
}

/**
 * Generate new "survey_instance" according to the survey_id provided.
 *
 * The new survey instance will contain:
 *     a. A new "choices_id", which comes from the new choice instance
 *        set up according to the choices in "ta_survey_choices" of the
 *        original survey. We duplicate a new choice instance rather than
 *        referencing on the existing choice instance because we
 *        do not want the choices in the "survey_instance" to be changed anymore.
 *     b. A newly generated "override_token"
 *
 * @param survey_id:int The id of the survey
 * @param user_id:string The utorid of the user
 * @param course_code:string Default to "UofT" if not set
 * @param section_code:string Default to "Tutorial" if not set
 * @param term:string Default to null if not set`
 */
function handle_launch_survey(
    $survey_id,
    $user_id,
    $course_code = "UofT",
    $section_code = "Tutorial",
    $term = null
) {
    // generate a unique override token
    $override_token = get_unique_override_token();

    // get the current survey
    $sql = gen_query_survey_get_all();
    $survey_package = execute_sql($sql, ["survey_id" => $survey_id], "select");
    if (count($survey_package) == 0) {
        throw new Exception(
            "Cannot launch survey. Found no surveys with id '$survey_id'"
        );
    }
    $survey_package = $survey_package[0];

    $choices = get_survey_choices(
        $survey_id,
        $survey_package,
        $GLOBALS["conn"]
    );

    // create a new choices object
    $sql = gen_query_insert_new_choices();
    execute_sql($sql, $choices);
    $query_result = execute_sql(gen_query_get_last(), [], "select");
    $new_choices_id = $query_result[0]["LAST_INSERT_ID()"];

    // XXX we should be able to provide better information than this, like
    // the section_id. This is just a stopgap.
    $user_association_id = ensure_association(
        $user_id,
        $course_code,
        $section_code,
        $term
    );

    $sql =
        "INSERT INTO survey_instances (survey_id, choices_id, user_association_id, override_token, survey_open, survey_close, viewable_by_others, name) " .
        "VALUES (:survey_id, :choices_id, :user_association_id, :override_token, :survey_open, :survey_close, :viewable_by_others, :name);";
    $bound = [
        'survey_id' => $survey_id,
        'choices_id' => $new_choices_id,
        'user_association_id' => $user_association_id,
        'override_token' => $override_token,
        'survey_open' => $survey_package["default_survey_open"],
        'survey_close' => $survey_package["default_survey_close"],
        'viewable_by_others' => 0,
        'name' => $survey_package['name']
    ];
    execute_sql($sql, $bound);
    $query_result = execute_sql(gen_query_get_last(), [], "select");
    $survey_instance_id = $query_result[0]["LAST_INSERT_ID()"];

    // XXX this isn't a complete survey package
    $ret = [
        "TYPE" => "survey_package",
        "DATA" => [
            "survey_instance_id" => $survey_instance_id,
            "override_token" => $override_token
        ]
    ];
    do_result($ret);
    exit();
}

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
            $new_survey_id = handle_survey_branching($survey_id, $level);
            // Get back the survey package of the new survey and add it into the return data
            $return_data["DATA"] = get_survey_package($new_survey_id);
            do_result($return_data);
            exit();
            break;

        case 'delete':
            // Call the function handle_survey_delete
            handle_survey_delete($survey_id, $return_data);
            break;
        default:
            throw new Exception("Unrecognized action '$action'");
    }
}

/**
 * Return "branch_and_update" if we decide to branch first and then update, and
 * return "only_update" if we decide to directly update on the existing survey
 *
 * @param survey_id:int The id of the survey we want to check
 * @param level:string One of "dept", "course", "section"
 */
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
    if (count($update_cols) > 0) {
        $sql = gen_query_update_survey_col($update_cols);
        $bind_variables = [];
        foreach ($update_cols as $col) {
            $bind_variables[$col] = $data[$col];
        }
        $bind_variables["survey_id"] = $survey_id;
        execute_sql($sql, $bind_variables);
    }
    // 3. Next we update the the choices by first getting the choice_id and then
    // setting the choices. If the choice_id is null, we need to create a new
    // entry in the choices table.

    switch ($level) {
        case "dept":
            $table = "dept_survey_choices";
            $ref_id = $data[$table]["department_name"];
            if ($ref_id == null) {
                // provide a suitable default if nothing is set
                $ref_id = ensure_department("");
            }
            $level_choices = "dept_survey_choice_id";
            break;
        case "course":
            $table = "course_survey_choices";
            $ref_id = $data[$table]["course_code"];
            if ($ref_id == null) {
                // provide a suitable default if nothing is set
                $ref_id = ensure_course("UofT");
            }
            $level_choices = "course_survey_choice_id";
            break;
        case "section":
        case "ta":
            $table = "ta_survey_choices";
            $ref_id = $data[$table]["section_id"];
            if ($ref_id == null) {
                // provide a suitable default if nothing is set
                $ref_id = ensure_section("Tutorial", "UofT");
            }
            $level_choices = "ta_survey_choice_id";
            break;
    }
    $level_choices_id = $orig_survey_info[$level_choices];
    $choices = $data[$table]["choices"];

    if ($level_choices_id == null) {
        // in this case the choices were null. We need to first create
        // the a row in the `choices` table and then make a row
        // in the `*_survey_choices` table.

        // If $chioces == null, we should set all the choices to
        // null. This is a placeholder so that we know that this
        // survey has been edited.
        if ($choices == null) {
            $choices = [null, null, null, null, null, null];
        }

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
        $sql = gen_query_insert_new_survey_choices($level);
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
    } elseif ($choices != null) {
        // in this case, the choices reference already exists, so we
        // just need to update it. However, we have to get a
        // reference to the choices_id first.
        $sql = "SELECT choices_id FROM $table WHERE id = :id;";
        $query_result = execute_sql(
            $sql,
            ["id" => $level_choices_id],
            "select"
        );
        $choice_id = $query_result[0]["choices_id"];

        $sql = gen_query_update_choice();
        execute_sql($sql, [
            "choice1" => $choices[0],
            "choice2" => $choices[1],
            "choice3" => $choices[2],
            "choice4" => $choices[3],
            "choice5" => $choices[4],
            "choice6" => $choices[5],
            "choices_id" => $choice_id
        ]);
    }
    // Get back the survey package and put it into the return data
    $return_data["DATA"] = get_survey_package($survey_id);
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
                "ta_survey_choice_id" => null,
                "survey_id" => $new_survey_id
            ]);
            break;
        case "course":
            $active_column = "course_survey_choice_id";
            $sql = gen_query_update_survey_col(["ta_survey_choice_id"]);
            execute_sql($sql, [
                "ta_survey_choice_id" => null,
                "survey_id" => $new_survey_id
            ]);
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
        )[0]["choices_id"];

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

            // set the clone to reference the new choices
            $sql = gen_query_set_choices_id_by_level($level);
            execute_sql($sql, [
                "choices_id" => $new_choices_id,
                "id" => $level_survey_choices_id
            ]);
        }
    }

    return $new_survey_id;
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
