CREATE DATABASE IF NOT EXISTS `ta_feedback` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ta_feedback`;

CREATE TABLE IF NOT EXISTS `ta_feedback`.`users` (
    `utorid` VARCHAR(10) PRIMARY KEY,
    `type` ENUM('admin','prof','ta'),
    `name` VARCHAR(50) NOT NULL,
    `photo` VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`courses` (
    `course_code` VARCHAR(10) PRIMARY KEY,
    `title` VARCHAR(50) NOT NULL,
    `department_name` VARCHAR(50) REFERENCES `department`(`name`)
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`department` (
    `name` VARCHAR(50) PRIMARY KEY
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`sections` (
    `section_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `course_id` VARCHAR(10) REFERENCES `courses`(`course_code`),
    `term` INT NOT NULL,
    `meeting_time` date NOT NULL,
    `room` VARCHAR(10) NOT NULL,
    `section_code` VARCHAR(10) NOT NULL
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`user_association` (
    `id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `user_id` VARCHAR(10) REFERENCES `users`(`utorid`),
    `course_id` VARCHAR(8) REFERENCES `courses`(`course_code`),
    `section_code` VARCHAR(10) REFERENCES `sections`(`section_code`)
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`questions` (
    `question_id` INT(0) AUTO_INCREMENT PRIMARY KEY,
    `answer_type` ENUM('open_ended', 'scale', 'binary'),
    `content` VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`dept_question_choice` (
    `department_id` VARCHAR(50) REFERENCES `department`(`name`),
    `term` INT NOT NULL,
    `question_id` INT REFERENCES `questions`(`question_id`),
    `user_id` VARCHAR(10) REFERENCES `users`(`utorid`),
    `locked` bit,
    `position` INT NOT NULL
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`course_question_choice` (
    `survey_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `question_id` INT REFERENCES `questions`(`question_id`),
    `user_id` VARCHAR(10) REFERENCES `users`(`utorid`),
    `locked` bit,
    `position` INT NOT NULL
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`ta_question_choice` (
    `survey_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `section_id` INT,
    `term` INT NOT NULL,
    `question_id` INT REFERENCES `questions`(`question_id`),
    `user_id` VARCHAR(10) REFERENCES `users`(`utorid`),
    `locked` bit,
    `position` INT NOT NULL
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`surveys` (
    `id` INT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `course_id` VARCHAR(10) REFERENCES `courses`(`course_code`),
    `term` INT NOT NULL,
    `default_time_window` time,
    `default_start_time` date
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`survey_instances` (
    `id` INT PRIMARY KEY,
    `user_association_id` INT REFERENCES `user_association`(`id`),
    `override_token` VARCHAR(20) NOT NULL,
    `time_window` time,
    `start_time` date NOT NULL
);

CREATE TABLE IF NOT EXISTS `ta_feedback`.`response` (
    `response_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `survey_instance_id` INT REFERENCES `survey_instances`(`id`),
    `question_id` INT REFERENCES `questions`(`question_id`),
    `answer` VARCHAR(2000),
    `user_id` VARCHAR(10) REFERENCES `users`(`utorid`)
);
