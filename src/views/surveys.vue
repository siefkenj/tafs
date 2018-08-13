<template>

<div id="question-select">
    <SurveyList :term="this.$route.params.term"></SurveyList>
</div>

</template>

<script>
import SurveyList from "./components/survey_list.vue";
export default {
    name: "question-select",
    methods: {
        sendError: function(value) {
            this.$emit("error", value);
        }
    },
    components: {
        SurveyList
    }
};
</script>
