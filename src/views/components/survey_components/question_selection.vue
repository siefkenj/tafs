<style>
#banner {
    width: 100%;
    height: 8vh;
    background-color: #4d4dff;
    text-align: center;
}
h1 {
    margin: 0px;
}
button {
    cursor: pointer;
}
#question_list li {
    border: 1px solid #000;
    display: block;
    text-align: center;
    width: 60%;
    height: 65px;
}
#question_list li div {
    /*position: absolute;*/
    width: 5%;
    height: 100%;
    float: right;
    display: table-cell;
    vertical-align: middle;
}
#question_list .question_type {
    width: 10%;
    height: 100%;
    float: left;
}
#change_question {
    position: absolute;
    left: 40%;
    top: 20%;
    width: 40%;
    height: 40vh;
    background-color: #f5f5dc;
}
#edit {
    cursor: pointer;
}
</style>
<template>
<div>
    <v-btn color="blue-grey" @click="dialog = true" class="white--text">Edit</v-btn>
    <v-dialog v-model="dialog" width="600">
        <v-card>
            <v-card-title>Edit Survey</v-card-title>

            <v-layout row wrap>
              <v-flex xs20 sm6 offset-sm3>
                  <v-card>
                      <v-toolbar color="blue-dark" dark>
                          <v-toolbar-title>Replace Questions</v-toolbar-title>
                          <v-spacer></v-spacer>
                          </v-btn>
                      </v-toolbar>
                      <v-list two-line>
                          <template v-for="(question, index) in questions">
                              <v-list-tile :key="index" avatar ripple @click="">
                                  <v-list-tile-content >
                                      <div v-if="!contain_cross(index)">
                                          <v-select
                                          v-model="selected_question"
                                          :items="backup_questions"
                                          item-text="title"
                                          return-object
                                          single-line>
                                          </v-select>
                                      </div>
                                      <div v-if="contain_cross(index)">
                                          <v-list-tile-title>{{ JSON.parse(question.content).type}}</v-list-tile-title>
                                          <v-list-tile-sub-title class="text-primary">{{JSON.parse(question.content).title}}</v-list-tile-sub-title>
                                          <v-list-tile-sub-title>{{ JSON.parse(question.content).name }}</v-list-tile-sub-title>
                                      </div>
                                  </v-list-tile-content>
                                  <v-list-tile-action v-if="contain_cross(index)">
                                      <v-list-tile-action-text></v-list-tile-action-text>
                                      <v-icon color="grey lighten-1" @click="replace_panel(index)">&otimes;</v-icon>
                                  </v-list-tile-action>
                              </v-list-tile>
                              <v-divider v-if="index + 1 < questions.length" :key="`divider-${index}`"></v-divider>
                          </template>
                      </v-list>
                  </v-card>
              </v-flex>
            </v-layout>
            <v-card-actions>
                <v-btn
                  color="error"
                  flat
                  @click="dialog = false"
                >
                  Cancel
                </v-btn>
              <v-spacer></v-spacer>
              <v-btn color="blue-grey" @click="dialog = false">
                  Finish the setting
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
    <v-layout row>

    </v-layout>
</div>
</template>
<script>
import generate_query_string from "../generate_query_string";
import { Survey, Model } from "survey-vue";
export default {
    name: "QuestionList",
    props: ["data"],
    created: function() {
        this.survey_id = this.data.survey_id;
        this.user_id = this.$route.params.user_id;
        // Fetch the list of questions from the API call
        let url = {
            what: "surveys",
            survey_id: this.survey_id,
            user_id: this.$route.params.user_id
        };
        fetch("get_info.php?" + generate_query_string(url))
            .then(res => res.json())
            .then(data => this.parseData(data))
            .catch(err => {
                this.$emit("error", err.toString());
            });
    },
    data: function() {
        return {
            survey_package: null,
            dialog: false,
            name: null,
            edit_click: false,
            checkbox_indices: [],
            question_id_array: [],
            term: "201801",
            level: "course",
            seen0: true,
            seen1: true,
            seen2: true,
            seen3: true,
            seen4: true,
            seen5: true,
            selected_question: null,
            // The questions is gonna be a list of question object
            questions: [],
            // The backup questions is gonna be a list of back up questions
            backup_questions: []
        };
    },
    methods: {
        sendError: function(value) {
            this.$emit("error", value);
        },
        parseData: function(fetchedJSON) {
            this.survey_package = fetchedJSON.DATA;
            let data = fetchedJSON.DATA[0];
            if (data == null) {
                this.$emit("error", "No data received");
            }
            // Assign question package to this.questions
            this.questions = data.questions.map(el => {
                el.content = JSON.parse(JSON.stringify(el.content));
                return el;
            });
            // Sort the questions in the list to make it obey the order of positions
            this.questions.sort(this.compare("position"));

            // Fill in the question_id_array
            for (let i = 0; i < this.questions.length; i++) {
                this.question_id_array.push(this.questions[i].question_id);
            }
            // Fetch all the questions back from the API
            fetch("get_info.php?what=questions")
                .then(res => res.json())
                .then(return_data => {
                    this.backup_questions = return_data.DATA.filter(
                        this.question_filter
                    ).map(el => JSON.parse(el.content));
                })
                .catch(err => {
                    this.$emit("error", err.toString());
                });
        },
        // helper function for filtering out the questions in the backup question list
        question_filter: function(question) {
            // return false if it is already in the question_id_array
            return !(this.question_id_array.indexOf(question.question_id) >= 0);
        },
        contain_cross: function(index) {
            return this["seen" + index];
        },
        // replace the current panel with a new panel that gives you access to changing questions
        replace_panel: function(index) {
            this["seen" + index] = !this["seen" + index];
        },
        // cancel the edit panel
        cancel: function() {
            this.selected = null;
            this.edit_click = false;
        },
        /*
         * The following four methods are for checking whether the list element
         * should contain the up/down error, 'edit' button and the checkbox.
         */
        // Function that is for specifying the order of the sorting
        compare: function(attribute) {
            return function(o1, o2) {
                let temp1 = o1.position;
                let temp2 = o2.position;
                return temp1 - temp2;
            };
        },
        // If the user exits this page, it will jump into next page
        save: function() {
            this.questions[
                this.checkbox_indices[0]
            ].locked = this.check_status[0];
            this.questions[
                this.checkbox_indices[1]
            ].locked = this.check_status[1];
            this.$router.push({
                path: `/user_id/${this.$route.params.user_id}/surveys`
            });
        },

        get_index: function() {
            for (let index = 0; index < this.questions.length; index++) {
                if (this["seen" + index] == false) {
                    return index;
                }
            }
        }
    },
    watch: {
        selected_question: function(val) {
            let index = this.get_index();
            this.questions[index].content = JSON.stringify(val);
            this["seen" + index] = !this["seen" + index];
        }
    },
    components: {
        Survey
    }
};
</script>
