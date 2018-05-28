# Create Testuser
CREATE USER 'myuser'@'localhost' IDENTIFIED BY 'mypassword';
GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP ON ta_feedback.* TO 'myuser'@'localhost';
