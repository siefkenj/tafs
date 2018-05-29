
USE ta_feedback;
INSERT INTO ta_feedback.users VALUES('abcd1000', 'admin', 'abcd0000', null);
INSERT INTO ta_feedback.users VALUES('abcd1001', 'admin', 'abcd0001', null);
INSERT INTO ta_feedback.users VALUES('abcd1002', 'admin', 'abcd0002', null);
INSERT INTO ta_feedback.users VALUES('abcd1003', 'admin', 'abcd0003', null);
INSERT INTO ta_feedback.users VALUES('abcd1004', 'admin', 'abcd0004', null);
INSERT INTO ta_feedback.users VALUES('abcd1005', 'admin', 'abcd0005', null);
INSERT INTO ta_feedback.users VALUES('abcd1006', 'admin', 'abcd0006', null);
INSERT INTO ta_feedback.users VALUES('abcd1007', 'admin', 'abcd0007', null);
INSERT INTO ta_feedback.users VALUES('abcd1008', 'admin', 'abcd0008', null);
INSERT INTO ta_feedback.users VALUES('abcd1009', 'admin', 'abcd0009', null);

INSERT INTO ta_feedback.users VALUES('abcd2000', 'prof', 'abcd3000', null);
INSERT INTO ta_feedback.users VALUES('abcd2001', 'prof', 'abcd3001', null);
INSERT INTO ta_feedback.users VALUES('abcd2002', 'prof', 'abcd3002', null);
INSERT INTO ta_feedback.users VALUES('abcd2003', 'prof', 'abcd3003', null);
INSERT INTO ta_feedback.users VALUES('abcd2004', 'prof', 'abcd3004', null);
INSERT INTO ta_feedback.users VALUES('abcd2005', 'prof', 'abcd3005', null);
INSERT INTO ta_feedback.users VALUES('abcd2006', 'prof', 'abcd3006', null);
INSERT INTO ta_feedback.users VALUES('abcd2007', 'prof', 'abcd3007', null);
INSERT INTO ta_feedback.users VALUES('abcd2008', 'prof', 'abcd3008', null);
INSERT INTO ta_feedback.users VALUES('abcd2009', 'prof', 'abcd3009', null);

INSERT INTO ta_feedback.users VALUES('abcd4000', 'ta', 'abcd5000', null);
INSERT INTO ta_feedback.users VALUES('abcd4001', 'ta', 'abcd5001', null);
INSERT INTO ta_feedback.users VALUES('abcd4002', 'ta', 'abcd5002', null);
INSERT INTO ta_feedback.users VALUES('abcd4003', 'ta', 'abcd5003', null);
INSERT INTO ta_feedback.users VALUES('abcd4004', 'ta', 'abcd5004', null);
INSERT INTO ta_feedback.users VALUES('abcd4005', 'ta', 'abcd5005', null);
INSERT INTO ta_feedback.users VALUES('abcd4006', 'ta', 'abcd5006', null);
INSERT INTO ta_feedback.users VALUES('abcd4007', 'ta', 'abcd5007', null);
INSERT INTO ta_feedback.users VALUES('abcd4008', 'ta', 'abcd5008', null);
INSERT INTO ta_feedback.users VALUES('abcd4009', 'ta', 'abcd5009', null);


INSERT INTO ta_feedback.courses VALUES('CSC100', 'aaa', 'CSC');
INSERT INTO ta_feedback.courses VALUES('CSC101', 'bbb', 'CSC');
INSERT INTO ta_feedback.courses VALUES('CSC102', 'ccc', 'CSC');
INSERT INTO ta_feedback.courses VALUES('CSC103', 'ddd', 'CSC');
INSERT INTO ta_feedback.courses VALUES('CSC104', 'eee', 'CSC');
INSERT INTO ta_feedback.courses VALUES('CSC105', 'fff', 'CSC');
INSERT INTO ta_feedback.courses VALUES('CSC106', 'ggg', 'CSC');
INSERT INTO ta_feedback.courses VALUES('CSC107', 'hhh', 'CSC');
INSERT INTO ta_feedback.courses VALUES('CSC108', 'iii', 'CSC');
INSERT INTO ta_feedback.courses VALUES('CSC109', 'jjj', 'CSC');


