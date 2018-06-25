<style>
@import "../../node_modules/handsontable/dist/handsontable.full.css";
#table-format {
    width: 90%;
    height: 600px;
    margin: auto;
    overflow: hidden;
    border: 2px solid #09b4ed;
}
</style>

<template>

<div>
    <h1>Course and Section Data</h1>
    <div>
        <button>Import Data</button>
        <button>Download Sample Data</button>

        <p>Specify Course: </p>
        <select v-model="specify_course">
            <option>All Courses</option>
            <option v-for="course in this.list_courses">{{course}}</option>
        </select>

        <p>Specify Term: </p>
        <select v-model="specify_term">
            <option>All Terms</option>
            <option v-for="term in this.list_terms">{{term}}</option>
        </select>

        <p>Group By</p>
        <select v-model="column" options="this.column">
            <option>Instructor</option>
            <option>TA</option>
        </select>
    </div>

    <div id="table"></div>
</div>

</template>

<script>
import HotTable from "@handsontable/vue";
import Handsontable from "handsontable";
import generate_get_url from "./components/url_generator";

export default {
    data: function() {
        return {
            table: null,
            specify_term: "All Terms",
            column: "Instructor",
            root: "table-format",
            specify_course: "All Courses",
            user_associations: [],
            list_courses: [],
            list_terms: [],
            tableSettings: {
                data: [],
                stretchH: "all",
                colHeaders: []
            }
        };
    },
    /**
     * Function is called when page is created to get course pairings from API.
     * Data is used to populate the spreadsheet and options for select
     */
    created: function() {
        this.getData();
    },

    watch: {
        /**
         * Function is called when specify_course is modified. The data is used
         * to populate the page with the specified course.
         */
        specify_course: function() {
            this.getData();
        },

        /**
         * Function is called when specify_term is modified. The data is used
         * to populate the page with the specified term.
         */
        specify_term: function() {
            this.getData();
        },

        /**
         * Function is called when column is modified. The data is used
         * to populate the page with the specified column.
         */
        column: function() {
            this.getData();
        }
    },
    methods: {
        /**
         * Function gets course pairing data from API for the specified
         * course, term and column and calls parseData to parse retuned data.
         */
        getData: function() {
            // Converting to parameter enums
            var column_name = this.column === "TA" ? "ta" : "instructor";

            var course_where = "";
            var term_where = "";
            if (this.specify_term != "All Terms") {
                term_where = `${this.specify_term}`;
            }
            if (this.specify_course != "All Courses") {
                course_where = `${this.specify_course}`;
            }

            let url =
                "{" +
                '"what":"course_pairings",' +
                `"user_id":"${this.$route.params.user_id}",` +
                `"term":"${term_where}",` +
                `"course_code":"${course_where}",` +
                `"column":"${column_name}"` +
                "}";

            url = JSON.parse(url);
            fetch(generate_get_url(url))
                .then(res => res.json())
                .then(data => this.parseData(data))
                .catch(err => {
                    this.$emit("error", err.toString());
                });
        },

        /**
         * Function parses data from API call that will be rendered on the
         * spreadsheet and select component.
         */
        parseData: function(data) {
            var pairings = {};
            for (let element of data.DATA) {
                // Create an associative array with keys as course code and values
                // as an array of instructors/tas for that couse.
                if (element.course_code in pairings) {
                    pairings[element.course_code].push({
                        name: element.name,
                        user_id: element.user_id
                    });
                } else {
                    pairings[element.course_code] = [
                        {
                            name: element.name,
                            user_id: element.user_id
                        }
                    ];
                }

                // Pushing list of distinct terms
                if (this.list_terms.indexOf(element.term) < 0) {
                    this.list_terms.push(element.term);
                }
            }

            var user_associations = [];
            var index_pairings = 0;
            for (var key in pairings) {
                // Converting associative arrays to indexed array where first
                // element is key value
                user_associations[index_pairings] = [];
                user_associations[index_pairings].push(key);
                user_associations[index_pairings].push.apply(
                    user_associations[index_pairings],
                    pairings[key]
                );

                // Pushing list of distinct courses
                if (this.list_courses.indexOf(key) < 0) {
                    this.list_courses.push(key);
                }
                index_pairings++;
            }

            this.user_associations = user_associations;
            this.createTable(user_associations);
        },

        /**
         * Function creates spreadsheet component.
         */
        createTable: function(user_associations) {
            var container = document.getElementById("table");

            // Finding longest array
            var longest = -1;
            var index_longest = -1;
            for (var index = 0; index < user_associations.length; index++) {
                if (user_associations[index].length > longest) {
                    longest = user_associations[index].length;
                    index_longest = index;
                }
            }

            //Creating column headers
            var headers = [];
            headers.push("Courses");
            for (var index = 1; index < longest; index++) {
                headers.push(this.column + " " + index);
            }
            this.tableSettings.colHeaders = headers;

            // Creating table data
            var arr_pairings = [];
            var curr_index = 0;
            for (let element of user_associations) {
                arr_pairings[curr_index] = [];
                arr_pairings[curr_index].push(element[0]);
                for (
                    var name_index = 1;
                    name_index < element.length;
                    name_index++
                ) {
                    arr_pairings[curr_index].push(element[name_index].name);
                }
                curr_index++;
            }
            this.tableSettings.data = arr_pairings;

            if (this.table != null) {
                this.table.destroy();
            }
            this.table = new Handsontable(container, this.tableSettings);
        }
    },
    components: {
        HotTable
    }
};
</script>
