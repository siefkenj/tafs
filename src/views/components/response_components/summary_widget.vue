<template>
<div v-if="loading">
    loading
</div>
<div v-else-if="survey_id_list" class="summary">
    <div v-for="survey_id in survey_id_list">
      <SurveySummary :summary_package=" Object.assign({survey_id: survey_id}, summary_package) "> </SurveySummary>
    </div>
</div>
<div v-else>
    no-data
</div>

</template>

<script>
import generate_query_string from "../generate_query_string.js";
import SurveySummary from "./survey_summary.vue";

export default {
    name: "Summary",
    //summary package includes:
    //  { num_responses: int, numerical_response_ave: float}
    props: ["summary_package"],
    created() {
        this.get_survey_list(this.summary_package);
        // this.get_survey_data(this.summary_package);
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
                    console.log(err);
                    this.loading = false;
                });
        }
    },
    components: {
        SurveySummary
    }
};
</script>
