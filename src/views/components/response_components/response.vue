

<template>

<div>
    <div v-for="survey in survey_responses">
        <h1>{{survey.name}}</h1>
        <li v-for="question in survey.questions">
            {{question.content}}
            <p v-for="response in question.responses">
                {{response}}
            </p>
        </li>
    </div>
</div>

</template>

<script>
export default {
    name: "response",
    props: ["survey_responses", "view_mode"],
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
            survey.mode = 'display';
            return survey;
        }
    },
    components: {
        Survey,
        Summary
    }
};
</script>
