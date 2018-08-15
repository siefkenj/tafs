<style>
@import "../../node_modules/handsontable/dist/handsontable.full.css";
#table-format {
    width: 90%;
    height: 600px;
    margin: auto;
    overflow: hidden;
    border: 2px solid #09b4ed;
}

#table-button-row {
    margin-bottom: 50px;
}

.over-column {
    z-index: 300;
}
</style>

<template>

<div>
    <h1>Course and Section Data</h1>
    <div>
        <v-container>
            <v-layout align-center justify-center row fill-height>
                <v-flex xs12 sm5 md3>
                    <v-btn color="blue-grey" class="white--text">Import Data</v-btn>
                </v-flex>

                <v-flex xs12 sm5 md3>
                    <v-btn color="blue-grey" class="white--text">Download Sample Data</v-btn>
                </v-flex>
            </v-layout>
        </v-container>

        <v-container>
            <v-layout align-center justify-center row fill-height>
                <v-flex xs12 sm5 md3>
                    <v-select
                        outline
                        :items="courses_list"
                        label="Select Course"
                        v-model="specify_course"
                        class="over-column">
                    </v-select>
                </v-flex>

                <v-flex xs12 sm5 md3>
                    <v-select
                        outline
                        :items="list_terms"
                        label="Select Term"
                        v-model="specify_term"
                        class="over-column">
                    </v-select>
                </v-flex>

                <v-flex xs12 sm5 md3>
                    <v-select
                        outline
                        :items="['Instructor', 'TA']"
                        label="Group By"
                        v-model="column"
                        class="over-column">
                    </v-select>
                </v-flex>
            </v-layout>
        </v-container>

        <div id="table-button-row">
            <div v-if="tableSettings.readOnly">
                <v-btn color="blue-grey" @click="edit" class="white--text">Edit</v-btn>
            </div>
            <div v-else>
                <v-container>
                    <v-layout align-center justify-center row fill-height>
                        <v-flex xs12 sm5 md3>
                            <v-btn color="error" @click="edit" class="white--text">Cancel</v-btn>
                        </v-flex>

                        <v-flex xs12 sm5 md3>
                            <v-btn color="blue-grey"@click="saveData" class="white--text">Save</v-btn>
                        </v-flex>
                    </v-layout>
                </v-container>
            </div>
        </div>
    </div>

    <div id="table"></div>
</div>

</template>

<script>
import HotTable from "@handsontable/vue";
import Handsontable from "handsontable";
import generate_query_string from "./components/generate_query_string";
export default {
    name: "CourseParings",
    data: function() {
        return {
            table: null,
            specify_term: "All Terms",
            column: "Instructor",
            root: "table-format",
            specify_course: "All Courses",
            user_associations: [],
            courses_list: [],
            temp_data: [],
            list_terms: [],
            changes: [],
            tableSettings: {
                data: [],
                stretchH: "all",
                colHeaders: [],
                afterChange: this.afterChangeVue,
                readOnly: true
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
                user_id: this.$route.query.user_id,
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
                    user_id: element.user_id,
                    section_id: element.section_id
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
            // Input: {["CSC100"]=>[{name:"Name", user_id:"User_id",section_id:1,
            // {name:"Name1", user_id:"User_id1", section_id:2}]}
            //
            // Output: [["CSC100", {name:"Name", user_id:"User_id", section_id:1},
            // {name:"Name1", user_id:"User_id1", section_id:2}]]
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
            // Output if table in readOnly mode: [["CSC100", "Name (User_id)", "Name1(User_id1)"]]
            // Output if table in edit mode: [["CSC100", "User_id", "User_id1"]]
            let arr_pairings;
            if (this.tableSettings.readOnly) {
                arr_pairings = this.user_associations.map(elm => {
                    let [course_code, ...names] = elm;
                    names = names.map(x => `${x.name} (${x.user_id})`);
                    return [course_code, ...names];
                });
            } else {
                arr_pairings = this.user_associations.map(elm => {
                    let [course_code, ...names] = elm;
                    names = names.map(x => x.user_id);
                    return [course_code, ...names];
                });
            }

            // If a row shorter than the longest row, add null elements to
            // increase length to be the same length as the longest row.
            // This is required to display all columns of the table.
            for (let index = 0; index < arr_pairings.length; index++) {
                if (arr_pairings[index].length < longest) {
                    arr_pairings[index].push(
                        ...new Array(longest - arr_pairings[index].length)
                    );
                }
            }

            this.tableSettings.data = arr_pairings;
            if (this.table != null) {
                this.table.destroy();
            }
            if (container != null) {
                this.table = new Handsontable(container, this.tableSettings);
            }
        },

        /**
         * Function called by table when cell is changed. Changes are stored in
         * changes state.
         *
         * @param changes Array containing changed cell information
         * @param source Type of change
         */
        afterChangeVue: function(changes, source) {
            if (source === "edit") {
                var row, column, oldValue, newValue;
                [row, column, oldValue, newValue] = changes[0];
                this.changes.push(changes[0]);
            }
        },

        /**
         * Function toggles read only mode for table
         */
        edit: function() {
            this.tableSettings.readOnly = !this.tableSettings.readOnly;
            this.createTable();
        },
        /**
         * POST new table changes to database
         */
        saveData: function() {
            this.changes.map(elm => {
                let [row, column, oldValue, newValue] = elm;

                this.postData(
                    this.user_associations[row][0],
                    this.user_associations[row][column].section_id,
                    oldValue,
                    "delete"
                );
                if (newValue != "") {
                    this.postData(
                        this.user_associations[row][0],
                        this.user_associations[row][column].section_id,
                        newValue,
                        "add_or_update"
                    );
                }
            });
            this.edit();
            this.getData();
        },

        /**
         * Post new user association data to API
         *
         * @param course_code Course code of association
         * @param section_id Section ID of association
         * @param user_id user_id of association
         * @param action action to perform according to API documentation
         */
        postData: function(course_code, section_id, user_id, action) {
            let body = JSON.stringify({
                association_list: [
                    {
                        course: {
                            course_code: course_code
                        },
                        section: {
                            section_id: section_id
                        },
                        user_id: user_id
                    }
                ]
            });

            let url = {
                what: "course_pairings",
                user_id: this.$route.query.user_id,
                mode: "user_associations",
                action: action
            };

            fetch("post_info.php?" + generate_query_string(url), {
                method: "POST",
                body: body
            }).catch(err => this.$emit("error", err.toString()));
        }
    },
    components: {
        HotTable
    }
};
</script>
