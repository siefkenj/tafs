<style>
@import "./css/app.css";
</style>

<template>

<div id="app">
    <div>
        <v-app style="margin-left:0">
            <Nav @error="sendError"></Nav>
            <div v-if=this.error>
                <ErrorBlock :error_message="this.error_message"></ErrorBlock>
            </div>
            <router-view @error="sendError"></router-view>
        </v-app>
    </div>
</div>

</template>

<script>
import Nav from "./views/components/navbar.vue";
import ErrorBlock from "./views/components/error_block.vue";
export default {
    name: "app",
    methods: {
        sendError: function(value) {
            this.error_message = value;
            this.error = true;
        }
    },
    data: function() {
        return {
            error: false,
            error_message: ""
        };
    },
    components: {
        Nav,
        ErrorBlock
    }
};
</script>
