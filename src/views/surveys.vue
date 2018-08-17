<template>
<div>
  <Navbar @error="sendError" @editName="edit_name=true" v-bind:name="name" v-bind:user_id="user_id"></Navbar>

  <v-dialog
  v-model="edit_name"
  max-width="900"
  >
      <NameModal v-bind:user_package="user_package" @close="edit_name=false" @error="sendError" @saveName="saveName"></NameModal>
  </v-dialog>

  <v-layout>
    <v-flex sm12 md12 lg10 xl6 offset-lg1 offset-xl3>
      <v-card class="mt-2">
        <v-card-title primary-title>
          <div>
            <h3 class="headline mb-0 orange--text">Surveys</h3>
            <div>Surveys may be edited (by changing the survey name or questions) and launched.
	    To allow students to give you feedback, you must launch a survey by clicking "LAUNCH". Afterwards, you will
	    be presented with a Token. Distribute the token
	    to your students so they may respond to that particular survey.</div>
          </div>
        </v-card-title>
        <v-card-title class="py-0">
          <div>
            <h3 class="title my-1 blue--text">Survey Templates</h3>
            <div>Below is a list of surveys that you may edit and launch. These surveys will always be
		    available. When you edit one of these surveys, a copy of the survey will appear in
		    "Customized Surveys".</div>
          </div>
        </v-card-title>
        <v-card-text>
	    <template v-for="(survey, index) in template_surveys">
		<div :key="`survey-${index}`">
		    <v-divider v-if="index != 0"></v-divider>
		    <SurveyDisplay
			:is_instance="false"
			:survey_package="survey"
			@edit="startEdit(survey)"
			@launch="launchDialog(survey)"
			color="blue"></SurveyDisplay>
		</div>
	    </template>
        </v-card-text>
        <v-card-title class="py-0">
          <div>
            <h3 class="title my-1 blue--text">Customized Surveys</h3>
            <div>Below is a list of launched surveys that you have customized by editing either
	    the questions or the title of the survey. It's good practice to include the name
	    of your course in the title of your surveys so you can remember which survey is which.</div>
          </div>
        </v-card-title>
        <v-card-text>
	    <template v-for="(survey, index) in custompized_surveys">
		<div :key="`survey-${index}`">
		    <v-divider v-if="index != 0"></v-divider>
		    <SurveyDisplay
			:is_instance="false"
			:survey_package="survey"
			@edit="startEdit(survey)"
			@launch="launchDialog(survey)"></SurveyDisplay>
		</div>
	    </template>
        </v-card-text>
      </v-card>

      <v-card class="my-4">
        <v-card-title primary-title>
          <div>
            <h3 class="headline mb-0 orange--text">Launched Surveys</h3>
            <div>Below is a list of launched surveys. By distributing the "Token"
	    for a launched survey to your students, they will be able to give you
	    feedback by responding to that survey. By clicking a launched survey, you can
	    see detailed survey results.</div>
          </div>
        </v-card-title>
        <v-card-text>
	    <template v-for="(survey, index) in survey_instances">
		<div :key="`survey_instance-${index}`">
		    <v-divider v-if="index != 0"></v-divider>
		    <SurveyDisplay :is_instance="true" :survey_package="survey"></SurveyDisplay>
		</div>
	    </template>
       </v-card-text>
      </v-card>
    </v-flex>
  </v-layout>

    <!-- dialogs for managing surveys -->
    <v-dialog
        v-model="modal_is_open_edit"
        lazy
        :scrollable="true"
        max-width="900"
        >
        <SurveyQuestionEditor
            :level="level" :survey_package="current_survey"
            :question_choices="questions"
            @cancel="modal_is_open_edit = false"
            @save="editSurvey"
            ></SurveyQuestionEditor>
    </v-dialog>
    <v-dialog
        v-model="modal_is_open_launch"
        :scrollable="true"
        max-width="900"
        >
        <LaunchModal
            :survey_package="launched_survey"
            @launch="launchSurvey"
            @cancel="modal_is_open_launch = false"
            ></LaunchModal>
    </v-dialog>
    <v-dialog
        v-model="modal_is_open_token"
        lazy
        :scrollable="true"
        max-width="900"
        >
        <TokenDisplay
            :token="current_token"
            @ok="modal_is_open_token = false"
            ></TokenDisplay>
    </v-dialog>

</div>
</template>

