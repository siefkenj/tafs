// This is the mocha testing file for settings component
const chai = require("chai");
const expect = chai.expect;
import QuestionTime from "./../../src/views/components/survey_components/question_time_setting.vue";
import { shallowMount, createLocalVue } from "@vue/test-utils";
import VueRouter from "vue-router";

const localVue = createLocalVue();
localVue.use(VueRouter);
const route = [
    {
        path: "/user_id/admin0/surveys/question_time",
        component: QuestionTime
    }
];
const router = new VueRouter({ router: route });

describe("Test Surveys Component", function() {
    let wrapper;
    beforeEach(function() {
        wrapper = shallowMount(QuestionTime, {
            localVue,
            router
        });
    });

    it("Question filter test should return false", function() {
        wrapper.setData({
            question_id_array: [1, 2, 3, 4]
        });
        let result = wrapper.vm.question_filter({ question_id: 2 });
        expect(result).to.be.equal(false);
    });

    it("Question filter test should return true", function() {
        wrapper.setData({
            question_id_array: [1, 2, 3, 4]
        });
        let result = wrapper.vm.question_filter({ question_id: 12 });
        expect(result).to.be.equal(true);
    });

    it("cancel should set selected null and edit_click to false", function() {
        wrapper.setData({
            selected: "",
            edit_click: true
        });
        wrapper.vm.cancel();
        expect(wrapper.vm.selected).to.be.equal(null);
        expect(wrapper.vm.edit_click).to.be.equal(false);
    });

    it("checkbox_click test should set first element of check_status to true", function() {
        wrapper.setData({
            questions: [{ question_id: 1 }, { question_id: 2 }],
            checkbox_indices: [0, 1],
            check_status: [false, false]
        });
        wrapper.vm.checkbox_click({ question_id: 1 });
        expect(wrapper.vm.check_status[0]).to.be.equal(true);
    });

    it("checkbox_click test should set second element of check_status to true", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 }
            ],
            checkbox_indices: [2, 3],
            check_status: [false, false]
        });
        wrapper.vm.checkbox_click({ question_id: 1 });
        expect(wrapper.vm.check_status[1]).to.be.equal(true);
    });

    it("checkbox_click test should set first element of check_status to true", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 }
            ],
            checkbox_indices: [2, 3],
            check_status: [false, false]
        });
        wrapper.vm.checkbox_click({ question_id: 3 });
        expect(wrapper.vm.check_status[0]).to.be.equal(true);
    });

    it("contain_edit test should return true", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 }
            ],
            checkbox_indices: [1, 2]
        });
        let result = wrapper.vm.contain_edit({ question_id: 3 });
        expect(result).to.be.equal(true);
    });

    it("contain_edit test should return false", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 }
            ],
            checkbox_indices: [1, 2]
        });
        let result = wrapper.vm.contain_edit({ question_id: 1 });
        expect(result).to.be.equal(false);
    });

    it("contain_checkbox test should return true", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 }
            ],
            checkbox_indices: [1, 2]
        });
        let result = wrapper.vm.contain_edit({ question_id: 2 });
        expect(result).to.be.equal(true);
    });

    it("contain_checkbox test should return false", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 }
            ],
            checkbox_indices: [1, 2]
        });
        let result = wrapper.vm.contain_edit({ question_id: 1 });
        expect(result).to.be.equal(false);
    });

    it("contain_up_arrow test should return true", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 }
            ],
            checkbox_indices: [1, 2]
        });
        let result = wrapper.vm.contain_up_arrow({ question_id: 3 });
        expect(result).to.be.equal(true);
    });

    it("contain_up_arrow test should return false", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 }
            ],
            checkbox_indices: [1, 2]
        });
        let result = wrapper.vm.contain_up_arrow({ question_id: 2 });
        expect(result).to.be.equal(false);
    });

    it("contain_down_arrow test should return true", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 }
            ],
            checkbox_indices: [1, 2]
        });
        let result = wrapper.vm.contain_down_arrow({ question_id: 2 });
        expect(result).to.be.equal(true);
    });

    it("contain_down_arrow test should return false", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 }
            ],
            checkbox_indices: [1, 2]
        });
        let result = wrapper.vm.contain_down_arrow({ question_id: 1 });
        expect(result).to.be.equal(false);
    });

    it("contain_down_arrow test should return false", function() {
        wrapper.setData({
            questions: [
                { question_id: 1 },
                { question_id: 2 },
                { question_id: 3 },
                { question_id: 4 },
                { question_id: 5 },
                { question_id: 6 }
            ],
            checkbox_indices: [1, 2]
        });
        let result = wrapper.vm.contain_down_arrow({ question_id: 6 });
        expect(result).to.be.equal(false);
    });

    it("show_choice test should set selected to question and edit_click to true", function() {
        wrapper.setData({
            selected: null,
            edit_click: false
        });
        wrapper.vm.show_choice(1);
        expect(wrapper.vm.selected).to.be.equal(1);
        expect(wrapper.vm.edit_click).to.be.equal(true);
    });

    it("compare test should return function that computes differences in positon", function() {
        let result = wrapper.vm.compare();
        let difference = result({ position: 10 }, { position: 30 });
        expect(difference).to.be.equal(-20);
    });

    it("up_exchange test should move question_id 6 up one position", function() {
        wrapper.setData({
            questions: [
                { question_id: 1, position: 1 },
                { question_id: 2, position: 2 },
                { question_id: 3, position: 3 },
                { question_id: 4, position: 4 },
                { question_id: 5, position: 5 },
                { question_id: 6, position: 6 }
            ]
        });
        wrapper.vm.up_exchange({ question_id: 6, position: 6 });
        expect(wrapper.vm.questions[4].question_id).to.be.equal(6);
        expect(wrapper.vm.questions[5].question_id).to.be.equal(5);
    });

    it("down_exchange test should move question_id 5 down one position", function() {
        wrapper.setData({
            questions: [
                { question_id: 1, position: 1 },
                { question_id: 2, position: 2 },
                { question_id: 3, position: 3 },
                { question_id: 4, position: 4 },
                { question_id: 5, position: 5 },
                { question_id: 6, position: 6 }
            ]
        });
        wrapper.vm.down_exchange({ question_id: 5, position: 5 });
        expect(wrapper.vm.questions[5].question_id).to.be.equal(5);
        expect(wrapper.vm.questions[4].question_id).to.be.equal(6);
    });

    it("finish test should set lock position of selected questions with all locked", function() {
        wrapper.setData({
            questions: [
                { question_id: 1, locked: 0 },
                { question_id: 2, locked: 0 },
                { question_id: 3, locked: 0 },
                { question_id: 4, locked: 0 }
            ],
            checkbox_indices: [1, 2],
            check_status: [1, 1]
        });
        wrapper.vm.save();
        expect(wrapper.vm.questions[1].locked).to.be.equal(1);
        expect(wrapper.vm.questions[2].locked).to.be.equal(1);
    });

    it("finish test should set lock position of selected questions with one not locked", function() {
        wrapper.setData({
            questions: [
                { question_id: 1, locked: 0 },
                { question_id: 2, locked: 0 },
                { question_id: 3, locked: 0 },
                { question_id: 4, locked: 0 }
            ],
            checkbox_indices: [1, 2],
            check_status: [1, 0]
        });
        wrapper.vm.save();
        expect(wrapper.vm.questions[1].locked).to.be.equal(1);
        expect(wrapper.vm.questions[2].locked).to.be.equal(0);
    });
});
