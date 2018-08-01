<template>

<div display="flex">
    <P style="margin-bottom:30px; font-size: 35px;">Your Current Courses:</P>
    <v-btn style="float:right;"> Edit/Launch Surveys</v-btn>
    <div v-for="course in current_courses">
      {{course.course_code}} {{course.section_code}}
    </div>

    <h1>Past Courses</h1>
    <div v-for="course in past_courses">
      {{course.course_code}} {{course.section_code}}
    </div>
</div>

</template>

<script>
import get_unique_attribute from "./get_unique_attribute";
import generate_query_string from "./generate_query_string";
export default {
    name: "CourseList",
    props: ["current_term","user_id"],
    data: function() {
        return {
            current_courses: null,
            past_courses: null
        };
    },
    created() {
        let url = {
            what: "course_pairings",
            user_id: this.$route.params.user_id,
            column: "instructor"
        };
        fetch("get_info.php?" + generate_query_string(url))
            .then(res => res.json())
            .then(data => {
                let current_courses = data.DATA.filter(
                    el => el.current_term == this.term && el.user_id == this.user_id
                );
                console.log(data);
                console.log(current_courses);
                let past_courses = data.DATA.filter(el => el.term != this.current_term && el.user_id === this.user_id);
                this.current_courses = current_courses;
                this.past_courses = past_courses;
            })
            .catch(err => {
                this.$emit("error", err.toString());
            });
    }
};
</script>
