<?php
/**
 * Functions for common queries are included here.
 */
require 'utils.php';
require 'post_query_generators.php';
require 'db_config.php';

// put this information in a global variable because PHP
// doesn't have closures...there is probably a better way to
// do this---like to have a global database object.
$GLOBALS["DB_INFO"] = [
    "servername" => $servername,
    "database" => $database,
    "password" => $password,
    "username" => $username
];

class QueryResult
{
    public $conn;
    public $stmt;
    public $result;
}

/**
 * Ensure $conn is a connection to the database
 */
function ensure_connection($conn = null)
{
    if ($conn != null) {
        return $conn;
    }
    $servername = $GLOBALS["DB_INFO"]["servername"];
    $database = $GLOBALS["DB_INFO"]["database"];
    $password = $GLOBALS["DB_INFO"]["password"];
    $username = $GLOBALS["DB_INFO"]["username"];
    $ret = new PDO(
        "mysql:host=$servername;dbname=$database",
        $username,
        $password
    );
    return $ret;
}

/**
 * Execute a query with bound variables `$bound`. A new
 * connection will be established unless `$conn` is passed in.
 */
function do_query($sql, $bound, $conn = null)
{
    // If there's a place to store debug information, do so.
    if (
        isset($GLOBALS['DEBUG_INFO']) &&
        isset($GLOBALS['DEBUG_INFO']['executed_sql'])
    ) {
        $GLOBALS['DEBUG_INFO']["executed_sql"][] = [
            "query" => $sql,
            "bindings" => $bound
        ];
    }

    // if a connection is not passed in, get a new one.
    $conn = ensure_connection($conn);

    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare($sql);
    foreach ($bound as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    $ret = new QueryResult();
    $ret->conn = $conn;
    $ret->stmt = $stmt;
    return $ret;
}

/**
 * Same as do_query, but does a fetchAll and returns the
 * result in the `"fetched"` attribute.
 */
function do_select_query($sql, $bound, $conn = null)
{
    $res = do_query($sql, $bound, $conn);
    $result = $res->stmt->fetchAll(PDO::FETCH_ASSOC);

    $ret = new QueryResult();
    $ret->conn = $res->conn;
    $ret->stmt = $res->stmt;
    $ret->result = $result;
    return $ret;
}

/**
 * Gets the choices associated with a survey. Returns
 * a list of `choice_id`s.
 *
 *  The data is assumed to be proper.
 *  An example "rendering" is:
 *    default_choices = [1,    2,    3,    4,   5,    6]
 *    dept_choices =    [3,    8,    5,    7,   4,    8]
 *    course_choices =  [NULL, NULL, 3,    NULL,4,    7]
 *    ta_choices =      [NULL, NULL, NULL, NULL,5,    9]
 *    result =          [3,    8,    3,    7,   5,    9]
 */
function get_survey_choices($survey_id, $survey_table_row = null, $conn = null)
{
    if ($survey_table_row == null) {
        $bound = ["survey_id" => $survey_id];
        $res = do_select_query(gen_query_survey_get_all(), $bound);
        if (count($res->result) == 0) {
            throw new Exception("No survey with survey_id='$survey_id'");
        }
        $survey_table_row = $res->result[0];
    }
    // We need to get all the level choices so they can be rendered
    // into the final survey.
    $nulls = [
        "choice1" => null,
        "choice2" => null,
        "choice3" => null,
        "choice4" => null,
        "choice5" => null,
        "choice6" => null
    ];
    $choices_array = [[-1, -1, -1, -1, -1, -1]];
    foreach (
        [
            ["dept", "dept_survey_choice_id"],
            ["course", "course_survey_choice_id"],
            ["section", "ta_survey_choice_id"]
        ]
        as $l
    ) {
        $sql = gen_query_get_choices_by_level($l[0]);
        $bound = ["id" => $survey_table_row[$l[1]]];
        $res = do_select_query($sql, $bound, $conn);
        $choices = $nulls;
        if (count($res->result) > 0) {
            $choices = $res->result[0];
        }
        $choices_array[] = [
            $choices["choice1"],
            $choices["choice2"],
            $choices["choice3"],
            $choices["choice4"],
            $choices["choice5"],
            $choices["choice6"]
        ];
    }
    // Render the choices array into `$choices`
    // Assume data is proper
    //  default_choices = [1,    2,    3,    4,   5,    6]
    //  dept_choices =    [3,    8,    5,    5,   4,    8]
    //  course_choices =  [NULL, NULL, 3,    5,   4,    7]
    //  ta_choices =      [NULL, NULL, NULL, NULL,5,    9]
    //  result =          [3,    8,    3,    5,   5,    9]
    $choices = [];
    foreach ($choices_array as $level_choices) {
        foreach ($level_choices as $key => $value) {
            if ($value != null) {
                $choices["choice" . ($key + 1)] = $value;
            }
        }
    }
    return $choices;
}

/**
 * Pass in an array (e.g., [1,2,3,5]) of question
 * choices or an associative array (e.g., ["choice1" => 1, "choice2" => 6, ..., "choice6" => 3])
 * and get back a list of questions in the same order.
 */
function get_question_text($question_id_list, $conn = null)
{
    if (count($question_id_list) == 0) {
        return [];
    }
    // `$question_id_list` can be either an array or
    // an object indexed by 'choice1',...,'choice6'
    // Handle either case.
    if (isset($question_id_list["choice1"])) {
        $question_id_list = [
            $question_id_list["choice1"],
            $question_id_list["choice2"],
            $question_id_list["choice3"],
            $question_id_list["choice4"],
            $question_id_list["choice5"],
            $question_id_list["choice6"]
        ];
    }

    // Since there aren't many questions, grab them all and then assemble the return;
    $sql = "SELECT * FROM questions";
    $res = do_select_query($sql, [], $conn);
    $question_hash = [];
    foreach ($res->result as $question) {
        $question_hash[$question["question_id"]] = $question;
    }

    // assemble the return list
    $ret = [];
    foreach ($question_id_list as $key => $question_id) {
        if (isset($question_hash[$question_id])) {
            $question = $question_hash[$question_id];
        } else {
            // if there is no corresponding question, return the `noquestion` question
            $question = [
                "question_id" => $question_id,
                "answer_type" => "noquestion",
                "content" =>
                    '{"type":"noquestion","name":"noquestion","title":"noquestion"}'
            ];
        }
        // A reminant of the old API, but kept in for consistency.
        $question["position"] = $key + 1;
        $ret[] = $question;
    }
    return $ret;
}

/**
 * Given a survey_id or a survey_instance_id, assemble a survey package.
 */
function get_survey_package(
    $survey_id = null,
    $survey_instance_id = null,
    $conn = null
) {
    if ($survey_id == null && $survey_instance_id == null) {
        throw new Exception(
            "One of `survey_id` or `survey_instance_id` must be set."
        );
    }

    $ret = ["timedate_open" => null, "timedate_close" => null];
    $choices = null;

    // If we specified a survey_instance, get all needed data.
    if ($survey_instance_id != null) {
        // in this case, we want to render a survey package for a survey instance.
        // We need to get the $survey_id from the survey instance.
        $sql =
            "SELECT * FROM survey_instances WHERE survey_instance_id = :survey_instance_id";
        $bound = ["survey_instance_id" => $survey_instance_id];
        $res = do_select_query($sql, $bound, $conn);
        // We'll be doing a lot of queries. Keep the connection for efficiency.
        $conn = $res->conn;
        if (count($res->result) == 0) {
            throw new Error(
                "No matching `survey_instance_id` to `$survey_instance_id`"
            );
        }
        $result = $res->result[0];
        $survey_id = $result["survey_id"];
        $ret["survey_instance_id"] = $survey_instance_id;
        $ret["override_token"] = $result["override_token"];
        $ret["timedate_open"] = $result["survey_open"];
        $ret["timedate_close"] = $result["survey_close"];

        // XXX TODO no data is pulled from the user association

        // A survey instance has pre-rendered choices,
        // so we can fetch them right away.
        $choices_id = $res->result[0]["choices_id"];
        $sql = "SELECT * FROM choices WHERE choices_id = :choices_id";
        $bound = ["choices_id" => $choices_id];
        $res = do_select_query($sql, $bound, $conn);
        $choices = $res->result[0];
    }

    $ret["survey_id"] = $survey_id;

    // Grab information about the survey
    $sql = gen_query_survey_get_all();
    $bound = ["survey_id" => $survey_id];
    $res = do_select_query($sql, $bound, $conn);
    // Save the connection for future queries.
    $conn = $res->conn;

    $survey_table_row = $res->result[0];
    $ret["name"] = $survey_table_row["name"];
    $ret["term"] = $survey_table_row["term"];
    if ($ret["timedate_open"] == null) {
        $ret["timedate_open"] = $survey_table_row["default_survey_open"];
    }
    if ($ret["timedate_close"] == null) {
        $ret["timedate_close"] = $survey_table_row["default_survey_close"];
    }
    // if `$choices` already has something in it,
    // we got our choices from the survey_instance. If
    // not, we need to render them now.
    if ($choices == null) {
        $choices = get_survey_choices($survey_id, $survey_table_row, $conn);
    }
    $questions = get_question_text($choices);
    $ret["questions"] = $questions;

    // If we are a regular survey, we're done
    if ($survey_instance_id == null) {
        return $ret;
    }

    $responses = get_responses($survey_instance_id, $conn);
    // If we're here, we're a survey instance.
    // Populate with responses.
    foreach ($ret["questions"] as &$question) {
        if (isset($responses[$question["question_id"]])) {
            $question["responses"] = $responses[$question["question_id"]];
        } else {
            $question["responses"] = [];
        }
    }
    return $ret;
}

/**
 * Gets all responses associated with a survey_instance and returns
 * an associative array, indexed by question_id's.
 */
function get_responses($survey_instance_id, $conn = null)
{
    $sql =
        "SELECT question_id, answer FROM responses WHERE survey_instance_id = :survey_instance_id";
    $bound = ["survey_instance_id" => $survey_instance_id];
    $res = do_select_query($sql, $bound, $conn);

    // assemble the return package, which is indexed by question IDs
    $ret = [];
    foreach ($res->result as $response) {
        $question_id = $response["question_id"];
        if (!isset($ret[$question_id])) {
            $ret[$question_id] = [];
        }
        $ret[$question_id][] = $response["answer"];
    }
    return $ret;
}

/**
 * Returns the info associated with a user
 */
function get_user_info($user_id, $conn = null)
{
    $sql = "SELECT * FROM users WHERE user_id = :user_id";
    $bound = ["user_id" => $user_id];
    $res = do_select_query($sql, $bound, $conn);
    if (count($res->result) == 0) {
        throw new Exception("No user with user_id '$user_id'.");
    }
    return $res->result[0];
}

/**
 * Returns true if `$user_id` is allowed
 * to see the results for the survey instance
 * with the given `$survey_instance_id`
 *
 * A user has permission if
 *    a) they are the associated user; or
 *    b) they are associated with the same section
 *    and have instructor/admin privledges.
 */
function can_view_survey_instance($survey_instance_id, $user_id, $conn = null)
{
    $sql =
        "SELECT * FROM (user_associations JOIN survey_instances " .
        "ON survey_instances.user_association_id = user_associations.user_association_id) " .
        "WHERE survey_instances.survey_instance_id = :survey_instance_id";
    $bound = ["survey_instance_id" => $survey_instance_id];
    $res = do_select_query($sql, $bound, $conn);
    $conn = $res->conn;

    foreach ($res->result as $association_info) {
        $associated_user_id = $association_info["user_id"];
        // If we are a directly associated we have permission to see
        if ($associated_user_id == $user_id) {
            return true;
        }

        if (
            $association_info["course_code"] == null &&
            $association_info["section_id"] == null
        ) {
            // if we're not associated with a course or section, it's impossible
            // for anyone else to have rights to view our survey.
            return false;
        }

        // Get the associated course and department
        $course_code = $association_info["course_code"];
        if ($course_code == null) {
            $sql =
                "SELECT course_code FROM sections WHERE section_id = :section_id";
            $bound = ["section_id" => $association_info["section_id"]];
            $res = do_select_query($res, $bound, $conn);
            // There's a foreign key constraint here, so the array must be populated.
            $course_code = $res->result[0];
        }

        // Now we do checks based on instructor/dept permissions
        $user_info = get_user_info($user_id, $conn);

        // check if we're an instructor assigned to that section as well
        if (
            ($user_info["is_instructor"] || $user_info["is_admin"]) &&
            $association_info["section_id"] != null
        ) {
            $sql =
                "SELECT user_id FROM user_associations WHERE user_id = :user_id AND section_id = :section_id";
            $bound = [
                "user_id" => $user_id,
                "section_id" => $association_info["section_id"]
            ];
            $res = do_select_query($sql, $bound, $conn);
            if (count($res->result) > 0) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Get a list of all survey instances associated with a user_id. The
 * search may be narrowed by further specifying a `course_code` and a `term`
 */
function get_associated_survey_instances(
    $user_id,
    $course_code,
    $term,
    $conn = null
) {
    if ($course_code == null) {
        // if we don't specify these parameters, make them
        // SQL wildcards
        $course_code = "%";
    }
    if ($term == null) {
        $term = "%";
    }
    $sql =
        "SELECT i.survey_instance_id FROM (survey_instances AS i " .
        "JOIN user_associations AS u ON i.user_association_id = u.user_association_id " .
        "JOIN sections AS s ON u.section_id = s.section_id) " .
        "WHERE u.user_id = :user_id AND s.course_code LIKE :course_code AND s.term LIKE :term";
    $bound = [
        "user_id" => $user_id,
        "course_code" => $course_code,
        "term" => $term
    ];
    $res = do_select_query($sql, $bound, $conn);

    // unpack the query to be a regular list of ids
    $ret = [];
    foreach ($res->result as $x) {
        $ret[] = $x["survey_instance_id"];
    }
    return $ret;
}

/**
 * If user does not exist within database, a new TA user is inserted.
 *
 * @param user_id user_id of the user
 */
function check_new_user($user_id)
{
    try {
        get_user_info($user_id);
    } catch (Exception $e) {
        $bound = ["user_id" => $user_id, "name" => strtoupper($user_id)];
        $sql =
            "INSERT INTO users (user_id, is_ta, is_instructor, is_admin, name, photo)" .
            " VALUES (:user_id, 1, 0, 0, :name, NULL)";
        do_query($sql, $bound, null);
    }
}
