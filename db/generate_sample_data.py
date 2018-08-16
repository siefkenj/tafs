import os,os.path
import string,base64
import random
import json
from datetime import date

class GenExamples:
    """Generate randomized things for seeding!"""
    LOREM = 'lorem ipsum dolor sit amet sea ea sadipscing reprehendunt brute everti ex cum diam nemore cum eu in ius enim erant iudico autem legere sea ut cu velit utamur eos tamquam periculis vel eu ad modus soluta ullamcorper qui offendit elaboraret usu ei'.split()
    FIRST_NAMES = ['James', 'John', 'Robert', 'Michael', 'Mary', 'William', 'David', 'Richard', 'Charles', 'Joseph', 'Thomas', 'Patricia', 'Christopher', 'Linda', 'Barbara', 'Daniel', 'Paul', 'Mark', 'Elizabeth', 'Donald', 'Jennifer', 'George', 'Maria', 'Kenneth', 'Susan', 'Steven', 'Edward', 'Margaret', 'Brian', 'Ronald', 'Dorothy', 'Anthony', 'Lisa', 'Kevin', 'Nancy', 'Karen', 'Betty', 'Helen', 'Jason', 'Matthew', 'Gary', 'Timothy', 'Sandra', 'Jose', 'Larry', 'Jeffrey', 'Frank', 'Donna', 'Carol', 'Ruth', 'Scott', 'Eric', 'Stephen', 'Andrew', 'Sharon', 'Michelle', 'Laura', 'Sarah', 'Kimberly', 'Deborah', 'Jessica', 'Raymond', 'Shirley', 'Cynthia', 'Angela', 'Melissa', 'Brenda', 'Amy', 'Jerry', 'Gregory', 'Anna', 'Joshua', 'Virginia', 'Rebecca', 'Kathleen', 'Dennis', 'Pamela', 'Martha', 'Debra', 'Amanda', 'Walter', 'Stephanie', 'Willie', 'Patrick', 'Terry', 'Carolyn', 'Peter', 'Christine', 'Marie', 'Janet', 'Frances', 'Catherine']
    LAST_NAMES = ['Smith', 'Johnson', 'Williams', 'Jones', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Garcia', 'Martinez', 'Robinson', 'Clark', 'Rodriguez', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'Hernandez', 'King', 'Wright', 'Lopez', 'Hill', 'Scott', 'Green', 'Adams', 'Baker', 'Gonzalez', 'Nelson', 'Carter', 'Mitchell', 'Perez', 'Roberts', 'Turner', 'Phillips', 'Campbell', 'Parker', 'Evans', 'Edwards', 'Collins', 'Stewart', 'Sanchez', 'Morris', 'Rogers', 'Reed', 'Cook', 'Morgan', 'Bell', 'Murphy', 'Bailey', 'Rivera', 'Cooper', 'Richardson', 'Cox', 'Howard', 'Ward', 'Torres', 'Peterson', 'Gray', 'Ramirez', 'James', 'Watson', 'Brooks', 'Kelly', 'Sanders', 'Price', 'Bennett', 'Wood', 'Barnes', 'Ross', 'Henderson', 'Coleman', 'Jenkins', 'Perry', 'Powell', 'Long', 'Patterson', 'Hughes', 'Flores', 'Washington', 'Butler', 'Simmons', 'Foster', 'Gonzales', 'Bryant', 'Alexander', 'Russell', 'Griffin', 'Diaz', 'Hayes', 'Myers', 'Ford', 'Hamilton', 'Graham', 'Sullivan', 'Wallace', 'Woods', 'Cole', 'West', 'Jordan', 'Owens', 'Reynolds', 'Fisher', 'Ellis', 'Harrison', 'Gibson', 'Mcdonald', 'Cruz', 'Marshall', 'Ortiz', 'Gomez', 'Murray', 'Freeman', 'Wells', 'Webb', 'Simpson', 'Stevens', 'Tucker', 'Porter', 'Hunter', 'Hicks', 'Crawford', 'Henry', 'Boyd']
    DEPTS = {
                'Mathematics': 'MAT',
                'Computer Science': 'CSC',
                'Psychology': 'PSY',
                'Biology': 'BIO',
                'History': 'HIS'
            }
    ROOMS = ['LM159', 'LM161', 'BA1130', 'BA1160', 'BA1170', 'BA1180']
    TERMS = ['201701','201705','201709','201801','201805','201809']
    QUESTIONS = [
            '''{"type":"rating","name":"Understanding","title":"The tutorial/lab helped me better understand the course material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Orgainization","title":"The tutorial/lab sessions were organized.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Preparedness","title":"The teaching assistant was well-prepared for tutorial/lab sessions.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Explanations","title":"The teaching assistant explained tutorial/lab topics and concepts clearly.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Respectfulness","title":"The teaching assistant responded respectfully to student questions during lab/tutorial sessions.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Learning","title":"The support my teaching assistant provided in the course contributed to my overall learning.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Feedback","title":"The teaching assistant's feedback on course assignments, projects, papers and/or tests helped me understand the grades I received.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Feedback2","title":"The teaching assistant's feedback on course assignments, projects, papers and/or tests improved my understanding of the course material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Enthusiasm","title":"The teaching assistant was enthusiastic about the tutorial/lab material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Quality","title":"Overall, the quality of support the teaching assistant provided in the tutorial/lab was:","rateValues":[{"value":"1","text":"Poor"},{"value":"2","text":"Fair"},{"value":"3","text":"Good"},{"value":"4","text":"Very Good"},{"value":"5","text":"Excellent"}]}''',
            '''{"type":"rating","name":"Quality2","title":"Overall, the quality of my learning experience in the tutorial/lab was:","rateValues":[{"value":"1","text":"Poor"},{"value":"2","text":"Fair"},{"value":"3","text":"Good"},{"value":"4","text":"Very Good"},{"value":"5","text":"Excellent"}]}''',
            '''{"type":"comment","name":"Comments","title":"Please comment on your overall learning experience in the tutorial/lab session."}'''
            ]

    def __init__(self):
        # set the seed so we always get the same sequence of randoms
        random.seed(1234)

    def name(self):
        return "{} {}".format(random.choice(self.FIRST_NAMES), random.choice(self.LAST_NAMES))

    def sentence(self):
        words = [random.choice(self.LOREM) for _ in range(random.randint(5,10))]
        return " ".join(words + ["."]).capitalize()

    def paragraph(self):
        sentences = [self.sentence() for _ in range(random.randint(2,5))]
        return " ".join(sentences)

    def utorid(self, name=None):
        if not name:
            name = random.choice(self.LAST_NAMES)
        if " " in name:
            name = name.split()[-1]
        return name[:6].lower() + "{}".format(random.randint(0,100))

    def courses(self, department=None):
        num = random.randint(5,10)
        if not department:
            department = random.choice(list(self.DEPTS.keys()))
        return [self.DEPTS[department] + "{}".format(random.randint(0,300) + 100) for _ in range(num)]

    def sections(self):
        num = random.randint(3,7)
        return ["LEC0{}".format(i + 100) for i in range(num)]

    def build_choice_ids(self, id_range):
        ids = list(range(1,id_range))
        ids.extend(["NULL","NULL","NULL","NULL","NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "," NULL "])
        return ids
    def default(self):
        out = [
        '''INSERT INTO users VALUES ('Hame23',1,0,0,'Duran Hame',NULL);''',
        '''INSERT INTO courses VALUES('UofT101','UofT101','History');''',
        '''INSERT INTO sections (course_code, term, meeting_time, room, section_code) VALUES('UofT101','201809','2018-09-6','LM159','LEC0105');''',
        '''INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('Hame23','UofT101',163);''',
        '''INSERT INTO choices(choice5,choice6) VALUES (2,8);''',
        '''INSERT INTO choices(choice1,choice2,choice3,choice4,choice5,choice6) VALUES (6,3,7,5,2,8);''',
        '''INSERT INTO ta_survey_choices (choices_id, section_id, user_id) VALUES (31,163,'Hame23');''',
        '''INSERT INTO surveys(dept_survey_choice_id,course_survey_choice_id,ta_survey_choice_id,name,term,default_survey_open,default_survey_close) VALUES (4,NULL,164,'Crafted Survey','201809','2018-11-09 00:00:00','2018-11-12 00:00:00');''',
        '''INSERT INTO survey_instances(viewable_by_others,survey_id,choices_id,user_association_id,override_token,survey_open,survey_close,name) VALUES (0,101,32,814,'SD1B61','2018-11-09 00:00:00','2018-11-12 00:00:00','Crafted Survey');''',
        '''INSERT INTO responses(survey_instance_id,question_id,answer,user_id) VALUES (200,1,'5','allen52');''',
        '''INSERT INTO responses(survey_instance_id,question_id,answer,user_id) VALUES (200,1,'1','ramire63');''',
        '''INSERT INTO responses(survey_instance_id,question_id,answer,user_id) VALUES (200,1,'1','hunter79');''',
        '''INSERT INTO responses(survey_instance_id,question_id,answer,user_id) VALUES (200,1,'5','wood81');''',
        '''INSERT INTO responses(survey_instance_id,question_id,answer,user_id) VALUES (200,1,'3','ward90');''',
        '''INSERT INTO responses(survey_instance_id,question_id,answer,user_id) VALUES (200,1,'5','woods94');''',
        '''INSERT INTO responses(survey_instance_id,question_id,answer,user_id) VALUES (200,1,'2','hender86');'''
        ];
        return out

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
    survey_choices_ta = []
    survey_choices_dept = []
    survey_choices_course = []
    for _ in range(1,21):
        q = "INSERT INTO choices(choice1,choice2,choice3,choice4,choice5,choice6) VALUES ({},{},{},{},{},{});".format(*random.sample(available_questions,6))
        new_id = len(survey_choices) + 1
        survey_choices.append(new_id)
        survey_choices_dept.append(new_id)
        out.append(q)
    for _ in range(1,6):
        q = "INSERT INTO choices(choice3,choice4,choice5,choice6) VALUES ({},{},{},{});".format(*random.sample(available_questions,4))
        new_id = len(survey_choices) + 1
        survey_choices.append(new_id)
        survey_choices_course.append(new_id)
        out.append(q)
    for _ in range(1,6):
        q = "INSERT INTO choices(choice5,choice6) VALUES ({},{});".format(*random.sample(available_questions,2))
        new_id = len(survey_choices) + 1
        survey_choices.append(new_id)
        survey_choices_ta.append(new_id)
        out.append(q)
    out.append("")

    # dept_survey_choices
    # example insert statment:
    # INSERT INTO dept_survey_choices (choices_id, department_name, user_id) VALUES (1,'CSC','admin0');
    current_data_set['num_deparment_survey_choices'] = len(current_data_set['depts'].keys())
    for dept in current_data_set['depts'].keys():
        admin = random.choice([admin for admin in current_data_set['users'] if current_data_set['users'][admin]['is_admin'] == 1])
        q = "INSERT INTO dept_survey_choices (choices_id, department_name, user_id) VALUES ({},'{}','{}');".format(random.choice(survey_choices_dept),dept,admin)
        out.append(q)
    out.append("")

    # course_survey_choices
    # example insert statment:
    # INSERT INTO course_survey_choices (choices_id, course_code, user_id) VALUES (9,'CSC101','prof2');
    num_courses = 0
    for dept in current_data_set['depts'].keys():
        courses = current_data_set['depts'][dept]['courses'].keys()
        for course in courses:
            num_courses += 1
            instructor = random.choice([instructor for instructor in current_data_set['users'] if current_data_set['users'][instructor]['is_instructor'] == 1])
            course = random.choice(current_data_set['users'][instructor]['user_associations'])[0]
            q = "INSERT INTO course_survey_choices (choices_id, course_code, user_id) VALUES ({},'{}','{}');".format(random.choice(survey_choices_course),course,instructor)
            out.append(q)
    current_data_set['num_course_survey_choices'] = num_courses
    out.append("")

    # ta_survey_choices
    # example insert statment:
    # INSERT INTO ta_survey_choices (choices_id, section_id, user_id) VALUES (24,17,'ta14');
    num_sections = 0
    for dept in current_data_set['depts'].keys():
        courses = current_data_set['depts'][dept]['courses'].keys()
        for course in courses:
            sections = current_data_set['depts'][dept]['courses'][course]['sections']
            for section in sections:
                num_sections += 1
                ta = random.choice([ta for ta in current_data_set['users'] if current_data_set['users'][ta]['is_ta'] == 1])
                section_id = random.choice(current_data_set['users'][ta]['user_associations'])[1]
                q = "INSERT INTO ta_survey_choices (choices_id, section_id, user_id) VALUES ({},{},'{}');".format(random.choice(survey_choices_ta),section_id,ta)
                out.append(q)
    current_data_set['num_ta_survey_choices'] = num_sections
    out.append("")

    # surveys
    # example insert statment:
    # INSERT INTO surveys(dept_survey_choice_id,course_survey_choice_id,ta_survey_choice_id,name,term,default_survey_open,default_survey_close) VALUES (NULL,6,13,'survey40',201709,'2008-11-09 00:00:00','2008-11-12 00:00:00');
    for i in range(1,101):
        survey_name = "survey"+str(i)
        dept_survey_choice_id = random.choice(range(1,len(current_data_set['depts'])))
        course_survey_choice_id = random.choice(gen.build_choice_ids(num_courses))
        ta_survey_choice_id = random.choice(gen.build_choice_ids(num_sections))
        term = random.choice(gen.TERMS)
        q = "INSERT INTO surveys(dept_survey_choice_id,course_survey_choice_id,ta_survey_choice_id,name,term,default_survey_open,default_survey_close) VALUES ({},{},{},'{}','{}','{}','{}');".format(dept_survey_choice_id,course_survey_choice_id,ta_survey_choice_id,survey_name,term,'2008-11-09 00:00:00','2008-11-12 00:00:00')
        out.append(q)
    out.append("")

    # survey_instances
    # example insert statment:
    # INSERT INTO survey_instances(survey_id,choices_id,user_association_id,override_token,survey_open,survey_close) VALUES (42,42,26,'P6DDQJTMAZUGV99RZ8AW','2008-11-09 00:00:00','2008-11-12 00:00:00');
    for _ in range(1,200):
        ta = random.choice([ta for ta in current_data_set['users'] if current_data_set['users'][ta]['is_ta'] == 1])
        user_association = random.choice(current_data_set['users'][ta]['user_associations'])
        section_id = user_association[1]
        user_association_id = user_association[2]
        token = ''.join(random.choice("ABCDEFGHIJKLMNOPQRSTUVWXYZ" + '23456789') for _ in range(6))
        survey_id = random.randint(1,100)
        choices_id = random.choice(survey_choices_dept)
        q = "INSERT INTO survey_instances(viewable_by_others,survey_id,choices_id,user_association_id,override_token,survey_open,survey_close,name) VALUES ({},{},{},{},'{}','{}','{}','{}');".format(0,survey_id,choices_id,user_association_id,token,'2008-11-09 00:00:00','2008-11-12 00:00:00','survey'+str(survey_id))
        out.append(q)
    out.append("")

    # DONE
    # responses
    # example insert statment:
    # INSERT INTO responses(survey_instance_id,question_id,answer,user_id) VALUES (17,1,'2','cruz85');
    for q_id in range(1,13):
        q_type = current_data_set['questions'][q_id-1]['type']
        for _ in range(0,200):
            response = str(random.choice([1,2,3,4,5]))
            if q_type == "comment":
                response = gen.paragraph()
            current_data_set['questions'][q_id-1]['responses'] = response;
            r = "INSERT INTO responses(survey_instance_id,question_id,answer,user_id) VALUES ({},{},'{}','{}');".format(random.randint(1,30),q_id,response,gen.utorid(gen.name()))
            out.append(r)
    out.extend(gen.default())
    print("\n".join(out))
