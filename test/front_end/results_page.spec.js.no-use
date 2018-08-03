import { shallowMount, createLocalVue } from "@vue/test-utils";
import VueRouter from "vue-router";
import TaPage from "../../src/views/components/response_components/survey_instance_list.vue";
import ResponsePage from "../../src/views/components/response_components/response_page.vue";
import ResponseComponent from "../../src/views/components/response_components/response.vue";
import chai from "chai";
import Vuetify from "vuetify"
const expect = chai.expect;
const localVue = createLocalVue();
localVue.use(Vuetify);
localVue.use(VueRouter);
const route = [
    {
        path: "",
        name: "ta_list_page",
        component: TaPage
    },
    {
        path: "responses",
        name: "response_page",
        component: ResponsePage
    }
];
const router = new VueRouter({ routes: route });

describe("survey results page", function() {
    let wrapper;
    beforeEach(function() {
        wrapper = shallowMount(TaPage, {
            localVue,
            router
        });
    });

});

describe("Survey response page", function() {
    let wrapper;
    beforeEach(function() {
        wrapper = shallowMount(ResponsePage, {
            localVue,
            router
        });
    });

    it("should return results for ta1, viewed by ta1", async function() {
        await wrapper.vm.init("ta1", "ta1", "daniel", null, "CSC103");
        expect(wrapper.vm.user_id).to.be.equal("ta1");
        expect(wrapper.vm.course).to.be.equal("CSC103");
        expect(wrapper.vm.term).to.be.equal(null);
        expect(wrapper.vm.selected_ta_id).to.be.equal("ta1");
    });

    it("should return results for ta1, viewed by prof1", async function() {
        await wrapper.vm.init("ta1", "prof1", "daniel", null, "CSC103");
        expect(wrapper.vm.user_id).to.be.equal("prof1");
        expect(wrapper.vm.course).to.be.equal("CSC103");
        expect(wrapper.vm.term).to.be.equal(null);
        expect(wrapper.vm.selected_ta_id).to.be.equal("ta1");
    });

    it("should return results for ta1, viewed by admin0", async function() {
        await wrapper.vm.init("ta1", "admin0", "daniel", null, "CSC103");
        expect(wrapper.vm.user_id).to.be.equal("admin0");
        expect(wrapper.vm.course).to.be.equal("CSC103");
        expect(wrapper.vm.term).to.be.equal(null);
        expect(wrapper.vm.selected_ta_id).to.be.equal("ta1");
    });
});
