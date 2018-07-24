import Vue from "vue";
import VueRouter from "vue-router";
import App from "./App.vue";
import CoursePairings from "./views/course_pairings.vue";
import Surveys from "./views/surveys.vue";
import Settings from "./views/settings.vue";
import Results from "./views/results.vue";
import SurveyList from "./views/components/survey_components/survey_list.vue";
import QuestionTime from "./views/components/survey_components/question_time_setting.vue";
import ResponsePage from "./views/components/response_components/response_page.vue";
import TaPage from "./views/components/response_components/select_ta.vue";
import Redirect from "./views/redirect.vue";
import StudentSurveys from "./views/student-surveys.vue";
import StudentLandingPage from "./views/student-landing.vue";

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

const survey_results_route = {
    path: "/user_id/:user_id/results",
    component: Results,
    children: [
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
    ]
};

const routes = [
    { path: "/user_id/:user_id/courses", component: CoursePairings },
    { path: "/user_id/:user_id/settings", component: Settings },
    survey_results_route,
    { path: "/", component: Redirect },
    survey_route,
    {
        path:
            "/user_id/:user_id/override_token/:override_token/student-landing",
        component: StudentLandingPage
    },
    {
        path: "/user_id/:user_id/override_token/:override_token/student",
        component: StudentSurveys
    }
];

const router = new VueRouter({
    routes: routes
});

new Vue({
    el: "#app",
    router: router,
    render: h => h(App)
});
