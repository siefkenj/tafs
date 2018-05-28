import Vue from "vue";
import VueRouter from "vue-router";
import App from "./App.vue";
import EditCourses from "./views/edit-courses.vue";
import QuestionSelect from "./views/question-select.vue";
import Settings from "./views/settings.vue";
import SurveyRelease from "./views/survey-release.vue";
import TAResultsPermission from "./views/ta-results-permission.vue";
import TAResults from "./views/ta-results.vue";

// Enabling routing
Vue.use(VueRouter);

const routes = [
    { path: "/EditCourses", component: EditCourses },
    { path: "/QuestionSelect", component: QuestionSelect },
    { path: "/Settings", component: Settings },
    { path: "/SurveyRelease", component: SurveyRelease },
    { path: "/TAResultsPermission", component: TAResultsPermission },
    { path: "/TAResults", component: TAResults },
    { path: "/", component: EditCourses }
];

const router = new VueRouter({
    routes: routes
});

new Vue({
    el: "#app",
    router: router,
    render: (h) => h(App)
});
