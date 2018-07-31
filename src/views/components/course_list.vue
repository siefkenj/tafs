<template>

<div>
    <h1>Current Courses</h1>
    <div v-for="course in current_courses">
      {{course}}
    </div>

    <h1>Past Courses</h1>
    <div v-for="course in past_courses">
      {{course}}
    </div>
</div>

</template>

<script>
import get_unique_attribute from "./get_unique_attribute";
import generate_query_string from "./generate_query_string";
export default {
    name: "CourseList",
    props: ["term"],
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
                    el => el.term == this.term
                );
                let past_courses = data.DATA.filter(el => el.term != this.term);
                this.current_courses = get_unique_attribute(
                    current_courses,
                    "course_code"
                );
                this.past_courses = get_unique_attribute(
                    past_courses,
                    "course_code"
                );
            })
            .catch(err => {
                this.$emit("error", err.toString());
            });
    }
};
</script>
