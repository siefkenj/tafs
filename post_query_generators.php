<?php
/*
 * This file contains functions that return specific POST SQL queries
 * to be used for operating the database.
 */

/**
 * This function is for generating SQL statements that can update the user info
 * @param action:string add_or_update/delete
 * @param user_id:array The id of a user
 * @param name:string The name of the user
 * @param photo:string|null The photo of the user
 * @return string An SQL statement
 */
function query_update_user_info (
    $action,
    $user_id,
    $name,
    $photo
) {
    if ($photo == null) {
        $photo = "NULL";
    }
    // If the user wants to delete a specific instance
    if ($action == "delete") {
        return "DELETE FROM users WHERE user_id = '" . $user_id . "';";
    }
    // If the user wants to add or update
    else {
        $sql = "INSERT INTO users (user_id, name, photo) VALUES ('" . $user_id .
            "', '" . $name . "', '" . $photo . "') ON DUPLICATE KEY UPDATE name = '" .
            $name . "', photo = '" . $photo . "';";
        return $sql;
    }
}

/* ------------------------------------------------------------------------------------- */

/**
 * This function is for generating SQL statements that can update the survey
 * question choices for one survey
 * @param user_id:string The utorid of the user who wants to perform this operation
 * @param level:string The level can be "dept", "course", "section".
 * @param survey_id:int The id that uniquely distinguishes one survey
 * @param action:string whether the user wants to add/update or delete
 * @param dept_survey_choices:object object that contain department_name and question choices
 * @param course_survey_choices:object object that contain course_code and question choices
 * @param ta_survey_choices:object object that contain section_id and question choices
 * @param term:int
 * @param name:string The name of the survey
 * @return list A list of SQL statements
 */
function query_update_survey_question (
    $user_id,
    $level,
    $survey_id,
    $action,
    $dept_survey_choices,
    $course_survey_choices,
    $ta_survey_choices,
    $term,
    $name
) {
    // If it is the first time the user sets this survey, a new survey will be branched
    if ($action == "branch") {
        $sql_array = array();
        $sql_choice = "INSERT INTO choices (choice1, choice2, choice3, choice4, choice5, choice6) VALUES (";
        switch ($level) {
            case "dept":
                $sql_choice .=
                    $dept_survey_choices['choices'][0] . ", " . $dept_survey_choices['choices'][1] . ", " .
                    $dept_survey_choices['choices'][2] . ", " . $dept_survey_choices['choices'][3] . ", " .
                    $dept_survey_choices['choices'][4] . ", " . $dept_survey_choices['choices'][5] . ");";
                break;
            case "course":
                $sql_choice .=
                    $dept_survey_choices['choices'][0] . ", " . $dept_survey_choices['choices'][1] . ", " .
                    $dept_survey_choices['choices'][2] . ", " . $dept_survey_choices['choices'][3] . ", " .
                    $dept_survey_choices['choices'][4] . ", " . $dept_survey_choices['choices'][5] . ");";
                break;
            case "section":
                $sql_choice .=
                    $dept_survey_choices['choices'][0] . ", " . $dept_survey_choices['choices'][1] . ", " .
                    $dept_survey_choices['choices'][2] . ", " . $dept_survey_choices['choices'][3] . ", " .
                    $dept_survey_choices['choices'][4] . ", " . $dept_survey_choices['choices'][5] . ");";
                break;

            $sql_choice_id = "SELECT LAST_INSERT_ID() FROM choices;";
            array_push($sql_array, $sql_choice, $sql_choice_id);
            return $sql_array;
        }
    }
    // If the user wants to reset the survey he/she already sets, then an update
    // operation will be performed
    elseif ($action == "add_or_update") {
        $sql_array = array();
        $sql_update_survey = "UPDATE surveys ON name = \'" . $name . "\', term = " .
            $term . ", default_survey_open = \'" . $default_survey_open . "\', default_survey_close = \'" .
            $default_survey_close . "\' WHERE survey_id = " . $survey_id . ";";
        array_push($sql_array, $sql_update_survey);
        switch ($level) {
            case "dept":
                $sql_survey_choice_id = "SELECT dept_survey_choices FROM surveys WHERE survey_id = " . $survey_id . ";";
                break;
            case "course":
                $sql_survey_choice_id = "SELECT course_survey_choices FROM surveys WHERE survey_id = " . $survey_id . ";";
                break;
            case "section":
                $sql_survey_choice_id = "SELECT ta_survey_choices FROM surveys WHERE survey_id = " . $survey_id . ";";
                break;
        }
        array_push($sql_array, $sql_survey_choice_id);
        return $sql_array;
    }
    // If the user wants to delete a survey, return an sql statement that deletes that survey and
    // survey instance generated by that survey
    else {
        return "DELETE FROM surveys WHERE survey_id = \'" . $survey_id . "\'; " .
            "DELETE FROM survey_instances WHERE survey_id = \'" . $survey_id . "\';";
    }
}

