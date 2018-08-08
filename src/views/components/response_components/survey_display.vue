<style>
.survey-display .v-expansion-panel__header {
    padding: unset;
    height: unset;
}
.survey-display .v-list__tile {
    padding: unset;
}
.survey-display .v-list__tile__action {
    min-width: unset;
}
</style>

<template>

<v-expansion-panel class="survey-display">

    <v-expansion-panel-content hide-actions lazy>
            <v-list-tile slot="header">
                <v-list-tile-action>
                        <v-icon class="mx-2">keyboard_arrow_down</v-icon>
                </v-list-tile-action>
                <v-list-tile-content style="width: 7em;">
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
                            {{(new Date(closed_date)).toISOString().slice(0,10)}}
                        </v-list-tile-action-text>
                </v-list-tile-action>
                <v-list-tile-action>
                        <v-list-tile-action-text class="mx-1">
                                <v-btn flat class="grey--text" @click="buttonClick($event, 'clone')">Clone</v-btn>
                                <v-btn v-if="!is_instance" flat class="grey--text" @click="buttonClick($event, 'edit')">Edit</v-btn>
                                <v-btn v-if="!is_instance" flat class="grey--text" @click="buttonClick($event, 'launch')">Launch</v-btn>
                        </v-list-tile-action-text>
                </v-list-tile-action>
            </v-list-tile>
            <v-card class="grey lighten-4 px-1 py-2">
                <v-card color="blue" dark class="text-xs-center title"><v-card-text>{{survey_package.name}}</v-card-text></v-card>
                 <ResponseSummary v-bind:responses="survey_package.questions" :compact="false"></ResponseSummary>
            </v-card>
    </v-expansion-panel-content>

</v-expansion-panel>

</template>

<script>
import ResponseSummary from "./response_summary.vue";
export default {
    name: "SurveyDisplay",
    props: ["survey_package", "is_instance"],
    methods: {
        buttonClick: function(e, action) {
            // prevent the expansion-panel from expanding.
            e.stopPropagation();
            this.$emit(action, Object.assign({}, this.survey_package));
        }
    },
    computed: {
        closed_date: function() {
            return this.survey_package.timedate_close;
        },
        num_responses: function() {
            let lengths = this.survey_package.questions.map(
                x => (x.responses || []).length
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
