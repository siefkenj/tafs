INSERT INTO users VALUES('abcd1000', 'admin', 'abcd0000', null);
INSERT INTO users VALUES('abcd1001', 'admin', 'abcd0001', null);
INSERT INTO users VALUES('abcd1002', 'admin', 'abcd0002', null);
INSERT INTO users VALUES('abcd1003', 'admin', 'abcd0003', null);
INSERT INTO users VALUES('abcd1004', 'admin', 'abcd0004', null);
INSERT INTO users VALUES('abcd1005', 'admin', 'abcd0005', null);
INSERT INTO users VALUES('abcd1006', 'admin', 'abcd0006', null);
INSERT INTO users VALUES('abcd1007', 'admin', 'abcd0007', null);
INSERT INTO users VALUES('abcd1008', 'admin', 'abcd0008', null);
INSERT INTO users VALUES('abcd1009', 'admin', 'abcd0009', null);


INSERT INTO users VALUES('abcd2000', 'prof', 'abcd3000', null);
INSERT INTO users VALUES('abcd2001', 'prof', 'abcd3001', null);
INSERT INTO users VALUES('abcd2002', 'prof', 'abcd3002', null);
INSERT INTO users VALUES('abcd2003', 'prof', 'abcd3003', null);
INSERT INTO users VALUES('abcd2004', 'prof', 'abcd3004', null);
INSERT INTO users VALUES('abcd2005', 'prof', 'abcd3005', null);
INSERT INTO users VALUES('abcd2006', 'prof', 'abcd3006', null);
INSERT INTO users VALUES('abcd2007', 'prof', 'abcd3007', null);
INSERT INTO users VALUES('abcd2008', 'prof', 'abcd3008', null);
INSERT INTO users VALUES('abcd2009', 'prof', 'abcd3009', null);


INSERT INTO users VALUES('abcd4000', 'prof', 'abcd5000', null);
INSERT INTO users VALUES('abcd4001', 'prof', 'abcd5001', null);
INSERT INTO users VALUES('abcd4002', 'prof', 'abcd5002', null);
INSERT INTO users VALUES('abcd4003', 'prof', 'abcd5003', null);
INSERT INTO users VALUES('abcd4004', 'prof', 'abcd5004', null);
INSERT INTO users VALUES('abcd4005', 'prof', 'abcd5005', null);
INSERT INTO users VALUES('abcd4006', 'prof', 'abcd5006', null);
INSERT INTO users VALUES('abcd4007', 'prof', 'abcd5007', null);
INSERT INTO users VALUES('abcd4008', 'prof', 'abcd5008', null);
INSERT INTO users VALUES('abcd4009', 'prof', 'abcd5009', null);


INSERT INTO departments VALUES('CSC');
INSERT INTO departments VALUES('MAT');
INSERT INTO departments VALUES('POL');
INSERT INTO departments VALUES('PSY');
INSERT INTO departments VALUES('BIO');
INSERT INTO departments VALUES('STA');
INSERT INTO departments VALUES('HIS');
INSERT INTO departments VALUES('PHY');
INSERT INTO departments VALUES('CHM');
INSERT INTO departments VALUES('ENG');


INSERT INTO courses VALUES('CSC100', 'owl', 'CSC');
INSERT INTO courses VALUES('CSC101', 'tci', 'CSC');
INSERT INTO courses VALUES('CSC102', 'vcb', 'CSC');
INSERT INTO courses VALUES('CSC103', 'gpt', 'CSC');
INSERT INTO courses VALUES('CSC104', 'ocy', 'CSC');
INSERT INTO courses VALUES('CSC105', 'fva', 'CSC');
INSERT INTO courses VALUES('CSC106', 'mvr', 'CSC');
INSERT INTO courses VALUES('CSC107', 'pem', 'CSC');
INSERT INTO courses VALUES('CSC108', 'xie', 'CSC');
INSERT INTO courses VALUES('CSC109', 'pxa', 'CSC');


