<template>
  <v-layout>
    <v-flex xs12 sm6 offset-sm3>
      <v-alert :value="existing_response_id" type="info">
        You have already filled out this survey. You may retake it to override your previous answers.
      </v-alert>
      <div v-if="name==null">
        <h1>Loading...</h1>
      </div>
      <v-card>
        <v-card-title primary-title>
          <div>
            <p>Give feedback to <span class="cyan--text">{{name}}</span> by taking a short survey.
              Please keep {{course_code}} {{section}} in mind when giving feedback to {{name}}.</p>

            <p class="grey--text">If you would like to give feedback to a different TA, please
            enter an override token.</p>
          </div>
        </v-card-title>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn flat color="orange" v-on:click="enter_token">Enter Different Token</v-btn>
          <v-btn color="orange white--text" v-on:click="take_survey">
            <span v-if="existing_response_id">Retake Survey</span><span v-else>Take Survey</span>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-flex>
  </v-layout>
</template>

<script>
import generate_query_string from "../components/generate_query_string";
export default {
    data: function() {
        return {
            name: null,
            photo: null,
            section: null,
            course_code: null,
            new_override: null,
            existing_response_id: false
        };
    },
    created: function() {
        this.getData();
    },
    methods: {
        /**
         * Getting data from API.
         * All errors are emitted to parent component.
         */
        getData: function() {
            let url = {
                what: "get_ta",
                user_id: this.$route.params.user_id,
                override_token: this.$route.params.override_token
            };
            fetch("student_survey.php?" + generate_query_string(url))
                .then(res => res.json())
                .then(data => this.parseData(data))
                .catch(err => {
                    this.$emit("error", err.toString());
                });
        },
        /**
         * Saving data to data
         * All errors are emitted to parent component.
         *
         * @param data The data received from API call
         */
        parseData: function(data) {
            if (!data || !data.DATA) {
                this.$emit("error", "No data received");
            }
            if (!data.DATA.name) {
                this.$emit(
                    "error",
                    `No survey associated with token ${
                        this.$route.params.override_token
                    }`
                );
            }
            this.name = data.DATA.name || "";
            this.photo = data.DATA.photo;
            this.section = data.DATA.section;
            this.course_code = data.DATA.course_code;
            this.existing_response_id = data.DATA.existing_response_id;
        },
        /**
         * Replaces URL to display given survey
         */
        take_survey: function() {
            this.$router.push({ name: "student" });
        },
        /**
         * Replaces URL to display overriden survey
         */
        new_token: function() {
            this.$router.push({
                name: "student",
                params: { override_token: this.new_override }
            });
        },
        /**
         * Reroute to the page for entering a token
         */
        enter_token: function() {
            this.$router.replace({ name: "override" });
        }
    }
};
</script>
