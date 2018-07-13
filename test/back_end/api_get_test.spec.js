// This is the mocha testing file for get_info.php
const chai = require("chai");
const chaiHttp = require("chai-http");
chai.use(chaiHttp);
const expect = chai.expect;

describe("Test GET requests to API", function() {
    it("should return user info for prof1", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=user_info&user_id=prof1"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("user_package");
    });

    it("should return list of user_info", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=user_info&user_id=prof1,admin0"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("user_package");
    });
    it("should return null for each photo field", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=user_info&user_id=admin0,admin1,ta1&include_photo=false"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("user_package");

    });
    it("user info for admin0, admin1 and ta1 with photo, photo defaults to true unless specified", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=user_info&user_id=admin0,admin1,ta1&include_photo=true"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("user_package");
    });

    it("should return the list of 20 questions", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=questions"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("questions_package");
        expect(fetchedJSON.DATA).to.have.lengthOf(20);
        fetchedJSON.DATA.forEach(function(value) {
            expect(value).to.have.ownPropertyDescriptor(
                "question_id",
                "answer_type",
                "content"
            );
        });
    });

    it("should return course pairings for profs and courses", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=course_pairings&user_id=admin0&column=instructor"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
        fetchedJSON.DATA.forEach(function(value) {
            expect(value).to.have.ownPropertyDescriptor(
                "course_code",
                "department_name",
                "term",
                "user_id",
                "name",
                "section_code"
            );
        });
    });
    it("should return course pairings for tas and courses", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=course_pairings&user_id=admin0&column=ta"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
        fetchedJSON.DATA.forEach(function(value) {
            expect(value).to.have.ownPropertyDescriptor(
                "course_code",
                "department_name",
                "term",
                "user_id",
                "name",
                "section_code"
            );
        });
    });

    it("should return list of sections ta1 is enrolled in", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=course_pairings&user_id=ta1&column=sections"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
        fetchedJSON.DATA.forEach(function(value) {
            expect(value).to.have.ownPropertyDescriptor(
                "course_code",
                "department_name",
                "term",
                "user_id",
                "name",
                "section_code"
            );
        });
    });
    it("should return course parings for instructors in term 201801 under admin0", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=course_pairings&user_id=admin0&column=instructor&term=201801"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
    });
    it("should return limits course parings for instructors in CSC100 under admin0", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=course_pairings&user_id=admin0&column=instructor&course_code=CSC100"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("course_pairings_package");

    });
    it("should return limits course parings for instructors in CSC100 and term 201709 under admin0", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=course_pairings&user_id=admin0&column=instructor&course_code=CSC100&term=201709"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
    });
    it("should return list of tas under admin0", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=tas&user_id=admin0"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("ta_package");
    });
    it("should return list of tas under admin0 in term 201709", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=tas&user_id=admin0&term=201709"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("ta_package");
    });
    it("should return list of tas under admin0 CSC104", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=tas&user_id=admin0&course_code=CSC104"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("ta_package");
    });
    it("should return list of tas under admin0 in term 201709 and CSC104", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=tas&user_id=admin0&course_code=CSC104&term=201709"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("ta_package");
    });
    it("should return all surveys related to admin0", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=surveys&user_id=admin0"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("survey_package");
    });
    it("should return all surveys related to admin0 in term 201709", async function() {
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?what=surveys&user_id=admin0&term=201709"
        ).catch(function(err) {
            throw err;
        });
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("survey_package");
    });
});
