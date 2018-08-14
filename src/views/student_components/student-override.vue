<template>
  <v-layout>
    <v-flex xs12 sm6 offset-sm3>
      <v-card>
        <v-card-title primary-title>
          <div>
            <p>Please enter a token, so you can to take the correct survey.</p>
            <p class="grey--text">A token looks like a mix of six letters/numbers; e.g., HLF43W.</p>
          </div>
        </v-card-title>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-layout row>
            <v-flex>
              <v-text-field
                label="Token"
                placeholder="Token"
                color="orange"
                solo
                clearable
                :value="override_token"
                @input="upperCase"
              ></v-text-field>
            </v-flex>
            <v-flex>
              <v-subheader>
                <v-btn color="orange white--text" v-on:click="getData">Use Token</v-btn>
              </v-subheader>
            </v-flex>
          </v-layout>
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
            override_token: null
        };
    },
    created: function() {},
    methods: {
        /**
         * Getting data from API.
         * All errors are emitted to parent component.
         */
        getData: function() {
            let url = {
                what: "get_ta",
                user_id: this.$route.params.user_id,
                override_token: this.override_token
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
                    `No survey associated with token "${this.override_token ||
                        " "}"`
                );
            } else {
                this.$emit("no-error");
                this.new_token();
            }
        },
        /**
         * Replaces URL to display overriden survey
         */
        new_token: function() {
            this.$router.push({
                name: "student-landing",
                params: { override_token: this.override_token }
            });
        },
        /**
         * UpperCase token
         */
        upperCase: function(value) {
            if (!value) {
                value = "";
            }
            this.override_token = value.toUpperCase();
        }
    }
};
</script>
