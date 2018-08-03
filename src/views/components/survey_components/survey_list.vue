<style>
li {
    display: block;
    font-size: 1.2em;
    margin: 0.4em;
    cursor: pointer;
}
</style>

<template>
    <v-progress-circular
      v-if="loading"
      :size="70"
      :width="7"
      color="primary"
      indeterminate
      ></v-progress-circular>
    <div v-else>
        <h1>Surveys</h1>
        <div v-for="survey in surveys">
            <SurveySummary :summary_package="Object.assign({survey_id: survey.survey_id}, {user_id, term: current_term, course}) " :is_instance="false"> </SurveySummary>
        </div>
    </div>
</template>

<script>
import SurveySummary from "../response_components/survey_summary.vue";
export default {
    name: "SurveyList",
    props: ["term"],
    data: function() {
        return {
            loading: true,
            surveys: [],
            user_id: this.$route.params.user_id,
            course: null,
            current_term: this.term || null
        };
    },
    created: async function() {
        let fetchedSurvey = await fetch(
            `get_info.php?what=surveys&user_id=${this.user_id}`
        );
        let fetchedJSON = await fetchedSurvey.json();
        this.surveys = fetchedJSON.DATA;
        setTimeout(() => (this.loading = false), 3000);
    },
    components: {
        SurveySummary
    }
};
</script>
