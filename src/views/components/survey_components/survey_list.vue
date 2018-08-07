<template>

<div>
    <h1>Surveys</h1>
    <div v-for="(survey, index) in surveys"
         :key="`summary-${index}`">
        <SurveySummary
            :summary_package="Object.assign({survey_id: survey.survey_id}, {user_id, term: current_term, course}) "
            :is_instance="false"
            @edit="passEditData"
            @launch="passLaunchData"
            ></SurveySummary>
    </div>
    <v-dialog v-model="edit">
        <SurveyQuestionEditor
            :level="myLevel" :survey_package="edit_data"
            :question_choices="datAllQuestionChoices"
            @cancel="edit=false"
            @save="saveEdit"
            ></SurveyQuestionEditor>
    </v-dialog>
    <v-dialog v-model="launch">
        <LaunchModal :survey_package="launch_data" @cancel="launch=false"></LaunchModal>
    </v-dialog>
</div>

</template>

<script>
import SurveyQuestionEditor from "./survey_question_editor.vue";
import LaunchModal from "../response_components/launch-modal.vue";
import SurveySummary from "../response_components/survey_summary.vue";
import generate_query_string from "../generate_query_string";
export default {
    name: "SurveyList",
    props: ["term"],
    data: function() {
        return {
            myLevel: "section",
            loading: true,
            surveys: [],
            user_id: this.$route.params.user_id,
            course: null,
            current_term: this.term || null,
            edit: false,
            launch: false,
            edit_data: {
                questions: []
            },
            launch_data: null,
            datAllQuestionChoices: []
        };
    },
    created: async function() {
        let fetchedSurvey = await fetch(
            `get_info.php?what=surveys&user_id=${this.user_id}`
        );
        let fetchedJSON = await fetchedSurvey.json();
        this.surveys = fetchedJSON.DATA;
        setTimeout(() => (this.loading = false), 3000);
        // Fetch all the questions back from the API
        let fetchedQuestion = await fetch("get_info.php?what=questions");
        fetchedJSON = await fetchedQuestion.json();
        this.datAllQuestionChoices = fetchedJSON.DATA;
    },
    methods: {
        saveEdit: function(new_survey) {
            let params = {
                what: "surveys",
                survey_id: new_survey.survey_id,
                user_id: this.$route.params.user_id,
                level: "section",
                action: "add_or_update"
            };
            let survey_data = Object.assign({}, new_survey);
            survey_data.ta_survey_choices = {};
            survey_data.ta_survey_choices.section_id = null;
            survey_data.ta_survey_choices.choices = [];

            for (let question of survey_data.questions) {
                survey_data.ta_survey_choices.choices.push(
                    parseInt(question.question_id)
                );
            }
            let url = "post_info.php?" + generate_query_string(params);
            fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json; charset=utf-8"
                },
                body: JSON.stringify(survey_data)
            }).catch(error => console.error(`Fetch Error =\n`, error));
        },
        passEditData: function(data) {
            this.edit_data = data;
            this.edit = true;
        },
        passLaunchData: function(data) {
            this.launch_data = data;
            this.launch = true;
        }
    },
    components: {
        SurveySummary,
        LaunchModal,
        SurveyQuestionEditor
    }
};
</script>
