<template>
    <div>
        <h1>students</h1>
        <div v-if="survey">
            <survey :survey="survey"></survey>
        </div>
        <div v-else>
            <h3>Loading...</h3>
        </div>
    </div>
</template>

<script>
import generate_query_string from "./components/generate_query_string";
import { Survey, Model } from "survey-vue";

export default {
    components: {
        Survey
    },
    data: function() {
        return {
            survey: null,
            survey_id: null
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
                what: "surveys",
                user_id: this.$route.params.user_id,
                survey_id: 1
            };
            fetch("get_info.php?" + generate_query_string(url))
                .then(res => res.json())
                .then(data => this.parseData(data))
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
        parseData: function(data) {
            if (!data || !data.DATA) {
                this.$emit("error", "No data received");
            }

            // Creating JSON data for survey
            let questions = data.DATA[0].questions;
            var json = {
                title: data.DATA[0].name,
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

            // When survey is completed, responses are stored to corresponding
            // question id
            this.survey.onComplete.add(function(result) {
                let responses = [];
                for (let [name, response] of Object.entries(result.data)) {
                    responses.push({
                        question_id: question_id_map[name],
                        response: response
                    });
                }
                let ret = {
                    survey_id: data.DATA[0].survey_id,
                    responses: responses
                };
            });
        }
    }
};
</script>
