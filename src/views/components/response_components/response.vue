

<template>

<div>
    <ul v-for="question in survey.questions">
        <Survey :survey="parse_question(question.content)"></Survey>
        <p v-for="response in question.responses">
            {{response}}
        </p>
    </ul>
</div>

</template>

<script>
import { Survey, Model } from "survey-vue";
export default {
    name: "response",
    props: ["survey", "view_mode"],
    methods: {
        //parse the question content and build into a view only question
        parse_question: function(content) {
            let content_object = JSON.parse(content);
            // change the question type to display the title in html to
            // avoid displaying inputs for the question
            let json = {
                elements: [
                    {
                        type: "html",
                        html: content_object.title
                    }
                ]
            };
            let survey = new Model(json);
            // to remove the submit button for survey, set to read-only mode
            survey.mode = "display";
            return survey;
        }
    },
    components: {
        Survey
    }
};
</script>
