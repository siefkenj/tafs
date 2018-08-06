<style>
.response-summary table.v-table thead tr,
.response-summary table.v-table td {
    height: unset;
}
.response-summary table.v-table thead th {
    padding: unset;
}
.response-summary table.v-table {
    border-collapse: unset;
    background-color: unset;
}
.response-summary .ellipsize {
    overflow: hidden;
    text-overflow: ellipsis;
}
.response-summary .v-card {
    background-color: unset;
}
</style>

<template>

<v-container fluid class="pa-0 response-summary">
<v-layout v-if="compact" row xs12>
        <v-flex v-for="(dat, index) in summary" :key="index" xs2>
                <v-card flat tile class="mx-1" v-bind:title="dat.title">
                        <div class="caption ellipsize">
                                {{dat.name}}
                        </div>
                        <div class="caption">
                                {{dat.rounded_mean}}
                        </div>
                        <v-progress-linear v-bind:value="dat.mean/5*100" height="2" class="my-0">fds</v-progress-linear>
                </v-card>
        </v-flex>
</v-layout>
<v-layout v-if="!compact" column xs12>
        <v-flex v-for="(dat, index) in summary" :key="index" xs2>
                <v-divider v-if="index !== 0" class="my-2"></v-divider>
                <v-card flat tile class="mx-1" v-bind:title="dat.title">
                        <div class="body-2 ellipsize blue--text text--darken-3">
                                {{dat.title}}
                        </div>
                        <div class="body-1">
                                <v-data-table
                                    :headers="dat.headers"
                                    :items="dat.rows"
                                    hide-actions
                                    class="text-xs-center"
                                        >
                                        <template slot="items" slot-scope="props">
                                                <!-- conditionally format the numerical items to be light if they're zero -->
                                                <td :class="[{'grey--text': props.item[0] < 1}, 'text--lighten-2']"> {{ props.item[0] }} </td>
                                                <td :class="[{'grey--text': props.item[1] < 1}, 'text--lighten-2']"> {{ props.item[1] }} </td>
                                                <td :class="[{'grey--text': props.item[2] < 1}, 'text--lighten-2']"> {{ props.item[2] }} </td>
                                                <td :class="[{'grey--text': props.item[3] < 1}, 'text--lighten-2']"> {{ props.item[3] }} </td>
                                                <td :class="[{'grey--text': props.item[4] < 1}, 'text--lighten-2']"> {{ props.item[4] }} </td>
                                                <td class="blue--text text--darken-2"> {{ props.item[5] }} </td>
                                                <td class="grey--text"> {{ props.item[6] }} </td>
                                        </template>
                                </v-data-table>
                        </div>
                </v-card>
        </v-flex>
</v-layout>
</v-container>

</template>

<script>
export default {
    name: "ResponseSummary",
    props: {
        responses: Array,
        compact: { type: Boolean, default: true }
    },
    data: function() {
        return {};
    },
    methods: {
        /* Count how many responses fall into each bin,
            useful for building a histogram. */
        binResponses: function(responses) {
            let ret = [0, 0, 0, 0, 0, 0];
            for (let r of responses) {
                ret[r] += 1;
            }
            // The lowest response is a 1, but arrays are 0-indexed
            // so get rid of the first bin.
            ret.shift();
            return ret;
        },
        /* provide a summary of a given question */
        summaryFromQuestion: function(question) {
            let content = JSON.parse(question.content);
            let mean, headers, rows, binned, rounded_mean;
            const FORMAT = { sortable: false, align: "center" };
            if (question.answer_type === "rating") {
                // compute statistics
                let sum = 0,
                    len = (question.responses || []).length;
                for (let a of question.responses) {
                    sum += +a;
                }
                mean = sum / len;
                rounded_mean = Math.round(mean * 10) / 10;

                // prepare table information
                binned = this.binResponses(question.responses);
                headers = content.rateValues.map((x, i) =>
                    Object.assign({ text: `${x.text} (${i + 1})` }, FORMAT)
                );
                headers.push(
                    Object.assign(
                        { text: "Mean", class: "blue--text text--darken-2" },
                        FORMAT
                    )
                );
                headers.push(Object.assign({ text: "Reponses" }, FORMAT));
                rows = [[...binned, rounded_mean, len]];
            }
            let ret = {
                name: content.name,
                title: content.title,
                responses: question.responses,
                binned_responses: binned,
                headers,
                rows,
                mean,
                rounded_mean
            };
            return ret;
        }
    },
    computed: {
        summary: function() {
            return this.responses.map(this.summaryFromQuestion);
        }
    }
};
</script>
