<?php
require 'db/config.php';
require 'get_query_generators.php';

try {
    $method = "";
    if (isset($_SERVER['REQUEST_METHOD'])) {
        $query = "";
        $method = $_SERVER['REQUEST_METHOD'];
        // Request stored as associative array
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {
            case "GET":
                $query = handle_get($_GET);
                break;

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

// Attempt to execute sql command and print response in json format.
// If a sql error occurs, a JSON error object is returned.
try {
    // connect to the mysql database
    $conn = new PDO(
        "mysql:host=$servername;dbname=$database",
        $username,
        $password
    );
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare($query);

    bind_variables($stmt, $_GET);
    $stmt->execute();

    if ($method == "GET") {
        //fetch all results
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['TYPE'] = $_GET["what"] . "_package";
        $result['DATA'] = $fetched;
    } else {
        $result = set_http_response(202);
    }

    echo json_encode($result);
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

/**
 * This function handles GET requests and returns a sql query that gets the
 * required data for the what according to the API documentation.
 *
 * @throws InvalidPage The requested what does not exist
 * @return sql query to the corresponding what parameter
 */
function handle_get($parameters)
{
    switch ($parameters["what"]) {
        case "tas":
            return set_parameters(true, $parameters);

        case "questions":
            return gen_query_questions();

        case "course_pairings":
            return set_parameters(false, $parameters);

        case "user_info":
            $include_photo = true;
            if (isset($parameters["include_photo"])) {
                if ($parameters["include_photo"] == 'false') {
                    $include_photo = false;
                }
            }
            $list_of_users = is_list_of_users($parameters["user_id"]);
            return gen_query_user_info($include_photo, $list_of_users);

        default:
            throw new Exception("InvalidPage");
    }
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
 * Binds variables to SQL query based on what parameter
 *
 * @param stmt Reference to PDO object that is a statement to be executed.
 * @param parameters Array of parameters and values
 */
function bind_variables(&$stmt, $parameters)
{
    $stmt->bindParam(':user_id', $parameters["user_id"]);
    if (
        ($parameters["what"] == "course_pairings") ||
        ($parameters["what"] == "tas")
    ) {
        if (isset($parameters["term"])) {
            $stmt->bindParam(':term', $parameters["term"]);
        }

        if (isset($parameters["course_code"])) {
            $stmt->bindParam(':course_code', $parameters["course_code"]);
        }
    }
}

/**
 * This functions sets parameters for gen_query_course_associations based on
 * what parameter. Function returns the result of gen_query_course_associations.
 *
 * @param num The HTTP status code
 * @return array containing the HTTP status of request
 */
function set_parameters($ta_list, $parameters)
{
    $course_code = false;
    if (isset($parameters["course_code"])) {
        $course_code = true;
    }

    $term = false;
    if (isset($parameters["term"])) {
        $term = true;
    }

    $is_ta = true;
    if ($parameters["course_code"] == "course_pairings") {
        if ($parameters["column"] == "instructor") {
            $is_ta = false;
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
