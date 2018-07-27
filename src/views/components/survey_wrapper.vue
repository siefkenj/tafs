<template>
<div>
      <SurveySummary :summary_package="summary_package" v-on:show="change_display"> </SurveySummary>
      <div v-if="show">
          <button v-if="!is_instance" type="button" name="button">Launch</button>
          <button type="button" name="button">Clone</button>
          <button v-if="!is_instance" type="button" name="button">Edit</button>
          <button v-if="is_instance" type="button" name="button">Allow Instructor Access</button>
      </div>
</div>
</template>

<script>
import SurveySummary from "./response_components/survey_summary.vue";

export default {
    name: "SurveyWrapper",
    props: ["summary_package", "is_instance"],
    data: function() {
        return {
            show: false
        };
    },
    methods: {
        change_display: function(show) {
            this.show = show;
        }
    },
    components: {
        SurveySummary
    }
};
</script>
