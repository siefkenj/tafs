<style scoped>
button {
  all: unset;
}
input {
  all: unset;
}
.empty {
  all: unset;
}
</style>

<template>

<div style="all: unset;">
    <ul v-for="question in survey.questions">
        <Survey class=".empty" :survey="parse_question(question.content)"></Survey>
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
    data: function() {
        return {
            vmode: this.view_mode
        };
    },
    methods: {
        //parse the question content and build into a view only question
        parse_question: function(content) {
          let content_object;
            if (typeof content != "string"){
              content_object = content;
            }else{
              content_object = JSON.parse(content);
            }
            let json;
            switch (this.view_mode) {
                case "Student-View":
                    // change the question type to display the title in html to
                    // avoid displaying inputs for the question
                    json = {
                        elements: [content_object]
                    };
                    break;
                case "Q-Only":
                    json = {
                        elements: [
                            {
                                type: "html",
                                html: content_object.title
                            }
                        ]
                    };
                    break;
                default:
                    console.warn(
                        this.view_mode, "is not recognized as a view mode"
                    );
            }

            let survey = new Model(json);
            // to remove the submit button for survey, set to read-only mode
            survey.mode = "display";
            survey.showQuestionNumbers = "off";
            return survey;
        }
    },
    watch: {
        view_mode: function() {
            this.vmode = this.view_mode;
        }
    },
    components: {
        Survey
    }
};
</script>
