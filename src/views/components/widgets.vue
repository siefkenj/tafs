<template>

<v-content>
        <v-container fluid>
                <h3>Widgets showcase!</h3>
                <p>Here you can view all of the custom widgets with sample data provided.</p>
                <v-list>
                        <v-subheader>Response Summary <v-spacer></v-spacer><code>ResponseSummary</code> (<code>./views/components/response_components/response_summary.vue</code>)</v-subheader>
                        <v-list-tile>
                                <div  style="border: 1px dashed grey; width: 100%;">
                                        <ResponseSummary v-bind:responses="datResponseSummary"></ResponseSummary>
                                </div>
                        </v-list-tile>
                        
                        <v-divider></v-divider>
                        
                        <v-subheader>Survey Display <v-spacer></v-spacer><code>SurveyDisplay</code> (<code>./views/components/response_components/survey_display.vue</code>)</v-subheader>
                        <v-list-tile>
                                <div  style="border: 1px dashed grey; width: 100%; margin-bottom: 4px;">
                                        <SurveyDisplay v-bind:survey_package="datSurveyDisplay"></SurveyDisplay>
                                </div>
                        </v-list-tile>
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
                name: "Fall Survey",
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
                            '{"type":"rating","name":"Feedback","title":"The teaching assistant\ufffd\ufffds feedback on course assignments, projects, papers and/or tests helped me understand the grades I received.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                        position: 5,
                        responses: ["1", "2", "4", "1", "4", "5", "3", "4", "2"]
                    },
                    {
                        question_id: "7",
                        answer_type: "rating",
                        content:
                            '{"type":"rating","name":"Feedback","title":"The teaching assistant\ufffd\ufffds feedback on course assignments, projects, papers and/or tests helped me understand the grades I received.","rateValues":[{"value":"1","text":"Not at all"},{"value":"2","text":"Somewhat"},{"value":"3","text":"Moderately"},{"value":"4","text":"Mostly"},{"value":"5","text":"A great deal"}]}',
                        position: 6,
                        responses: ["1", "2", "4", "1", "4", "5", "3", "4", "2"]
                    }
                ]
            }
        };
    },
    components: {
        ResponseSummary,
        SurveyDisplay
    }
};
</script>