INSERT INTO sections(course_code, term, meeting_time, room, section_code) VALUES('CSC100', 201809, '2008-11-09', 'BA1160', 'LEC0101');
INSERT INTO sections(course_code, term, meeting_time, room, section_code) VALUES('CSC100', 201801, '2008-11-09', 'LM161', 'LEC0102');
INSERT INTO sections(course_code, term, meeting_time, room, section_code) VALUES('CSC100', 201809, '2008-11-09', 'LM161', 'LEC0103');
INSERT INTO sections(course_code, term, meeting_time, room, section_code) VALUES('CSC100', 201701, '2008-11-09', 'BA1170', 'LEC0104');
INSERT INTO sections(course_code, term, meeting_time, room, section_code) VALUES('CSC100', 201805, '2008-11-09', 'LM159', 'LEC0501');
INSERT INTO sections(course_code, term, meeting_time, room, section_code) VALUES('CSC108', 201807, '2008-11-09', 'LM161', 'LEC0101');
INSERT INTO sections(course_code, term, meeting_time, room, section_code) VALUES('CSC108', 201807, '2008-11-09', 'LM161', 'LEC0102');
INSERT INTO sections(course_code, term, meeting_time, room, section_code) VALUES('CSC108', 201809, '2008-11-09', 'BA1130', 'LEC0103');
INSERT INTO sections(course_code, term, meeting_time, room, section_code) VALUES('CSC108', 201709, '2008-11-09', 'LM159', 'LEC0104');
INSERT INTO sections(course_code, term, meeting_time, room, section_code) VALUES('CSC108', 201709, '2008-11-09', 'LM161', 'LEC0501');


INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('abcd2000', 'CSC105', 7);
INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('abcd1000', 'CSC102', 4);
INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('abcd4002', 'CSC103', 1);
INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('abcd2008', 'CSC102', 10);
INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('abcd1008', 'CSC104', 4);
INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('abcd4001', 'CSC101', 8);
INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('abcd2006', 'CSC104', 2);
INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('abcd2001', 'CSC105', 7);
INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('abcd2009', 'CSC105', 4);
INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('abcd1001', 'CSC103', 6);


INSERT INTO questions(answer_type, content) VALUES ('open_ended', 'qkg');
INSERT INTO questions(answer_type, content) VALUES ('scale', 'xix');
INSERT INTO questions(answer_type, content) VALUES ('binary', 'wai');
INSERT INTO questions(answer_type, content) VALUES ('scale', 'dpq');
INSERT INTO questions(answer_type, content) VALUES ('scale', 'uxr');
INSERT INTO questions(answer_type, content) VALUES ('scale', 'rya');
INSERT INTO questions(answer_type, content) VALUES ('binary', 'euy');
INSERT INTO questions(answer_type, content) VALUES ('scale', 'mes');
INSERT INTO questions(answer_type, content) VALUES ('binary', 'bkb');
INSERT INTO questions(answer_type, content) VALUES ('binary', 'qsr');


