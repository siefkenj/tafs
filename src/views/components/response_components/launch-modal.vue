<style>
</style>

<template>
        <v-card>
            <v-card-title>Launch Survey</v-card-title>

            <v-layout row wrap>
                <v-flex xs12 sm6 md4>
                        <v-menu
                          ref="menu"
                          :close-on-content-click="false"
                          v-model="menu"
                          :nudge-right="40"
                          :return-value.sync="date"
                          lazy
                          transition="scale-transition"
                          offset-y
                          full-width
                          min-width="290px">
                          <v-text-field
                            slot="activator"
                            v-model="date"
                            label="Launch Date"
                            prepend-icon="event"
                            readonly>
                        </v-text-field>
                          <v-date-picker v-model="date" no-title scrollable>
                            <v-spacer></v-spacer>
                            <v-btn flat color="primary" @click="menu = false">Cancel</v-btn>
                            <v-btn flat color="primary" @click="$refs.menu.save(date);">OK</v-btn>
                          </v-date-picker>
                        </v-menu>
                </v-flex>

                <v-flex xs12 sm6 md4>
                    <v-text-field
                        label="Duration of Survey (Days)"
                        placeholder="Duration"
                        v-model="duration"
                        >
                    </v-text-field>
                </v-flex>
            </v-layout>

            <v-card-actions>
                <v-btn
                  color="error"
                  flat
                  @click="$emit('cancel')"
                >
                  Cancel
                </v-btn>
              <v-spacer></v-spacer>
              <v-btn
                color="primary"
                flat
                @click="launchSurvey()">
                Launch
              </v-btn>
            </v-card-actions>
        </v-card>
</template>

<script>
import generate_query_string from "../generate_query_string";
export default {
    props: {
        survey_package: Object
    },
    data() {
        return {
            date: null,
            menu: false,
            menu2: null,
            time: null,
            duration: 5
        };
    },
    methods: {
        /**
         * Function launches survey for selected time and duration
         */
        launchSurvey: function() {
            let currentDate = new Date();
            if (!this.time) {
                this.time = "00:00";
            }
            if (!this.date)
                this.date =
                    currentDate.getFullYear() +
                    "-" +
                    (currentDate.getMonth() + 1) +
                    "-" +
                    currentDate.getDate();

            // Launching Survey
            // Creating a survey instance
            let url = {
                what: "launch_survey",
                user_id: this.$route.params.user_id,
                survey_id: this.survey_package.survey_id
            };

            fetch("post_info.php?" + generate_query_string(url))
                .then(res => res.json())
                .then(data => {
                    this.$emit("token", data.DATA);
                })
                .catch(err => this.$emit("error", err.toString()));

            // Emit to parent to close dialog
            this.$emit("launch");
        },
        /**
         * Saves selected release time
         */
        modifyLaunchDate: function(ref) {
            ref.menu2.save(this.time);
            this.changeDate = true;
        }
    }
};
</script>
