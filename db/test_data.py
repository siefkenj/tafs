import random
import string
import json
admins = ['\'admin0\'','\'admin1\'']
profs = []
tas = []
dept = ['\'CSC\'', '\'MAT\'']
cs_courses = []
math_courses = []
sections = []
ba_rooms = []
mp_rooms = []
sections = []
terms = [201709,201801]
sql_file = open("test_data.sql", "w")
for i in range(0,5):
    profs.append("\'prof"+str(i)+"\'")
    cs_courses.append("\'CSC10"+str(i)+"\'")
    math_courses.append("\'MAT10"+str(i)+"\'")
    ba_rooms.append("\'BA110"+str(i)+"\'")
    mp_rooms.append("\'MP110"+str(i)+"\'")
    sections.append("\'LEC010"+str(i)+"\'")

for i in range(0,20):
    tas.append("\'ta"+str(i)+"\'")

sql = ""
#create departments
for i in dept:
    sql += "INSERT INTO departments VALUES(" + i + "); \n"
sql += "\n\n"
# 2 admins
# 5 profs
# 20 tas
for i in admins:
    sql += "INSERT INTO users VALUES ("+i+","+ str(0) +","+ str(0) +","+ str(1) +","+ i +",NULL);\n"
for i in profs:
    sql += "INSERT INTO users VALUES ("+i+"," + str(0) +","+ str(1) +","+ str(0) +","+ i +",NULL);\n"
for i in tas:
    sql += "INSERT INTO users VALUES ("+i+"," + str(1) +","+ str(0) +","+ str(0) +","+ i +",NULL);\n"


course_parings = {}
section_parings = {}
user_association_pairings = {}
i=1
user_association_id = 1

#create cs_courses and sections associated to cs_courses
for course in cs_courses:
    sql += "INSERT INTO courses VALUES ("+course+","+course+",'CSC');\n"
    for term in terms:
        for section in sections:
            sql += "INSERT INTO sections (course_code, term, meeting_time, room, section_code) VALUES ("+course+","+str(term)+",'2008-11-09',"+ random.choice(ba_rooms) +","+section+");\n"

    #admin for cs_courses
    for section in range(1,len(sections)*2+1):
        ta = random.choice(tas)
        prof = random.choice(profs)

        if ta not in section_parings:
            section_parings[ta] = [i]
        else:
            if i not in section_parings[ta]:
                section_parings[ta].append(i)
        if prof not in course_parings:
            course_parings[prof] = [course]
        else:
            if course not in course_parings[prof]:
                course_parings[prof].append(course)
        sql += "INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('admin0',"+course+ ","+str(i)+");\n"
        if ta not in user_association_pairings:
            user_association_pairings['admin0'] = [user_association_id]
        else:
            if i not in user_association_pairings['admin0']:
                user_association_pairings['admin0'].append(user_association_id)
        user_association_id+=1
        sql += "INSERT INTO user_associations(user_id, course_code, section_id) VALUES ("+ta+","+course+ ","+str(i)+");\n"
        if ta not in user_association_pairings:
            user_association_pairings[ta] = [user_association_id]
        else:
            if i not in user_association_pairings[ta]:
                user_association_pairings[ta].append(user_association_id)
        user_association_id+=1
        sql += "INSERT INTO user_associations(user_id, course_code, section_id) VALUES ("+prof+","+course+ ","+str(i)+");\n"
        if prof not in user_association_pairings:
            user_association_pairings[prof] = [user_association_id]
        else:
            if i not in user_association_pairings[prof]:
                user_association_pairings[prof].append(user_association_id)
        user_association_id+=1
        i+=1


#create math_courses and sections associated to math_courses
for course in math_courses:
    sql += "INSERT INTO courses VALUES ("+course+","+course+",'MAT');\n"
    for term in terms:
        for section in sections:
            sql += "INSERT INTO sections (course_code, term, meeting_time, room, section_code) VALUES ("+course+","+str(term)+",'2008-11-09',"+ random.choice(mp_rooms) +","+section+");\n"

    p = random.choice(profs)
    #admin for math_courses
    for section in range(1,len(sections)*2+1):
        sql += "INSERT INTO user_associations(user_id, course_code, section_id) VALUES ('admin1',"+course+ ","+str(i)+");\n"
        sql += "INSERT INTO user_associations(user_id, course_code, section_id) VALUES ("+random.choice(tas)+","+course+ ","+str(i)+");\n"
        sql += "INSERT INTO user_associations(user_id, course_code, section_id) VALUES ("+random.choice(profs)+","+course+ ","+str(i)+");\n"
        i+=1


answer_types = ['\'text\'', '\'radiogroup\'']
questions = []
qtitle = ['How is my pacing when I explain material?', 'Do I appear prepared for tutorial?', 'Do I create a good learning environment?', 'Overall, how would you rate my effectiveness as a TA?', 'What am I doing well?']
responses = []
department_survey_names = []
course_survey_names = []
ta_survey_names = []
ta_choices = []
course_choices = []
department_choices = []

