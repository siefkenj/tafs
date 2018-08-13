<?php
require 'handle_request.php';
require 'utils.php';
header("Content-type: application/json");
try {
    $url_params = handle_request();
    if (!array_get($url_params, "what")) {
        throw new Exception("Must provide a 'what' action");
    }

    switch ($url_params['what']) {
        case "fas_course":
            if (!array_get($url_params, "prefix")) {
                throw new Exception("Must provide a prefix");
            }
            echo json_encode(get_fas_info($url_params));
            exit();
        default:
            throw new Exception(
                "Unrecognized 'what' action '" . $url_params['what'] . "'"
            );
    }
} catch (Exception $e) {
    $result = set_http_response(400);
    $result['error_text'] = $e->getMessage();

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
 * Fetch course information from FAS servers via the timetable API
 *
 * @params must have a 'prefix' and a 'term' entry.
 */
function get_fas_info($params)
{
    $term = normalize_term(array_get($params, "term"));

    $url =
        "timetable.iit.artsci.utoronto.ca/api/" .
        $term .
        "/courses?org=&code=" .
        $params['prefix'];

    // fetch the data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $info = curl_exec($ch);
    curl_close($ch);

    // convert to PHP array
    $info = json_decode($info, true);

    // create the return arrray
    $ret = ["TYPE" => "course_pairings", "DATA" => ["association_list" => []]];

    foreach ($info as $course_name => $data) {
        $course = [
            "course" => normalize_course_name($course_name),
            "title" => $data["courseTitle"],
            "department_name" => $data["org"],
            "term" => $data["session"] . $data["section"]
        ];
        foreach ($data['meetings'] as $section => $sec_info) {
            $ret["DATA"]["association_list"][] = [
                "course" => $course,
                "section" =>
                    [
                        "term" => $data["session"] . $data["section"],
                        "section_code" => $section
                    ]
            ];
        }
    }

    return $ret;
}

/**
 * Take FAS full course code (e.g., "APM346H1-Y-20185")
 * and turn it into a short course code (e.g. "APM346").
 *
 * @name str for course code
 */
function normalize_course_name($name)
{
    return substr($name, 0, 6);
}