/**
 * Return an SQL statement that will ge back the id of dept_survey_choices,
 * course_survey_choices and ta_survey_choices for use when branching a new survey
 * @param survey_id:int The id of the survey
 */
function query_get_survey_choice ($survey_id) {
    return "SELECT dept_survey_choices, course_survey_choices, ta_survey_choices FROM surveys " .
        "WHERE survey_id = " . $survey_id . ";";
}


/**
 * This function is being called when we want to create a new dept/course/ta_survey_choices
 * instance and get its id back
 * @param choice_id:int the newly inserted choice
 * @param code:int|string It can be department_name, course_code and section_id
 * @param level:string It can be dept, course, section
 * @param user_id:string The utorid of the user
 */
function query_set_survey_choice (
    $choice_id,
    $code,
    $level,
    $user_id
) {
    $sql_array = array();
    switch ($level) {
        case "dept":
            $sql_survey_choice =
                "INSERT INTO dept_survey_choices (choices_id, department_name, user_id) VALUES (" .
                $choice_id . ", \'" . $code . "\', \'" . $user_id . "\');";
            $sql_survey_choice_id = "SELECT LAST_INSERT_ID() FROM dept_survey_choices;";
            break;
        case "course":
            $sql_survey_choice =
                "INSERT INTO course_survey_choices (choices_id, course_code, user_id) VALUES (" .
                $choice_id . ", \'" . $code . "\', \'" . $user_id . "\');";
            $sql_survey_choice_id = "SELECT LAST_INSERT_ID() FROM course_survey_choices;";
            break;
        case "section":
            $sql_survey_choice =
                "INSERT INTO ta_survey_choices (choices_id, section_id, user_id) VALUES (" .
                $choice_id . ", \'" . $code . "\', \'" . $user_id . "\');";
            $sql_survey_choice_id = "SELECT LAST_INSERT_ID() FROM ta_survey_choices;";
            break;
    }
    array_push($sql_array, $sql_survey_choice, $sql_survey_choice_id);
    return $sql_array;
}


/**
 * This function will return an SQL statement that set up a new survey
 * @param dept_survey_choice_id:int The dept_survey_choices in the original survey
 * @param course_survey_choice_id:int The course_survey_choices in the original survey
 * @param ta_survey_choice_id:int The ta_survey_choices in the original survey
 * @param survey_choice_id:int The newly set up survey choice instance
 * @param name:string The name of the new survey
 * @param term:int The term
 * @param level:int ENUM[dept, course, section]
 * @param default_survey_open:datetime The date and time that the survey will happen
 * @param default_survey_close:datetime The date and time that the survey will close
 */
