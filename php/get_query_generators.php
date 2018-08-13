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
        "SELECT t1.section_id, t1.course_code, t1.term, t1.room, t1.section_code, " .
        "t3.name, t4.department_name, t3.user_id " .
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
    return "SELECT question_id, GROUP_CONCAT(answer) as answers
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
        "SELECT t1.user_id, t1.course_code, t2.term, t2.section_code, t2.section_id " .
        "FROM ($user_courses) t1 JOIN ($sections) t2 ON t1.section_id=t2.section_id";

    // Getting course information for the courses the user is authorized to see
    $authorized_courses =
        "SELECT t1.course_code, t1.title, t1.department_name, t2.section_id," .
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
        "SELECT DISTINCT t1.user_id, t1.section_id, t1.course_code, t1.name, t2.term, t2.section_code, " .
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
 * @param result TRUE if survey_result is requested
 * @param course TRUE if course code is specified
 * @param term TRUE if term is specified
 * @return String A sql command to get the survey package
 */
function gen_query_surveys($role, $term, $course, $result)
{
    //specifications for courses and term
    $specification = "";
    if ($course && $term) {
        $specification = "WHERE course_code=:course_code AND term=:term";
    } elseif ($term) {
        $specification = "WHERE term=:term";
    } elseif ($course) {
        $specification = "WHERE course_code=:course_code";
    }

    // get associated survey to user based on their role
    if ($role["is_admin"] == "TRUE") {
        $user_survey = "dept_survey_choices";
        $user_choice_id = "dept_survey_choice_id";
    } elseif ($role["is_instructor"] == "TRUE") {
        $user_survey = "course_survey_choices";
        $user_choice_id = "course_survey_choice_id";
    } elseif ($role["is_ta"] == "TRUE") {
        $user_survey = "ta_survey_choices";
        $user_choice_id = "ta_survey_choice_id";
    } else {
        $error = "invalid role";
        throw new Exception($error);
    }
    if ($result) {
        //request for list of survey results
        return gen_query_list_of_survey_instances($specification);
    }
    return gen_query_list_of_surveys(
        $user_survey,
        $user_choice_id,
        $specification
    );
}

/**
 * Returns sql query that returnes a list of surveys instances of a particular user
 *
 * @param specification specification of term and course in a WHERE clause
 * @return String A sql command to get all surveys instance related to user
 */
function gen_query_list_of_survey_instances($specification)
{
    //get all courses associated with user
    $user_relations =
        "SELECT DISTINCT course_code as course FROM user_associations WHERE user_id = :user_id";

    // All survey instances of target_ta that are related to user
    $survey_package = "SELECT DISTINCT survey_instance_id, surveys.name, user_associations.user_association_id ,user_associations.user_id FROM user_associations
    JOIN (survey_instances,surveys, ($user_relations) u)
    ON survey_instances.user_association_id = user_associations.user_association_id
    AND surveys.survey_id = survey_instances.survey_id
    AND u.course = user_associations.course_code
    $specification
    HAVING user_id = :target_ta";
    return "SELECT survey_instance_id, name FROM ($survey_package) a";
}

/**
 * Returns sql query that returnes a list of surveys from a particular user
 *
 * @param user_survey the survey_choice of a user
 * @param user_choice_id the choice_id of a user
 * @param specification specification of term and course in a WHERE clause
 * @return String A sql command to get all surveys related to user
 */
function gen_query_list_of_surveys(
    $user_survey,
    $user_choice_id,
    $specification
) {
    //return list of surveys
    $survey_package = "SELECT DISTINCT survey_id, surveys.name, user_associations.user_id FROM user_associations
                        JOIN (surveys, $user_survey)
                        ON $user_survey.user_id = user_associations.user_id
                        AND $user_survey.id = surveys.$user_choice_id
                        $specification
                        HAVING user_id = :user_id";

    return "SELECT DISTINCT survey_id, name FROM ($survey_package) a";
}

/**
 * Returns sql query that gets list of choices for a survey based on user role,
 * also provides the parent survey_id of the given survey
 *
 *
 */
function gen_query_get_survey_data()
{
    //Get the column of a survey based on the $role
    $survey = "SELECT DISTINCT survey_id, name, default_survey_open as timedate_open, default_survey_close as timedate_close
    FROM surveys
    WHERE FIND_IN_SET(survey_id,:survey_id);";
    return $survey;
}
/**
 * Returns sql query that switches the department_survey_choice_id with the id
 * from the choices table
 *
 */
function gen_query_get_survey_choices()
{
    //translate the 3 choice id into actual choices id in choice table
    $survey = "SELECT DISTINCT dsc.choices_id as dept_choices_id,
    csc.choices_id as course_choices_id,
    tsc.choices_id as ta_choices_id
    FROM surveys LEFT JOIN dept_survey_choices as dsc
    ON dsc.id = surveys.dept_survey_choice_id
    LEFT JOIN course_survey_choices as csc
    ON csc.id = surveys.course_survey_choice_id
    LEFT JOIN ta_survey_choices as tsc
    ON tsc.id = surveys.ta_survey_choice_id
    WHERE survey_id = :survey_id;";
    return $survey;
}

/**
 * Returns sql query to get data for choices
 */
function gen_query_get_choices()
{
    //Get the choices based on $choice
    $choices = "SELECT *
    FROM choices
    WHERE choices_id = :choices_id;";

    return $choices;
}
