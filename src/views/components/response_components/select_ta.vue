

<template>

<div v-if="loading">
    loading...
</div>
<div v-else>
    <select v-model="term">
        <option v-bind:value="null">All terms</option>
        <option v-for="term in all_terms" v-bind:value="term">{{term}}</option>
    </select>
    <select v-model="course">
        <option v-bind:value="null">All courses</option>
        <option v-for="course in all_courses">{{course}}</option>
    </select>
    <div v-if="isEmpty(ta_package)">
        No ta are available for this term
    </div>
    <ul v-for="(course, course_code) in ta_package">
        {{course_code}}
        <button v-for="ta in course" @click="select_ta(ta.id,ta.name, term, course_code, user_id)">
            {{ta.name}}
        </button>
    </ul>
</div>

</template>

<script>
import generate_query_string from "../generate_query_string.js";

export default {
    name: "select_ta",
    data: function() {
        return {
            type: "admin",
            user_id: null,
            user_name: "Daniel",
            term: null,
            course: null,
            ta_package: null,
            loading: false,
            all_terms: null,
            all_courses: null
        };
    },
    created() {
        this.user_id = this.$route.params.user_id;
        this.init(this.type, this.term, this.course, this.user_id);
    },
    methods: {
        //get initial page info based on user_type
        init: async function(user_type, term, course, user_id) {
            let url =
                "get_info.php?" +
                generate_query_string({
                    what: "tas",
                    term: term,
                    course_code: course,
                    user_id: user_id
                });
            switch (user_type) {
                case "admin":
                case "instructor":
                    await fetch(url)
                        .then(response => {
                            return response.json();
                        })
                        .then(data => {
                            this.ta_package = this.process_tas(data);
                            this.all_terms = [201709, 201801, 201701];
                            if (!this.course) {
                                this.all_courses = Object.keys(this.ta_package);
                            }
                        })
                        .catch(err => {
                            this.$emit("error", err.toString());
                        });
                    break;
                case "ta":
                    this.target_ta = user_id;
                    this.select_ta(
                        user_id,
                        this.user_name,
                        term,
                        course,
                        user_id
                    );
                    break;
                default:
                    console.warn(
                        "`user_type` of ",
                        user_type,
                        "is unrecognized."
                    );
            }
        },
        select_ta: function(ta_id, ta_name, term, course, user_id) {
            this.$router.push({
                name: "response_page",
                query: {
                    target_ta: ta_id,
                    term: term,
                    course: course,
                    user_id: user_id,
                    selected_ta: ta_name
                }
            });
        },
        // group ta by courses
        // Input: {DATA: [{course_code: "CSC100", name: "Daniel", user_id: "ta1"},
        //               {course_code: "CSC100", name: "Darren", user_id: "ta2"},
        //               {course_code: "CSC101", name: "Daniel", user_id: "ta1"},
        //               {course_code: "CSC101", name: "Darren", user_id: "ta2"}
        //  ]}
        // Output : { CSC100: [{name: "Daniel", id: "ta1"},{name: "Dareen", id: "ta2"}]
        //            CSC101: [{name: "Daniel", id: "ta1"},{name: "Dareen", id: "ta2"}]
        //}
        process_tas: function(data) {
            let ta_package = {};
            for (let row of data.DATA) {
                ta_package[row.course_code] = ta_package[row.course_code] || [];
                ta_package[row.course_code].push({
                    name: row.name,
                    id: row.user_id
                });
            }
            return ta_package;
        },
        // return false if object is null or empty
        isEmpty: function(object) {
            return !object || Object.keys(object).length === 0;
        }
    },
    computed: {
        // returns the updated component of term and course combined
        // added date to be unique every time
        term_and_course() {
            //these are accessed via getter and thus are binded into the dependency list of "term_and_course"
            //This solution was found on a issue submitted for Vuejs: https://github.com/vuejs/vue/issues/844
            //for technical details checkout https://forum.vuejs.org/t/how-vuejs-knows-the-dependencies-of-computed-properties-for-caching/4945
            //and https://skyronic.com/blog/vuejs-internals-computed-properties
            this.term;
            this.course;
            return Date.now();
        }
    },
    watch: {
        // call to get_info when term and course filters are updated
        term_and_course: function(term,course) {
            this.init(this.type, this.term, this.course, this.user_id);
        }
    }
};
</script>