INSERT INTO ta_feedback.department VALUES('CSC');
INSERT INTO ta_feedback.department VALUES('MAT');
INSERT INTO ta_feedback.department VALUES('POL');
INSERT INTO ta_feedback.department VALUES('PSY');
INSERT INTO ta_feedback.department VALUES('BIO');
INSERT INTO ta_feedback.department VALUES('STA');
INSERT INTO ta_feedback.department VALUES('HIS');
INSERT INTO ta_feedback.department VALUES('PHY');
INSERT INTO ta_feedback.department VALUES('CHM');
INSERT INTO ta_feedback.department VALUES('ENG');


INSERT INTO ta_feedback.sections(course_id, term, meeting_time, room, section_code)
    VALUES('CSC100', 201709, '2008-11-09', 'LM159', 'LEC0101');
INSERT INTO ta_feedback.sections(course_id, term, meeting_time, room, section_code)
    VALUES('CSC100', 201709, '2008-11-09', 'LM161', 'LEC0102');
INSERT INTO ta_feedback.sections(course_id, term, meeting_time, room, section_code)
    VALUES('CSC100', 201709, '2008-11-09', 'BA1130', 'LEC0501');
INSERT INTO ta_feedback.sections(course_id, term, meeting_time, room, section_code)
    VALUES('CSC100', 201801, '2008-11-09', 'LM159', 'LEC0101');
INSERT INTO ta_feedback.sections(course_id, term, meeting_time, room, section_code)
    VALUES('CSC100', 201801, '2008-11-09', 'BA1130', 'LEC0501');
INSERT INTO ta_feedback.sections(course_id, term, meeting_time, room, section_code)
    VALUES('CSC411', 201709, '2008-11-09', 'BA1160', 'LEC0101');
INSERT INTO ta_feedback.sections(course_id, term, meeting_time, room, section_code)
    VALUES('CSC411', 201709, '2008-11-09', 'BA1170', 'LEC0102');
INSERT INTO ta_feedback.sections(course_id, term, meeting_time, room, section_code)
    VALUES('CSC411', 201709, '2008-11-09', 'BA1180', 'LEC0501');
INSERT INTO ta_feedback.sections(course_id, term, meeting_time, room, section_code)
    VALUES('CSC411', 201801, '2008-11-09', 'BA1180', 'LEC0101');
INSERT INTO ta_feedback.sections(course_id, term, meeting_time, room, section_code)
    VALUES('CSC411', 201801, '2008-11-09', 'BA1170', 'LEC0501');


INSERT INTO ta_feedback.user_association(user_id, course_id, section_code)
    VALUES('abcd1000', 'CSC100', 'LEC0101');
INSERT INTO ta_feedback.user_association(user_id, course_id, section_code)
    VALUES('abcd1001', 'CSC100', 'LEC0101');
INSERT INTO ta_feedback.user_association(user_id, course_id, section_code)
    VALUES('abcd1002', 'CSC100', 'LEC0101');
INSERT INTO ta_feedback.user_association(user_id, course_id, section_code)
    VALUES('abcd1003', 'CSC100', 'LEC0102');
INSERT INTO ta_feedback.user_association(user_id, course_id, section_code)
    VALUES('abcd1004', 'CSC100', 'LEC0102');
INSERT INTO ta_feedback.user_association(user_id, course_id, section_code)
    VALUES('abcd1005', 'CSC100', 'LEC0102');
INSERT INTO ta_feedback.user_association(user_id, course_id, section_code)
    VALUES('abcd1006', 'CSC100', 'LEC0102');
INSERT INTO ta_feedback.user_association(user_id, course_id, section_code)
    VALUES('abcd1007', 'CSC100', 'LEC0501');
INSERT INTO ta_feedback.user_association(user_id, course_id, section_code)
    VALUES('abcd1008', 'CSC100', 'LEC0501');
INSERT INTO ta_feedback.user_association(user_id, course_id, section_code)
    VALUES('abcd1009', 'CSC100', 'LEC0501');


INSERT INTO ta_feedback.questions(answer_type, content)
    VALUES('open_ended', 'Do you like me?');
INSERT INTO ta_feedback.questions(answer_type, content)
    VALUES('open_ended', 'Do you like me?');
INSERT INTO ta_feedback.questions(answer_type, content)
    VALUES('scale', 'Do you like me?');
