<template>
<div>
  <v-layout>
    <v-flex sm12 md10 lg8 xl6 offset-md1 offset-lg2 offset-xl3>
      <v-card class="mt-2">
        <v-card-title primary-title>
          <div>
            <h3 class="headline mb-0 orange--text">Available Surveys</h3>
            <div>Below is a list of surveys that you may edit and launch. When you click LAUNCH,
	    you will be given a token to distribute to your students and a copy of the survey
	    will appear in the Launched Surveys area below. If you launch
	    a survey multiple times, multiple copies of the survey, each with a different token,
	    will be created.</div>
          </div>
        </v-card-title>
        <v-card-text>
	    <template v-for="(survey, index) in surveys">
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

/**
 * Filter surveys so that multiple copies of surveys with the same
 * name and same questions don't appear. Surveys whose level_choices
 * are not null are preferred.
 */
function filter_surveys(surveys) {
    function hash_survey(s) {
        let questions = s.questions.map(x => x.question_id);
        return questions.join(",") + ":" + s.name;
    }
    function get_dominant_survey(s1, s2) {
        function compare_on_level(level) {
            if (s1.level_choices[level] != null) {
                return s1;
            }
            if (s1.level_choices[level] != null) {
                return s2;
            }
            return null;
        }
        return (
            compare_on_level("ta") ||
            compare_on_level("course") ||
            compare_on_level("dept") ||
            s1
        );
    }
    let ret = [];
    let hash = {};
    for (let s of surveys) {
        let survey_hash = hash_survey(s);
        if (hash[survey_hash] == null) {
            // in this case, there is no survey with the same
            // questions and name
            hash[survey_hash] = ret.length;
            ret.push(s);
        } else {
            // we've found a survey with the same questions and name.
            // decide which one to keep.
            ret[hash[survey_hash]] = get_dominant_survey(
                ret[hash[survey_hash]],
                s
            );
        }
    }
    return ret;
}

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
            launched_survey: {}
        };
    },
    created: async function() {
        this.getQuestions();
        this.getSurveys();
        this.getSurveyInstances();
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

            // Launch the survey
            params = {
                what: "launch_survey",
                user_id: this.user_id,
                survey_id: new_survey.survey_id
            };
            try {
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
        }
    },
    computed: {
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
            return filter_surveys(this.raw_surveys);
        }
    },
    components: {
        SurveyDisplay,
        LaunchModal,
        SurveyQuestionEditor,
        TokenDisplay
    }
};
</script>
