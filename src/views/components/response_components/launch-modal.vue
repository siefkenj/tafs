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
                        v-model.number="duration"
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
    watch: {
        survey_package: function() {
            if (this.survey_package) {
                this.getData();
            }
        }
    },
    methods: {
        getData: function() {
            let url = {
                what: "surveys",
                user_id: this.$route.params.user_id,
                survey_id: this.survey_package.survey_id
            };
            fetch("get_info.php?" + generate_query_string(url))
                .then(res => res.json())
                .then(data => this.parseData(data))
                .catch(err => this.$emit("error", err.toString()));
        },
        parseData: function(data) {
            let open = new Date(data.DATA[0].timedate_open);
            let close = new Date(data.DATA[0].timedate_close);

            // 86400000 is the constant to divide between 2 dates to get
            // number of days between dates
            this.duration = Math.floor((close - open) / 86400000);

            this.date =
                open.getFullYear() +
                "-" +
                (open.getMonth() + 1) +
                "-" +
                open.getDate();
        },
        /**
         * Function launches survey for selected time and duration
         */
        launchSurvey: function() {
            let url = {
                what: "surveys",
                survey_id: this.survey_package.survey_id,
                user_id: this.$route.params.user_id,
                level: "section",
                action: "add_or_update"
            };
            // Create timedate
            // Start time and date
            let start_time = new Date(this.date);
            start_time.setHours(0, 0, 0);
            start_time = this.formatDate(start_time);

            // End time and date
            let end_time = new Date(this.date);
            end_time.setDate(end_time.getDate() + this.duration);
            end_time.setHours(0, 0, 0);
            end_time = this.formatDate(end_time);

            let body = JSON.stringify({
                ta_survey_choices: null,
                name: null,
                term: null,
                default_survey_open: start_time,
                default_survey_close: end_time
            });

            fetch("post_info.php?" + generate_query_string(url), {
                method: "POST",
                body: body
            }).catch(err => this.$emit("error", err.toString()));

            // Launching Survey
            // Creating a survey instance
            url = {
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
         * Accepts a date object and returns date in format YYYY-MM-DD HH:MM:SS
         */
        formatDate: function(date) {
            return (
                date.getFullYear() +
                "-" +
                (date.getMonth() + 1) +
                "-" +
                date.getDate() +
                " " +
                ("0" + date.getHours()).slice(-2) +
                ":" +
                ("0" + date.getMinutes()).slice(-2) +
                ":" +
                ("0" + date.getSeconds()).slice(-2)
            );
        }
    }
};
</script>
