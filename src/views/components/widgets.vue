<template>

<v-content>
        <v-container fluid>
                <h3>Widgets showcase!</h3>
                <p>Here you can view all of the custom widgets with sample data provided.</p>
                <v-list>
                        <v-subheader>Response Summary <v-spacer></v-spacer><code>ResponseSummary :compact="true"</code> (<code>./views/components/response_components/response_summary.vue</code>)</v-subheader>
                        <v-subheader>Events/Attributes<v-spacer></v-spacer>
                                <code>:responses</code>
                                <code>:compact</code>
                        </v-subheader>
                            <div class="px-3 pb-3">
                                <div style="border: 1px dashed grey; width: 100%;">
                                    <ResponseSummary :responses="datResponseSummary"></ResponseSummary>
                                </div>
                            </div>

                        <v-subheader>Response Summary <v-spacer></v-spacer><code>ResponseSummary :compact="false"</code> (<code>./views/components/response_components/response_summary.vue</code>)</v-subheader>
                            <div class="px-3 pb-3">
                                <div  style="border: 1px dashed grey; width: 100%;">
                                    <ResponseSummary :responses="datResponseSummary" :compact="false"></ResponseSummary>
                                </div>
                            </div>

                        <v-divider></v-divider>

                        <v-subheader>Survey Display <v-spacer></v-spacer><code>SurveyDisplay</code> (<code>./views/components/response_components/survey_display.vue</code>)</v-subheader>
                        <v-subheader>Events/Attributes<v-spacer></v-spacer>
                                <code>:survey_package</code>
                                <code>@clone(survey_package)</code>
                        </v-subheader>
                            <div class="px-3 pb-3">
                                <div  style="border: 1px dashed grey; width: 100%; margin-bottom: 4px;">
                                    <SurveyDisplay 
                                        :survey_package="datSurveyDisplay"
                                        @clone="(v) => console.log('clone clicked', v)"></SurveyDisplay>
                                </div>
                            </div>
                        <v-divider></v-divider>

                        <v-subheader>Survey Question Editor <v-spacer></v-spacer><code>SurveyQuestionEditor</code> (<code>./views/survey_components/survey_question_editor.vue</code>)</v-subheader>
                        <v-subheader>Events/Attributes<v-spacer></v-spacer>
                                <code>:survey_package</code>
                                <code>:question_choices</code>
                                <code>:level</code>
                                <code>@cancel</code>
                                <code>@save(new_survey_package)</code>
                        </v-subheader>
                            <div class="px-3 pb-3">
                                <div  style="border: 1px dashed grey; width: 100%; margin-bottom: 4px;">
                                    <SurveyQuestionEditor 
                                        :survey_package="datSurveyDisplay" 
                                        :question_choices="datAllQuestionChoices"
                                        @cancel="() => console.log('cancel clicked')"
                                        @save="(v) => console.log('save clicked', v)"></SurveyQuestionEditor>
                                </div>
                            </div>
                        <v-divider></v-divider>
                        <div>next widget</div>
                        <v-divider></v-divider>
                </v-list>
        </v-container>
</v-content>

</template>

