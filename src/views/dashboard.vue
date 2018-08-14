

<template>

<div>
    <UserInfo></UserInfo>
    <div>
        <CourseList :current_term="201809"></CourseList>
    </div>
</div>

</template>

<script>
import UserInfo from "./components/user_info.vue";
import CourseList from "./components/course_list.vue";
export default {
    name: "Dashboard",
    components: {
        UserInfo,
        CourseList
    }
};
</script>
