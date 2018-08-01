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
    <!-- Operations on the selected questions -->
    <!-- <div id="change_question" v-if="edit_click">
        <br/>
        <br/>
        <br/>
        <br/>
        Change Question choice from &ensp; -->
        <!-- The original selected questions -->
        <!-- <p v-if="this.selected">{{this.selected.content}}</p>
        &ensp;to &ensp; -->
        <!-- The back up questions to choose from -->
        <!-- <select v-model="unselected">
            <option v-for="question in backup_questions" v-bind:value = "question">{{question.content}}</option>
        </select>
        <br/>
        <br/>
        <br/>
        <button type="submit" v-on:click="replace()">
            Change</button>
        &ensp;
        <button type="submit" v-on:click="cancel">Cancel</button>
        </br>
    </div> -->
    <v-btn color="blue-grey" @click="dialog = true" class="white--text">Edit</v-btn>
    <v-dialog v-model="dialog" width="600">
        <v-card>
            <v-card-title>Edit Survey</v-card-title>

            <v-layout row wrap>
              <v-flex xs20 sm6 offset-sm3>
                  <v-card>
                      <v-toolbar color="blue-dark" dark>
                          <!-- <v-toolbar-side-icon></v-toolbar-side-icon> -->
                          <v-toolbar-title>Replace Questions</v-toolbar-title>
                          <v-spacer></v-spacer>
                          <!-- <v-btn icon>
                              <v-icon>search</v-icon>
                          </v-btn>
                          <v-btn icon>
                              <v-icon>check_circle</v-icon> -->
                          </v-btn>
                      </v-toolbar>
                      <v-list two-line>
                          <template v-for="(question, index) in questions">
                              <v-list-tile :key="index" avatar ripple @click="">
                                  <v-list-tile-content >
                                      <div v-if="!contain_cross(index)">
                                          <!-- Change Question choice from &ensp; -->
                                          <!-- The original selected questions -->
                                          <!-- <p>{{question.content.title}}</p>
                                          &ensp;to &ensp; -->
                                          <!-- The back up questions to choose from -->
                                          <!-- Change question to -->
                                          <v-select
                                          v-model="selected_question"
                                          :items="backup_questions"
                                          item-text="title"
                                          return-object
                                          single-line>
                                          </v-select>
                                          <!-- <button type="submit" v-on:click="replace()">
                                              Change</button>
                                          &ensp; -->
                                          <!-- <button type="submit" v-on:click="cancel">Cancel</button> -->
                                          <!-- </br> -->
                                      </div>
                                      <div v-if="contain_cross(index)">
                                          <v-list-tile-title>{{ JSON.parse(question.content).type}}</v-list-tile-title>
                                          <v-list-tile-sub-title class="text-primary">{{JSON.parse(question.content).title}}</v-list-tile-sub-title>
                                          <v-list-tile-sub-title>{{ JSON.parse(question.content).name }}</v-list-tile-sub-title>
                                      </div>
                                  </v-list-tile-content>
                                  <v-list-tile-action v-if="contain_cross(index)">
                                      <v-list-tile-action-text></v-list-tile-action-text>
                                      <v-icon v-if=contain_edit(index) color="grey lighten-1" @click="replace_panel(index)">&otimes;</v-icon>
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
        // this.parseData(Object.assign({},this.data));
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
            // survey_id: null,
            name: null,
            edit_click: false,
            checkbox_indices: [],
            question_id_array: [],
            check_status: [0, 0],
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
            backup_questions: [],
            // items: [
            //     { action: '15 min', headline: 'Brunch this weekend?', title: 'Ali Connors', subtitle: "I'll be in your neighborhood doing errands this weekend. Do you want to hang out?" },
            //     { action: '2 hr', headline: 'Summer BBQ', title: 'me, Scrott, Jennifer', subtitle: "Wish I could come, but I'm out of town this weekend." },
            //     { action: '6 hr', headline: 'Oui oui', title: 'Sandra Adams', subtitle: 'Do you have Paris recommendations? Have you ever been?' },
            //     { action: '12 hr', headline: 'Birthday gift', title: 'Trevor Hansen', subtitle: 'Have any ideas about what we should get Heidi for her birthday?' },
            //     { action: '18hr', headline: 'Recipe to try', title: 'Britta Holt', subtitle: 'We should eat this: Grate, Squash, Corn, and tomatillo Tacos.' }
            // ]
        };
    },
    methods: {
        //parse the question content and build into a view only question
        parse_question: function(content) {
            let content_object = JSON.parse(JSON.stringify(content));
            let json = {
                elements: content_object
            };
            let survey = new Model(json);
            // to remove the submit button for survey, set to read-only mode
            // survey.mode = "display";
            survey.showQuestionNumbers = "off";
            return survey;
        },
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
            }
          );
            // Go through the question in questions and transform the content into js object
            // for (let index = 0; index < this.questions.length; index++) {
            //     this.questions[index].content = JSON.parse(this.questions[index].content);
            // }
            // Sort the questions in the list to make it obey the order of positions
            this.questions.sort(this.compare("position"));
            // Determine the indices that should contain checkboxes
            let level_index_label = {
                "dept": [1, 2],
                "course": [3, 4],
                "section": [5, 6]
            };
            this.checkbox_indices = level_index_label[this.level];
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
                    )
                    .map(el=> JSON.parse(el.content))
                    ;
                })
                .catch(err => {
                    this.$emit("error", err.toString());
                });
            // // Go through the question in questions and transform the content into js object

            // for (let index = 0; index < this.backup_questions.length; index++) {
            //     this.backup_questions[index].content = JSON.parse(this.backup_questions[index].content);
            // }
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
        replace_panel: function (index) {
            console.log(this["seen"+index]);
            this["seen"+index] = !this["seen"+index];
            // switch (index) {
            //     case 0:
            //         seen0 = !seen0;
            // }
            // console.log("The value of the seen[index]: " + this.seen[index]);
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
        // Determine whether the list element is supposed to contain an "Edit" button
        contain_edit: function(index) {
            // The index of the list element that will contain the 'edit' button are the
            // ones with indices starting from this.num_locked
            // Should return true when the index of question is equal to or
            // greater than the first element of checkbox_indices
            if (index + 1 == this.checkbox_indices[0] || index + 1 == this.checkbox_indices[1]) {
                return true;
            }
            return false;
        },
        // Function for showing the question change box
        show_choice: function(question) {
            this.selected = question;
            this.edit_click = true;
        },
        // Function that is for specifying the order of the sorting
        compare: function(attribute) {
            return function(o1, o2) {
                let temp1 = o1.position;
                let temp2 = o2.position;
                return temp1 - temp2;
            };
        },
        // Replace one of the selected questions with one of the backup questions
        // that the user selects
        replace: function() {
            if (this.unselected === null) {
                return;
            }
            this.edit_click = false;
            let origin = 0;
            let to_replace = 0;
            // Find out the index of the orignal object that we want to change from
            for (let i = 0; i < this.questions.length; i++) {
                if (
                    this.questions[i].question_id == this.selected.question_id
                ) {
                    origin = i;
                }
            }
            // Find out the index of the back up object that we want to change to
            for (let i = 0; i < this.backup_questions.length; i++) {
                if (
                    this.backup_questions[i].question_id ==
                    this.unselected.question_id
                ) {
                    to_replace = i;
                }
            }
            // Define a new object to be the one that we use to put in the 6 selected questions
            let move_replace = {
                question_id: this.backup_questions[to_replace].question_id,
                content: this.backup_questions[to_replace].content,
                answer_type: this.backup_questions[to_replace].answer_type,
                locked: 0,
                position: this.questions[origin].position
            };
            // Define a new object to be the one that we use to put in the back up questions
            let move_origin = {
                question_id: this.questions[origin].question_id,
                content: this.questions[origin].content,
                answer_type: this.questions[origin].answer_type
            };
            // Update the this.question_id_array
            this.question_id_array.push(
                this.backup_questions[to_replace].question_id
            );
            this.question_id_array.splice(
                this.question_id_array.indexOf(
                    this.questions[origin].question_id
                ),
                1
            );
            // Update the question list and the back up question list
            this.$set(this.questions, origin, move_replace);
            this.$set(this.backup_questions, to_replace, move_origin);
            this.selected = null;
            this.unselected = null;
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
        get_index: function () {
            console.log(this.questions);
            for (let index = 0; index < this.questions.length; index++) {
                if (this["seen"+index] == false) {
                    return index;
                }
            }
        }
    },
    watch: {
        selected_question: function (val) {
            let index = this.get_index();
            console.log(val);
            console.log(index);
            this.questions[index].content = JSON.stringify(val);
            this["seen"+index] = !this["seen"+index];
        }
    },
    components: {
        Survey
    }
};
</script>
