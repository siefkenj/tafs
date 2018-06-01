<?php
require 'db/config.php';
require 'error_check.php';

// Handle client request error
try {
    $method = "";
    if (isset($_SERVER['REQUEST_METHOD'])) {

        $query = "";
        $method = $_SERVER['REQUEST_METHOD'];
        // Request stored as associative array
        $data = json_decode(file_get_contents("php://input"), true);
        switch ($method) {

            case "POST":
                $query = handle_post($data);
                break;

            case "GET":
                $query = handle_get();
                break;

            case "PUT":
                $query = handle_put($data);
                break;

            case "DELETE":
                $query = handle_delete($data);
                break;

            default:
                throw new Exception("Invalid Request");
                break;
        }
    }else{
        $error = 'No Request Method Found';
        throw new Exception($error);
    }
} catch (Exception $e) {
    $result = HTTPStatus(400);
    print json_encode($result, JSON_PRETTY_PRINT);
    exit();
}

// Handle SQL error
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
    $stmt->execute();

    if($method == "GET"){
        //fetch all results
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['DATA'] = $fetched;
    }else{
		$result = HTTPStatus(202);
    }

    echo json_encode($result);
} catch (PDOException $e) {
    $result = HTTPStatus(500);
    print json_encode($result, JSON_PRETTY_PRINT);
    exit();
}

function handle_post($data){
	error_check($data, TRUE);

	$result = url_to_params(parse($data["url"]));

	$keys = "";
	$values = "";
	foreach($data['data'] as $key=>$value){
		$keys.=$key . ", ";
		$values.="'". $value . "', ";
	}
	$keys = rtrim($keys, ", ");
	$values = rtrim($values, ", ");

	// Whitelist check
	check_columns(array_keys($data['data']), $result['table'], 'post');

	return "INSERT INTO ta_feedback.$result[table] ($keys) VALUES ($values);";
}

function handle_get(){
    error_check($_GET, FALSE);

    //parses 'url' field from url then parse it with the parse function
    $parsed_url = parse(urldecode($_GET["url"]));
    $result = url_to_params($parsed_url);
    $table = $result['table'];
    $conditions =  $result['condition'];

    // Whitelist check
    check_columns(array(), $table, 'get');

    if ($conditions == ""){
        return "SELECT * FROM ta_feedback.$table";
    }
    return "SELECT * FROM ta_feedback.$table WHERE $conditions";
}

/**
* @return returns an SQL statment that updates a row
*/
function handle_put($data){
    error_check($data, TRUE);

    $url = parse($data["url"]);
    $result = url_to_params($url);

    // Whitelist check
    check_columns(array_keys($data['data']), $result['table'], 'put');

    $table = $result['table'];
    $request_data = $data['data'];
    $condition = $result['condition'];

    $column = "";
    foreach ($request_data as $key => $value) {
        $column.= "$key = '$value',";
    }
    $column = rtrim($column, ", ");

    return "UPDATE ta_feedback.$table SET $column WHERE $condition;";
}

/**
* @return returns an SQL statment that deletes a row
*/
function handle_delete($data){
    error_check($data, FALSE);

    $result = url_to_params(parse($data["url"]));

    // Whitelist check
    check_columns(array_keys($data['data']), $result['table'], 'delete');

    $table = $result['table'];
    $condition = $result['condition'];

    return "DELETE FROM ta_feedback.$table WHERE $condition;";
}

function HTTPStatus($num) {
    $http = array(
        200 => 'HTTP/1.1 200 OK',
        202 => 'HTTP/1.1 202 Accepted',
        400 => 'HTTP/1.1 400 Bad Request',
        500 => 'HTTP/1.1 500 Internal Server Error',
    );

    header($http[$num]);

    return
        array(
            'CODE' => $num,
            'ERROR' => $http[$num],
        );
}
?>
