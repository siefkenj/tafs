import os,os.path
import string,base64
import random
import json
from datetime import date
from generate_sample_data import GenExamples

if __name__ == "__main__":
    import json
    gen = GenExamples()
    #to keep track of all the data generated so far
    current_data_set = {}
    out = []
    # NOTE:
    # Order to add insert statements
    # users
    # departments
    # courses
    # sections
    # user_associations
    # questions
    # choices
    # dept_survey_choices
    # course_survey_choices
    # ta_survey_choices
    # surveys
    # survey_instances
    # responses


    out.append('USE `t_tafs`;')
    current_data_set['users'] = {}
    # users add admins (1,0,0), instructors (0,1,0), and tas (0,0,1) as well
    # as some mixed
    # example insert statment:
    # INSERT INTO users VALUES ('prof0',0,1,0,'prof0',NULL);
    for num,a,b,c in [(2,1,0,0),(2,1,1,0),(4,0,1,0),(20,0,0,1)]:
        names = [gen.name() for _ in range(10)]
        user_ids = [gen.utorid(name) for name in names]
        for name, user_id in zip(names, user_ids):
            q = "INSERT INTO users VALUES ('{}',{},{},{},'{}',NULL);".format(user_id, a, b, c, name)
            current_data_set['users'][user_id] = {'name': name, 'is_admin': c, 'is_instructor': b, 'is_ta': a}
            out.append(q)
    out.append("")

    current_data_set['depts'] = {}
    # departments
    # example insert statment:
    # INSERT INTO departments VALUES('MAT');
    for dept in gen.DEPTS.keys():
        q = "INSERT INTO departments VALUES('{}');".format(dept)
        current_data_set['depts'][dept] = {}
        out.append(q)
    out.append("")

    # courses
    # example insert statment:
    # INSERT INTO courses VALUES ('MAT104','MAT104','MAT');
    for dept in current_data_set['depts'].keys():
        courses = gen.courses(dept)
        current_data_set['depts'][dept]['courses'] = {}
        for course in courses:
            current_data_set['depts'][dept]['courses'][course] = {}
            q = "INSERT INTO courses VALUES('{}','{}','{}');".format(course,course,dept)
            out.append(q)
    out.append("")

    total_section_id = 1
    # sections
    # example insert statment:
    # INSERT INTO sections (course_code, term, meeting_time, room, section_code) VALUES ('MAT104',201801,'2008-11-09','MP1102','LEC0100');
    for dept in current_data_set['depts'].keys():
        for course in current_data_set['depts'][dept]['courses']:
            sections = gen.sections()
            current_data_set['depts'][dept]['courses'][course]['sections'] = {}
            for section in sections:
                current_data_set['depts'][dept]['courses'][course]['sections'][section] = total_section_id
                term = random.choice(gen.TERMS)
                time = "{}-{}-{}".format(term[0:4],term[4:6],random.randint(1,30))
                room = random.choice(gen.ROOMS)
                q = "INSERT INTO sections (course_code, term, meeting_time, room, section_code) VALUES('{}','{}','{}','{}','{}');".format(course,term,time,room,section)
                total_section_id += 1
                out.append(q)
    out.append("")

    # user_associations
    # example insert statment:
    # INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('prof4','MAT104',100);
    curr_user_association_id = 1
    for user_id in current_data_set['users'].keys():
        #store list of user_associations in each user
        current_data_set['users'][user_id]['user_associations'] = []

        #associate admin all courses and sections within a chosen department
        if current_data_set['users'][user_id]['is_admin'] == 1:
            dept = random.choice(list(current_data_set['depts'].keys()))
            for course in current_data_set['depts'][dept]['courses'].keys():
                for section_id in current_data_set['depts'][dept]['courses'][course]['sections'].values():
                    q = "INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('{}','{}',{});".format(user_id,course,section_id)
                    current_data_set['users'][user_id]['user_associations'].append((course,section_id,curr_user_association_id))
                    curr_user_association_id += 1
                    out.append(q)

        #associate instructor with all sections within 3 random courses
        if current_data_set['users'][user_id]['is_instructor'] == 1:
            dept = random.choice(list(current_data_set['depts'].keys()))
            for _ in range(0,3):
                course = random.choice(list(current_data_set['depts'][dept]['courses'].keys()))
                for section_id in current_data_set['depts'][dept]['courses'][course]['sections'].values():
                    q = "INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('{}','{}',{});".format(user_id,course,section_id)
                    current_data_set['users'][user_id]['user_associations'].append((course,section_id,curr_user_association_id))
                    curr_user_association_id += 1
                    out.append(q)

        #associate ta with 3 random section within 3 random courses
        if current_data_set['users'][user_id]['is_ta'] == 1:
            dept = random.choice(list(current_data_set['depts'].keys()))
            for _ in range(0,3):
                course = random.choice(list(current_data_set['depts'][dept]['courses'].keys()))
                for _ in range(0,4):
                    section_id = random.choice(list(current_data_set['depts'][dept]['courses'][course]['sections'].items()))[1]
                    q = "INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('{}','{}',{});".format(user_id,course,section_id)
                    current_data_set['users'][user_id]['user_associations'].append((course,section_id,curr_user_association_id))
                    curr_user_association_id += 1
                    out.append(q)
    out.append("")

    # questions
    # example insert statment:
    # INSERT INTO questions(answer_type,content) VALUES ('rating','{"type":"rating","name":"Understanding","title":"The tutorial/lab helped me better understand the course material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}');
    q_id = 0
    current_data_set['questions'] = []
    for question in gen.QUESTIONS:
        q_type = json.loads(question)['type']
        current_data_set['questions'].append(json.loads(question));
        q = "INSERT INTO questions(answer_type,content) VALUES ('{}','{}');".format(q_type, question.replace("'", "\\"))
        out.append(q)
    out.append("")

    # choices
    # example insert statment:
    # INSERT INTO choices(choice1,choice2,choice3,choice4,choice5,choice6) VALUES (6,11,10,12,4,12);
    # choices 1-20 are department choices
    # choices 21-70 are course choices
    # choices 71-170 are ta choices
    # choices 170-1000 are survey_instance_choices
    available_questions = list(range(1,13))
    survey_choices = []
    survey_choices_dept = []
    for _ in range(1,21):
        q = "INSERT INTO choices(choice1,choice2,choice3,choice4,choice5,choice6) VALUES ({},{},{},{},{},{});".format(*random.sample(available_questions,6))
        new_id = len(survey_choices) + 1
        survey_choices.append(new_id)
        survey_choices_dept.append(new_id)
        out.append(q)

    # dept_survey_choices
    # example insert statment:
    # INSERT INTO dept_survey_choices (choices_id, department_name, user_id) VALUES (1,'CSC','admin0');
    current_data_set['num_deparment_survey_choices'] = len(current_data_set['depts'].keys())
    for dept in current_data_set['depts'].keys():
        admin = random.choice([admin for admin in current_data_set['users'] if current_data_set['users'][admin]['is_admin'] == 1])
        q = "INSERT INTO dept_survey_choices (choices_id, department_name, user_id) VALUES ({},'{}','{}');".format(random.choice(survey_choices_dept),dept,admin)
        out.append(q)
    out.append("")

    # surveys
    # example insert statment:
    # INSERT INTO surveys(dept_survey_choice_id,course_survey_choice_id,ta_survey_choice_id,name,term,default_survey_open,default_survey_close) VALUES (NULL,6,13,'survey40',201709,'2008-11-09 00:00:00','2008-11-12 00:00:00');
    for year in range(2018,2024):
        for t in ['01','05','09']:
            term = str(year) + t
            survey_name = "Change the name of this Survey"
            dept_choices1 = random.randint(1,len(current_data_set['depts'].keys()));
            q = "INSERT INTO surveys(dept_survey_choice_id,course_survey_choice_id,ta_survey_choice_id,name,term,default_survey_open,default_survey_close) VALUES ({},NULL,NULL,'{}','{}','{}','{}');".format(dept_choices1,survey_name,term,'2008-11-09 00:00:00','2008-11-12 00:00:00')
            out.append(q)
    out.append("")
    print("\n".join(out))
