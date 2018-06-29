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


/**
 * This function is for generating SQL statements that can update the survey
 * question choices for one survey
 * @param survey_id:int The id that uniquely distinguishes one survey
 * @param action:string whether the user wants to add/update or delete
 * @param question_array:array An array of question objects that contain attribute like
 *                       position, locked status, etc.
 * @param survey_open:datetime The start time setting of this survey
 * @param survey_close:datetime The end time setting of this survey
 * @return string An SQL statement
 */
function query_update_survey_question (
    $survey_id,
    $action,
    $question_array,
    $survey_open,
    $survey_close
) {

}

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
