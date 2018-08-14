<style>
@import "./css/student_app.css";
</style>

<template>

<v-app>
  <v-toolbar class="headfoot override" app><span class="white--text"><v-toolbar-title>TA Feedback</v-toolbar-title></span></v-toolbar>
  <v-content>
    <v-alert :value="this.error" type="error" >{{this.error_message}}</v-alert>
    <router-view v-on:error="sendError" v-on:no-error="error=false"></router-view>
  </v-content>
  <v-footer class="headfoot override" app><span class="white--text text-md-center ml-2">University of Toronto</span></v-footer>
</v-app>

</template>

<script>
export default {
    name: "app",
    methods: {
        sendError: function(value) {
            this.error_message = value;
            this.error = true;
        },
        displayMessage: function(value) {
            console.log(value);
            this.displayMessageQueued = true;
            this.displayMessageType = value["type"] || "error";
            this.displayMessageText = value.message;
        }
    },
    data: function() {
        return {
            error: false,
            error_message: ""
        };
    },
    components: {}
};
</script>
