// This is the mocha testing file for get_info.php
const chai = require("chai");
const chaiHttp = require("chai-http");
chai.use(chaiHttp);
const expect = chai.expect;
const assert = chai.assert;

import generate_query_string from "../../src/views/components/generate_query_string.js";

describe("Test GET requests to API", function() {
    it("should return user info for Joshua Cook", async function() {
        let query_string = generate_query_string({
            what: "user_info",
            user_id: "cook19"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("user_package");
    });

    it("should return list of user_info", async function() {
        let query_string = generate_query_string({
            what: "user_info",
            user_id: "ortiz48,price24"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("user_package");
    });
    it("should return null for each photo field", async function() {
        let query_string = generate_query_string({
            what: "user_info",
            user_id: "lewis42,price24,ortiz48",
            include_photo: "false"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("user_package");
    });
    it("user info for admin0, admin1 and ta1 with photo, photo defaults to true unless specified", async function() {
        let query_string = generate_query_string({
            what: "user_info",
            user_id: "admin0,admin1,ta1",
            include_photo: "true"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("user_package");
    });

    it("should return the list of questions", async function() {
        let query_string = generate_query_string({
            what: "questions"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();

        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("questions_package");
        fetchedJSON.DATA.forEach(function(value) {
            expect(value).to.have.ownPropertyDescriptor(
                "question_id",
                "answer_type",
                "content"
            );
        });
    });

    it("should return course pairings for profs and courses for Christopher Butler", async function() {
        let query_string = generate_query_string({
            what: "course_pairings",
            user_id: "butler64",
            column: "instructor"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
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
    it("should return course pairings for tas and courses for Christopher Butler", async function() {
        let query_string = generate_query_string({
            what: "course_pairings",
            user_id: "butler64",
            column: "ta"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
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

    it("should return list of sections Karen Lopez is enrolled in", async function() {
        let query_string = generate_query_string({
            what: "course_pairings",
            user_id: "lopez44",
            column: "sections"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
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
    it("should return course parings for instructors in term 201801 under Larry Wells", async function() {
        let query_string = generate_query_string({
            what: "course_pairings",
            user_id: "wells8",
            column: "instructor",
            term: "201801"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();

        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
    });
    it("should return limits course parings for instructors in CSC100 under Larry Wells", async function() {
        let query_string = generate_query_string({
            what: "course_pairings",
            user_id: "wells8",
            column: "instructor",
            course_code: "CSC100"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();

        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
    });
    it("should return limits course parings for instructors in CSC100 and term 201709 under Larry Wells", async function() {
        let query_string = generate_query_string({
            what: "course_pairings",
            user_id: "wells8",
            column: "instructor",
            course_code: "CSC100",
            term: "201709"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();

        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
    });
    it("should return list of tas under Elizabeth Miller", async function() {
        let query_string = generate_query_string({
            what: "tas",
            user_id: "miller38"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();

        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("ta_package");
    });
    it("should return list of tas under Elizabeth Miller in term 201709", async function() {
        let query_string = generate_query_string({
            what: "tas",
            user_id: "miller38",
            term: "201709"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();

        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("ta_package");
    });
    it("should return list of tas under James Lee CSC104", async function() {
        let query_string = generate_query_string({
            what: "tas",
            user_id: "lee91",
            course_code: "CSC104"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();

        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("ta_package");
    });
    it("should return list of tas under James Lee in term 201709 and CSC104", async function() {
        let query_string = generate_query_string({
            what: "tas",
            user_id: "miller38",
            course_code: "CSC104",
            term: "201709"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();

        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("ta_package");
    });
    it("should return all surveys related to Laura King", async function() {
        let query_string = generate_query_string({
            what: "surveys",
            user_id: "king23"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();

        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("survey_package");
    });
    it("should return all surveys related to Laura King in term 201709", async function() {
        let query_string = generate_query_string({
            what: "surveys",
            user_id: "king23",
            term: "201709"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("survey_package");
    });
    it("should return all surveys related to Hame23", async function() {
        let query_string = generate_query_string({
            what: "surveys",
            user_id: "Hame23",
            term: "201809"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("survey_package");
        assert.isAtLeast(fetchedJSON.DATA.length, 1, 'Hamed has greater or equal to 1 surveys');
    });
    it("should return at least 1 survey related to Hame23 in term 201809", async function() {
        let query_string = generate_query_string({
            what: "surveys",
            user_id: "Hame23",
            term: "201809"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("survey_package");
        assert.isAtLeast(fetchedJSON.DATA.length, 1, 'Hamed has greater or equal to 1 surveys');
    });
    it("should return at least 1 survey related to Hame23 in term 201809 and course_code UofT101", async function() {
        let query_string = generate_query_string({
            what: "surveys",
            user_id: "Hame23",
            course_code: "UofT101",
            term: "201809"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("survey_package");
        assert.isAtLeast(fetchedJSON.DATA.length, 1, 'Hamed has greater or equal to 1 surveys');
    });
    it("should return at least 1 survey related to Hame23 in term 201801 with course UofT101", async function() {
        let query_string = generate_query_string({
            what: "surveys",
            user_id: "Hame23",
            course_code: "UofT101",
            term: "201801"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("survey_package");
        assert.isAtLeast(fetchedJSON.DATA.length, 1, 'Hamed has greater or equal to 1 surveys');
    });
    it("should return no surveys related to Hame23 in term 201809 with course CSC108", async function() {
        let query_string = generate_query_string({
            what: "surveys",
            user_id: "Hame23",
            course_code: "CSC108",
            term: "201809"
        });
        let fetched = await fetch(
            "http://localhost:3000/get_info.php?" + query_string
        );
        expect(fetched).to.have.status(200);
        let fetchedJSON = await fetched.json();
        expect(fetchedJSON).to.have.nested.property("TYPE");
        expect(fetchedJSON).to.have.nested.property("DATA");
        expect(fetchedJSON.TYPE).to.equal("survey_package");
        expect(fetchedJSON.DATA.length).to.equal(0);
    });
});
