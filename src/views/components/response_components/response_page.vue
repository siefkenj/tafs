

<template>

<div>
    <button @click="go_back()">go back</button>
    <h1 v-if="!survey_package">
        No survey are available for {{selected_ta}}
    </h1>
    <div v-else>
        <h3>All surveys</h3>
        <h1 v-if="!survey_package">
          No responses are available for this survey
      </h1>
        <div v-else>
            <div v-for="survey in survey_package" id='all-surveys'>
                <input type="checkbox" v-model="selected_surveys" v-bind:value="survey.survey_id">
                <label for="jack">{{survey.name}}</label>
                <Summary :summary_package="{ta_id: selected_ta_id, term: term, course: course, user_id: user_id, survey_id: survey.survey_id}"> </Summary>
            </div>
            <button type="button" @click="choose_survey(selected_surveys.join(','),user_id,selected_ta,term,course)">View Surveys</button>
            <response :survey_responses="survey_responses"> </response>
        </div>
    </div>
</div>

</template>

<script>
import Response from "./response.vue";
import generate_query_string from "../generate_query_string.js";
export default {
    name: "response_page",
    data: function() {
        return {
            survey_package: null,
            survey_responses: null,
            selected_ta: null,
            selected_surveys: [],
            loading: false,
            error: false,
            user_id: null,
            term: null,
            course: null
        };
    },

    created() {
        let data = this.$route.query;
        this.init(
            data.target_ta,
            data.user_id,
            data.selected_ta,
            data.term,
            data.course
        );
    },

    methods: {
        init: function(selected_ta_id, user_id, selected_ta, term, course) {
            this.loading = true;
            this.selected_ta_id = selected_ta_id;
            this.user_id = user_id;
            this.selected_ta = selected_ta;
            this.term = term;
            this.course = course;
            let url =
                "get_info.php?" +
                generate_query_string({
                    what: "survey_results",
                    term: term,
                    course_code: course,
                    user_id: user_id,
                    target_ta: selected_ta_id
                });
            fetch(url)
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    this.survey_package = data.DATA;
                    this.loading = false;
                })
                .catch(err => {
                    this.$emit("error", err.toString());
                });
        },

        // get survey result of a selected survey
        choose_survey: function(
            survey_instance_id,
            user_id,
            selected_ta_id,
            term,
            course
        ) {
            this.loading = true;
            let url =
                "get_info.php?" +
                generate_query_string({
                    what: "survey_results",
                    term: term,
                    course_code: course,
                    user_id: user_id,
                    target_ta: selected_ta_id,
                    survey_id: survey_instance_id
                });
            fetch(url)
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    this.survey_responses = data.DATA;
                    this.loading = false;
                })
                .catch(err => {
                    this.$emit("error", err.toString());
                });
        },

        //return admin/instructor to ta_selecting page
        go_back: function() {
            this.$router.push({
                name: "ta_list_page"
            });
        }
    },
    components: {
        Response
    }
};
</script>
