// mocha unit testing for post_info.php
const chai = require("chai");
const chaiHttp = require("chai-http");
const fetch = require("isomorphic-fetch");
chai.use(chaiHttp);
const expect = chai.expect;

describe("Test POST/UPDATE/DELETE requests to API", function() {
    describe("Test survey setting", function() {
        it("branch survey with survey_id = 1", async function() {
            try {
                let fetched = await fetch(
                    "http://localhost:3000/post_info.php?what=surveys&user_id=admin0&level=dept&survey_id=1&action=branch",
                    {
                        body: JSON.stringify({
                            dept_survey_choices: null,
                            course_survey_choices: null,
                            ta_survey_choices: null,
                            name: "test survey",
                            term: 201801,
                            default_survey_open: null,
                            default_survey_close: null
                        }),
                        headers: new Headers({
                            "content-type": "application/json"
                        }),
                        method: "POST",
                        mode: "cors"
                    }
                );
                expect(fetched).to.have.status(200);
                let fetchedJSON = await fetched.json();
                expect(fetchedJSON.TYPE).to.be.equal("success");
                expect(fetchedJSON.data).to.be.equal(null);
            } catch (error) {
                throw error;
            }
        });

        it("update survey with survey_id = 1", async function() {
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?what=surveys&user_id=admin0&level=dept&survey_id=1&action=add_or_update",
                {
                    body: JSON.stringify({
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
                    }),
                    headers: new Headers({
                        "content-type": "application/json"
                    }),
                    method: "POST",
                    mode: "cors"
                }
            ).catch(function(error) {
                throw error;
            });
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.data).to.be.equal(null);
        });

        it("delete survey with survey_id = 2", async function() {
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?what=surveys&user_id=admin0&level=dept&survey_id=2&action=delete",
                {
                    body: JSON.stringify({
                        dept_survey_choices: null,
                        course_survey_choices: null,
                        ta_survey_choices: null,
                        name: "updated survey",
                        term: 201801,
                        default_survey_open: null,
                        default_survey_close: null
                    }),
                    headers: new Headers({
                        "content-type": "application/json"
                    }),
                    method: "POST",
                    mode: "cors"
                }
            ).catch(function(error) {
                throw error;
            });
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.data).to.be.equal(null);
        });
    });

    describe("Test user info updating", function() {
        it("add a new user", async function() {
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?what=user_info&action=add_or_update",
                {
                    body: JSON.stringify({
                        user_list: [
                            {
                                user_id: "newTA",
                                name: "Darren",
                                photo: null
                            }
                        ]
                    }),
                    headers: new Headers({
                        "content-type": "application/json"
                    }),
                    method: "POST",
                    mode: "cors"
                }
            ).catch(function(error) {
                throw error;
            });
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.data).to.be.an("array");
        });

        it("update a user", async function() {
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?what=user_info&action=add_or_update",
                {
                    body: JSON.stringify({
                        user_list: [
                            {
                                user_id: "newTA",
                                name: "Bob",
                                photo: null
                            }
                        ]
                    }),
                    headers: new Headers({
                        "content-type": "application/json"
                    }),
                    method: "POST",
                    mode: "cors"
                }
            ).catch(function(error) {
                throw error;
            });
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.data).to.be.an("array");
        });

        it("delete a user", async function() {
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?what=user_info&action=delete",
                {
                    body: JSON.stringify({
                        user_list: [
                            {
                                user_id: "newTA",
                                name: "Bob",
                                photo: null
                            }
                        ]
                    }),
                    headers: new Headers({
                        "content-type": "application/json"
                    }),
                    method: "POST",
                    mode: "cors"
                }
            ).catch(function(error) {
                throw error;
            });
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.data).to.be.an("array");
        });
    });

    describe("Test user association updating", function() {
        it("add a user association", async function() {
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?what=course_pairings&user_id=admin1&mode=user_associations&action=add_or_update",
                {
                    body: JSON.stringify({
                        association_list: [
                            {
                                course: {
                                    course_code: "CSC100",
                                    title: "CSC100",
                                    department_name: "CSC",
                                    term: 201801
                                },
                                section: {
                                    section_code: "CSC100",
                                    term: 201801,
                                    section_id: 1
                                },
                                user_id: "admin1"
                            }
                        ]
                    }),
                    headers: new Headers({
                        "content-type": "application/json"
                    }),
                    method: "POST",
                    mode: "cors"
                }
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.data).to.be.an("array");
        });

        it("delete a user association", async function() {
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?what=course_pairings&user_id=admin1&mode=user_associations&action=delete",
                {
                    body: JSON.stringify({
                        association_list: [
                            {
                                course: {
                                    course_code: "CSC100",
                                    title: "CSC100",
                                    department_name: "CSC",
                                    term: 201801
                                },
                                section: {
                                    section_code: "CSC100",
                                    term: 201801,
                                    section_id: 1
                                },
                                user_id: "admin1"
                            }
                        ]
                    }),
                    headers: new Headers({
                        "content-type": "application/json"
                    }),
                    method: "POST",
                    mode: "cors"
                }
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.data).to.be.an("array");
        });
    });

    describe("Test updating courses and sections", function() {
        it("add a section for a course", async function() {
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?what=course_pairings&user_id=admin0&mode=courses_sections&action=add_or_update",
                {
                    body: JSON.stringify({
                        association_list: [
                            {
                                course: {
                                    course_code: "CSC100",
                                    title: "CSC100",
                                    department_name: "CSC",
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
                    }),
                    headers: new Headers({
                        "content-type": "application/json"
                    }),
                    method: "POST",
                    mode: "cors"
                }
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.data).to.be.an("array");
        });

        it("delete a section for a course", async function() {
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?what=course_pairings&user_id=admin0&mode=courses_sections&action=delete",
                {
                    body: JSON.stringify({
                        association_list: [
                            {
                                course: {
                                    course_code: "CSC100",
                                    title: "CSC100",
                                    department_name: "CSC",
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
                    }),
                    headers: new Headers({
                        "content-type": "application/json"
                    }),
                    method: "POST",
                    mode: "cors"
                }
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.data).to.be.an("array");
        });

        it("delete a course and its related sections", async function() {
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?what=course_pairings&user_id=admin0&mode=courses_sections&action=delete",
                {
                    body: JSON.stringify({
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
                    }),
                    headers: new Headers({
                        "content-type": "application/json"
                    }),
                    method: "POST",
                    mode: "cors"
                }
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.data).to.be.an("array");
        });
    });
});
