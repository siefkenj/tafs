

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
            <SurveyInstance :summary_package="{ta_id: selected_ta_id, term: term, course: course, user_id: user_id}"> </SurveyInstance>
        </div>
    </div>
</div>

</template>

<script>
import Response from "./response.vue";
import SurveyInstance from "./survey_instance.vue";
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
        //return admin/instructor to ta_selecting page
        go_back: function() {
            this.$router.push({
                name: "ta_list_page"
            });
        }
    },
    components: {
        Response,
        SurveyInstance
    }
};
</script>
