DROP DATABASE IF EXISTS `t_tafs`;
CREATE DATABASE IF NOT EXISTS `t_tafs` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `t_tafs`;
GRANT USAGE ON *.* TO 'test'@'localhost';
DROP USER 'test'@'localhost';
CREATE USER `test`@`localhost` IDENTIFIED BY 'mypassword';
GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP ON t_tafs.* TO `test`@`localhost`;

CREATE TABLE IF NOT EXISTS `users` (
    `user_id` VARCHAR(10) PRIMARY KEY,
    `is_ta` INT,
    `is_instructor` INT,
    `is_admin` INT,
    `name` VARCHAR(50) NOT NULL,
    `photo` VARCHAR(100)
);


CREATE TABLE IF NOT EXISTS `departments` (
    `department_name` VARCHAR(50) PRIMARY KEY
);

CREATE TABLE IF NOT EXISTS `courses` (
    `course_code` VARCHAR(10) PRIMARY KEY,
    `title` VARCHAR(50) NOT NULL,
    `department_name` VARCHAR(50),
    FOREIGN KEY(department_name) REFERENCES departments(department_name)
);


CREATE TABLE IF NOT EXISTS `sections` (
    `section_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `course_code` VARCHAR(10) NOT NULL,
    `term` VARCHAR(10) NOT NULL,
    `meeting_time` DATE,
    `room` VARCHAR(10),
    `section_code` VARCHAR(10) NOT NULL,
    FOREIGN KEY(course_code) REFERENCES courses(course_code)
);

CREATE TABLE IF NOT EXISTS `user_associations` (
    `user_association_id` INT(0) AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(10) NOT NULL,
    `course_code` VARCHAR(10),
    `section_id` INT,
    FOREIGN KEY(user_id) REFERENCES users(user_id),
    FOREIGN KEY(course_code) REFERENCES courses(course_code),
    FOREIGN KEY(section_id) REFERENCES sections(section_id)
);

CREATE TABLE IF NOT EXISTS `questions` (
    `question_id` INT(0) AUTO_INCREMENT PRIMARY KEY,
    `answer_type` ENUM('radiogroup','comment','rating'),
    `content` VARCHAR(2000) NOT NULL
);

CREATE TABLE IF NOT EXISTS `choices` (
    `choices_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `choice1` INT,
    `choice2` INT,
    `choice3` INT,
    `choice4` INT,
    `choice5` INT,
    `choice6` INT,
    FOREIGN KEY(choice1) REFERENCES questions(question_id),
    FOREIGN KEY(choice2) REFERENCES questions(question_id),
    FOREIGN KEY(choice3) REFERENCES questions(question_id),
    FOREIGN KEY(choice4) REFERENCES questions(question_id),
    FOREIGN KEY(choice5) REFERENCES questions(question_id),
    FOREIGN KEY(choice6) REFERENCES questions(question_id)
);

CREATE TABLE IF NOT EXISTS `dept_survey_choices` (
    `id` INT(0) AUTO_INCREMENT PRIMARY KEY,
    `choices_id` INT,
    `department_name` VARCHAR(50),
    `user_id` VARCHAR(10),
    FOREIGN KEY(choices_id) REFERENCES choices(choices_id),
    FOREIGN KEY(department_name) REFERENCES departments(department_name),
    FOREIGN KEY(user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS `course_survey_choices` (
    `id` INT(0) AUTO_INCREMENT PRIMARY KEY,
    `choices_id` INT,
    `course_code` VARCHAR(50),
    `user_id` VARCHAR(10),
    FOREIGN KEY(choices_id) REFERENCES choices(choices_id),
    FOREIGN KEY(course_code) REFERENCES courses(course_code),
    FOREIGN KEY(user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS `ta_survey_choices` (
    `id` INT(0) AUTO_INCREMENT PRIMARY KEY,
    `choices_id` INT,
    `section_id` INT,
    `user_id` VARCHAR(10),
    FOREIGN KEY(choices_id) REFERENCES choices(choices_id),
    FOREIGN KEY(section_id) REFERENCES sections(section_id),
    FOREIGN KEY(user_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS `surveys` (
    `survey_id` INT(0) AUTO_INCREMENT PRIMARY KEY,
    `dept_survey_choice_id` INT,
    `course_survey_choice_id` INT,
    `ta_survey_choice_id` INT,
    `name` VARCHAR(256) NOT NULL,
    `term` VARCHAR(10) NOT NULL,
    `default_survey_open` DATETIME,
    `default_survey_close` DATETIME,
    FOREIGN KEY(dept_survey_choice_id) REFERENCES dept_survey_choices(id),
    FOREIGN KEY(course_survey_choice_id) REFERENCES course_survey_choices(id),
    FOREIGN KEY(ta_survey_choice_id) REFERENCES ta_survey_choices(id)
);


CREATE TABLE IF NOT EXISTS `survey_instances` (
    `survey_instance_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `viewable_by_others` INT,
    `survey_id` INT,
    `choices_id` INT,
    `user_association_id` INT,
    `override_token` VARCHAR(20) NOT NULL,
    `survey_open` DATETIME NOT NULL,
    `survey_close` DATETIME NOT NULL,
    FOREIGN KEY(user_association_id) REFERENCES user_associations(user_association_id),
    FOREIGN KEY(choices_id) REFERENCES choices(choices_id),
    FOREIGN KEY(survey_id) REFERENCES surveys(survey_id)
);

CREATE TABLE IF NOT EXISTS `responses` (
    `response_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `survey_instance_id` INT,
    `question_id` INT,
    `answer` VARCHAR(2000),
    `user_id` VARCHAR(10),
    FOREIGN KEY(survey_instance_id) REFERENCES survey_instances(survey_instance_id),
    FOREIGN KEY(question_id) REFERENCES questions(question_id)
);