function query_set_new_survey (
    $dept_survey_choice_id,
    $course_survey_choice_id,
    $ta_survey_choice_id,
    $survey_choice_id,
    $name,
    $term,
    $default_survey_open,
    $default_survey_close,
    $level
) {
    switch ($level) {
        case "dept":
            return "INSERT INTO surveys (dept_survey_choices, course_survey_choices, ta_survey_choices, name, term, default_survey_open, default_survey_close) VALUES (" .
                $survey_choice_id . ", " . $course_survey_choice_id . ", " . $ta_survey_choice_id . ", '" . $name . "', " . $term . ", '" .
                $default_survey_open . "', '" . $default_survey_close . "');";
             break;
        case "course":
            return "INSERT INTO surveys (dept_survey_choices, course_survey_choices, ta_survey_choices, name, term, default_survey_open, default_survey_close) VALUES (" .
                $dept_survey_choice_id . ", " . $survey_choice_id . ", " . $ta_survey_choice_id . ", '" . $name . "', " . $term . ", '" .
                $default_survey_open . "', '" . $default_survey_close . "');";
            break;
        case "section":
            return "INSERT INTO surveys (dept_survey_choices, course_survey_choices, ta_survey_choices, name, term, default_survey_open, default_survey_close) VALUES (" .
                $dept_survey_choice_id . ", " . $course_survey_choice_id . ", " . $survey_choice_id . ", '" . $name . "', " . $term . ", '" .
                $default_survey_open . "', '" . $default_survey_close . "');";
            break,
    }
}

/**
 * This function is for returning an SQL statement which will get the choice_id back
 * from the "survey_choice" table given the survey_choice_id
 * @param survey_choice_id:int The id of dept/course/ta_survey_choices
 * @param level:string dept/course/section
 * @return string: An SQL statement
 */
function query_get_choice_id (
    $survey_choice_id,
    $level
) {
    switch ($level) {
        case 'dept':
            return "SELECT choices_id FROM dept_survey_choices WHERE id = " .
                $survey_choice_id . ";";
            break;

        case 'course':
            return "SELECT choices_id FROM course_survey_choices WHERE id = " .
                $survey_choice_id . ";";
            break;

        default:
            return "SELECT choices_id FROM ta_survey_choices WHERE id = " .
                $survey_choice_id . ";";
            break;
    }
}

/**
 * This function is for returning an SQL statement which update the choice instance
 * @param choices_id:int The choice instance that the user wants to update
 * @param level:string dept/course/section
 * @param dept_survey_choices:object contains the new choice object
 * @param course_survey_choices:object contains the new choice object
 * @param ta_survey_choices: object contains the new choice obejct
 */
function query_update_choices (
    $choices_id,
    $level,
    $dept_survey_choices,
    $course_survey_choices,
    $ta_survey_choices
) {
    switch ($level) {
        case "dept":
            return "UPDATE choices ON choice1 = " .
                $dept_survey_choices['choices'][0] . ", " .
                "choice2 = " . $dept_survey_choices['choices'][1] . ", " .
                "choice3 = " . $dept_survey_choices['choices'][2] . ", " .
                "choice4 = " . $dept_survey_choices['choices'][3] . ", " .
                "choice5 = " . $dept_survey_choices['choices'][4] . ", " .
                "choice6 = " . $dept_survey_choices['choices'][5] .
                " WHERE choices_id = " . $choices_id . ";";
            break;
        case "dept":
            return "UPDATE choices ON choice1 = " .
                $course_survey_choices['choices'][0] . ", " .
                "choice2 = " . $course_survey_choices['choices'][1] . ", " .
                "choice3 = " . $course_survey_choices['choices'][2] . ", " .
                "choice4 = " . $course_survey_choices['choices'][3] . ", " .
                "choice5 = " . $course_survey_choices['choices'][4] . ", " .
                "choice6 = " . $course_survey_choices['choices'][5] .
                " WHERE choices_id = " . $choices_id . ";";
            break;
        case "dept":
            return "UPDATE choices ON choice1 = " .
                $ta_survey_choices['choices'][0] . ", " .
                "choice2 = " . $ta_survey_choices['choices'][1] . ", " .
                "choice3 = " . $ta_survey_choices['choices'][2] . ", " .
                "choice4 = " . $ta_survey_choices['choices'][3] . ", " .
                "choice5 = " . $ta_survey_choices['choices'][4] . ", " .
                "choice6 = " . $ta_survey_choices['choices'][5] .
                " WHERE choices_id = " . $choices_id . ";";
            break;
    }
}

