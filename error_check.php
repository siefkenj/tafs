<?php
/**
 * Checks if required fields are selected
 *
 * @param data array containing request items
 * @param need_data If request has a data section
 *
 * @throws No_URL_Select If no URL is present
 * @throws No_Data If request requires data but none passed in
 */
function error_check($data, $need_data){

	if (!isset($data["url"])){
		$error = "No URL Selected";
		throw new Exception($error);
	}
	if ($need_data){
		if (!isset($data["data"])){
			$error = "No Data";
			throw new Exception($error);
		}
	}
}

/**
 * Split url based on /
 *
 * @param url url for request
 *
 * @return associative array with keys as database and values as ids
 */
function parse($url){
    // Splitting string by /
    $url_arr = preg_split("/\//", $url);

    // If there are odd number of parameters, the max even number of parameters
    // are stored in the associative array. The remaining one will be added
    // later and the value will be set to null.
    $odd = FALSE;
    if (sizeof($url_arr)%2==0){
        $max = sizeof($url_arr);
    }else{
        $max = sizeof($url_arr)-1;
        $odd = TRUE;
    }

    $parsed_url = array();
    for ($index = 0; $index<$max; $index+=2){
        $parsed_url[$url_arr[$index]] = $url_arr[$index+1];
    }

    if ($odd){
        $parsed_url[$url_arr[$max]] = NULL;
    }

    return $parsed_url;
}

/**
* Parse url to get an associative array with table and condition for operation
* on table
* @return returns an associative array that provies the table and condition
* to operate on
*/
function url_to_params($url){
	$result = array('table' => NULL, 'condition' => "");
	$i=0;
	foreach ($url as $key => $value) {
		if($value != NULL){
			switch($key){
				case 'users':
					$result['condition'] .= "utorid = '$value' AND ";
					break;
				case 'courses':
					$result['condition'] .= "course_code = '$value' AND ";
					break;
				case 'sections':
					$result['condition'] .= "section_id = $value AND ";
					break;
				case 'survey':
					$result['condition'] .= "id = $value AND ";
					break;
				case 'responses':
					$result['condition'] .= "response_id = $value AND ";
					break;
				default:
					$error = "invalid url key";
					throw new Exception($error);
			}
		}
		++$i;
		if($i == count($url)){
			$result['table'] = $key;
		}
	}
	$result['condition'] = substr($result['condition'], 0,-4);
	return $result;
}

/**
 * Check if table exist and if the specified column is valid for the table
 *
 * @param columns array containing column names
 * @param table table name
 * @param operation the operation performed (POST, GET, PUT, DELETE)
 *
 * @throws invalid_column error thrown if column doesn't exist
 */
function check_columns($columns, $table, $operation){
    // Array containing columns of all tables
    // Primary key columns are not included
    $tables =array(
        "course_question_choice" => array('question_id', 'user_id', 'locked', 'position'),
        "courses" => array('course_code', 'title', 'department_name'),
        "department" => array('name'),
        "dept_question_choice" => array('department_id', 'term', 'question_id', 'user_id', 'locked', 'position'),
        "questions" => array('answer_type', 'content'),
        "response" => array('survey_instance_id', 'question_id', 'answer', 'user_id'),
        "sections" => array('course_id', 'term', 'meeting_time', 'room', 'section_code'),
        "survey_instances" => array('user_association_id', 'override_token', 'time_window', 'start_time'),
        "surveys" => array('name', 'course_id', 'term', 'default_time_window', 'default_start_time'),
        "ta_question_choice" => array('section_id', 'term', 'question_id', 'user_id', 'locked', 'position'),
        "user_association" => array('user_id', 'course_id', 'section_code'),
        "users" => array('utorid', 'type', 'name1', 'photo1')
	);

	$selected_table = NULL;
    if (array_key_exists($table, $tables)){
        $selected_table = $tables[$table];
    }else{
        throw new Exception('Table: '. $table . ' does not exist');
    }

    if(!(isset($selected_table))){
        throw new Exception("Invalid operation");
    }

    if ($operation=='post'){
        if (sizeof($columns)!=sizeof($selected_table)){
            throw new Exception("Invalid number of columns");
        }
    }

    foreach($columns as $column){
        if (!in_array($column, $selected_table)){
            throw new Exception('Column: '. $column . ' does not exist');
        }
    }
}
?>
