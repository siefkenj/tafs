import os,os.path
import string,base64
import random
import json

DEPTS = {
        'Mathematics': 'MAT',
        'Computer Science': 'CSC',
        'Psychology': 'PSY',
        'Biology': 'BIO',
        'History': 'HIS'
    }
out = []
survey_names = ["Early Term Survey", "Late Term Survey", "After Term Survey"]
year = ['2017 ','2017 ','2017 ','2018 ','2018 ','2018 ']
terms = ['Winter','Summer Term 1','Summer Term 2','Fall']

for i in range(1,109):
    name =  random.choice(list(DEPTS.values())) + " " + random.choice(survey_names) + " " + random.choice(year) + random.choice(terms)
    q = "UPDATE surveys SET name = \'{}\' WHERE survey_id = {};".format(name, i)
    out.append(q)
out.append("")
print("\n".join(out))