/* ----------------------------------------------------------------------------------- */

/**
 * This function is for generating SQL statements that can update the profs/tas for
 * one particular course or section
 * @param action:string whether the user wants to add/update or delete
 * @param course_code:string the course_code of a course
 * @param section_id:string the id of a section
 * @param user_id:string the prof/ta tied with that section
 * @return string An SQL statement
 */
function query_update_user_association (
    $action,
    $course_code,
    $section_id,
    $user_id
) {
    // If the user wants to add an user association
    if ($action == "add_or_update") {
        return "INSERT INTO user_associations (user_id, course_code, section_id) VALUES ('" .
            $user_id . "', '" . $course_code . "', '" . $section_id . "');";
    }
    // If the user wants to delete one user association
    else {
        return "DELETE FROM user_associations WHERE user_id = '" . $user_id .
            "' AND course_code = '" . $course_code . "' AND section_id = '" . $section_id . "';";
    }
}

/* ------------------------------------------------------------------------------------------- */

/**
 * This function is for generating SQL statements that can update courses or
 * sections for a specific term
 * @param action:string whether the user wants to add/update or delete
 * @param course:object including course_code, department_name, term
 * @param section:object including term, section_code, section_id
 * @param user_id:string The id of the user
 * @return string An SQL statement
 */
function query_update_courses_sections (
    $action,
    $course,
    $section,
    $user_id
) {
    /* If the user wants to add or update one course/section.*/
    if ($action == "add_or_update") {
        // 1. Insert otherwise update the courses table with this course object
        $sql_course = "INSERT INTO courses VALUES ('" . $course->course_code . "', '" .
            $course->title . "', '" . $course->department_name . "') ON DUPLICATE KEY UPDATE title = '" .
            $course->title . "', department_name = '" . $course->department_name . "';";
        $sql_section = '';
        // 2. Insert otherwise update the sections table with this section object
        if ($section->section_id == null) {
            // Insert if the section id is null
            $sql_section = "INSERT INTO sections (course_code, term, section_code) VALUES ('" .
                $course->course_code . "', '" . $section->term . "', '" . $section->section_code . "');";
        } else {
            // Update if it has a specific section id
            $sql_section = "UPDATE sections SET course_code = '" . $course->course_code . "', term = '" .
                $section->term . "', section_code = '" . $section->section_code . "' WHERE section_id = '" .
                $section->section_id . "';";
        }
        return $sql_course . $sql_section;
    }
    /* If the user wants to delete one course/section. */
    else {
        /* If the user wants to delete one section under a specific course. */
        if ($section != null) {
            // 1. Delete the section instances with this specific "section_id"
            $sql_section = "DELETE FROM sections WHERE section_id = '" . $section->section_id . "';";
            // 2. Delete the user associations with this specific "section_id"
            $sql_user_association = "DELETE FROM user_associations WHERE section_id = '" .
                $section->section_id . "';";
            return $sql_section . $sql_user_association;
        }
        /* If the user wants to delete a course with all the sections of it. */
        else {
            // 1. Delete the course with "course_code" from the courses table
            $sql_course = "DELETE FROM courses WHERE course_code = '" . $course->course_code . "';";
            // 2. Delete the section with "course_code" from the sections table
            $sql_section = "DELETE FROM sections WHERE course_code = '" . $course->course_code . "';";
            // 3. Delete the user association with "course_code" from the user_associations table
            $sql_user_association = "DELETE FROM user_associations WHERE course_code = '" . $course->course_code . "';";
            return $sql_course . $sql_section . $sql_user_association;
        }
    }
}
?>
