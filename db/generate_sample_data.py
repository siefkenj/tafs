import os,os.path
import string,base64
import random

sql_file = open("data.sql", "w")
# insert users "admin"
user_id_admin = ["abcd1000", "abcd1001", "abcd1002", "abcd1003", "abcd1004",\
    "abcd1005", "abcd1006", "abcd1007", "abcd1008", "abcd1009"]
for i in range(0, 10):
    sql = "INSERT INTO tafs.users VALUES(\'" + user_id_admin[i] +\
        "', 'admin', 'abcd000" + str(i) + "', null);"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')
# insert users "prof"
user_id_prof = ["abcd2000", "abcd2001", "abcd2002", "abcd2003", "abcd2004",\
    "abcd2005", "abcd2006", "abcd2007", "abcd2008", "abcd2009"]
for i in range(0, 10):
    sql = "INSERT INTO tafs.users VALUES(\'" + user_id_prof[i] +\
        "', 'prof', 'abcd300" + str(i) + "', null);"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert users "ta"
user_id_ta = ["abcd4000", "abcd4001", "abcd4002", "abcd4003", "abcd4004",\
    "abcd4005", "abcd4006", "abcd4007", "abcd4008", "abcd4009"]
for i in range(0, 10):
    sql = "INSERT INTO tafs.users VALUES(\'" + user_id_ta[i] +\
        "', 'prof', 'abcd500" + str(i) + "', null);"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "department" table
dept = ['CSC', 'MAT', 'POL', 'PSY', 'BIO', 'STA', 'HIS', 'PHY', 'CHM', 'ENG']
for i in range(0, 10):
    sql = "INSERT INTO tafs.departments VALUES(\'" + dept[i] + "');"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "course" table
for i in range(0, 10):
    title = ''.join(random.choice(string.ascii_lowercase) for _ in range(3))
    sql = "INSERT INTO tafs.courses VALUES(\'CSC10" + str(i) + \
        "', \'" + title + "', 'CSC');"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "sections" table
course_choice = ['CSC100', 'CSC101', 'CSC102', 'CSC103', 'CSC104', 'CSC105'];
term_choice = [201709, 201701, 201801, 201809, 201805, 201807];
room_choice = ['LM159', 'LM161', 'BA1130', 'BA1160', 'BA1170', 'BA1180'];
course_section_combination = [['CSC100', 'LEC0101'], ['CSC100', 'LEC0102'], ['CSC100', 'LEC0103'],\
    ['CSC100', 'LEC0104'], ['CSC100', 'LEC0501'], ['CSC108', 'LEC0101'], ['CSC108', 'LEC0102'], \
    ['CSC108', 'LEC0103'], ['CSC108', 'LEC0104'], ['CSC108', 'LEC0501']]
for i in range(0, 10):
    course_section = course_section_combination[i]
    sql = "INSERT INTO tafs.sections(course_code, term, meeting_time, room, section_code)" + \
        " VALUES(\'" + \
        course_section[0] + "', " + str(random.choice(term_choice))+ \
        ", '2008-11-09', '" + random.choice(room_choice) +\
        "\', \'" + course_section[1] + "\');"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "user_associations" table
user_id_list = user_id_admin + user_id_prof + user_id_ta
for i in range(0, 10):
    course_section = random.choice(course_section_combination)
    sql = "INSERT INTO tafs.user_associations(user_id, course_code, section_id) VALUES" +\
        " (\'" + random.choice(user_id_list) + "\', \'" + random.choice(course_choice) +\
        "\', " + str(random.choice(range(1, 11))) + ");"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "questions" table
answer = ['open_ended', 'scale', 'binary']
for i in range(0, 10):
    content = ''.join(random.choice(string.ascii_lowercase) for _ in range(3))
    sql = "INSERT INTO tafs.questions(answer_type, content) VALUES" +\
        " (\'" + random.choice(answer) + "\', \'" + content + "\');"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "surveys" table
