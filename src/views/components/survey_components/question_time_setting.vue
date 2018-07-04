
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
        <div id="banner">
            <h1><b>Survey ID: {{ survey_id }} &ensp; Survey name: {{ name }}</b></h1>
        </div>
        <h2>Set the order of the selected elements</h2>
        <!-- A list of selected questions being displayed on the screen -->
        <div id="question_list">
            <ol ref="list_parent"><center>
                <li v-for="question in questions">
                    <div class="question_type">{{question.answer_type}}</div>
                    {{question.content}}
                    <!-- The checkbox of the question -->
                    <div><input v-if="contain_checkbox(question)" type="checkbox" v-on:click="checkbox_click(question)"></input></div>
                    <!-- The 'Edit' button of the question -->
                    <div v-if="contain_edit(question)" v-on:click="show_choice(question)" id="edit">Edit</div>
                    <!-- The 'move up' arrow of the question -->
                    <div v-if="contain_up_arrow(question)" v-on:click="up_exchange(question)">&uarr;</div>
                    <!-- The 'move down' arrow of the question -->
                    <div v-if="contain_down_arrow(question)" v-on:click="down_exchange(question)">&darr;</div>
                </li>
            </center></ol>
        </div>
        <!-- Operations on the selected questions -->
        <div id="change_question" v-if="edit_click">
            <br/>
            <br/>
            <br/>
            <br/>
            Change Question choice from &ensp;
            <!-- The original selected questions -->
            <p v-if="this.selected">{{this.selected.content}}</p>
            &ensp;to &ensp;
            <!-- The back up questions to choose from -->
            <select v-model="unselected">
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
        </div>
        <br/>

        <h2><b>Set the start time</b></h2>
        <p>(Note: Please use the same format as default)</p>
        <input type="datetime-local" v-model="start_time"/>
        <h2><b>Set the end time</b></h2>
        <p>(Note: Please use the same format as default)</p>
        <input type="datetime-local" v-model="end_time"/>
        <br/><br/><br/><br/>

        <button type="submit" v-on:click="save">
            Finish the setting</button>
    </div>
</template>

<script>
import generate_query_string from "../generate_query_string";
export default {
    name: "question_time_setting",
    data: function() {
        return {
            survey_package: null,
            num_locked: 0,
            selected: null,
            unselected: null,
            survey_id: null,
            name: null,
            edit_click: false,
            checkbox_indices: [],
            question_id_array: [],
            check_status: [0, 0],
            term: "201801",
            start_time: "2000-01-01T00:00:00",
            end_time: "2000-01-01T00:00:00",
            // The questions is gonna be a list of question object
            questions: null,
            // The backup questions is gonna be a list of back up questions
            backup_questions: []
        };
    },
    created: function() {
        this.survey_id = this.$route.query.survey_id;
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
    methods: {
        parseData: function(fetchedJSON) {
            this.survey_package = fetchedJSON.DATA;
            let data = fetchedJSON.DATA[0];
            if (data == null) {
                this.$emit("error", "No data received");
            }
            if (data.number_locked_by_department != null) {
                this.num_locked += parseInt(data.number_locked_by_department);
            }
            if (data.number_locked_by_course != null) {
                this.num_locked += parseInt(data.number_locked_by_course);
            }

            // Assign question package to this.questions
            this.questions = data.questions;
            // Sort the questions in the list to make it obey the order of positions
            this.questions.sort(this.compare("position"));
            // Determine the indices that should contain checkboxes
            this.checkbox_indices.push(this.num_locked);
            this.checkbox_indices.push(this.num_locked + 1);

            // Set the default survey start and end time according to the JSON data
            // sent back
            let [date, time] = data.start_time.split(" ");
            this.start_time = date + "T" + time;
            [date, time] = data.end_time.split(" ");
            this.end_time = date + "T" + time;

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
                    );
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

        // cancel the edit panel
        cancel: function() {
            this.selected = null;
            this.edit_click = false;
        },

        // Checkbox on click action
        checkbox_click: function(question) {
            for (let index = 0; index < this.questions.length; index++) {
                if (this.questions[index].question_id == question.question_id) {
                    let set_index = index === this.checkbox_indices[0] ? 0 : 1;
                    this.$set(
                        this.check_status,
                        set_index,
                        !this.check_status[index]
                    );
                }
            }
        },
        /*
         * The following four methods are for checking whether the list element
         * should contain the up/down error, 'edit' button and the checkbox.
         */
        // Determine whether the list element is supposed to contain an "Edit" button
        contain_edit: function(question) {
            // The index of the list element that will contain the 'edit' button are the
            // ones with indices starting from this.num_locked
            // Should return true when the index of question is equal to or
            // greater than the first element of checkbox_indices
            for (let index = 0; index < this.questions.length; index++) {
                if (this.questions[index].question_id == question.question_id) {
                    if (index >= this.checkbox_indices[0]) {
                        return true;
                    }
                }
            }
            return false;
        },
        // Determine whether this list element is supposed to contain a checkbox
        contain_checkbox: function(question) {
            // The position in the checkbox_indices will be the ones that contain checkboxes
            for (let index = 0; index < this.questions.length; index++) {
                if (this.questions[index].question_id == question.question_id) {
                    if (
                        index === this.checkbox_indices[0] ||
                        index === this.checkbox_indices[1]
                    ) {
                        return true;
                    }
                }
            }
            return false;
        },
        // Determine whether the element should contain an up arrow
        contain_up_arrow: function(question) {
            // The position with indices greater than checkbox_indices[0] will be
            // the ones containing up arrows
            // Returns true if index of question is larger than first elements
            // of checkbox_indices
            for (let index = 0; index < this.questions.length; index++) {
                if (this.questions[index].question_id == question.question_id) {
                    if (index > this.checkbox_indices[0]) {
                        return true;
                    }
                }
            }
            return false;
        },
        // Determine whether the element should contain a down arrow
        contain_down_arrow: function(question) {
            // The position with indices greater than or equal to checkbox_indices[0]
            // and that is not the last index will be the ones containing up arrows
            for (let index = 0; index < this.questions.length; index++) {
                if (this.questions[index].question_id == question.question_id) {
                    if (index < this.checkbox_indices[0] || index == 5) {
                        return false;
                    }
                }
            }
            return true;
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

        // When the "up" arrow is clicked, the question is gonna be
        // exchanged with the upper one above it.
        up_exchange: function(question) {
            let ind = -1;
            for (let index = 0; index < this.questions.length; index++) {
                if (this.questions[index].question_id == question.question_id) {
                    ind = index;
                }
            }
            let down = this.questions[ind];
            let up = this.questions[ind - 1];
            let intermediate_position = down.position;
            down.position = up.position;
            up.position = intermediate_position;
            this.$set(this.questions, ind, up);
            this.$set(this.questions, ind - 1, down);
        },

        // When the "down" arrow is clicked, the question is gonna be
        // exchanged with the lower one below it.
        down_exchange: function(question) {
            let ind = -1;
            for (let index = 0; index < this.questions.length; index++) {
                if (this.questions[index].question_id == question.question_id) {
                    ind = index;
                }
            }
            let down = this.questions[ind];
            let up = this.questions[ind + 1];
            let intermediate_position = down.position;
            down.position = up.position;
            up.position = intermediate_position;
            this.$set(this.questions, ind, up);
            this.$set(this.questions, ind + 1, down);
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
        }
    }
};
</script>
