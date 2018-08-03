// This is the mocha testing file for get_info.php
const chai = require("chai");
const chaiHttp = require("chai-http");
chai.use(chaiHttp);
const expect = chai.expect;
const base64o = require("../../src/base64js.min.js");
const base64 = base64o.base64

describe("Test POST/UPDATE/DELETE requests to API", function() {
    describe("Test survey setting", function() {
        it("branch survey with survey_id = 1", async function() {
            let body = JSON.stringify({
                dept_survey_choices: null,
                course_survey_choices: null,
                ta_survey_choices: null,
                name: "test survey",
                term: 201709,
                default_survey_open: null,
                default_survey_close: null
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=surveys&user_id=woods13&level=dept&survey_id=1&action=branch&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.equal(null);
            } catch (error) {
                throw error;
            }
        });

        it("update survey with survey_id = 1 (purely update a current survey)", async function() {
            let body = JSON.stringify({
                dept_survey_choices: {
                    department_name: "CSC",
                    choices: [1, 2, 3, 4, 5, 6]
                },
                course_survey_choices: null,
                ta_survey_choices: null,
                name: "updated survey",
                term: 201801,
                default_survey_open: null,
                default_survey_close: null
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=surveys&user_id=admin0&level=dept&survey_id=1&action=add_or_update&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.equal(null);
            } catch (error) {
                throw error;
            }
        });

        it("update survey with survey_id = 1 (branch first and then update)", async function() {
            let body = JSON.stringify({
	               "dept_survey_choices": null,
	               "course_survey_choices": {
		                "course_code": "CSC100",
		                "choices": [6, 5, 4, 3, 2, 1]
	               },
	               "ta_survey_choices": null,
	               "name": "hahaha",
	               "term": 201709,
	               "default_survey_open": null,
	               "default_survey_close": null
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=surveys&user_id=prof3&level=course&survey_id=1&action=add_or_update&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.equal(null);
            } catch (error) {
                throw error;
            }
        });

        it("delete survey with survey_id = 2", async function() {
            let body = JSON.stringify({
                dept_survey_choices: null,
                course_survey_choices: null,
                ta_survey_choices: null,
                name: "updated survey",
                term: 201801,
                default_survey_open: null,
                default_survey_close: null
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=surveys&user_id=admin0&level=dept&survey_id=2&action=delete&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.equal(null);
            } catch (error) {
                throw error;
            }
        });

        it("change the 'viewable_by_others' attribute in survey_instances with survey_id=1 to 1", async function () {
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=surveys&user_id=admin0&survey_id=1&viewable_by_others=true&action=add_or_update"
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.data).to.be.equal(null);
            } catch (error) {
                throw error;
            }
        });

        it("change the 'viewable_by_others' attribute in survey_instances with survey_id=2 to 0", async function () {
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=surveys&user_id=admin0&survey_id=0&viewable_by_others=0&action=add_or_update"
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.equal(null);
            } catch (error) {
                throw error;
            }
        });
    });

    describe("Test user info updating", function() {
        it("add a new user", async function() {
            let body = JSON.stringify({
                user_list: [
                    {
                        user_id: "newTA",
                        name: "Darren",
                        photo: null
                    }
                ]
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=user_info&action=add_or_update&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.an("array");
            } catch (error) {
                throw error;
            }
        });

        it("update a user", async function() {
            let body = JSON.stringify({
                user_list: [
                    {
                        user_id: "newTA",
                        name: "Bob",
                        photo: null
                    }
                ]
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=user_info&action=add_or_update&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.an("array");
            } catch (error) {
                throw error;
            }
        });

        it("delete a user", async function() {
            let body = JSON.stringify({
                user_list: [
                    {
                        user_id: "newTA",
                        name: "Bob",
                        photo: null
                    }
                ]
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=user_info&action=delete&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.an("array");
            } catch (error) {
                throw error;
            }
        });
    });

    describe("Test user association updating", function() {
        it("add a user association", async function() {
            let body = JSON.stringify({
                association_list: [
                    {
                        course: {
                            course_code: "HIS399",
                            title: "HIS399",
                            department_name: "HIS",
                            term: 201801
                        },
                        section: {
                            section_code: "HIS399",
                            term: 201801,
                            section_id: 1
                        },
                        user_id: "allen89"
                    }
                ]
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=course_pairings&user_id=admin1&mode=user_associations&action=add_or_update&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.an("array");
            } catch (error) {
                throw error;
            }
        });

        it("delete a user association", async function() {
            let body = JSON.stringify({
                association_list: [
                    {
                        course: {
                            course_code: "HIS399",
                            title: "HIS399",
                            department_name: "HIS",
                            term: 201801
                        },
                        section: {
                            section_code: "HIS399",
                            term: 201801,
                            section_id: 1
                        },
                        user_id: "allen89"
                    }
                ]
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=course_pairings&user_id=admin1&mode=user_associations&action=delete&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.an("array");
            } catch (error) {
                throw error;
            }
        });
    });

    describe("Test updating courses and sections", function() {
        it("add a section for a course", async function() {
            let body = JSON.stringify({
                association_list: [
                    {
                        course: {
                            course_code: "CSC100",
                            title: "CSC100",
                            department_name: "History",
                            term: 201801
                        },
                        section: {
                            section_code: "LEC0501",
                            term: 201801,
                            section_id: null
                        },
                        user_id: "admin0"
                    }
                ]
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=course_pairings&user_id=admin0&mode=courses_sections&action=add_or_update&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.an("array");
            } catch (error) {
                throw error;
            }
        });

        /*
        it("delete a section for a course", async function() {
            let body = JSON.stringify({
                association_list: [
                    {
                        course: {
                            course_code: "CSC100",
                            title: "CSC100",
                            department_name: "History",
                            term: 201801
                        },
                        section: {
                            section_code: "CSC100",
                            term: 201801,
                            section_id: 1
                        },
                        user_id: "admin0"
                    }
                ]
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=course_pairings&user_id=admin0&mode=courses_sections&action=delete&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.data).to.be.an("array");
            } catch (error) {
                throw error;
            }
        });
        */

        it("delete a course and its related sections", async function() {
            let body = JSON.stringify({
                association_list: [
                    {
                        course: {
                            course_code: "MAT100",
                            title: "MAT100",
                            department_name: "MAT",
                            term: 201801
                        },
                        section: null,
                        user_id: "admin0"
                    }
                ]
            });
            let post_body = base64.encode(body);
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=course_pairings&user_id=admin0&mode=courses_sections&action=delete&post_body=base64:" + post_body
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.DATA).to.be.an("array");
            } catch (error) {
                throw error;
            }
        });
    });
});