INSERT INTO surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('sef', 'CSC103', 201805, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('qrm', 'CSC103', 201701, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('vdy', 'CSC103', 201701, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('rgy', 'CSC100', 201807, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('fjf', 'CSC105', 201807, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('cqu', 'CSC105', 201709, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('nsa', 'CSC102', 201701, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('taa', 'CSC100', 201701, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('myf', 'CSC105', 201807, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('duj', 'CSC103', 201801, '2008-11-09 00:00:00', '2008-11-09 12:00:00');


INSERT INTO dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (7, 'CSC', 201701, 8, 'abcd2005', 0, 3);
INSERT INTO dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (6, 'PSY', 201805, 10, 'abcd1003', 0, 5);
INSERT INTO dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (10, 'HIS', 201801, 9, 'abcd2001', 1, 2);
INSERT INTO dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (1, 'CSC', 201805, 7, 'abcd2004', 0, 5);
INSERT INTO dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (6, 'BIO', 201805, 3, 'abcd2009', 0, 3);
INSERT INTO dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (4, 'MAT', 201807, 3, 'abcd4008', 1, 5);
INSERT INTO dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (9, 'MAT', 201701, 4, 'abcd4003', 0, 5);
INSERT INTO dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (6, 'MAT', 201801, 7, 'abcd4008', 0, 3);
INSERT INTO dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (10, 'ENG', 201809, 2, 'abcd4002', 0, 6);
INSERT INTO dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (5, 'ENG', 201805, 7, 'abcd4009', 1, 3);


INSERT INTO course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (5, 3, 'abcd2008', 0, 1);
INSERT INTO course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (6, 2, 'abcd2004', 1, 4);
INSERT INTO course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (6, 3, 'abcd2001', 0, 5);
INSERT INTO course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (8, 5, 'abcd2007', 0, 3);
INSERT INTO course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (10, 2, 'abcd4008', 1, 5);
INSERT INTO course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (8, 1, 'abcd4004', 1, 2);
INSERT INTO course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (9, 10, 'abcd1003', 1, 4);
INSERT INTO course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (3, 7, 'abcd4002', 0, 1);
INSERT INTO course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (7, 5, 'abcd4001', 0, 2);
INSERT INTO course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (7, 4, 'abcd4007', 0, 5);


INSERT INTO ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (2, 1, 201807, 6, 'abcd2009', 1, 4);
INSERT INTO ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (2, 1, 201805, 10, 'abcd1007', 0, 3);
INSERT INTO ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (6, 5, 201805, 1, 'abcd2006', 0, 5);
INSERT INTO ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (7, 10, 201807, 1, 'abcd1008', 0, 2);
INSERT INTO ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (9, 3, 201801, 3, 'abcd1005', 1, 4);
INSERT INTO ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (2, 10, 201809, 8, 'abcd4003', 0, 5);
INSERT INTO ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (9, 7, 201809, 7, 'abcd1007', 1, 4);
INSERT INTO ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (2, 10, 201801, 4, 'abcd4003', 1, 5);
INSERT INTO ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (9, 5, 201805, 6, 'abcd4006', 0, 4);
INSERT INTO ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (10, 7, 201801, 9, 'abcd2009', 1, 2);


INSERT INTO survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (5, 3, 'xtd', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (1, 10, 'kgn', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (7, 10, 'tzt', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (5, 2, 'yjc', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (10, 10, 'ngq', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (2, 3, 'yza', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (6, 7, 'tjj', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (6, 1, 'vdx', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (5, 8, 'vbj', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (4, 7, 'lzh', '2008-11-09 00:00:00', '2008-11-09 12:00:00');


INSERT INTO responses(survey_instance_id, question_id, answer, user_id) VALUES (10, 5, 'eks', 'abcd1005');
INSERT INTO responses(survey_instance_id, question_id, answer, user_id) VALUES (8, 4, 'rht', 'abcd2008');
INSERT INTO responses(survey_instance_id, question_id, answer, user_id) VALUES (5, 9, 'ryt', 'abcd1001');
INSERT INTO responses(survey_instance_id, question_id, answer, user_id) VALUES (9, 7, 'mvp', 'abcd2006');
INSERT INTO responses(survey_instance_id, question_id, answer, user_id) VALUES (5, 2, 'ups', 'abcd2003');
INSERT INTO responses(survey_instance_id, question_id, answer, user_id) VALUES (2, 4, 'gaj', 'abcd4007');
INSERT INTO responses(survey_instance_id, question_id, answer, user_id) VALUES (10, 2, 'ibj', 'abcd2006');
INSERT INTO responses(survey_instance_id, question_id, answer, user_id) VALUES (7, 2, 'anx', 'abcd1009');
INSERT INTO responses(survey_instance_id, question_id, answer, user_id) VALUES (9, 8, 'dsa', 'abcd1000');
INSERT INTO responses(survey_instance_id, question_id, answer, user_id) VALUES (1, 5, 'ava', 'abcd2002');


