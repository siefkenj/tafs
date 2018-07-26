

<template>

<div>
    Hello! {{user.name}}
    <UserInfo></UserInfo>
    <CurrentSurveys></CurrentSurveys>
    <PastSurveys></PastSurveys>
</div>

</template>

<script>
import ErrorBlock from "./components/errorBlock.vue";
import UserInfo from "./settings.vue";
import CurrentSurveys from "./components/current_survey.vue";
import PastSurveys from "./components/past_survey.vue";
export default {
    name: "survey_results_component",
    data: function() {
        return {
            user: {
                utorid: "",
                name: "Daniel"
            }
        };
    },
    components: {
        UserInfo,
        CurrentSurveys,
        PastSurveys
    }
};
</script>
