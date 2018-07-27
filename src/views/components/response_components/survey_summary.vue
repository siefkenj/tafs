<style media="screen">
.percentage-bar {
    background-color: #4caf50;
}

.average {
    background-color: #ddd;
}

.numerical-average {
    float: left;
}

.numerical-question {
    display: block;
}

.survey-summary {
    display: inline-block;
}

.survey-responses {
    display: inline-block;
}
</style>

<template>

<div v-if="loading">
    loading
</div>
<div v-else-if="numerical_data && numerical_data.num_responses !== 0" class="survey-summary" @click="toggle()">
    {{numerical_data.name}} Total Responses: {{numerical_data.num_responses}}
    <div class="numerical-question">
        <div v-for="question in numerical_data.questions" class="numerical-average">
            Question:{{question.position}}
            <div class="average">
                <div class="percentage-bar" v-bind:style="{width: numerical_data.numerical_average/5*100 + '%'}">
                    {{question.numerical_average}}
                </div>
            </div>
        </div>
    </div>
    <div v-if="show_response" class="survey-responses">
        <response :survey="text_data"> </response>
    </div>
</div>

</template>

<script>
import Response from "./response.vue";
import generate_query_string from "../generate_query_string.js";

export default {
    name: "SurveySummary",
    //summary package includes:
    //  { num_responses: int, numerical_response_ave: float}
    props: ["summary_package"],
    created() {
        this.get_survey_data(this.summary_package);
    },
    data: function() {
        return {
            loading: false,
            results_data: null,
            numerical_data: null,
            text_data: null,
            show_response: false
        };
    },
    methods: {
        // summarizes the results data to be displayed fetched the survey data
        get_survey_data: function(sum_pack) {
            this.loading = true;
            let url =
                "get_info.php?" +
                generate_query_string({
                    what: "survey_results",
                    term: sum_pack.term,
                    course_code: sum_pack.course,
                    user_id: sum_pack.user_id,
                    target_ta: sum_pack.ta_id,
                    survey_id: sum_pack.survey_id
                });
            fetch(url)
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    this.results_data = data.DATA[0];
                    this.loading = false;
                })
                .catch(err => {
                    this.$emit("error", err.toString());
                    this.loading = false;
                });
        },
        show: function(show) {
            this.$emit("show", show);
        },
        // summarizes the results data to be displayed
        get_summary: function(data) {
            if (
                !data ||
                Object.keys(data).length === 0 ||
                data.questions.length === 0
            ) {
                return null;
            }
            data.num_responses = data.questions
                .filter(el => el.responses)
                .reduce((previous, key) => previous + key.responses.length, 0);
            //reduce questions to only contain numerical questions and summarize each question
            data.questions = data.questions
                .filter(el => el.answer_type !== "comment")
                .map(el => {
                    if (!el.responses) {
                        return el;
                    }
                    el.num_response = el.responses.length;
                    //sum all numerical responses and round to 1 decimal place
                    el.numerical_average =
                        Math.round(
                            el.responses.reduce(
                                (previous, key) => previous + parseInt(key, 10),
                                0
                            ) /
                                el.num_response *
                                10
                        ) / 10;
                    return el;
                });
            //show component if data exist
            if (data.num_responses && data.num_responses >= 0) {
                this.show(true);
            }
            return data;
        },
        toggle: function() {
            this.show_response = !this.show_response;
        },
        get_text_questions: function(data) {
            if (
                !data ||
                Object.keys(data).length === 0 ||
                data.questions.length === 0
            ) {
                return null;
            }
            data.questions = data.questions.filter(
                el => el.answer_type === "comment" || el.answer_type === "text"
            );
            return data;
        }
    },
    watch: {
        // call to get_info when term and course filters are updated
        results_data: function() {
            //pass in a copy of result data to prevent mutating original data being passed in
            this.numerical_data = this.get_summary(
                Object.assign({}, this.results_data)
            );
            this.text_data = this.get_text_questions(
                Object.assign({}, this.results_data)
            );
        }
    },
    components: {
        Response
    }
};
</script>