<script>
import ResponseSummary from "./response_components/response_summary.vue";
import SurveyDisplay from "./response_components/survey_display.vue";
import SurveyQuestionEditor from "./survey_components/survey_question_editor.vue";
export default {
    name: "Widgets",
    data: function() {
        return {
            datResponseSummary: [
                {
                    question_id: "6",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Learning","title":"The support my teaching assistant provided in the course contributed to my overall learning.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                    position: 1,
                    responses: ["3", "4", "5", "2", "3", "3", "3"],
                    num_response: 7,
                    numerical_average: 3.3
                },
                {
                    question_id: "3",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Preparedness","title":"The teaching assistant was well-prepared for tutorial/lab sessions.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                    position: 2,
                    responses: ["5", "5", "3", "3", "5", "4", "4"],
                    num_response: 7,
                    numerical_average: 4.1
                },
                {
                    question_id: "2",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Orgainization","title":"The tutorial/lab sessions were organized.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                    position: 3,
                    responses: ["5", "2", "2", "5", "5"],
                    num_response: 5,
                    numerical_average: 3.8
                },
                {
                    question_id: "9",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Enthusiasm","title":"The teaching assistant was enthusiastic about the tutorial/lab material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                    position: 4,
                    responses: ["3", "3", "3", "4", "3", "4"],
                    num_response: 6,
                    numerical_average: 3.3
                },
                {
                    question_id: "2",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Orgainization","title":"The tutorial/lab sessions were organized.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                    position: 5,
                    responses: ["5", "2", "2", "5", "5"],
                    num_response: 5,
                    numerical_average: 3.8
                }
            ],
            datSurveyDisplay: {
                survey_id: "13",
                name: "Fall Survey with a really long title",
                timedate_open: "2008-11-09 00:00:00",
                timedate_close: "2008-11-12 00:00:00",
                questions: [
                    {
                        question_id: "1",
                        answer_type: "rating",
                        content:
                            '{"type":"rating","name":"Understanding","title":"The tutorial/lab helped me better understand the course material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                        position: 1,
                        responses: ["2", "3", "1", "1"]
                    },
                    {
                        question_id: "2",
                        answer_type: "rating",
                        content:
                            '{"type":"rating","name":"Orgainization","title":"The tutorial/lab sessions were organized.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                        position: 2,
                        responses: ["2", "1", "5", "5", "4", "4", "5", "5"]
                    },
                    {
                        question_id: "9",
                        answer_type: "rating",
                        content:
                            '{"type":"rating","name":"Enthusiasm","title":"The teaching assistant was enthusiastic about the tutorial/lab material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                        position: 3,
                        responses: ["4", "4", "4", "1", "4"]
                    },
                    {
                        question_id: "1",
                        answer_type: "rating",
                        content:
                            '{"type":"rating","name":"Understanding","title":"The tutorial/lab helped me better understand the course material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                        position: 4,
                        responses: ["2", "3", "1", "1"]
                    },
                    {
                        question_id: "7",
                        answer_type: "rating",
                        content:
                            '{"type":"rating","name":"Feedback","title":"The teaching assistant\'s feedback on course assignments, projects, papers and/or tests helped me understand the grades I received.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                        position: 5,
                        responses: ["1", "2", "4", "1", "4", "5", "3", "4", "2"]
                    },
                    {
                        question_id: "7",
                        answer_type: "rating",
                        content:
                            '{"type":"rating","name":"Feedback","title":"The teaching assistant\'s feedback on course assignments, projects, papers and/or tests helped me understand the grades I received.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                        position: 6,
                        responses: ["1", "2", "4", "1", "4", "5", "3", "4", "2"]
                    }
                ]
            },
            datAllQuestionChoices: [
                {
                    question_id: "1",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Understanding","title":"The tutorial/lab helped me better understand the course material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}'
                },
                {
                    question_id: "2",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Orgainization","title":"The tutorial/lab sessions were organized.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}'
                },
                {
                    question_id: "3",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Preparedness","title":"The teaching assistant was well-prepared for tutorial/lab sessions.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}'
                },
                {
                    question_id: "4",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Explanations","title":"The teaching assistant explained tutorial/lab topics and concepts clearly.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}'
                },
                {
                    question_id: "5",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Respectfulness","title":"The teaching assistant responded respectfully to student questions during lab/tutorial sessions.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}'
                },
                {
                    question_id: "6",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Learning","title":"The support my teaching assistant provided in the course contributed to my overall learning.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}'
                },
                {
                    question_id: "7",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Feedback","title":"The teaching assistant\'s feedback on course assignments, projects, papers and/or tests helped me understand the grades I received.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}'
                },
                {
                    question_id: "8",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Feedback2","title":"The teaching assistant\'s feedback on course assignments, projects, papers and/or tests improved my understanding of the course material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}'
                },
                {
                    question_id: "9",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Enthusiasm","title":"The teaching assistant was enthusiastic about the tutorial/lab material.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}'
                },
                {
                    question_id: "10",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Quality","title":"Overall, the quality of support the teaching assistant provided in the tutorial/lab was:Overall, the quality of my learning experience in the tutorial/lab was:","rateValues":[{"value":"1","text":"Poor"},{"value":"2","text":"Fair"},{"value":"3","text":"Good"},{"value":"4","text":"Very Good"},{"value":"5","text":"Excellent"}]}'
                },
                {
                    question_id: "11",
                    answer_type: "rating",
                    content:
                        '{"type":"rating","name":"Quality2","title":"Overall, the quality of my learning experience in the tutorial/lab was:","rateValues":[{"value":"1","text":"Poor"},{"value":"2","text":"Fair"},{"value":"3","text":"Good"},{"value":"4","text":"Very Good"},{"value":"5","text":"Excellent"}]}'
                },
                {
                    question_id: "12",
                    answer_type: "comment",
                    content:
                        '{"type":"comment","name":"Comments","title":"Please comment on your overall learning experience in the tutorial/lab session."}'
                }
            ]
        };
    },
    computed: {
        console: function() {
            return console;
        }
    },
    components: {
        ResponseSummary,
        SurveyDisplay,
        SurveyQuestionEditor
    }
};
</script>