INSERT INTO ta_feedback.questions(answer_type, content)
    VALUES('scale', 'Do you like me?');
INSERT INTO ta_feedback.questions(answer_type, content)
    VALUES('scale', 'Do you like me?');
INSERT INTO ta_feedback.questions(answer_type, content)
    VALUES('binary', 'Do you like me?');
INSERT INTO ta_feedback.questions(answer_type, content)
    VALUES('binary', 'Do you like me?');
INSERT INTO ta_feedback.questions(answer_type, content)
    VALUES('binary', 'Do you like me?');
INSERT INTO ta_feedback.questions(answer_type, content)
    VALUES('binary', 'Do you like me?');
INSERT INTO ta_feedback.questions(answer_type, content)
    VALUES('binary', 'Do you like me?');


INSERT INTO ta_feedback.dept_question_choice VALUES('CSC', 201801, 10, 'abcd1000', 0, 1);
INSERT INTO ta_feedback.dept_question_choice VALUES('MAT', 201801, 10, 'abcd1000', 0, 1);
INSERT INTO ta_feedback.dept_question_choice VALUES('POL', 201801, 10, 'abcd1000', 0, 1);
INSERT INTO ta_feedback.dept_question_choice VALUES('PSY', 201801, 10, 'abcd1000', 0, 1);
INSERT INTO ta_feedback.dept_question_choice VALUES('BIO', 201801, 10, 'abcd1000', 0, 1);
INSERT INTO ta_feedback.dept_question_choice VALUES('STA', 201801, 10, 'abcd1000', 0, 1);
INSERT INTO ta_feedback.dept_question_choice VALUES('HIS', 201801, 10, 'abcd1000', 0, 1);
INSERT INTO ta_feedback.dept_question_choice VALUES('PHY', 201801, 10, 'abcd1000', 0, 1);
INSERT INTO ta_feedback.dept_question_choice VALUES('CHM', 201801, 10, 'abcd1000', 0, 1);
INSERT INTO ta_feedback.dept_question_choice VALUES('ENG', 201801, 10, 'abcd1000', 0, 1);


INSERT INTO ta_feedback.course_question_choice(question_id, user_id, locked, position)
    VALUES(10, 'abcd2000', 0, 1);
INSERT INTO ta_feedback.course_question_choice(question_id, user_id, locked, position)
    VALUES(10, 'abcd2000', 0, 1);
INSERT INTO ta_feedback.course_question_choice(question_id, user_id, locked, position)
    VALUES(10, 'abcd2000', 0, 1);
INSERT INTO ta_feedback.course_question_choice(question_id, user_id, locked, position)
    VALUES(10, 'abcd2000', 0, 1);
INSERT INTO ta_feedback.course_question_choice(question_id, user_id, locked, position)
    VALUES(10, 'abcd2000', 0, 1);
INSERT INTO ta_feedback.course_question_choice(question_id, user_id, locked, position)
    VALUES(10, 'abcd2000', 0, 1);
INSERT INTO ta_feedback.course_question_choice(question_id, user_id, locked, position)
    VALUES(10, 'abcd2000', 0, 1);
INSERT INTO ta_feedback.course_question_choice(question_id, user_id, locked, position)
    VALUES(10, 'abcd2000', 0, 1);
INSERT INTO ta_feedback.course_question_choice(question_id, user_id, locked, position)
    VALUES(10, 'abcd2000', 0, 1);
INSERT INTO ta_feedback.course_question_choice(question_id, user_id, locked, position)
    VALUES(10, 'abcd2000', 0, 1);


INSERT INTO ta_feedback.ta_question_choice(section_id, term, question_id, user_id, locked, position)
    VALUES(0, 201801, 0, 'abcd4000', 0, 1);
INSERT INTO ta_feedback.ta_question_choice(section_id, term, question_id, user_id, locked, position)
    VALUES(0, 201801, 0, 'abcd4000', 0, 1);
INSERT INTO ta_feedback.ta_question_choice(section_id, term, question_id, user_id, locked, position)
    VALUES(0, 201801, 0, 'abcd4000', 0, 1);
INSERT INTO ta_feedback.ta_question_choice(section_id, term, question_id, user_id, locked, position)
    VALUES(0, 201801, 0, 'abcd4000', 0, 1);
