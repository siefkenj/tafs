// This is the mocha testing file for settings component
const chai = require("chai");
const expect = chai.expect;
import Settings from "./../src/views/settings.vue";
import { shallowMount, createLocalVue } from "@vue/test-utils";
import VueRouter from "vue-router";

const localVue = createLocalVue();
localVue.use(VueRouter);
const route = [
    {
        path: "user_id/admin0/settings",
        component: Settings
    }
];
const router = new VueRouter({ router: route });

describe("Test Settings Component", function() {
    let wrapper;
    beforeEach(function() {
        wrapper = shallowMount(Settings, {
            localVue,
            router
        });
    });

    it("Only display and temporary data should be mutated`", function() {
        wrapper.setData({
            name: "name",
            photo: "photo",
            temp_name: "No Change Name",
            temp_photo: "No Change Photo"
        });
        wrapper.vm.saveData(false);
        expect(wrapper.vm.name).to.be.equal("name");
        expect(wrapper.vm.photo).to.be.equal("photo");
        expect(wrapper.vm.display).to.be.equal(false);
    });

    it("Name and photo should be set to temp photo and name field`", function() {
        wrapper.setData({
            name: "name",
            photo: "photo",
            temp_name: "Change Name",
            temp_photo: "Change Photo"
        });
        wrapper.vm.saveData(true);
        expect(wrapper.vm.name).to.be.equal("Change Name");
        expect(wrapper.vm.photo).to.be.equal("Change Photo");
        expect(wrapper.vm.temp_name).to.be.equal(null);
        expect(wrapper.vm.temp_photo).to.be.equal(null);
        expect(wrapper.vm.display).to.be.equal(false);
    });

    it("Only name should be set to temp name field`", function() {
        wrapper.setData({
            name: "name",
            photo: "photo",
            temp_name: "Change Name"
        });
        wrapper.vm.saveData(true);
        expect(wrapper.vm.name).to.be.equal("Change Name");
        expect(wrapper.vm.photo).to.be.equal("photo");
        expect(wrapper.vm.temp_name).to.be.equal(null);
        expect(wrapper.vm.temp_photo).to.be.equal(null);
        expect(wrapper.vm.display).to.be.equal(false);
    });

    it("Only photo should be set to temp photo field`", function() {
        wrapper.setData({
            name: "name",
            photo: "photo",
            temp_photo: "Change Photo"
        });
        wrapper.vm.saveData(true);
        expect(wrapper.vm.name).to.be.equal("name");
        expect(wrapper.vm.photo).to.be.equal("Change Photo");
        expect(wrapper.vm.temp_name).to.be.equal(null);
        expect(wrapper.vm.temp_photo).to.be.equal(null);
        expect(wrapper.vm.display).to.be.equal(false);
    });
});
