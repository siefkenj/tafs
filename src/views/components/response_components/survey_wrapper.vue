
<template>
    <SurveyDisplay v-if="data"
    :survey_package="data"
    @clone="" @edit="passEditData" @launch="passLaunchData"></SurveyDisplay>
</template>

<script>
import SurveyDisplay from "./survey_display.vue";
export default {
    name: "SurveyWrapperButtons",
    props: ["data"],

    methods: {
        passEditData: function(data) {
            this.$emit("edit", data);
        },
        passLaunchData: function(data) {
            this.$emit("launch", data);
        }
    },
    components: {
        SurveyDisplay
    }
};
</script>