<script>
import generate_query_string from "./components/generate_query_string.js";
import SurveyQuestionEditor from "./components/survey_question_editor.vue";
import LaunchModal from "./components/launch_modal.vue";
import TokenDisplay from "./components/token_display.vue";
import SurveyDisplay from "./components/survey_display.vue";
import Navbar from "./components/navbar.vue";
import NameModal from "./components/name_modal.vue";

export default {
    name: "Surveys",
    data: function() {
        return {
            questions: [],
            raw_surveys: [],
            survey_ids: [],
            survey_instances: [],
            survey_instance_ids: [],
            current_survey: { questions: [] },
            current_token: "",
            modal_is_open_edit: false,
            modal_is_open_launch: false,
            modal_is_open_token: false,
            launched_survey: {},
            edit_name: false,
            name: null
        };
    },
    created: async function() {
        this.getQuestions();
        this.getSurveys();
        this.getSurveyInstances();
        this.getUserName();
    },
    methods: {
        sendError: function(value) {
            this.$emit("error", value);
        },
        getSurveys: async function() {
            // Fetch all the surveys
            let fetched = await fetch(
                "get_info.php?" +
                    generate_query_string({
                        what: "surveys",
                        user_id: this.user_id,
                        term: this.term
                    })
            );
            let fetchedJSON = await fetched.json();
            if (fetchedJSON.TYPE !== "survey_package") {
                this.sendError("Could not retrieve survey ids");
                return;
            }
            this.survey_ids = fetchedJSON.DATA;

            let all_ids = this.survey_ids.map(
                ({ name, survey_id }) => survey_id
            );
            fetched = await fetch(
                "get_info.php?" +
                    generate_query_string({
                        what: "surveys",
                        user_id: this.user_id,
                        survey_id: all_ids.join(",")
                    })
            );
            fetchedJSON = await fetched.json();
            if (fetchedJSON.TYPE !== "survey_package") {
                this.sendError("Could not retrieve surveys");
                return;
            }
            this.raw_surveys = fetchedJSON.DATA;
            return this.raw_surveys;
        },
        getSurveyInstances: async function() {
            // Fetch all the surveys
            let fetched = await fetch(
                "get_info.php?" +
                    generate_query_string({
                        what: "survey_results",
                        user_id: this.user_id,
                        target_ta: this.user_id,
                        term: this.term
                    })
            );
            let fetchedJSON = await fetched.json();
            if (fetchedJSON.TYPE !== "survey_package") {
                this.sendError("Could not retrieve survey ids");
                return;
            }
            this.survey_instances = fetchedJSON.DATA;
            return this.survey_instances;
        },
        getQuestions: async function() {
            let fetched, fetchedJSON;
            // Fetch all the questions back from the API
            fetched = await fetch(
                "get_info.php?" + generate_query_string({ what: "questions" })
            );
            fetchedJSON = await fetched.json();
            if (fetchedJSON.TYPE !== "questions_package") {
                this.sendError("Could not retrieve quesitons list");
                return;
            }
            this.questions = fetchedJSON.DATA;
            return this.questions;
        },
        startEdit: function(survey) {
            this.current_survey = survey;
            this.modal_is_open_edit = true;
        },
        editSurvey: async function(new_survey) {
            let params = {
                what: "surveys",
                survey_id: new_survey.survey_id,
                user_id: this.user_id,
                level: this.level,
                action: "add_or_update"
            };
            let post = {
                name: new_survey.name,
                survey_id: new_survey.survey_id,
                term: new_survey.term,
                timedate_open: new_survey.timedate_open,
                timedate_close: new_survey.timedate_close
            };

            // The naming convention for the API is different depending
            // on what `level` we are setting. We either need to set
            // "dept_survey_choices", "course_survey_choices", or "ta_survey_choices".
            // However, the API ignores any choices at the wrong level, so
            // set them all.
            let choices = new_survey.questions.map(
                ({ question_id }) => +question_id
            );
            post.dept_survey_choices = { department_name: null, choices };
            post.course_survey_choices = { course_code: null, choices };
            post.ta_survey_choices = { section_id: null, choices };

            try {
                let url = "post_info.php?" + generate_query_string(params);
                let fetched = await fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json; charset=utf-8"
                    },
                    body: JSON.stringify(post)
                });

                // Now that the survey has been updated, we need to close
                // the dialog and refresh the survey list.
                this.modal_is_open_edit = false;
                this.getSurveys();

                let fetchedJSON = await fetched.json();
                // XXX TODO check that the returned survey package has the questions that
                // we set and throw an error if it doesn't.
            } catch (e) {
                this.sendError(e.toString());
            }
        },
        launchDialog: function(survey) {
            this.launched_survey = survey;
            this.modal_is_open_launch = true;
        },
        launchSurvey: async function(survey) {
            // Before we launch, we need to set the default open and close
            // time on the original survey.
            let fetched, fetchedJSON, params;
            /*
            // XXX TODO the launch API should be changed to allow passing in
            // opening and closing datetimes
            let params = {
                what: "surveys",
                survey_id: survey.survey_id,
                user_id: this.user_id,
                level: this.level,
                action: "add_or_update"
            };
            let body = {
                default_survey_open: survey.default_survey_open,
                default_survey_close: survey.default_survey_close
            };
            let fetched, fetchedJSON, new_survey;

            // Update the survey open/close dates
            try {
                this.modal_is_open_launch = false;
                fetched = await fetch(
                    "post_info.php?" + generate_query_string(params),
                    { method: "POST", body: JSON.stringify(body) }
                );
                fetchedJSON = await fetched.json();
                // It is possible that when we set the date, the survey was cloned,
                // so we'd better preserve the survey_id of the (possibly) new survey
                new_survey = fetchedJSON.DATA;
            } catch (e) {
                this.sendError(e.toString());
                return;
            }
	    */

            // Launch the survey
            params = {
                what: "launch_survey",
                user_id: this.user_id,
                survey_id: survey.survey_id,
                survey_open: survey.default_survey_open,
                survey_close: survey.default_survey_close
            };
            try {
                this.modal_is_open_launch = false;
                fetched = await fetch(
                    "post_info.php?" + generate_query_string(params)
                );
                fetchedJSON = await fetched.json();
                this.current_token = fetchedJSON.DATA.override_token;
                this.modal_is_open_token = true;
            } catch (e) {
                this.sendError(e.toString());
                return;
            }
            // When the survey is launched, refresh the survey
            // instances list--a new survey should show up!
            this.getSurveyInstances();
        },
        /**
         * Save updated name to database
         */
        saveName: async function(new_name) {
            let url = {
                what: "user_info",
                user_id: this.user_id,
                action: "add_or_update"
            };
            try {
                let fetched = await fetch(
                    "post_info.php?" + generate_query_string(url),
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json; charset=utf-8"
                        },
                        body: JSON.stringify({
                            user_list: [
                                {
                                    user_id: this.$route.query.user_id,
                                    name: new_name
                                }
                            ]
                        })
                    }
                );
            } catch (e) {
                this.$emit("error", "Cannot modify name");
            }
            this.edit_name = false;
            this.getUserName();
        },
        /**
         * Get user name from API
         */
        getUserName: async function() {
            let url = {
                what: "user_info",
                ensure_exists: true,
                user_id: this.$route.query.user_id
            };
            let fetched, fetchedJSON;
            try {
                fetched = await fetch(
                    "get_info.php?" + generate_query_string(url)
                );
                fetchedJSON = await fetched.json();
            } catch (error) {
                this.$emit("error", "Could not retrieve user data");
            }

            if (fetchedJSON.DATA.length < 1) {
                this.$emit("error", "Could not retrieve user data");
            } else {
                this.name = fetchedJSON.DATA[0].name;
            }
            if (this.user_id.toUpperCase() === this.name) {
                this.edit_name = true;
            }
        }
    },
    computed: {
        user_package: function() {
            return {
                name: this.name,
                user_id: this.user_id
            };
        },
        term: function() {
            return this.$route.query.term;
        },
        user_id: function() {
            return this.$route.query.user_id;
        },
        level: function() {
            return "section";
        },
        surveys: function() {
            return this.raw_surveys;
        },

        template_surveys: function() {
            let level = this.level;
            if (level == "section") {
                level = "ta";
            }
            // if the level_choices for our level are not set, we're a "template"
            return this.raw_surveys.filter(function(s) {
                return !(s.level_choices || {})[level];
            });
        },

        custompized_surveys: function() {
            let level = this.level;
            if (level == "section") {
                level = "ta";
            }

            // if the level_choices for our level are set, we're "custom"
            return this.raw_surveys.filter(function(s) {
                return (s.level_choices || {})[level];
            });
        }
    },
    components: {
        SurveyDisplay,
        LaunchModal,
        SurveyQuestionEditor,
        TokenDisplay,
        Navbar,
        NameModal
    }
};
</script>
