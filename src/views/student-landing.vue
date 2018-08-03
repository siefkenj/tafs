<template>
    <div>
        <div v-if="name==null">
            <h1>Loading...</h1>
        </div>

        <div v-else>
            <div v-if="existing_response_id">
                <h1>You have already filled out this survey. Would you like to resubmit your answers?</h1>
                <h2>Selected TA:</h2>
                <h3>{{name}}</h3>
                <h3>{{photo}}</h3>
                <h3>{{section}}</h3>
                <h3>{{course_code}}</h3>
                <button v-on:click="correct">Submit New Response</button>
            </div>

            <div v-else>
                <h1>Fill out a survey for:</h1>
                <h3>{{name}}</h3>
                <h3>{{photo}}</h3>
                <h3>{{section}}</h3>
                <h3>{{course_code}}</h3>

                <button v-on:click="correct">Take Survey</button>
            </div>

            <h1>Submit New Token</h1>
            <input v-model="new_override">
            <button v-on:click="new_token">Submit New Token</button>
        </div>
    </div>
</template>

<script>
import generate_query_string from "./components/generate_query_string";

export default {
    data: function() {
        return {
            name: null,
            photo: null,
            section: null,
            course_code: null,
            new_override: null,
            existing_response_id: false
        };
    },

    created: function() {
        this.getData();
    },
    methods: {
        /**
         * Getting data from API.
         * All errors are emitted to parent component.
         */
        getData: function() {
            let url = {
                what: "get_ta",
                user_id: this.$route.params.user_id,
                override_token: this.$route.params.override_token
            };

            fetch("student_survey.php?" + generate_query_string(url))
                .then(res => res.json())
                .then(data => this.parseData(data))
                .catch(err => {
                    this.$emit("error", err.toString());
                });
        },

        /**
         * Saving data to data
         * All errors are emitted to parent component.
         *
         * @param data The data received from API call
         */
        parseData: function(data) {
            if (!data || !data.DATA) {
                this.$emit("error", "No data received");
            }

            this.name = data.DATA.name;
            this.photo = data.DATA.photo;
            this.section = data.DATA.section;
            this.course_code = data.DATA.course_code;
            this.existing_response_id = data.DATA.existing_response_id;
        },

        /**
         * Replaces URL to display given survey
         */
        correct: function() {
            this.$router.replace({
                path: `/user_id/${this.$route.params.user_id}/override_token/${
                    this.$route.params.override_token
                }/student`
            });
        },

        /**
         * Replaces URL to display overriden survey
         */
        new_token: function() {
            this.$router.replace({
                path: `/user_id/${this.$route.params.user_id}/override_token/${
                    this.new_override
                }/student`
            });
        }
    }
};
</script>
