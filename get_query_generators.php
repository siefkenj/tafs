<?php
/**
 * This file contains functions that returns sql queries for GET requests
 * according to the API documentation.
 */

/**
 * Returns sql query that returns an unordered list of questions.
 *
 * @return String A sql command that returns an unorderedlist of questions.
 */
function gen_query_questions()
{
    return "SELECT question_id, answer_type, content FROM questions;";
}

/**
 * Returns sql query that returns an unordered list of tas or instructors.
 * If $ta_list is true, a list of tas is returned. If $ta_list is false,
 * the returned list depends on column parameter in the url.
 *
 * For what=tas, term parameter sepcifies the term returned and course_code
 * sepcifies the course. term and course_code parameters are optional.
 *
 * For what=course_pairings, column parameter is required. term and course_code
 * are optional parameters.
 *
 * @param ta_list True if function call if for list of TAs
 * @return String A sql command that returns an unorderedlist of questions.
 */
function gen_query_course_associations($course_code, $term, $is_ta)
{
    $specify_course = "";
    if ($course_code) {
        $specify_course = "WHERE course_code=:course_code";
    }

    $specify_term = "";
    if ($term) {
        $specify_term = "WHERE sections.term=:term";
    }

    // Getting all users of the specified column type
    if ($is_ta) {
        $instructor_list =
            "SELECT users.user_id, users.name FROM users " .
            "WHERE users.is_ta=1";
    } else {
        $instructor_list =
            "SELECT users.user_id, users.name FROM users " .
            "WHERE users.is_instructor=1";
    }

    // Getting all courses for the specified courses
    $courses =
        "SELECT courses.course_code, courses.title, courses.department_name " .
        "FROM courses $specify_course";

    // Getting all sections from the specified term
    $sections =
        "SELECT sections.section_id, sections.term, sections.section_code " .
        "FROM sections $specify_term";

    // Get user associations for user_id
    $user_courses =
        "SELECT user_associations.user_id, user_associations.course_code, " .
        "user_associations.section_id " .
        "FROM user_associations WHERE user_associations.user_id=:user_id";

    // Getting limiting courses from user_id based on sections
    $section_filted_courses =
        "SELECT t1.user_id, t1.course_code, t2.term, t2.section_code " .
        "FROM ($user_courses) t1 JOIN ($sections) t2 ON t1.section_id=t2.section_id";

    // Getting course information for the courses the user is authorized to see
    $authorized_courses =
        "SELECT t1.course_code, t1.title, t1.department_name, " .
        "t2.user_id, t2.term, t2.section_code " .
        "FROM ($courses) t1 JOIN ($section_filted_courses) t2 " .
        "ON t1.course_code=t2.course_code";

    // Getting all user associations for the courses associated with user_id
    $all_users_for_courses =
        "SELECT user_associations.user_id, " .
        "user_associations.course_code, user_associations.section_id, t1.department_name " .
        "FROM user_associations JOIN ($authorized_courses) t1 " .
        "ON t1.course_code=user_associations.course_code";

    // Get all user-types for courses from previous query
    $instructor_for_courses =
        "SELECT t1.user_id, t1.course_code, t1.section_id, t2.name, t1.department_name " .
        "FROM ($all_users_for_courses) t1 JOIN ($instructor_list) t2 ON t1.user_id=t2.user_id";

    // Limiting results based on specified section
    $return_query =
        "SELECT DISTINCT t1.user_id, t1.course_code, t1.name, t2.term, t2.section_code, " .
        "t1.department_name FROM ($instructor_for_courses) t1 JOIN ($sections) t2 " .
        "ON t1.section_id=t2.section_id";

    return $return_query;
}

/**
 * Returns sql query to get user data. If include_photo is set to FALSE
 * then photos are not returned. In any other case, photos are returned.
 *
 * @return String A sql command that returns unordered list of user data
 */
function gen_query_user_info($include_photo, $list_of_users)
{
    $photo = "";
    if ($include_photo) {
        $photo = ", users.photo";
    }

    if ($list_of_users) {
        return "SELECT users.user_id, users.name$photo FROM users WHERE FIND_IN_SET(users.user_id, :user_id);";
    }
    return "SELECT users.user_id, users.name$photo FROM users WHERE users.user_id=:user_id;";
}
