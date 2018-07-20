<?php
/*
 * Handle all data in the $_GET and $_POST variables. This
 * module provides the only functions that should access
 * the $_GET and $_POST variables.
 *
 * Any data sent to $_GET or $_POST that starts with "base64" will
 * get base64 decoded automatically.
 */

/**
 * Parse all data from $_GET and $_POST and pack it in
 * an array that gets returned.
 *
 */
function handle_request()
{
    $result_array = [];
    $tmp_array = [];
    // "post_body" should be gotten directly from the post contents first
    // because a "post_body" URL parameter will override this data
    $tmp_array["post_body"] = file_get_contents('php://input');

    // grab all of the URL parameters
    foreach ($_REQUEST as $key => $value) {
        $tmp_array[$key] = $value;
    }

    // decode anything that starts with base64
    foreach ($tmp_array as $key => $value) {
        try {
            if (substr($value, 0, 7) == "base64:") {
                $result_array[$key] = base64_decode(substr($value, 7));
            } else {
                $result_array[$key] = $value;
            }
        } catch (Exception $e) {
            // on a decode error we do nothing
            // and just leave the array how it was
            $result_array[$key] = $value;
        }
    }
    return $result_array;
}
