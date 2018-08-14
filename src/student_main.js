import Vue from "vue";
import VueRouter from "vue-router";
import App from "./student_App.vue";
// Components
import Redirect from "./views/student_redirect.vue";
import StudentSurveys from "./views/student_components/student-surveys.vue";
import StudentLanding from "./views/student_components/student-landing.vue";
import StudentOverride from "./views/student_components/student-override.vue";
import "material-design-icons-iconfont/dist/material-design-icons.css";
import "vuetify/dist/vuetify.min.css"; // Ensure you are using css-loader
import Vuetify from "vuetify";
// Material Design UI widgets
Vue.use(Vuetify);
// Enabling routing
Vue.use(VueRouter);

// monkey-patch `fetch` to always include credentials.
// This is needed for Shibboleth integration
let originalFetch = fetch;
fetch = function(a, b) {
    return originalFetch(
        a,
        Object.assign(Object.assign({}, b), { credentials: "same-origin" })
    );
};

const routes = [
    { path: "/", component: Redirect },
    {
        path:
            "/user_id/:user_id/override_token/:override_token/student-landing",
        component: StudentLanding,
        name: "student-landing"
    },
    {
        path: "/user_id/:user_id/override_token/:override_token/student",
        component: StudentSurveys,
        name: "student"
    },
    {
        path: "/user_id/:user_id/",
        component: StudentOverride,
        name: "override"
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
