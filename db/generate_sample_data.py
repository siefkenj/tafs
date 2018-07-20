#!/usr/bin/python
import os,os.path
import string,base64
import random
import json

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
    QUESTIONS = [
            '''{"type":"rating","name":"Understanding","title":"The tutorial/lab helped me better understand the course material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Orgainization","title":"The tutorial/lab sessions were organized.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Preparedness","title":"The teaching assistant was well-prepared for tutorial/lab sessions.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Explanations","title":"The teaching assistant explained tutorial/lab topics and concepts clearly.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Respectfulness","title":"The teaching assistant responded respectfully to student questions during lab/tutorial sessions.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Learning","title":"The support my teaching assistant provided in the course contributed to my overall learning.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Feedback","title":"The teaching assistant’s feedback on course assignments, projects, papers and/or tests helped me understand the grades I received.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Feedback2","title":"The teaching assistant’s feedback on course assignments, projects, papers and/or tests improved my understanding of the course material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Enthusiasm","title":"The teaching assistant was enthusiastic about the tutorial/lab material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}''',
            '''{"type":"rating","name":"Quality","title":"Overall, the quality of support the teaching assistant provided in the tutorial/lab was:Overall, the quality of my learning experience in the tutorial/lab was:","rateValues":[{"value":"1","text":"Poor"},{"value":"2","text":"Fair"},{"value":"3","text":"Good"},{"value":"4","text":"Very Good"},{"value":"5","text":"Excellent"}]}''',
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

if __name__ == "__main__":
    import json
    gen = GenExamples()

    out = []
    # departments
    for dept in gen.DEPTS:
        q = "INSERT INTO departments VALUES('{}');".format(dept)
        out.append(q)
    out.append("")

    # users add admins (1,0,0), instructors (0,1,0), and tas (0,0,1) as well
    # as some mixed
    for num,a,b,c in [(2,1,0,0),(2,1,1,0),(4,0,1,0),(20,0,0,1)]:
        names = [gen.name() for _ in range(10)]
        user_ids = [gen.utorid(name) for name in names] 
        for name, user_id in zip(names, user_ids):
            q = "INSERT INTO users VALUES ('{}',{},{},{},'{}',NULL);".format(name, a, b, c, user_id)
            out.append(q)
    out.append("")

    # questions
    for question in gen.QUESTIONS:
        q_type = json.loads(question)['type']
        q = "INSERT INTO questions(answer_type,content) VALUES ('{}','{}')".format(q_type, question)
        out.append(q)
    out.append("")

    print("\n".join(out))