INSERT INTO ta_feedback.ta_question_choice(section_id, term, question_id, user_id, locked, position)
    VALUES(0, 201801, 0, 'abcd4000', 0, 1);
INSERT INTO ta_feedback.ta_question_choice(section_id, term, question_id, user_id, locked, position)
    VALUES(0, 201801, 0, 'abcd4000', 0, 1);
INSERT INTO ta_feedback.ta_question_choice(section_id, term, question_id, user_id, locked, position)
    VALUES(0, 201801, 0, 'abcd4000', 0, 1);
INSERT INTO ta_feedback.ta_question_choice(section_id, term, question_id, user_id, locked, position)
    VALUES(0, 201801, 0, 'abcd4000', 0, 1);
INSERT INTO ta_feedback.ta_question_choice(section_id, term, question_id, user_id, locked, position)
    VALUES(0, 201801, 0, 'abcd4000', 0, 1);
INSERT INTO ta_feedback.ta_question_choice(section_id, term, question_id, user_id, locked, position)
    VALUES(0, 201801, 0, 'abcd4000', 0, 1);


INSERT INTO ta_feedback.surveys VALUES(0, 'best survey', 'CSC411', 201709, '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.surveys VALUES(1, 'best survey', 'CSC411', 201709, '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.surveys VALUES(2, 'best survey', 'CSC411', 201709, '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.surveys VALUES(3, 'best survey', 'CSC411', 201709, '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.surveys VALUES(4, 'best survey', 'CSC411', 201709, '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.surveys VALUES(5, 'best survey', 'CSC411', 201709, '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.surveys VALUES(6, 'best survey', 'CSC411', 201709, '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.surveys VALUES(7, 'best survey', 'CSC411', 201709, '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.surveys VALUES(8, 'best survey', 'CSC411', 201709, '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.surveys VALUES(9, 'best survey', 'CSC411', 201709, '00:00:00', '2008-11-09');


INSERT INTO ta_feedback.survey_instances VALUES(0, 9, 'use this', '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.survey_instances VALUES(1, 9, 'use this', '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.survey_instances VALUES(2, 9, 'use this', '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.survey_instances VALUES(3, 9, 'use this', '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.survey_instances VALUES(4, 9, 'use this', '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.survey_instances VALUES(5, 9, 'use this', '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.survey_instances VALUES(6, 9, 'use this', '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.survey_instances VALUES(7, 9, 'use this', '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.survey_instances VALUES(8, 9, 'use this', '00:00:00', '2008-11-09');
INSERT INTO ta_feedback.survey_instances VALUES(9, 9, 'use this', '00:00:00', '2008-11-09');


INSERT INTO ta_feedback.response(survey_instance_id, question_id, answer, user_id)
    VALUES(9, 10, 'your tutorial is good!', 'abcd4001');
INSERT INTO ta_feedback.response(survey_instance_id, question_id, answer, user_id)
    VALUES(9, 10, 'your tutorial is good!', 'abcd4001');
INSERT INTO ta_feedback.response(survey_instance_id, question_id, answer, user_id)
    VALUES(9, 10, 'your tutorial is good!', 'abcd4001');
INSERT INTO ta_feedback.response(survey_instance_id, question_id, answer, user_id)
    VALUES(9, 10, 'your tutorial is good!', 'abcd4001');
INSERT INTO ta_feedback.response(survey_instance_id, question_id, answer, user_id)
    VALUES(9, 10, 'your tutorial is good!', 'abcd4001');
INSERT INTO ta_feedback.response(survey_instance_id, question_id, answer, user_id)
    VALUES(9, 10, 'your tutorial is good!', 'abcd4001');
INSERT INTO ta_feedback.response(survey_instance_id, question_id, answer, user_id)
    VALUES(9, 10, 'your tutorial is good!', 'abcd4001');
INSERT INTO ta_feedback.response(survey_instance_id, question_id, answer, user_id)
    VALUES(9, 10, 'your tutorial is good!', 'abcd4001');
INSERT INTO ta_feedback.response(survey_instance_id, question_id, answer, user_id)
    VALUES(9, 10, 'your tutorial is good!', 'abcd4001');
INSERT INTO ta_feedback.response(survey_instance_id, question_id, answer, user_id)
    VALUES(9, 10, 'your tutorial is good!', 'abcd4001');
