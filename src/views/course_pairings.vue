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
            <option v-for="course in this.courses_list">{{course}}</option>
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
import generate_query_string from "./components/generate_query_string";

export default {
    data: function() {
        return {
            table: null,
            specify_term: "All Terms",
            column: "Instructor",
            root: "table-format",
            specify_course: "All Courses",
            user_associations: [],
            courses_list: [],
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

            let url = {
                what: "course_pairings",
                user_id: this.$route.params.user_id,
                term: term_where,
                course_code: course_where,
                column: column_name
            };
            fetch("get_info.php?" + generate_query_string(url))
                .then(res => res.json())
                .then(data => this.parseData(data))
                .catch(err => {
                    this.$emit("error", err.toString());
                });
        },

        /**
         * Function parses data from API call that will be rendered on the
         * spreadsheet and options for select component.
         */
        parseData: function(data) {
            var pairings = {};
            for (let element of data.DATA) {
                // Create an associative array with keys as course code and values
                // as an array of instructors/tas for that couse.
                pairings[element.course_code] =
                    pairings[element.course_code] || [];
                pairings[element.course_code].push({
                    name: element.name,
                    user_id: element.user_id
                });

                // Pushing list of distinct terms
                if (this.list_terms.indexOf(element.term) < 0) {
                    this.list_terms.push(element.term);
                }

                if (this.courses_list.indexOf(element.course_code) < 0) {
                    this.courses_list.push(element.course_code);
                }
            }
            // Converting associative array to array with key as first
            // element and value as remaining index.
            //
            // Input: {["CSC100"]=>[{name:"Name", user_id:"User_id"},
            // {name:"Name1", user_id:"User_id1"}]}
            //
            // Output: [["CSC100", {name:"Name", user_id:"User_id"},
            // {name:"Name1", user_id:"User_id1"}]]
            var user_associations = Object.keys(pairings).map(course => {
                return [course].concat(pairings[course]);
            });
            this.user_associations = user_associations;
            this.createTable();
        },

        /**
         * Function creates spreadsheet component.
         */
        createTable: function() {
            var container = document.getElementById("table");

            // Finding longest array
            let longest = Math.max(
                ...this.user_associations.map(element => element.length)
            );

            // Creating column headers
            var headers = ["Courses"];
            for (var index = 1; index < longest; index++) {
                headers.push(this.column + " " + index);
            }
            this.tableSettings.colHeaders = headers;

            // Creating array with course code and instructor/ta name
            // Input: [["CSC100", {name:"Name", user_id:"User_id"},
            // {name:"Name1", user_id:"User_id1"}]]
            //
            // Output: [["CSC100", "Name", "Name1"]]
            let arr_pairings = this.user_associations.map(elm => {
                let [course_code, ...names] = elm;
                names = names.map(x => x.name);
                return [course_code, ...names];
            });
            this.tableSettings.data = arr_pairings;

            if (this.table != null) {
                this.table.destroy();
            }

            if (container != null) {
                this.table = new Handsontable(container, this.tableSettings);
            }
        }
    },
    components: {
        HotTable
    }
};
</script>
