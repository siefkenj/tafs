// This is the mocha testing file for settings component
const chai = require("chai");
const expect = chai.expect;
import CoursePairings from "./../../src/views/course_pairings.vue";
import { shallowMount, createLocalVue } from "@vue/test-utils";
import VueRouter from "vue-router";

const localVue = createLocalVue();
localVue.use(VueRouter);
const route = [
    {
        path: "user_id/admin0/courses",
        component: CoursePairings
    }
];
const router = new VueRouter({ router: route });

describe("Test Course Pairings Component", function() {
    let wrapper;
    beforeEach(function() {
        wrapper = shallowMount(CoursePairings, {
            localVue,
            router
        });
    });

    it("Converts all input data into an array`", function() {
        wrapper.setData({
            user_associations: []
        });

        let data = {
            TYPE: "course_pairings_package",
            DATA: [
                {
                    user_id: "prof4",
                    course_code: "CSC100",
                    name: "prof4",
                    term: "201709",
                    section_code: "LEC0100",
                    department_name: "CSC",
                    section_id: 1
                },
                {
                    user_id: "prof3",
                    course_code: "CSC101",
                    name: "prof3",
                    term: "201709",
                    section_code: "LEC0101",
                    department_name: "CSC",
                    section_id: 2
                },
                {
                    user_id: "prof3",
                    course_code: "CSC100",
                    name: "prof3",
                    term: "201709",
                    section_code: "LEC0102",
                    department_name: "CSC",
                    section_id: 1
                },
                {
                    user_id: "prof2",
                    course_code: "CSC101",
                    name: "prof2",
                    term: "201709",
                    section_code: "LEC0103",
                    department_name: "CSC",
                    section_id: 3
                }
            ]
        };
        let expect_result = [
            [
                "CSC100",
                { name: "prof4", user_id: "prof4", section_id: 1 },
                { name: "prof3", user_id: "prof3", section_id: 1 }
            ],
            [
                "CSC101",
                { name: "prof3", user_id: "prof3", section_id: 2 },
                { name: "prof2", user_id: "prof2", section_id: 3 }
            ]
        ];

        wrapper.vm.parseData(data);
        expect(wrapper.vm.user_associations).deep.equal(expect_result);
        expect(wrapper.vm.user_associations.length).to.be.equal(
            expect_result.length
        );
    });
});
