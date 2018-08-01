
<style>
li {
    display: block;
    font-size: 1.2em;
    margin: 0.4em;
    cursor: pointer;
}
</style>

<template>
    <div>
        <!-- <h1>Surveys</h1> -->
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
    },
    components: {
        SurveySummary
    }
};
</script>
