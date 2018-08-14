<template>
  <v-layout >
    <v-flex xm12 md8 offset-md2 lg6 offset-lg3 xl4 offset-xl4>
      <div v-if="survey==null">
         <h1>Loading...</h1>
      </div>
      <v-card >
        <div v-if="survey">
          <survey :survey="survey"></survey>
        </div>

        <v-card-actions  v-if="surveyInProgress">
          <v-spacer></v-spacer>
          <v-btn flat color="orange" v-on:click="navBack">Back</v-btn>
          <v-btn color="orange white--text" v-on:click="submitSurvey">
		  Submit Survey
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-flex>
  </v-layout>
</template>

<script>
import generate_query_string from "../components/generate_query_string";
import { Survey, Model } from "survey-vue";
export default {
    components: {
        Survey
    },
    data: function() {
        return {
            survey: null,
            surveyInProgress: true
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
                what: "get_surveys",
                user_id: this.$route.params.user_id,
                override_token: this.$route.params.override_token
            };
            fetch("student_survey.php?" + generate_query_string(url))
                .then(res => res.json())
                .then(data => this.parseData(data, this.$route.params.user_id))
                .catch(err => {
                    this.$emit("error", err.toString());
                });
        },
        /**
         * Function parses questions from API into JSON format and creates
         * survey.
         *
         * @param data Data returned from the API call
         */
        parseData: function(data, user_id) {
            if (!data || !data.DATA) {
                this.$emit("error", "No data received");
            }
            // Creating JSON data for survey
            let questions = data.DATA.questions;
            var json = {
                title: data.DATA.name,
                pages: [{ name: "page1", elements: [] }]
            };
            // question_id_map is a hash tables that maps question names to
            // their question ids
            var question_id_map = {};
            for (let question of questions) {
                let content = JSON.parse(question.content);
                question_id_map[content.name] = question.question_id;
                json.pages[0].elements.push(content);
            }
            // Creating survey
            this.survey = new Model(json);
            window.surv = this.survey;
            // When survey is completed, responses are stored to corresponding
            // question id
            this.survey.onComplete.add(result => {
                let responses = [];
                for (let [name, response] of Object.entries(result.data)) {
                    responses.push({
                        question_id: question_id_map[name],
                        response: response
                    });
                }
                let ret = {
                    what: "post_surveys",
                    survey_instance_id: data.DATA.survey_instance_id,
                    user_id: user_id,
                    question_responses: responses
                };
                fetch("student_survey.php", {
                    method: "POST",
                    body: JSON.stringify(ret)
                })
                    .then(response => {
                        this.surveyInProgress = false;
                    })
                    .catch(err => this.$emit("error", err.toString()));
            });
        },
        submitSurvey: function() {
            this.survey.completeLastPage();
        },
        navBack: function() {
            this.$router.push({
                path: `/user_id/${this.$route.params.user_id}/override_token/${
                    this.$route.params.override_token
                }/student-landing`
            });
        }
    }
};
</script>
