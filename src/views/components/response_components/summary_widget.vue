<style media="screen">
.percentage_bar {
    background-color: #4caf50;
}
.average {
    background-color: #ddd;
}
</style>

<template>
<div v-if="loading">
    loading
</div>
<div v-else-if="display_data" class="summary">
    Total Responses: {{display_data.num_responses}}
    Average:
    <div class="average">
      <div class="percentage_bar" v-bind:style="{width: display_data.numerical_response_ave/5*100 + '%'}">
         {{display_data.numerical_response_ave}}
      </div>
    </div>
</div>
<div v-else>
    no-data
</div>

</template>

<script>
import generate_query_string from "../generate_query_string.js";

export default {
    name: "Summary",
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
            display_data: null
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
                    target_ta: sum_pack.ta_id
                });
            fetch(url)
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    this.results_data = data.DATA;
                })
                .catch(err => {
                    this.$emit("error", err.toString());
                });
            this.loading = false;
        },
        // summarizes the results data to be displayed
        get_summary: function(data) {
            let total_response;
            let ave;
            if (!data) {
                return null;
            }
            //sum up the number of responses for all the surveys
            total_response = data.reduce(
                (previous_x, key_x) =>
                    previous_x +
                    key_x.questions.reduce(
                        (previous_y, key_y) =>
                            previous_y + key_y.responses.length,
                        0
                    ),
                0
            );
            //filter out all text questions
            let non_text_question_surveys = data.map(el =>
                el.questions.filter(question => question.answer_type !== "text")
            );
            // concatenate all the survey responses into an array per survey
            let list_of_responses = non_text_question_surveys.map(el =>
                el
                    .map(question => question.responses)
                    .reduce((a, b) => a.concat(b))
            );
            //get the sum of all numbered responses
            let sum_numbered_responses = list_of_responses.reduce(
                (previous, key) =>
                    previous +
                    key.reduce(
                        (previous_y, key_y) => previous_y + parseInt(key_y),
                        0
                    ),
                0
            );
            //get the number of numbered responses
            let num_numbered_responses = list_of_responses.reduce(
                (previous, key) => previous + key.length,
                0
            );
            ave = sum_numbered_responses / num_numbered_responses;
            //round average to 1 decimal place
            let rounded_ave = Math.round(ave * 10) / 10;

            return {
                num_responses: total_response,
                numerical_response_ave: rounded_ave
            };
        }
    },
    watch: {
        // call to get_info when term and course filters are updated
        results_data: function() {
            this.display_data = this.get_summary(this.results_data);
        }
    }
};
</script>
