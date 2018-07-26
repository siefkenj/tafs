// This is the mocha testing file for get_info.php
const chai = require("chai");
const chaiHttp = require("chai-http");
chai.use(chaiHttp);
const expect = chai.expect;

describe("Test GET requests to API", function() {
    it("should return user info for Joshua Cook", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=user_info&user_id=cook19"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("user_package");
        } catch (err) {
            throw err;
        }
    });

    it("should return list of user_info", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=user_info&user_id=ortiz48,price24"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("user_package");
        } catch (err) {
            throw err;
        }
    });
    it("should return null for each photo field", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=user_info&user_id=lewis42,price24,ortiz48&include_photo=false"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("user_package");
        } catch (err) {
            throw err;
        }
    });
    it("user info for admin0, admin1 and ta1 with photo, photo defaults to true unless specified", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=user_info&user_id=admin0,admin1,ta1&include_photo=true"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("user_package");
        } catch (err) {
            throw err;
        }
    });

    it("should return the list of questions", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=questions"
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
        } catch (err) {
            throw err;
        }
    });

    it("should return course pairings for profs and courses for Christopher Butler", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=course_pairings&user_id=butler64&column=instructor"
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
        } catch (err) {
            throw err;
        }
    });
    it("should return course pairings for tas and courses for Christopher Butler", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=course_pairings&user_id=butler64&column=ta"
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
        } catch (err) {
            throw err;
        }
    });

    it("should return list of sections Karen Lopez is enrolled in", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=course_pairings&user_id=lopez44&column=sections"
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
        } catch (err) {
            throw err;
        }
    });
    it("should return course parings for instructors in term 201801 under Larry Wells", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=course_pairings&user_id=wells8&column=instructor&term=201801"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();

            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
        } catch (err) {
            throw err;
        }
    });
    it("should return limits course parings for instructors in CSC100 under Larry Wells", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=course_pairings&user_id=wells8&column=instructor&course_code=CSC100"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();

            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
        } catch (err) {
            throw err;
        }
    });
    it("should return limits course parings for instructors in CSC100 and term 201709 under Larry Wells", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=course_pairings&user_id=wells8&column=instructor&course_code=CSC100&term=201709"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();

            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("course_pairings_package");
        } catch (err) {
            throw err;
        }
    });
    it("should return list of tas under Elizabeth Miller", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=tas&user_id=miller38"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();

            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("ta_package");
        } catch (err) {
            throw err;
        }
    });
    it("should return list of tas under Elizabeth Miller in term 201709", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=tas&user_id=miller38&term=201709"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();

            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("ta_package");
        } catch (err) {
            throw err;
        }
    });
    it("should return list of tas under James Lee CSC104", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=tas&user_id=lee91&course_code=CSC104"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();

            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("ta_package");
        } catch (err) {
            throw err;
        }
    });
    it("should return list of tas under James Lee in term 201709 and CSC104", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=tas&user_id=lee91&course_code=CSC104&term=201709"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();

            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("ta_package");
        } catch (err) {
            throw err;
        }
    });
    it("should return all surveys related to Laura King", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=surveys&user_id=king23"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();

            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("survey_package");
        } catch (err) {
            throw err;
        }
    });
    it("should return all surveys related to admin0 in term Laura King", async function() {
        try {
            let fetched = await fetch(
                "http://localhost:3000/get_info.php?what=surveys&user_id=king23&term=201709"
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON).to.have.nested.property("TYPE");
            expect(fetchedJSON).to.have.nested.property("DATA");
            expect(fetchedJSON.TYPE).to.equal("survey_package");
        } catch (err) {
            throw err;
        }
    });
});
