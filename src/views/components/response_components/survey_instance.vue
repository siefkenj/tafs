<!-- This page gets list of survey_instance_ids and render survey summaries-->
<template>
<div v-else-if="survey_id_list">
    <div v-for="survey_id in survey_id_list">
        <SurveySummary :summary_package=" Object.assign({survey_id: survey_id}, summary_package)" :is_instance="is_instance"> </SurveySummary>
    </div>
</div>
</template>

<script>
import generate_query_string from "../generate_query_string.js";
import SurveySummary from "./survey_summary.vue";

export default {
    name: "SurveyInstance",
    //summary package includes:
    //  { num_responses: int, numerical_response_ave: float}
    props: ["summary_package", "is_instance"],
    created() {
        this.get_survey_list(this.summary_package);
    },
    data: function() {
        return {
            loading: false,
            survey_id_list: null
        };
    },
    methods: {
        // summarizes the results data to be displayed fetched the survey data
        get_survey_list: function(ta) {
            this.loading = true;
            let survey_list_url =
                "get_info.php?" +
                generate_query_string({
                    what: "survey_results",
                    term: ta.term,
                    course_code: ta.course,
                    user_id: ta.user_id,
                    target_ta: ta.ta_id
                });
            let result = fetch(survey_list_url)
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    if (data.DATA) {
                        this.survey_id_list = data.DATA.map(
                            el => el.survey_instance_id
                        );
                    }
                    this.loading = false;
                })
                .catch(err => {
                    this.$emit("error", err.toString());
                    this.loading = false;
                });
        }
    },
    components: {
        SurveySummary
    }
};
</script>
