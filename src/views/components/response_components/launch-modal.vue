<style>
</style>

<template>
    <div style="float:left">
        <v-btn color="info" @click="dialog = true">Launch</v-btn>
        <v-dialog v-model="dialog" width="600">
            <v-card>
                <v-card-title>Launch Survey</v-card-title>

                <v-layout row wrap>
                    <v-flex xs12 sm6 md4>
                        <v-menu
                            ref="menu2"
                            :close-on-content-click="false"
                            v-model="menu2"
                            :return-value.sync="time"
                            lazy
                            transition="scale-transition"
                            offset-y
                            full-width
                            max-width="290px"
                            min-width="290px">

                            <v-text-field
                                slot="activator"
                                v-model="time"
                                label="Launch Now"
                                prepend-icon="access_time"
                                readonly>
                            </v-text-field>

                            <v-time-picker
                                v-if="menu2"
                                v-model="time"
                                >
                                <v-btn flat color="primary" @click="modifyLaunchDate($refs)">Save</v-btn>
                            </v-time-picker>
                        </v-menu>
                    </v-flex>

                    <v-flex xs12 sm6 md4>
                        <div v-if="changeDate">
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
                        </div>
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
                      @click="dialog = false"
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
        </v-dialog>
    </div>
</template>

<script>
export default {
    data() {
        return {
            dialog: false,
            date: null,
            menu: false,
            menu2: null,
            time: null,
            duration: 5,
            changeDate: false
        };
    },
    watch: {
        dialog: function() {
            if (!this.dialog) {
                this.changeDate = false;
                this.date = null;
                this.time = null;
            }
        }
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
            this.dialog = false;
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
