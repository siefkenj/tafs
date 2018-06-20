
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
        <h1>Surveys avaliable</h1>
        <ul>
            <li v-for="survey in surveys" @click="survey_to_question(survey)"> Survey ID: {{survey.survey_id}} &ensp; Survey name: {{survey.name}}</li>
        </ul>
    </div>
</template>

<script>
export default {
    name: "survey_list",
    data: function() {
        return {
            surveys: []
        };
    },
    methods: {
        // this function will direct the user to a specific question page depending on which link
        // the user is clicking.
        survey_to_question: function(survey) {
            this.$router.push({
                path:
                    "surveys/question_time?survey_id=" +
                    survey.survey_id +
                    "&name=" +
                    survey.name
            });
        }
    },
    created: async function() {
        let fetchedSurvey = await fetch(
            "get_info.php?what=surveys&user_id=admin0"
        );
        let fetchedJSON = await fetchedSurvey.json();
        this.surveys = fetchedJSON.DATA;
    }
};
</script>
