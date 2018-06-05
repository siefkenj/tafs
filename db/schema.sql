CREATE DATABASE IF NOT EXISTS `tafs` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `tafs`;

CREATE TABLE IF NOT EXISTS `users` (
    `user_id` VARCHAR(10) PRIMARY KEY,
    `type` ENUM('admin','prof','ta'),
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
    `course_code` VARCHAR(10),
    `term` INT NOT NULL,
    `meeting_time` date NOT NULL,
    `room` VARCHAR(10) NOT NULL,
    `section_code` VARCHAR(10) NOT NULL,
    FOREIGN KEY(course_code) REFERENCES courses(course_code)
);

CREATE TABLE IF NOT EXISTS `user_associations` (
    `user_association_id` INT(0) AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(10),
    `course_code` VARCHAR(10),
    `section_id` INT,
    FOREIGN KEY(user_id) REFERENCES users(user_id),
    FOREIGN KEY(course_code) REFERENCES courses(course_code),
    FOREIGN KEY(section_id) REFERENCES sections(section_id)
);

CREATE TABLE IF NOT EXISTS `questions` (
    `question_id` INT(0) AUTO_INCREMENT PRIMARY KEY,
    `answer_type` ENUM('open_ended', 'scale', 'binary'),
    `content` VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS `surveys` (
    `survey_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `course_code` VARCHAR(10),
    `term` INT NOT NULL,
    `default_survey_open` DATETIME,
    `default_survey_close` DATETIME,
    FOREIGN KEY(course_code) REFERENCES courses(course_code)
);


CREATE TABLE IF NOT EXISTS `dept_question_choices` (
    `survey_id` INT,
    `department_name` VARCHAR(50),
    `term` INT NOT NULL,
    `question_id` INT,
    `user_id` VARCHAR(10),
    `locked` bit,
    `position` INT NOT NULL,
    FOREIGN KEY(department_name) REFERENCES departments(department_name),
    FOREIGN KEY(question_id) REFERENCES questions(question_id),
    FOREIGN KEY(user_id) REFERENCES users(user_id),
    FOREIGN KEY(survey_id) REFERENCES surveys(survey_id)
);

CREATE TABLE IF NOT EXISTS `course_question_choices` (
    `survey_id` INT,
    `question_id` INT,
    `user_id` VARCHAR(10),
    `locked` bit,
    `position` INT NOT NULL,
    FOREIGN KEY(question_id) REFERENCES questions(question_id),
    FOREIGN KEY(user_id) REFERENCES users(user_id),
    FOREIGN KEY(survey_id) REFERENCES surveys(survey_id)
);

CREATE TABLE IF NOT EXISTS `ta_question_choices` (
    `survey_id` INT,
    `section_id` INT,
    `term` INT NOT NULL,
    `question_id` INT,
    `user_id` VARCHAR(10),
    `locked` bit,
    `position` INT NOT NULL,
    FOREIGN KEY(section_id) REFERENCES sections(section_id),
    FOREIGN KEY(question_id) REFERENCES questions(question_id),
    FOREIGN KEY(user_id) REFERENCES users(user_id),
    FOREIGN KEY(survey_id) REFERENCES surveys(survey_id)
);


CREATE TABLE IF NOT EXISTS `survey_instances` (
    `survey_instance_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `survey_id` INT,
    `user_association_id` INT,
    `override_token` VARCHAR(20) NOT NULL,
    `survey_open` DATETIME NOT NULL,
    `survey_close` DATETIME NOT NULL,
    FOREIGN KEY(user_association_id) REFERENCES user_associations(user_association_id),
    FOREIGN KEY(survey_id) REFERENCES surveys(survey_id)
);

CREATE TABLE IF NOT EXISTS `responses` (
    `response_id` INT(0) PRIMARY KEY AUTO_INCREMENT,
    `survey_instance_id` INT,
    `question_id` INT,
    `answer` VARCHAR(2000),
    `user_id` VARCHAR(10),
    FOREIGN KEY(survey_instance_id) REFERENCES survey_instances(survey_instance_id),
    FOREIGN KEY(question_id) REFERENCES questions(question_id),
    FOREIGN KEY(user_id) REFERENCES users(user_id)
);
