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
        "survey_close FROM survey_instances WHERE override_token=:override_token"
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
?>
