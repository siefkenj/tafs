// This is the mocha testing file for url generator
const chai = require("chai");
const assert = chai.assert;
import generate_query_string from "./../../src/views/components/generate_query_string.js"

describe("Test URL generator", function() {
    it("should return `get_info.php?what=questions`", function() {
        let url =
            {
            "what":"questions"
            };

        let result = generate_query_string(url);
        assert.equal(result, "what=questions");
    });

    it("should return `get_info.php?what=course_pairings&user_id=admin0&column=instructor&term=201801&course_code=CSC100`", function() {
        let url =
            {
            "what":"course_pairings",
            "user_id":"admin0",
            "column":"instructor",
            "term":201801,
            "course_code":"CSC100"
            };

        let result = generate_query_string(url);
        assert.equal(result, "what=course_pairings&user_id=admin0&column=instructor&term=201801&course_code=CSC100");
    });
});
