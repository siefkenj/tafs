
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
        <div id="question_listing">
            <QuestionList> </QuestionList>
        </div>
        <br/>

        <!-- <h2><b>Set the start time</b></h2>
        <p>(Note: Please use the same format as default)</p>
        <input type="datetime-local" v-model="start_time"/>
        <h2><b>Set the end time</b></h2>
        <p>(Note: Please use the same format as default)</p>
        <input type="datetime-local" v-model="end_time"/>
        <br/><br/><br/><br/> -->

        <v-btn type="submit" v-on:click="save">
            Finish the setting</v-btn>
    </div>
</template>

<script>
import QuestionList from "./question_selection.vue";
import generate_query_string from "../generate_query_string";
export default {
    name: "question_time_setting",
    data: function() {
        return {
            survey_package: null,
            survey_id: null,
            name: null,
            term: "201801",
            start_time: "2000-01-01T00:00:00",
            end_time: "2000-01-01T00:00:00"
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
        sendError: function(value) {
            this.$emit("error", value);
        },
        parseData: function(fetchedJSON) {
            this.survey_package = fetchedJSON.DATA;
            let data = fetchedJSON.DATA[0];
            if (data == null) {
                this.$emit("error", "No data received");
            }

            // Set the default survey start and end time according to the JSON data
            // sent back
            if (data.timedate_open) {
                let [date, time] = data.timedate_open.split(" ");
                this.start_time = date + "T" + time;
            }
            if (data.timedate_close) {
                [date, time] = data.timedate_close.split(" ");
                this.end_time = date + "T" + time;
            }
        },

        // If the user exits this page, it will jump into next page
        save: function() {
            this.$router.push({
                path: `/user_id/${this.$route.params.user_id}/surveys`
            });
        }
    },
    components: {
        QuestionList
    }
};
</script>
