

<template>

<div>
    <UserInfo></UserInfo>
    <div>
        <CourseList :current_term="201709" :user_id="user_id"></CourseList>
    </div>
</div>

</template>

<script>
import UserInfo from "./components/user_info.vue";
import CourseList from "./components/course_list.vue";
export default {
    name: "survey_results_component",
    data: function(){
      return {
         user_id: this.$route.params.user_id
      }
    },
    components: {
        UserInfo,
        CourseList
    }
};
</script>
