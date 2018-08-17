import Vue from "vue";
import VueRouter from "vue-router";
import App from "./App.vue";
import CoursePairings from "./views/course_pairings.vue";
import Surveys from "./views/surveys.vue";
import Redirect from "./views/redirect.vue";
import Widgets from "./views/components/widgets.vue";
import "material-design-icons-iconfont/dist/material-design-icons.css";
import "vuetify/dist/vuetify.min.css"; // Ensure you are using css-loader
import Vuetify from "vuetify";
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
    {
        path: "/courses",
        name: "coruses",
        component: CoursePairings
    },
    {
        path: "/",
        component: Redirect
    },
    {
        path: "/surveys",
        component: Surveys,
        name: "surveys"
    },
    {
        path: "/widgets",
        component: Widgets,
        name: "widgets"
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
