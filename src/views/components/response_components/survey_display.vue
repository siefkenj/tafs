<template>
            <v-list-tile>
                <v-list-tile-action>
                        <v-btn flat icon><v-icon>keyboard_arrow_down</v-icon></v-btn>
                </v-list-tile-action>
                <v-list-tile-content>
                        <v-list-tile-title class="title black--text font-weight-thin">{{survey_package.name}}</v-list-tile-title>
                        <v-list-tile-sub-title class="caption">{{num_responses}} Responses</v-list-tile-sub-title>
                </v-list-tile-content>
                <v-list-tile-content>
                        <ResponseSummary v-bind:responses="survey_package.questions"></ResponseSummary>
                </v-list-tile-content>
                <v-list-tile-action>
                        <v-list-tile-action-text v-if="closed_date">
                                Closed:
                        </v-list-tile-action-text>
                        <v-list-tile-action-text>
                            {{closed_date}}
                        </v-list-tile-action-text>
                </v-list-tile-action>
                <v-list-tile-action>
                        <v-list-tile-action-text>
                                <v-btn flat class="grey--text">Clone</v-btn>
                        </v-list-tile-action-text>
                </v-list-tile-action>
            </v-list-tile>
</template>

<script>
import ResponseSummary from "./response_summary.vue";
export default {
    name: "SurveyDisplay",
    props: ["survey_package", "is_instance"],
    computed: {
        closed_date: function() {
            return this.survey_package.timedate_close;
        },
        num_responses: function() {
            let lengths = this.survey_package.questions.map(
                (x) => (x.responses || []).length
            );
            let ret = Math.max(0, ...lengths);
            return ret;
        }
    },
    components: {
        ResponseSummary
    }
};
</script>
