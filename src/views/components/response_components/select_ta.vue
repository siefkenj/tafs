

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
    <input v-model="ta_name" placeholder="search by name">
    <div v-if="!ta_package">
        No ta are available for this term
    </div>
    <table v-else style="margin: 0 auto;">
        <tr v-for="ta in get_unique_tas()">
            <button @click="select_ta(ta.user_id, ta.name, term, course, user_id)">
                {{ta.name}}
            </button>
            <Summary :summary_package="{ta_id:ta.user_id, term, course, user_id}"> </Summary>
        </tr>
    </table>
</div>

</template>

<script>
import generate_query_string from "../generate_query_string.js";
import Summary from "./summary_widget.vue";
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
            filtered_display: null,
            loading: false,
            all_terms: null,
            all_courses: null,
            ta_name: null
        };
    },
    created() {
        this.user_id = this.$route.params.user_id;
        this.init(this.type, this.term, this.course, this.user_id);
    },
    methods: {
        //get initial page info based on user_type
        init: function(user_type, term, course, user_id) {
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
                    this.loading = true;
                    fetch(url)
                        .then(response => {
                            return response.json();
                        })
                        .then(data => {
                            if (data.DATA) {
                                //all the data available
                                this.ta_package = data.DATA;
                                //the data being displayed
                                this.filtered_display = data.DATA;

                                //get all unique term and courses from the current data
                                this.all_terms = this.get_unique_attribute(
                                    this.ta_package,
                                    "term"
                                );
                                this.all_courses = this.get_unique_attribute(
                                    this.ta_package,
                                    "course_code"
                                );
                            }
                            this.loading = false;
                        })
                        .catch(err => {
                            this.$emit("error", err.toString());
                            this.loading = false;
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
            this.loading = false;
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
        // return false if object is null or empty
        isEmpty: function(object) {
            return !object || Object.keys(object).length === 0;
        },
        // returns the list of unique values from the provided attribute an object in an array of objects
        // example input: [{a:1},{a:2},{a:5},{a:2},{a:5}]
        // example output: [1,2,5]
        get_unique_attribute: function(data, attribute) {
            //turn list of object into list of attirbutes within the object
            //then store list into a set which removes duplicates
            return [...new Set(data.map(el => el[attribute]))];
        },
        // filters the course by selected term, course and ta_name, null is defaulted to be everything
        filter_ta_list: function() {
            return this.ta_package.filter(
                el =>
                    el.term === (this.term || el.term) &&
                    el.course_code === (this.course || el.course_code) &&
                    el.name.includes(this.ta_name || "")
            );
        },
        // return a unique list tas for the given term, course and name provided
        get_unique_tas: function() {
            // get all unique user_id into a list
            let unique_ids = this.get_unique_attribute(
                this.filtered_display,
                "user_id"
            );

            // for every unique user_id find any entry of ta with the user_id
            let unique_tas = [];
            for (let id of unique_ids) {
                let ta = this.filtered_display.find(el => el.user_id === id);
                unique_tas.push(ta);
            }
            return unique_tas;
        }
    },
    computed: {
        // returns the updated component of term and course combined
        // added date to be unique every time
        term_course_name() {
            //these are accessed via getter and thus are binded into the dependency list of "term_and_course"
            //This solution was found on a issue submitted for Vuejs: https://github.com/vuejs/vue/issues/844
            //for technical details checkout https://forum.vuejs.org/t/how-vuejs-knows-the-dependencies-of-computed-properties-for-caching/4945
            //and https://skyronic.com/blog/vuejs-internals-computed-properties
            this.term;
            this.course;
            this.ta_name;
            return Date.now();
        }
    },
    watch: {
        // call to get_info when term and course filters are updated
        term_course_name: function() {
            this.filtered_display = this.filter_ta_list();
        }
    },
    components: {
        Summary
    }
};
</script>
