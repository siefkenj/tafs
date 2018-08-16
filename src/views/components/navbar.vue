<style>
</style>

<template>

<v-toolbar dark color="primary">
    <v-toolbar-title class="white--text">{{this.name}} ({{this.utorid}})</v-toolbar-title>

    <v-spacer></v-spacer>

    <v-toolbar-items>
        <v-btn flat>Edit Name</v-btn>
    </v-toolbar-items>
</v-toolbar>

</template>

<script>
import generate_query_string from "./generate_query_string";
export default {
    data: function() {
        return {
            utorid: null,
            name: null
        };
    },
    created: function() {
        this.getData();
    },
    methods: {
        /**
         * Function gets user data from API
         */
        getData: async function() {
            let url = {
                what: "user_info",
                include_photo: false,
                user_id: this.$route.query.user_id
            };
            let fetched, fetchedJSON;
            try {
                fetched = await fetch(
                    "get_info.php?" + generate_query_string(url)
                );
                fetchedJSON = await fetched.json();
            } catch (error) {
                this.$emit("error", "Could not retrieve user data");
            }

            if (fetchedJSON.DATA.length < 1) {
                this.$emit("error", "Could not retrieve user data");
            } else {
                this.utorid = fetchedJSON.DATA[0].user_id;
                this.name = fetchedJSON.DATA[0].name;
            }
        }
    }
};
</script>
