INSERT INTO tafs.users VALUES('abcd1000', 'admin', 'abcd0000', null);
INSERT INTO tafs.users VALUES('abcd1001', 'admin', 'abcd0001', null);
INSERT INTO tafs.users VALUES('abcd1002', 'admin', 'abcd0002', null);
INSERT INTO tafs.users VALUES('abcd1003', 'admin', 'abcd0003', null);
INSERT INTO tafs.users VALUES('abcd1004', 'admin', 'abcd0004', null);
INSERT INTO tafs.users VALUES('abcd1005', 'admin', 'abcd0005', null);
INSERT INTO tafs.users VALUES('abcd1006', 'admin', 'abcd0006', null);
INSERT INTO tafs.users VALUES('abcd1007', 'admin', 'abcd0007', null);
INSERT INTO tafs.users VALUES('abcd1008', 'admin', 'abcd0008', null);
INSERT INTO tafs.users VALUES('abcd1009', 'admin', 'abcd0009', null);


INSERT INTO tafs.users VALUES('abcd2000', 'prof', 'abcd3000', null);
INSERT INTO tafs.users VALUES('abcd2001', 'prof', 'abcd3001', null);
INSERT INTO tafs.users VALUES('abcd2002', 'prof', 'abcd3002', null);
INSERT INTO tafs.users VALUES('abcd2003', 'prof', 'abcd3003', null);
INSERT INTO tafs.users VALUES('abcd2004', 'prof', 'abcd3004', null);
INSERT INTO tafs.users VALUES('abcd2005', 'prof', 'abcd3005', null);
INSERT INTO tafs.users VALUES('abcd2006', 'prof', 'abcd3006', null);
INSERT INTO tafs.users VALUES('abcd2007', 'prof', 'abcd3007', null);
INSERT INTO tafs.users VALUES('abcd2008', 'prof', 'abcd3008', null);
INSERT INTO tafs.users VALUES('abcd2009', 'prof', 'abcd3009', null);


INSERT INTO tafs.users VALUES('abcd4000', 'prof', 'abcd5000', null);
INSERT INTO tafs.users VALUES('abcd4001', 'prof', 'abcd5001', null);
INSERT INTO tafs.users VALUES('abcd4002', 'prof', 'abcd5002', null);
INSERT INTO tafs.users VALUES('abcd4003', 'prof', 'abcd5003', null);
INSERT INTO tafs.users VALUES('abcd4004', 'prof', 'abcd5004', null);
INSERT INTO tafs.users VALUES('abcd4005', 'prof', 'abcd5005', null);
INSERT INTO tafs.users VALUES('abcd4006', 'prof', 'abcd5006', null);
INSERT INTO tafs.users VALUES('abcd4007', 'prof', 'abcd5007', null);
INSERT INTO tafs.users VALUES('abcd4008', 'prof', 'abcd5008', null);
INSERT INTO tafs.users VALUES('abcd4009', 'prof', 'abcd5009', null);


INSERT INTO tafs.departments VALUES('CSC');
INSERT INTO tafs.departments VALUES('MAT');
INSERT INTO tafs.departments VALUES('POL');
INSERT INTO tafs.departments VALUES('PSY');
INSERT INTO tafs.departments VALUES('BIO');
INSERT INTO tafs.departments VALUES('STA');
INSERT INTO tafs.departments VALUES('HIS');
INSERT INTO tafs.departments VALUES('PHY');
INSERT INTO tafs.departments VALUES('CHM');
INSERT INTO tafs.departments VALUES('ENG');


INSERT INTO tafs.courses VALUES('CSC100', 'myz', 'CSC');
INSERT INTO tafs.courses VALUES('CSC101', 'ejo', 'CSC');
INSERT INTO tafs.courses VALUES('CSC102', 'xop', 'CSC');
INSERT INTO tafs.courses VALUES('CSC103', 'alb', 'CSC');
INSERT INTO tafs.courses VALUES('CSC104', 'zho', 'CSC');
INSERT INTO tafs.courses VALUES('CSC105', 'aki', 'CSC');
INSERT INTO tafs.courses VALUES('CSC106', 'wtt', 'CSC');
INSERT INTO tafs.courses VALUES('CSC107', 'bdt', 'CSC');
INSERT INTO tafs.courses VALUES('CSC108', 'khk', 'CSC');
INSERT INTO tafs.courses VALUES('CSC109', 'uvy', 'CSC');


INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code) VALUES('CSC100', 201801, '2008-11-09', 'LM161', 'LEC0101');
INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code) VALUES('CSC100', 201709, '2008-11-09', 'BA1160', 'LEC0102');
INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code) VALUES('CSC100', 201701, '2008-11-09', 'LM161', 'LEC0103');
INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code) VALUES('CSC100', 201709, '2008-11-09', 'BA1180', 'LEC0104');
INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code) VALUES('CSC100', 201701, '2008-11-09', 'LM161', 'LEC0501');
INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code) VALUES('CSC108', 201805, '2008-11-09', 'BA1130', 'LEC0101');
INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code) VALUES('CSC108', 201709, '2008-11-09', 'BA1170', 'LEC0102');
INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code) VALUES('CSC108', 201801, '2008-11-09', 'BA1170', 'LEC0103');
INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code) VALUES('CSC108', 201709, '2008-11-09', 'BA1160', 'LEC0104');
INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code) VALUES('CSC108', 201801, '2008-11-09', 'BA1180', 'LEC0501');


INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES ('abcd2004', 'CSC105', 8);
INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES ('abcd4008', 'CSC105', 9);
INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES ('abcd2007', 'CSC104', 9);
INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES ('abcd4001', 'CSC102', 2);
INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES ('abcd2000', 'CSC105', 7);
INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES ('abcd4005', 'CSC103', 2);
INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES ('abcd2005', 'CSC100', 10);
INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES ('abcd2006', 'CSC101', 2);
INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES ('abcd4000', 'CSC105', 5);
INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES ('abcd1005', 'CSC104', 4);


INSERT INTO tafs.questions(answer_type, content) VALUES ('binary', 'cqm');
INSERT INTO tafs.questions(answer_type, content) VALUES ('open_ended', 'vib');
INSERT INTO tafs.questions(answer_type, content) VALUES ('scale', 'ewu');
INSERT INTO tafs.questions(answer_type, content) VALUES ('binary', 'dip');
INSERT INTO tafs.questions(answer_type, content) VALUES ('binary', 'ydg');
INSERT INTO tafs.questions(answer_type, content) VALUES ('scale', 'nap');
INSERT INTO tafs.questions(answer_type, content) VALUES ('open_ended', 'zcc');
INSERT INTO tafs.questions(answer_type, content) VALUES ('scale', 'eyv');
INSERT INTO tafs.questions(answer_type, content) VALUES ('scale', 'aqy');
INSERT INTO tafs.questions(answer_type, content) VALUES ('binary', 'qmy');


INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('icw', 'CSC100', 201807, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('kqu', 'CSC103', 201701, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('rqg', 'CSC101', 201805, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('myz', 'CSC103', 201807, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('fqk', 'CSC103', 201805, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('oqv', 'CSC105', 201709, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('dlu', 'CSC105', 201809, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('zii', 'CSC105', 201709, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('izs', 'CSC103', 201809, '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, default_survey_close) VALUES ('gfi', 'CSC100', 201809, '2008-11-09 00:00:00', '2008-11-09 12:00:00');


INSERT INTO tafs.dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (2, 'ENG', 201709, 2, 'abcd1009', 0, 6);
INSERT INTO tafs.dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (4, 'BIO', 201805, 5, 'abcd2001', 1, 3);
INSERT INTO tafs.dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (2, 'POL', 201809, 5, 'abcd2009', 1, 4);
INSERT INTO tafs.dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (2, 'CSC', 201801, 8, 'abcd1001', 1, 4);
INSERT INTO tafs.dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (4, 'PHY', 201709, 8, 'abcd2002', 0, 1);
INSERT INTO tafs.dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (9, 'CHM', 201801, 2, 'abcd2007', 0, 3);
INSERT INTO tafs.dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (7, 'HIS', 201809, 9, 'abcd1008', 0, 2);
INSERT INTO tafs.dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (5, 'STA', 201801, 5, 'abcd1006', 1, 1);
INSERT INTO tafs.dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (6, 'BIO', 201701, 2, 'abcd2004', 0, 3);
INSERT INTO tafs.dept_question_choices(survey_id, department_name, term, question_id, user_id, locked, position) VALUES (9, 'PHY', 201809, 9, 'abcd4006', 1, 1);


INSERT INTO tafs.course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (4, 2, 'abcd4003', 0, 4);
INSERT INTO tafs.course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (6, 4, 'abcd4003', 1, 1);
INSERT INTO tafs.course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (5, 8, 'abcd1009', 0, 3);
INSERT INTO tafs.course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (10, 10, 'abcd4001', 1, 6);
INSERT INTO tafs.course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (5, 10, 'abcd1004', 1, 3);
INSERT INTO tafs.course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (4, 8, 'abcd1006', 1, 2);
INSERT INTO tafs.course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (8, 1, 'abcd2008', 1, 5);
INSERT INTO tafs.course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (9, 8, 'abcd2002', 1, 4);
INSERT INTO tafs.course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (10, 8, 'abcd4000', 0, 2);
INSERT INTO tafs.course_question_choices(survey_id, question_id, user_id, locked, position) VALUES (7, 4, 'abcd4009', 0, 2);


INSERT INTO tafs.ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (7, 3, 201807, 2, 'abcd1000', 0, 5);
INSERT INTO tafs.ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (5, 5, 201805, 7, 'abcd4001', 1, 4);
INSERT INTO tafs.ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (10, 5, 201801, 10, 'abcd2005', 1, 3);
INSERT INTO tafs.ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (10, 9, 201809, 10, 'abcd2001', 0, 5);
INSERT INTO tafs.ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (2, 9, 201709, 5, 'abcd1001', 1, 2);
INSERT INTO tafs.ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (7, 2, 201807, 8, 'abcd4003', 1, 6);
INSERT INTO tafs.ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (3, 2, 201801, 8, 'abcd4009', 1, 1);
INSERT INTO tafs.ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (8, 4, 201809, 3, 'abcd1009', 1, 4);
INSERT INTO tafs.ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (1, 2, 201709, 5, 'abcd1007', 0, 5);
INSERT INTO tafs.ta_question_choices(survey_id, section_id, term, question_id, user_id, locked, position) VALUES (1, 4, 201701, 2, 'abcd4003', 1, 1);


INSERT INTO tafs.survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (3, 2, 'olq', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (2, 5, 'joj', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (7, 5, 'xim', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (2, 7, 'npm', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (9, 7, 'pbi', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (2, 4, 'ezl', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (1, 4, 'wap', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (4, 7, 'vgy', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (5, 8, 'ihn', '2008-11-09 00:00:00', '2008-11-09 12:00:00');
INSERT INTO tafs.survey_instances(user_association_id, survey_id, override_token, survey_open, survey_close) VALUES (5, 7, 'dzg', '2008-11-09 00:00:00', '2008-11-09 12:00:00');


INSERT INTO tafs.responses(survey_instance_id, question_id, answer, user_id) VALUES (1, 5, 'ycr', 'abcd2005');
INSERT INTO tafs.responses(survey_instance_id, question_id, answer, user_id) VALUES (3, 5, 'dcw', 'abcd1002');
INSERT INTO tafs.responses(survey_instance_id, question_id, answer, user_id) VALUES (3, 5, 'kod', 'abcd2004');
INSERT INTO tafs.responses(survey_instance_id, question_id, answer, user_id) VALUES (1, 9, 'siw', 'abcd1008');
INSERT INTO tafs.responses(survey_instance_id, question_id, answer, user_id) VALUES (1, 2, 'pcl', 'abcd1007');
INSERT INTO tafs.responses(survey_instance_id, question_id, answer, user_id) VALUES (7, 2, 'nxa', 'abcd4000');
INSERT INTO tafs.responses(survey_instance_id, question_id, answer, user_id) VALUES (9, 4, 'vnv', 'abcd2000');
INSERT INTO tafs.responses(survey_instance_id, question_id, answer, user_id) VALUES (10, 9, 'kia', 'abcd1003');
INSERT INTO tafs.responses(survey_instance_id, question_id, answer, user_id) VALUES (5, 4, 'efa', 'abcd4005');
INSERT INTO tafs.responses(survey_instance_id, question_id, answer, user_id) VALUES (6, 4, 'sot', 'abcd2000');


