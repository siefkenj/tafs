<template>
  <div v-if="survey_id_list">
      <div v-for="survey_id in survey_id_list">
          <SurveySummary :summary_package=" Object.assign({survey_id: survey_id}, summary_package)" :is_instance="is_instance"> </SurveySummary>
      </div>
  </div>
</template>

<script>
import SurveySummary from "./components/survey_summary.vue";
export default {
    name: "survey_results_component",
    data: function() {
        return {
            user_id: this.$route.params.user_id,
            term: this.$route.params.term,
            course: null,
            loading: false,
            survey_id_list: null
        };
    },

    created() {
        this.get_survey_list(this.user_id, this.term,term.course);
    },

    methods: {
        // summarizes the results data to be displayed fetched the survey data
        get_survey_list: function(user_id, term, course) {
            this.loading = true;
            let survey_list_url =
                "get_info.php?" +
                generate_query_string({
                    what: "survey_results",
                    term: term,
                    course_code: course,
                    user_id: user_id,
                    target_ta: user_id
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
