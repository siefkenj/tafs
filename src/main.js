import Vue from "vue";
import VueRouter from "vue-router";
import App from "./App.vue";
import Courses from "./views/courses.vue";
import Surveys from "./views/surveys.vue";
import Settings from "./views/settings.vue";
import Results from "./views/results.vue";
import SurveyList from "./views/components/survey_components/survey_list.vue";
import QuestionTime from "./views/components/survey_components/question_time_setting.vue";
import Redirect from "./views/redirect.vue";

// Enabling routing
Vue.use(VueRouter);

const survey_route = {
    path: "/user_id/:user_id/surveys",
    component: Surveys,
    children: [
        {
            path: "",
            component: SurveyList
        },
        {
            path: "question_time",
            component: QuestionTime
        }
    ]
};

const routes = [
    { path: "/user_id/:user_id/courses", component: Courses },
    { path: "/user_id/:user_id/settings", component: Settings },
    { path: "/user_id/:user_id/results", component: Results },
    { path: "/", component: Redirect },
    survey_route
];

const router = new VueRouter({
    routes: routes
});

new Vue({
    el: "#app",
    router: router,
    render: (h) => h(App)
});
