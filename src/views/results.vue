<template>
    <SurveyInstance :summary_package="{ta_id:user_id, term, course, user_id}" :is_instance="true"> </SurveyInstance>
</template>

<script>
import SurveyInstance from "./components/response_components/survey_instance.vue";

export default {
    name: "survey_results_component",
    data: function() {
        return {
            user_id: this.$route.params.user_id,
            term: this.$route.params.term,
            course: null
        };
    },
    components: {
        SurveyInstance
    }
};
</script>