default_survey_open = '2008-11-09 00:00:00'
default_survey_close = '2008-11-09 12:00:00'
for i in range(0, 10):
    name = ''.join(random.choice(string.ascii_lowercase) for _ in range(3))
    course_code = random.choice(course_choice)
    term = str(random.choice(term_choice))
    sql = "INSERT INTO tafs.surveys(name, course_code, term, default_survey_open, " +\
        "default_survey_close) VALUES (\'" + name + "\', \'" + course_code + "\', " +\
        term +  ", \'" + default_survey_open + "\', \'" + default_survey_close + "\');"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "dept_question_choices" table
for _ in range(0, 10):
    survey_id = str(random.choice(range(1, 11)))
    department_name = random.choice(dept)
    term = str(random.choice(term_choice))
    question_id = str(random.choice(range(1, 11)))
    user_id = random.choice(user_id_list)
    locked = str(random.choice([0, 1]))
    position = str(random.choice(range(1, 7)))
    sql = "INSERT INTO tafs.dept_question_choices(survey_id, department_name, " +\
        "term, question_id, user_id, locked, position) VALUES (" + survey_id +\
        ", \'" + department_name + "\', " + term + ", " + question_id +\
        ", \'" + user_id + "\', " + locked + ", " + position + ");"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "course_question_choices" table
for _ in range(0, 10):
    survey_id = str(random.choice(range(1, 11)))
    question_id = str(random.choice(range(1, 11)))
    user_id = random.choice(user_id_list)
    locked = str(random.choice([0, 1]))
    position = str(random.choice(range(1, 7)))
    sql = "INSERT INTO tafs.course_question_choices(survey_id, question_id, " +\
        "user_id, locked, position) VALUES (" + survey_id + ", " + question_id +\
        ", \'" + user_id + "\', " + locked + ", "  + position + ");"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "ta_question_choices" table
for _ in range(0, 10):
    survey_id = str(random.choice(range(1, 11)))
    section_id = str(random.choice(range(1, 11)))
    term = str(random.choice(term_choice))
    question_id = str(random.choice(range(1, 11)))
    user_id = random.choice(user_id_list)
    locked = str(random.choice([0, 1]))
    position = str(random.choice(range(1, 7)))
    sql = "INSERT INTO tafs.ta_question_choices(survey_id, " +\
        "section_id, term, question_id, user_id, locked, position) VALUES (" +\
        survey_id + ", " + section_id + ", " + term + ", " + \
        question_id + ", \'" + user_id + "\', " + locked + ", " + position +\
        ");"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "survey_instances" table
default_survey_open = '2008-11-09 00:00:00'
default_survey_close = '2008-11-09 12:00:00'
for _ in range(0, 10):
    user_association_id = str(random.choice(range(1, 11)))
    survey_id = str(random.choice(range(1, 11)))
    override_token = ''.join(random.choice(string.ascii_lowercase) for _ in range(3))
    sql = "INSERT INTO tafs.survey_instances(user_association_id, survey_id, " +\
        "override_token, survey_open, survey_close) VALUES (" +\
        user_association_id + ", " + survey_id + ", \'" + override_token + "\', \'" + \
        default_survey_open + "\', \'" + default_survey_close + "\');"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')

# insert data into "responses" table
for _ in range(0, 10):
    survey_instance_id = str(random.choice(range(1, 11)))
    question_id = str(random.choice(range(1, 11)))
    answer = ''.join(random.choice(string.ascii_lowercase) for _ in range(3))
    user_id = random.choice(user_id_list)
    sql = "INSERT INTO tafs.responses(survey_instance_id, question_id, " +\
        "answer, user_id) VALUES (" + survey_instance_id + ", " + question_id +\
        ", \'" + answer + "\', \'" + user_id + "\');"
    sql_file.write(sql + '\n')
    print(sql)

print("\n")
sql_file.write('\n\n')


sql_file.close()
