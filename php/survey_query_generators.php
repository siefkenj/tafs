<?php
/**
 * This file contains functions that returns sql queries for student survey
 * requests according to the API documentation.
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
 * Returns sql query that returns a survey instance package.
 *
 * @return String A sql command that returns a survey instance package.
 */
function gen_query_survey_instance()
{
    return (
        "SELECT survey_instance_id, choices_id, survey_id, survey_open, " .
        "survey_close, user_association_id FROM survey_instances WHERE " .
        "override_token=:override_token"
    );
}
/**
 * Returns sql query that returns an unordered list of choices.
 *
 * @return String A sql command that returns an unorderedlist of choices.
 */
function gen_query_choices()
{
    return (
        "SELECT choices_id, choice1, choice2, choice3, choice4, choice5, " .
        "choice6 FROM choices WHERE choices_id=:choices_id"
    );
}
/**
 * Returns sql query that returns the name for a specified survey.
 *
 * @return String A sql command that returns the name for a specified survey.
 */
function gen_query_surveys()
{
    return "SELECT name FROM surveys WHERE survey_id=:survey_id";
}
/**
 * Returns sql query that inserts a response into responses table.
 *
 * @return String A sql command that inserts a response into responses table.
 */
function gen_query_submit_responses()
{
    return (
        "INSERT INTO responses (survey_instance_id, question_id, answer, " .
        "user_id) VALUES (:survey_instance_id, :question_id, :answer, :user_id);"
    );
}
/**
 * Returns user association data for the specified user_association_id
 *
 * @return String A sql command that returns data for specified user_association_id
 */
function gen_query_user_association()
{
    return (
        "SELECT user_id, course_code, section_id FROM user_associations WHERE " .
        "user_association_id=:user_association_id"
    );
}
/**
 * Returns name and photo for specified user_id
 *
 * @return String A sql command that returns name and photo for specified user_id
 */
function gen_query_user_info()
{
    return "SELECT name, photo FROM users WHERE user_id=:user_id";
}
/**
 * Returns section_code for specified section_id
 *
 * @return String A sql command that returns section_code for specified section_id
 */
function gen_query_section()
{
    return "SELECT section_code FROM sections WHERE section_id=:section_id";
}
/**
 * Returns response_id for existing responses submitted by user for a survey_instance
 *
 * @return String A sql command that returns section_code for specified section_id
 */
function gen_query_existing_response()
{
    return "SELECT response_id FROM responses WHERE user_id=:user_id AND survey_instance_id=:survey_instance_id";
}
/**
 * Returns sql command to delete response with specified response_id
 *
 * @return String A sql command that returns section_code for specified section_id
 */
function gen_query_delete_response()
{
    return "DELETE FROM responses WHERE response_id=:response_id";
}
?>
