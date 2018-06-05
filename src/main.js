import Vue from "vue";
import VueRouter from "vue-router";
import App from "./App.vue";
import Courses from "./views/courses.vue";
import Surveys from "./views/surveys.vue";
import Settings from "./views/settings.vue";
import Results from "./views/results.vue";

// Enabling routing
Vue.use(VueRouter);

const routes = [
    { path: "/courses", component: Courses },
    { path: "/surveys", component: Surveys },
    { path: "/settings", component: Settings },
    { path: "/results", component: Results },
    { path: "/", component: Courses }
];

const router = new VueRouter({
    routes: routes
});

new Vue({
    el: "#app",
    router: router,
    render: (h) => h(App)
});
