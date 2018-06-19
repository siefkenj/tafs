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
 * Returns sql query that returns an unordered list of questions.
 *
 * @return String A sql command that returns an unorderedlist of questions.
 */
function gen_query_user_role()
{
    return "SELECT CASE WHEN is_admin = 1 THEN 'TRUE' ELSE 'FALSE' END AS is_admin,
    CASE WHEN is_instructor = 1 THEN 'TRUE' ELSE 'FALSE' END AS is_instructor,
    CASE WHEN is_ta = 1 THEN 'TRUE' ELSE 'FALSE' END AS is_ta
    FROM users WHERE user_id = :user_id;";
}

/**
 * Returns sql query that returns an unordered list of questions.
 *
 * @return String A sql command that returns an unorderedlist of questions.
 */
function gen_query_survey_responses()
{
    return "SELECT question_id, GROUP_CONCAT(answer) as answers, survey_instance_id
    FROM responses
    WHERE survey_instance_id = :survey_id
    GROUP BY question_id;";
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
        "ON t1.section_id=t2.section_id;";

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

/**
 * Returns sql query to get data for survey results.
 * If no survey is select, then it returns all the results available for the
 * selected ta.
 *
 * @return String A sql command to get the requested data
 */
function gen_query_surveys($role, $survey_id, $result, $term, $course)
{
    //specifications for courses and term
    $specify_course = "";
    if ($course) {
        $specify_course = "WHERE course_code=:course_code";
    }
    $specify_term = "";
    if ($term) {
        $specify_term = "WHERE surveys.term=:term";
    }
    $courses = "SELECT user_id FROM user_associations $specify_course";

    //if no specific survey_id is selected
    if (!$survey_id) {
        $user_to_survey = "";
        // get associated survey to user based on their role
        if ($role["is_admin"] == "TRUE") {
            $user_to_survey = "SELECT users.user_id,survey_id, choices_id
             FROM users JOIN dept_survey_choices JOIN ($courses) c
             ON users.user_id = dept_survey_choices.user_id
             AND c.user_id = users.user_id
             WHERE users.user_id = :user_id";
        } elseif ($role["is_instructor"] == "TRUE") {
            if ($course) {
                $specify_course = "AND course_code=:course_code";
            }
            $user_to_survey = "SELECT users.user_id, survey_id, choices_id
             FROM users JOIN course_survey_choices
             ON users.user_id = course_survey_choices.user_id
             WHERE users.user_id = :user_id $specify_course";
        } elseif ($role["is_ta"] == "TRUE") {
            $user_to_survey = "SELECT users.user_id, survey_id, choices_id, section_id
             FROM users JOIN ta_survey_choices JOIN ($courses) c
             ON users.user_id = ta_survey_choices.user_id
             AND c.user_id = users.user_id
             WHERE users.user_id = :user_id";
        } else {
            $error = "invalid role";
            throw new Exception($error);
        }

        //return list of surveys
        $survey_package = "SELECT DISTINCT user_to_survey.survey_id, surveys.name
                             FROM ($user_to_survey) user_to_survey JOIN surveys
                             ON user_to_survey.survey_id = surveys.survey_id
                             $specify_term";
        //if survey_results are requested
        if ($result) {
            //get survey_instances
            $survey_package = "SELECT DISTINCT survey_instances.survey_instance_id, surveys.name
                             FROM ($user_to_survey) user_to_survey JOIN (surveys,survey_instances)
                             ON user_to_survey.survey_id = surveys.survey_id
                             AND survey_instances.survey_id = surveys.survey_id
                             $specify_term";
        }
        return $survey_package;
    }

    //relate surveys to eachother with the parent attribute
    $survey_relationships = "SELECT surveys.survey_id as ta_survey_id,
     t2.survey_id as course_survey_id, t3.survey_id as dept_survey_id
     FROM surveys JOIN (surveys as t2, surveys as t3)
     ON surveys.parent_survey = t2.survey_id AND t2.parent_survey = t3.survey_id
     $specify_term";

    //get the locked columns required by each role
    $locked_questions = "";
    $locked_columns = "course_number_locked,dept_number_locked";
    if ($role["is_admin"] == "TRUE") {
        $locked_questions = "SELECT DISTINCT dept_survey_id as survey_id, choices_id,
        dept_survey_choices.number_locked as dept_number_locked
          FROM ($survey_relationships) survey_relationships JOIN dept_survey_choices
          ON dept_survey_id = dept_survey_choices.survey_id
          WHERE dept_survey_id = :survey_id AND dept_survey_choices.user_id = :user_id";
        $locked_columns = "dept_number_locked";
    } elseif ($role["is_instructor"] == "TRUE") {
        $locked_questions = "SELECT DISTINCT course_survey_id as survey_id,
        course_survey_choices.choices_id, course_survey_choices.number_locked as
        course_number_locked, dept_survey_choices.number_locked as dept_number_locked
          FROM ($survey_relationships) survey_relationships JOIN (course_survey_choices,dept_survey_choices)
          ON course_survey_id = course_survey_choices.survey_id AND dept_survey_id = dept_survey_choices.survey_id
          WHERE course_survey_id = :survey_id AND course_survey_choices.user_id = :user_id";
    } elseif ($role["is_ta"] == "TRUE") {
        $locked_questions = "SELECT ta_survey_id as survey_id, ta_survey_choices.choices_id,
        course_survey_choices.number_locked as course_number_locked, dept_survey_choices.number_locked as dept_number_locked
          FROM ($survey_relationships) survey_relationships JOIN (ta_survey_choices, course_survey_choices,dept_survey_choices)
          ON course_survey_id = course_survey_choices.survey_id AND dept_survey_id = dept_survey_choices.survey_id
          WHERE ta_survey_id = :survey_id AND ta_survey_choices.user_id = :user_id";
    } else {
        $error = "invalid role";
        throw new Exception($error);
    }

    // common columns for survey and survey packages
    $question_columns =
        " choice1, choice2, choice3, choice4,
     choice5, choice6, surveys.default_survey_open as start_time, surveys.default_survey_close as end_time ";

    // return survey info and question choices of a particular survey
    $survey_package = "SELECT surveys.survey_id, surveys.name, $locked_columns, $question_columns
     FROM ($locked_questions) locked_questions JOIN (choices,surveys)
     ON locked_questions.choices_id = choices.choices_id AND surveys.survey_id = locked_questions.survey_id;";

    //specified course
    $course = "SELECT user_association_id FROM user_associations $specify_course";

    //if survey_results are requested
    if ($result) {
        //get survey_instances
        $available_survey_instance = "SELECT survey_instances.survey_instance_id,
         surveys.name, choices.choices_id, user_association_id, $question_columns
         FROM survey_instances JOIN (choices,surveys)
         ON survey_instances.choices_id = choices.choices_id
         AND surveys.survey_id = survey_instances.survey_id
         WHERE survey_instance_id = :survey_id";

        // Join with specified term and course if provided and user
        $term = "SELECT survey_id FROM surveys $specify_term ";
        $course = "SELECT user_association_id FROM user_associations $specify_course";
        $user = "SELECT t1.user_association_id FROM user_associations JOIN ($course) t1
         ON user_associations.user_association_id = t1.user_association_id WHERE user_id = :user_id";

        $survey_package = "SELECT * FROM ($available_survey_instance) t1 JOIN ($user) t2 JOIN ($term) t3
         ON t1.user_association_id = t2.user_association_id AND t3.survey_id = t1.survey_instance_id ;";
    }
    return $survey_package;
}
