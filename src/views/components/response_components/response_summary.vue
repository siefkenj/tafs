<template>
        <v-container fluid class="pa-0">
<v-layout row xs12>
        <v-flex v-for="(dat, index) in summary" :key="index" xs2>
                <v-card flat tile class="mx-1">
                        <div class="caption" v-bind:title="dat.name" style="text-overflow: ellipsis; overflow: hidden;">
                                {{dat.name}}
                        </div>
                        <div class="caption">
                                {{dat.rounded_mean}}
                        </div>
                        <v-progress-linear v-bind:value="dat.mean/5*100" height="2" class="my-0">fds</v-progress-linear>
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
        /* provide a summary of a given question */
        summaryFromQuestion: function(question) {
            let content = JSON.parse(question.content);
            let name = content.name;
            let mean = null;
            if (question.answer_type === "rating") {
                let sum = 0;
                for (let a of question.responses) {
                    sum += +a;
                }
                mean = sum / question.responses.length;
            }
            return { name, mean, rounded_mean: Math.round(mean * 10) / 10 };
        }
    },
    computed: {
        summary: function() {
            return this.responses.map(this.summaryFromQuestion);
        }
    },
    watch: {
        view_mode: function() {
            this.vmode = this.view_mode;
        }
    }
};
</script>
