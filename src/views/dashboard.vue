<template>

<div>
    <UserInfo></UserInfo>
    <div>
        <h1>Current Surveys</h1>
        <SurveyList :term="null"></SurveyList>
        <SurveyInstanceList :term="201801" :view_only="false"></SurveyInstanceList>
    </div>
    <div>
        <h1>Past Surveys</h1>
        <SurveyInstanceList :term="null" :view_only="false"></SurveyInstanceList>
    </div>
</div>

</template>

<script>
import ErrorBlock from "./components/errorBlock.vue";
import UserInfo from "./components/user_info.vue";
import SurveyList from "./components/survey_components/survey_list.vue";
import SurveyInstanceList from "./components/response_components/survey_instance_list.vue";
export default {
    name: "survey_results_component",
    components: {
        UserInfo,
        SurveyList,
        SurveyInstanceList
    }
};
</script>
