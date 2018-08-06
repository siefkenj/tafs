<style media="screen">
.custom-loader {
    animation: loader 1s infinite;
    display: flex;
    font-size: 30;
    font-style: bold;
}

@keyframes loader {
    from {
        transform: rotate(0);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
<template>

<v-expansion-panel v-if="data">
    <v-expansion-panel-content>
        <div style="text-align: right; flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; height:inherit; flex-direction:row;" slot="header">

            <p style="float:left; font-family:'Times New Roman', Times, serif; font-size:xx-large;">{{data.name}}</p>
            <div v-if="data.num_responses !== 0 && is_instance">
                <p style="float:left;">Total Responses: {{data.num_responses}}</p>
                <div class="numerical-question">
                  <div v-for="question in data.questions" class="numerical-average">
                    Question:{{question.position}}
                    <div class="average">
                      <div class="percentage-bar" v-bind:style="{width: data.numerical_average/5*100 + '%'}">
                        {{question.numerical_average}}
                      </div>
                    </div>
                  </div>
                </div>
            </div>
            <LaunchModal style="float:right"></LaunchModal>
            <v-btn :loading="loading2" :disabled="loading2" color="blue-grey" class="white--text" @click.native="loader = 'loading2'">
              Clone
            </v-btn>
            <QuestionList :data=" Object.assign({}, data)" style="float:right">
            </QuestionList>
            <v-btn v-if="is_instance" :loading="loading4" :disabled="loading4" color="deep-purple lighten-2" class="white--text" @click.native="loader = 'loading4'">
              Allow Instructor Access
            </v-btn>
        </div>
        <v-select
        style="margin-left 10px;"
        v-model="selected_mode"
        :items="mode"
        item-text="mode"
        item-value="value"
        label="Select View Mode"
        single-line>
        </v-select>
        <response v-if="is_instance" :survey="data.text_data" :view_mode="selected_mode"> </response>
        <response v-else :survey="data" :view_mode="selected_mode"> </response>
    </v-expansion-panel-content>
</v-expansion-panel>
</template>

<script>
import Response from "./response.vue";
import LaunchModal from "./launch-modal.vue";
import QuestionList from "../survey_components/question_selection.vue";
export default {
    name: "SurveyWrapperButtons",
    props: ["data", "is_instance"],
    data: function() {
        return {
            loader: null,
            loading: false,
            loading2: false,
            loading3: false,
            loading4: false,
            mode: [
                { mode: "Question Only", value: "question_only" },
                { mode: "Student View", value: "student_view" }
            ],
            selected_mode: "Q-Only"
        };
    },
    watch: {
        loader() {
            const l = this.loader;
            this[l] = !this[l];
            //3 seconds loading for now
            setTimeout(() => (this[l] = false), 3000);

            this.loader = null;
        }
    },
    components: {
        Response,
        LaunchModal,
        QuestionList
    }
};
</script>