for i in range(0,20):
    questions.append("\'question"+str(i)+"\'")
for i in range(0,100):
    responses.append("\'answer"+str(i)+"\'")
for i in range(0,2):
    department_survey_names.append("\'department_survey"+str(i)+"\'")

for i in range(0,5):
    course_survey_names.append("\'course_survey"+str(i)+"\'")

for i in range(0,3):
    ta_survey_names.append("\'ta_survey"+str(i)+"\'")

#populate questions and surveys table
for i in range(1,11):
    content = {
        'type': "radiogroup",
        'name': "effectiveness" + str(i),
        'title': random.choice(qtitle),
        'choices': ["1", "2", "3", "4", "5"]
    }
    as_str = json.dumps(content)
    sql += "INSERT INTO questions(answer_type,content) VALUES ('radiogroup', "+ repr(as_str) +");\n"
for i in range(1,11):
    content = {
        'type': "text",
        'name': "feedback" + str(i),
        'title': random.choice(qtitle)
    }
    as_str = json.dumps(content)
    sql += "INSERT INTO questions(answer_type,content) VALUES ('text',"+ repr(as_str) +");\n"

#2 admin choices for surveys
#10 course choices
#30 ta choices
for i in range(1,3):
    sql += "INSERT INTO choices(choice1,choice2,choice3,choice4,choice5,choice6) VALUES ("+str(random.randint(1, 20))+","+str(random.randint(1, 20))+","+str(random.randint(1, 20))+","+str(random.randint(1, 20))+","+str(random.randint(1, 20))+","+str(random.randint(1, 20))+");\n"
for i in range(1,11):
    sql += "INSERT INTO choices(choice3,choice4,choice5,choice6) VALUES ("+str(random.randint(1, 20))+","+str(random.randint(1, 20))+","+str(random.randint(1, 20))+","+str(random.randint(1, 20))+");\n"
for i in range(1,31):
    sql += "INSERT INTO choices(choice5,choice6) VALUES ("+str(random.randint(1, 20))+","+str(random.randint(1, 20))+");\n"

# 2 deparment surveys in CS
# 5 course surveys per department
# 3 ta surveys per course
for i in department_survey_names:
    sql += "INSERT INTO dept_survey_choices (choices_id, department_name, user_id) VALUES ("+str(random.randint(1, 2))+","+dept[0]+","+admins[0]+");\n"
    for j in course_survey_names:
        cs_prof = random.choice(course_parings.keys())
        cs_course = random.choice(course_parings[cs_prof])
        sql += "INSERT INTO course_survey_choices (choices_id, course_code, user_id) VALUES ("+str(random.randint(3, 12))+","+cs_course+","+cs_prof+");\n"
        for k in ta_survey_names:
            cs_ta = random.choice(section_parings.keys())
            cs_section = str(random.choice(section_parings[cs_ta]))
            sql += "INSERT INTO ta_survey_choices (choices_id, section_id, user_id) VALUES ("+str(random.randint(13, 42))+","+cs_section+","+cs_ta+");\n"

#42 survey names
survey_names = []
for i in range(1,43):
    survey_names.append("\'survey"+str(i)+"\'")
for k in survey_names:
    sql += "INSERT INTO surveys(dept_survey_choice_id,course_survey_choice_id,ta_survey_choice_id,name,term,default_survey_open,default_survey_close) VALUES ("+random.choice([str(random.randint(1,2)),'NULL'])+","+random.choice([str(random.randint(1,10)),'NULL'])+","+random.choice([str(random.randint(1,30)),'NULL'])+","+k+","+ str(201709) +",\'"+'2008-11-09 00:00:00'+"\',\'"+'2008-11-12 00:00:00'+"\');\n"

#survey instances for surveys
y=13
for i in department_survey_names:
    for j in course_survey_names:
        for k in ta_survey_names:
            cs_ta = random.choice(section_parings.keys())
            cs_section = str(random.choice(section_parings[cs_ta]))
            token = ''.join(random.choice(string.ascii_uppercase + string.digits) for _ in range(20))
            user_association_id = random.choice(user_association_pairings[cs_ta])
            sql += "INSERT INTO survey_instances(survey_id,choices_id,user_association_id,override_token,survey_open,survey_close) VALUES ("+str(y)+","+str(y)+","+ str(user_association_id) +",\'"+token+"\',\'"+'2008-11-09 00:00:00'+"\',\'"+'2008-11-12 00:00:00'+"\');\n"
            y+=1

#responses for survey_instances
#30 survey instance
#20 questions per survey instances
#10 responses per question
for i in range(1,31):
    for j in range(1,21):
        for _ in range(0,10):
            user_id = ''.join(random.choice(string.ascii_uppercase + string.digits + string.ascii_lowercase) for _ in range(10))
            sql += "INSERT INTO responses(survey_instance_id,question_id,answer,user_id) VALUES ("+str(i)+","+str(j)+","+ random.choice(responses) +",\'"+user_id+"\');\n"



sql_file.write(sql)
