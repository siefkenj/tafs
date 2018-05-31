// This is the mocha testing file for api.php
import "isomorphic-fetch";
import chai from "chai";

describe("Test GET request to API", function () {
    it("get the list of users", async function () {
        let fetched = await fetch("localhost:3000/api.php?url=users%2F")
        .catch(function (err) {
            throw(err);
        });

    });

    it("get a user with specific utorid", async function () {
        let fetched = await fetch("localhost:3000/api.php?url=users%2Fabcd1000")
        .catch(function(err) {
            throw(err);
        });

    });

    it("get the list of courses", async function () {
        let fetched = await fetch("localhost:3000/api.php?url=courses%2F")
        .catch(function(err) {
            throw(err);
        });

    });

    it("get a specific course", async function () {
        let fetched = await fetch("localhost:3000/api.php?url=courses%2FCSC411")
        .catch(function(err) {
            throw(err);
        });

    });

    it("get a list of sections of a course", async function () {
        let fetched = await fetch("localhost:3000/api.php?url=courses%2FCSC411%2Fsections")
        .catch(function(err) {
            throw(err);
        });

    });

    it("get a specific section in a course", async function () {
        let fetched = await fetch("localhost:3000/api.php?url=courses%2FCSC411%2Fsections%2FLEC0101")
        .catch(function(err) {
            throw(err);
        });

    });

    it("get the list of surveys", async function () {
        let fetched = await fetch("localhost:3000/api.php?url=survey%2F")
        .catch(function(err) {
            throw(err);
        });

    });

    it("get a specific survey model", async function () {
        let fetched = await fetch("localhost:3000/api.php?url=survey%2F0")
        .catch(function(err) {
            throw(err);
        });

    });

    it("get reponses from a specific survey model", async function () {
        let fetched = await fetch("localhost:3000/api.php?url=survey%2F0%2Fresponses")
        .catch(function(err) {
            throw(err);
        });

    });
});


describe("Test POST request to API", function () {

    it("create a course", async function () {
        let fetched = fetch("localhost:3000/api.php", {
            method: 'POST',
            mode: 'cors',
            body: JSON.stringify()
        }).catch(function (err) {
            throw(err);
        });


    });

    it("create a section", async function () {
        let fetched = fetch("localhost:3000/api.php", {
            method: 'POST',
            mode: 'cors',
            body: JSON.stringify()
        }).catch(function (err) {
            throw(err);
        });


    });

    it("create a survey", async function () {
        let fetched = fetch("localhost:3000/api.php", {
            method: 'POST',
            mode: 'cors',
            body: JSON.stringify()
        }).catch(function (err) {
            throw(err);
        });

  
    });

    it("create a response", async function () {
        let fetched = fetch("localhost:3000/api.php", {
            method: 'POST',
            mode: 'cors',
            body: JSON.stringify()
        }).catch(function (err) {
            throw(err);
        });

 
    });
});
