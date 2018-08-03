DROP DATABASE IF EXISTS `t_tafs`;
CREATE DATABASE IF NOT EXISTS `t_tafs` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `t_tafs`;
GRANT USAGE ON *.* TO 'test'@'localhost';
DROP USER 'test'@'localhost';
CREATE USER `test`@`localhost` IDENTIFIED BY 'mypassword';
GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP ON t_tafs.* TO `test`@`localhost`;