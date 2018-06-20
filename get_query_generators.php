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
 * Returns sql query that returns an unordered list of sections that can be
 * related to a ta or instructor.
 *
 * @param course_code TRUE if course_code is specified
 * @param term TRUE if term is specified
 * @return String A sql command that returns an unorderedlist of sections.
 */
function gen_query_course_pairings_section($course_code, $term)
{
    $specify_course = "";
    if ($course_code) {
        $specify_course = "WHERE courses.course_code=:course_code";
    }

    $specify_term = "";
    if ($term) {
        $specify_term = "WHERE sections.term=:term";
    }

    $name =
        "SELECT users.name, users.user_id FROM users WHERE user_id=:user_id";

    $department = "SELECT courses.department_name, courses.course_code FROM courses $specify_course";

    $sections =
        "SELECT sections.section_id, sections.course_code, " .
        "sections.term, sections.room, sections.section_code FROM sections $specify_term";

    $ta_associations =
        "SELECT user_associations.section_id " .
        "FROM user_associations WHERE user_associations.user_id=:user_id";

    return (
        "SELECT t1.course_code, t1.term, t1.room, t1.section_code, t3.name, t4.department_name, t3.user_id " .
        "FROM ($sections) t1 JOIN ($ta_associations) t2 ON t1.section_id=t2.section_id " .
        "JOIN ($name) t3 JOIN ($department) t4 ON t4.course_code=t1.course_code;"
    );
}

/**
 * Returns sql query that returns the user_id's type
 *
 * @return String A sql command that returns the user_id's type
 */
function gen_query_user_role()
{
    return "SELECT CASE WHEN is_admin = 1 THEN 'TRUE' ELSE 'FALSE' END AS is_admin,
    CASE WHEN is_instructor = 1 THEN 'TRUE' ELSE 'FALSE' END AS is_instructor,
    CASE WHEN is_ta = 1 THEN 'TRUE' ELSE 'FALSE' END AS is_ta
    FROM users WHERE user_id = :user_id;";
}

/**
 * Returns sql query that returns an unordered list of survey responses
 *
 * @return String A sql command that returns an unordered list of survey responses
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
 * Returns sql query that returns an unordered course and ta pairing if $is_ta
 * is true.
 *
 * If course_code is true, returned pairings will only contain related course.
 * If term is true, returned pairings will only contain related term.
 *
 * @param course_code TRUE if course code is specified
 * @param term TRUE if term is specified
 * @return String A sql command that returns an unorderedlist of course pairings
 */
function gen_query_course_pairings($course_code, $term, $is_ta)
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
 * @param include_photo FALSE to not include photo in return query
 * @param list_of_users TRUE if returning data for list of users
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
 * If no survey_id is select, then it returns all the results available for the
 * selected user.
 *
 * @param role associative array for the role of a user
 * @param list_of_survey TRUE if a specific survey_id or survey_instance_id is specified
 * @param result TRUE if survey_result is requested
 * @param course TRUE if course code is specified
 * @param term TRUE if term is specified
 * @return String A sql command to get the survey package
 */
function gen_query_surveys($role, $list_of_survey, $result, $term, $course)
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
    if (!$list_of_survey) {
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
            $is_associated =
                "SELECT t1.user_id as supervisor_id, t2.user_id as ta_id FROM user_associations as t1 JOIN user_associations as t2
                                ON t1.course_code = t2.course_code AND t1.section_id = t2.section_id
                                WHERE t1.user_id = :user_id AND t2.user_id = :target_ta";

            $user_to_survey = "SELECT users.user_id, survey_id, choices_id, section_id
                                 FROM users JOIN ta_survey_choices JOIN ($courses) c
                                 ON users.user_id = ta_survey_choices.user_id
                                 AND c.user_id = users.user_id
                                 WHERE users.user_id = :target_ta";
            $associated_survey_results = "SELECT user_to_survey.user_id, user_to_survey.survey_id
                                            FROM ($user_to_survey) user_to_survey JOIN ($is_associated) i_s
                                            ON i_s.ta_id = user_to_survey.user_id";
            //get survey_instances
            $survey_package = "SELECT DISTINCT survey_instances.survey_instance_id, surveys.name
                             FROM ($associated_survey_results) a_s_r JOIN (surveys,survey_instances)
                             ON a_s_r.survey_id = surveys.survey_id
                             AND survey_instances.survey_id = surveys.survey_id
                             $specify_term ;";
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
    $locked_columns = "number_locked_by_course,number_locked_by_department";
    if ($role["is_admin"] == "TRUE") {
        $locked_questions = "SELECT DISTINCT dept_survey_id as survey_id, choices_id,
        dept_survey_choices.number_locked as number_locked_by_department
          FROM ($survey_relationships) survey_relationships JOIN dept_survey_choices
          ON dept_survey_id = dept_survey_choices.survey_id
          WHERE dept_survey_id = :survey_id AND dept_survey_choices.user_id = :user_id";
        $locked_columns = "number_locked_by_department";
    } elseif ($role["is_instructor"] == "TRUE") {
        $locked_questions = "SELECT DISTINCT course_survey_id as survey_id,
        course_survey_choices.choices_id, course_survey_choices.number_locked as
        number_locked_by_course, dept_survey_choices.number_locked as number_locked_by_department
          FROM ($survey_relationships) survey_relationships JOIN (course_survey_choices,dept_survey_choices)
          ON course_survey_id = course_survey_choices.survey_id AND dept_survey_id = dept_survey_choices.survey_id
          WHERE course_survey_id = :survey_id AND course_survey_choices.user_id = :user_id";
    } elseif ($role["is_ta"] == "TRUE") {
        $locked_questions = "SELECT ta_survey_id as survey_id, ta_survey_choices.choices_id,
        course_survey_choices.number_locked as number_locked_by_course, dept_survey_choices.number_locked as number_locked_by_department
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
         WHERE FIND_IN_SET(survey_instance_id, :survey_id)";

        $is_associated =
            "SELECT t1.user_id as supervisor_id, t2.user_id as ta_id FROM user_associations as t1 JOIN user_associations as t2
                             ON t1.course_code = t2.course_code AND t1.section_id = t2.section_id
                             WHERE t1.user_id = :user_id AND t2.user_id = :target_ta";
        // Join with specified term and course if provided and user
        $term = "SELECT survey_id FROM surveys $specify_term ";
        $course = "SELECT user_association_id FROM user_associations $specify_course";
        $user = "SELECT DISTINCT t1.user_association_id FROM user_associations JOIN ($course) t1 JOIN ($is_associated) i_s
         ON user_associations.user_association_id = t1.user_association_id
         AND user_associations.user_id = i_s.ta_id";

        $survey_package = "SELECT * FROM ($available_survey_instance) t1 JOIN ($user) t2 JOIN ($term) t3
         ON t1.user_association_id = t2.user_association_id AND t3.survey_id = t1.survey_instance_id ;";
    }
    return $survey_package;
}
