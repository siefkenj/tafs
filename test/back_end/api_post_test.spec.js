// This is the mocha testing file for get_info.php
const chai = require("chai");
const chaiHttp = require("chai-http");
chai.use(chaiHttp);
const expect = chai.expect;
const base64o = require("../../src/base64js.min.js");
const base64 = base64o.base64;
import generate_query_string from "../../src/views/components/generate_query_string.js";

describe("Test POST/UPDATE/DELETE requests to API", function() {
    describe("Test survey setting", function() {
        it("branch survey with survey_id = 1", async function() {
            let post_body = base64.encode(
                JSON.stringify({
                    dept_survey_choices: null,
                    course_survey_choices: null,
                    ta_survey_choices: null,
                    name: "test survey",
                    term: 201709,
                    default_survey_open: null,
                    default_survey_close: null
                })
            );
            let query_string = generate_query_string({
                what: "surveys",
                user_id: "woods13",
                level: "dept",
                survey_id: 1,
                action: "branch",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
        });

        it("update survey with survey_id = 1 (purely update a current survey)", async function() {
            let post_body = base64.encode(
                JSON.stringify({
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
                })
            );
            let query_string = generate_query_string({
                what: "surveys",
                user_id: "woods13",
                level: "dept",
                survey_id: 1,
                action: "add_or_update",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
        });

        it("update survey with survey_id = 1 (branch first and then update)", async function() {
            let post_body = base64.encode(
                JSON.stringify({
                    dept_survey_choices: null,
                    course_survey_choices: {
                        course_code: "CSC100",
                        choices: [6, 5, 4, 3, 2, 1]
                    },
                    ta_survey_choices: null,
                    name: "hahaha",
                    term: 201709,
                    default_survey_open: null,
                    default_survey_close: null
                })
            );
            let query_string = generate_query_string({
                what: "surveys",
                user_id: "butler84",
                level: "course",
                survey_id: 1,
                action: "add_or_update",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
        });

        it("send data package with no attributes to the 'update survey setting' API", async function() {
            let query_string = generate_query_string({
                what: "surveys",
                user_id: "butler84",
                level: "section",
                survey_id: 1,
                action: "add_or_update"
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
        });

        it("send data package with only time setting attributes to the 'update survey setting' API", async function() {
            let timedate_open = "2010-01-01 00:00:00";
            let timedate_close = "2010-01-02 00:00:00";
            let post_body = base64.encode(
                JSON.stringify({
                    dept_survey_choices: null,
                    course_survey_choices: null,
                    ta_survey_choices: null,
                    name: null,
                    term: null,
                    default_survey_open: timedate_open,
                    default_survey_close: timedate_close
                })
            );
            let query_string = generate_query_string({
                what: "surveys",
                user_id: "woods13",
                level: "section",
                survey_id: 1,
                action: "add_or_update",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);

            // Check if the "timedate_open" and "timedate_close" are changed in the database to what we revised
            query_string = generate_query_string({
                what: "surveys",
                survey_id: 1,
                user_id: "woods13"
            });
            fetched = await fetch(
                "http://localhost:3000/get_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("survey_package");
            expect(fetchedJSON.DATA[0].timedate_open).to.be.equal(
                timedate_open
            );
            expect(fetchedJSON.DATA[0].timedate_close).to.be.equal(
                timedate_close
            );
        });

        it("send data package with only 'name' attribute to the 'update survey setting' API", async function() {
            let name = "change the name of the survey";
            let post_body = base64.encode(
                JSON.stringify({
                    ta_survey_choices: null,
                    name: name,
                    term: null,
                    default_survey_open: null,
                    default_survey_close: null
                })
            );
            let query_string = generate_query_string({
                what: "surveys",
                user_id: "butler84",
                level: "section",
                survey_id: 1,
                action: "add_or_update",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);

            // Check if the "name" is changed in the database to what we revised
            query_string = generate_query_string({
                what: "surveys",
                user_id: "woods13",
                survey_id: 1
            });
            fetched = await fetch(
                "http://localhost:3000/get_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("survey_package");
            expect(fetchedJSON.DATA[0].name).to.be.equal(name);
        });

        it("delete survey with survey_id = 2", async function() {
            let post_body = base64.encode(
                JSON.stringify({
                    dept_survey_choices: null,
                    course_survey_choices: null,
                    ta_survey_choices: null,
                    name: "updated survey",
                    term: 201801,
                    default_survey_open: null,
                    default_survey_close: null
                })
            );
            let query_string = generate_query_string({
                what: "surveys",
                user_id: "woods13",
                level: "dept",
                survey_id: 2,
                action: "delete",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
        });

        it("launch the survey with survey_id = 1", async function() {
            let query_string = generate_query_string({
                what: "launch_survey",
                user_id: "woods13",
                survey_id: 1,
                action: "add_or_update"
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("survey_package");
            expect(parseInt(fetchedJSON.DATA.survey_instance_id)).to.satisfy(
                Number.isInteger
            );
            let survey_instance_id = fetchedJSON.DATA.survey_instance_id;
            let override_token = fetchedJSON.DATA.override_token;
            query_string = generate_query_string({
                what: "survey_results",
                user_id: "woods13",
                course: "UofT",
                target_ta: "woods13"
            });
            fetched = await fetch(
                "http://localhost:3000/get_info.php?" + query_string
            );
            fetchedJSON = await fetched.json();
            // Test whether the returned survey instance contains the survey_instance we just add
            let data_package = fetchedJSON.DATA;
            let contain_survey_instance_id = false;
            for (let obj of data_package) {
                if (obj.survey_instance_id == survey_instance_id) {
                    contain_survey_instance_id = true;
                }
            }
            expect(contain_survey_instance_id).to.be.true;
        });

        it("launch the survey with survey_id = 5", async function() {
            let query_string = generate_query_string({
                what: "launch_survey",
                user_id: "woods13",
                survey_id: 5,
                action: "add_or_update"
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("survey_package");
            let survey_instance_id1 = parseInt(
                fetchedJSON.DATA.survey_instance_id
            );
            expect(survey_instance_id1).to.satisfy(Number.isInteger);

            query_string = generate_query_string({
                what: "launch_survey",
                user_id: "woods13",
                survey_id: 5,
                action: "add_or_update"
            });
            fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("survey_package");
            let survey_instance_id2 = parseInt(
                fetchedJSON.DATA.survey_instance_id
            );
            expect(survey_instance_id2).to.satisfy(Number.isInteger);

            // Make sure that the "survey_instances" generated the second time has the different id from the
            // previous survey instance
            expect(survey_instance_id1 != survey_instance_id2).to.be.true;
        });

        it("change the 'viewable_by_others' attribute in survey_instances with survey_id=1 to 1", async function() {
            let query_string = generate_query_string({
                what: "surveys",
                user_id: "woods13",
                survey_id: 1,
                action: "add_or_update",
                viewable_by_others: 1
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.DATA[0]["viewable_by_others"]).to.be.equal(true);
        });

        it("change the 'viewable_by_others' attribute in survey_instances with survey_id=5 to 0", async function() {
            let query_string = generate_query_string({
                what: "surveys",
                user_id: "woods13",
                survey_id: 5,
                action: "add_or_update",
                viewable_by_others: "0"
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.TYPE).to.be.equal("success");
            expect(fetchedJSON.DATA[0]["viewable_by_others"]).to.be.equal(
                false
            );
        });
    });

    describe("Test user info updating", function() {
        it("add a new user", async function() {
            let post_body = base64.encode(
                JSON.stringify({
                    user_list: [
                        {
                            user_id: "newTA",
                            name: "Darren",
                            photo: null
                        }
                    ]
                })
            );
            let query_string = generate_query_string({
                what: "user_info",
                action: "add_or_update",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.DATA).to.be.an("array");
        });

        it("update a user", async function() {
            let post_body = base64.encode(
                JSON.stringify({
                    user_list: [
                        {
                            user_id: "newTA",
                            name: "Bob",
                            photo: null
                        }
                    ]
                })
            );
            let query_string = generate_query_string({
                what: "user_info",
                action: "add_or_update",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.DATA).to.be.an("array");
        });

        it("delete a user", async function() {
            let post_body = base64.encode(
                JSON.stringify({
                    user_list: [
                        {
                            user_id: "newTA",
                            name: "Bob",
                            photo: null
                        }
                    ]
                })
            );
            let query_string = generate_query_string({
                what: "user_info",
                action: "delete",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.DATA).to.be.an("array");
        });
    });

    describe("Test user association updating", function() {
        it("add a user association", async function() {
            let post_body = base64.encode(
                JSON.stringify({
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
                })
            );
            let query_string = generate_query_string({
                what: "course_pairings",
                user_id: "allen89",
                mode: "user_associations",
                action: "add_or_update",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.DATA).to.be.an("array");
        });

        it("delete a user association", async function() {
            let post_body = base64.encode(
                JSON.stringify({
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
                })
            );
            let query_string = generate_query_string({
                what: "course_pairings",
                user_id: "allen89",
                mode: "user_associations",
                action: "delete",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.DATA).to.be.an("array");
        });
    });

    describe("Test updating courses and sections", function() {
        it("add a section for a course", async function() {
            let post_body = base64.encode(
                JSON.stringify({
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
                })
            );
            let query_string = generate_query_string({
                what: "course_pairings",
                user_id: "allen89",
                mode: "courses_sections",
                action: "add_or_update",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.DATA).to.be.an("array");
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
                expect(fetchedJSON.DATA).to.be.an("array");
            } catch (error) {
                throw error;
            }
        });
        */

        it("delete a course and its related sections", async function() {
            let post_body = base64.encode(
                JSON.stringify({
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
                })
            );
            let query_string = generate_query_string({
                what: "course_pairings",
                user_id: "allen89",
                mode: "user_associations",
                action: "delete",
                post_body: "base64:" + post_body
            });
            let fetched = await fetch(
                "http://localhost:3000/post_info.php?" + query_string
            );
            expect(fetched).to.have.status(200);
            let fetchedJSON = await fetched.json();
            expect(fetchedJSON.DATA).to.be.an("array");
        });
    });
});
