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
    $nulls = [null, null, null, null, null, null];
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
