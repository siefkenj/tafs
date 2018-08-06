<style>
.survey-question-editor .selected {
    background-color: #90caf9;
}
.survey-question-editor .locked {
    background-color: #fafafa;
}
.survey-question-editor .selected:hover {
    background-color: #bbdefb;
}
</style>

<template>

    <v-card class="survey-question-editor">
        <v-card-title>
            <span class="headline">Edit Survey</span>
        </v-card-title>
        <v-card-text class="pt-0">
            <v-container>
                <v-layout column>
                    <v-flex>
                        <v-text-field label="Survey Name" persistent-hint v-model="survey_name"></v-text-field>
                    </v-flex>
                    <p class="caption grey--text my-2 text--darken-1">Survey Questions</p>
                    <v-flex>
                        <!-- List of actual questions in the survey -->
                        <v-list subheader three-line>
                            <v-list-tile
                                v-for="(value, index) in survey_q_list"
                                :key="index"
                                :class="{locked: !editable_mask[index]}"
                                >
                                <v-list-tile-avatar>
                                    {{ index + 1 }}
                                </v-list-tile-avatar>
                                <v-list-tile-content>
                                    <v-list-tile-title>
                                        {{ value.name }} <span v-if="value.type">({{ value.type }})</span>
                                    </v-list-tile-title>
                                    <v-list-tile-sub-title>
                                        {{ value.title }}
                                    </v-list-tile-sub-title>
                                </v-list-tile-content>
                                <v-list-tile-action>
                                    <template v-if="editable_mask[index]">
                                         <v-btn color="pink"
                                             v-on:click="editClicked($event, index)"
                                             flat dark fab small>
                                             <v-icon dark>edit</v-icon>
                                         </v-btn>
                                    </template>
                                    <template v-else>
                                         <v-btn color="grey" flat  fab small disabled>
                                             <v-icon dark>lock</v-icon>
                                         </v-btn>
                                    </template>
                                </v-list-tile-action>
                            </v-list-tile>
                        </v-list>

                    </v-flex>
                </v-layout>
            </v-container>
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="orange" flat @click="$emit('cancel')">Cancel</v-btn>
            <v-btn color="orange white--text" @click="$emit('save', getNewSurveyPackage())">Save</v-btn>
        </v-card-actions>

        <!-- the menu is rendered out-of-tree, so it doesn't matter where it
        is in the template. -->
        <v-menu
            v-model="questionMenuVisible"
            :position-x="questionMenuX"
            :position-y="questionMenuY"
            >
            <v-list subheader three-line dense
                class="survey-question-editor"
                >
                <template
                    v-for="(value, key, index) in q_list"
                    >
                    <v-list-tile
                        @click="questionClicked(value)"
                        :class="{selected: isCurrent(value)}"
                        v-if="!not_available_mask[value.question_id] || isCurrent(value) || value.question_id === '0'"
                        :key="index" 
                        >
                        <v-list-tile-content>
                            <v-list-tile-title>
                                {{ value.name }} <span v-if="value.type">({{ value.type }})</span>
                            </v-list-tile-title>
                            <v-list-tile-sub-title>
                                {{ value.title }}
                            </v-list-tile-sub-title>
                        </v-list-tile-content>
                    </v-list-tile>
                </template>
            </v-list>
        </v-menu>
    </v-card>

</template>

<script>
export default {
    name: "SurveyQuestionEditor",
    props: {
        question_choices: Array,
        survey_package: Object,
        level: { type: String, default: "section" }
    },
    data: function() {
        return {
            survey_name: this.survey_package.name,
            currently_editing: null,
            questionMenuVisible: false,
            questionMenuX: 0,
            questionMenuY: 0,
            // we'll be live-editing the survey package,
            // and we don't want to change a `props`, so
            // maintain "data" version.
            survey_package_local: this.survey_package
        };
    },
    methods: {
        /* Executed when new question has been selected from the
        menu */
        questionClicked: function(value) {
            let new_survey_package = this.getNewSurveyPackage();
            new_survey_package.questions[this.currently_editing] = value;

            // propagate up the changes
            this.survey_package_local = new_survey_package;
        },
        editClicked: function(e, index) {
            this.currently_editing = index;
            this.showQuestionMenu(e);
        },
        showQuestionMenu: function(e) {
            this.questionMenuVisible = false;
            this.questionMenuX = e.clientX;
            this.questionMenuY = e.clientY;
            this.$nextTick(() => {
                this.questionMenuVisible = true;
            });
        },
        isCurrent: function(value) {
            return (
                value.question_id ===
                (this.survey_q_list[this.currently_editing] || {}).question_id
            );
        },
        /* returns a cloned survey package based on survey_package_local */
        getNewSurveyPackage: function() {
            // Copy our props (we don't want to accidentally edit them)
            // we won't do a complete deep copy--we'll only copy the things
            // that might change
            let new_survey_package = Object.assign(
                {},
                this.survey_package_local
            );
            let new_question_list = [...new_survey_package.questions];
            new_survey_package.questions = new_question_list;

            // the name is stored separately, so update that manually
            new_survey_package.name = this.survey_name;

            return new_survey_package;
        }
    },
    computed: {
        /* list of possible questions to select from */
        q_list: function() {
            // initialize with the `noquestion` question
            let ret = {
                "0": {
                    question_id: "0",
                    answer_type: null,
                    name: "",
                    title: "No question",
                    content:
                        '{"type": null, "name": "", "title": "No Question"}'
                }
            };
            for (let q of this.question_choices) {
                ret[q.question_id] = Object.assign({}, q);
                // make sure we have access to the name, and title of the question.
                // This is stored as JSON in the `.content` attribute
                // in the survey.js format.
                Object.assign(ret[q.question_id], JSON.parse(q.content));
            }
            console.log(ret);
            return ret;
        },
        /* list of the current survey questions */
        survey_q_list: function() {
            // create a copy of `x` and then extract the JSON of the question content
            let extractContent = x => {
                return Object.assign(
                    Object.assign({}, x),
                    JSON.parse(x.content)
                );
            };
            return this.survey_package_local.questions.map(extractContent);
        },
        /* whether or not a particular survey question can be edited */
        editable_mask: function() {
            // an array of booleans indicating which questions are editable.
            let ret = [true, true, true, true, true, true];
            switch (this.level) {
                case "section":
                case "ta":
                    ret[2] = false;
                    ret[3] = false;
                case "course":
                    ret[0] = false;
                    ret[1] = false;
                case "dept":
                    break;
            }
            return ret;
        },
        /* whether a survey question should show up in the selection menu */
        not_available_mask: function() {
            let ret = {};
            for (let q of this.survey_q_list) {
                ret[q.question_id] = true;
            }
            return ret;
        }
    }
};
</script>
