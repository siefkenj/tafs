<style media="screen">
.custom-loader {
    animation: loader 1s infinite;
    display: flex;
}

@keyframes loader {
    from {
        transform: rotate(0);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
<template>
<v-expansion-panel v-if="data">
    <v-expansion-panel-content>
        <div slot="header">
            <h3 style="float:left;">{{data.name}}</h3>
            <div v-if="data.num_responses !== 0 && is_instance">
              {{data.name}} Total Responses: {{data.num_responses}}
              <div class="numerical-question">
                <div v-for="question in data.questions" class="numerical-average">
                  Question:{{question.position}}
                  <div class="average">
                    <div class="percentage-bar" v-bind:style="{width: data.numerical_average/5*100 + '%'}">
                      {{question.numerical_average}}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <v-btn v-if="!is_instance" :loading="loading" :disabled="loading" color="green accent-3" class="white--text" @click.native="loader = 'loading'">
              Launch
            </v-btn>
            <v-btn :loading="loading2" :disabled="loading2" color="blue-grey" class="white--text" @click.native="loader = 'loading2'">
              Clone
            </v-btn>
            <v-btn v-if="!is_instance" :loading="loading3" :disabled="loading3" color="blue darken-2" class="white--text" @click.native="loader = 'loading3'">
              Edit
              <span slot="loader" class="custom-loader">
                <v-icon light>cached</v-icon>
              </span>
            </v-btn>
            <v-btn v-if="is_instance" :loading="loading4" :disabled="loading4" color="deep-purple lighten-2" class="white--text" @click.native="loader = 'loading4'">
              Allow Instructor Access
            </v-btn>
        </div>
        <response :survey="data"> </response>
    </v-expansion-panel-content>
</v-expansion-panel>

</template>

<script>
import Response from "./response.vue";

export default {
    name: "SurveyWrapperButtons",
    props: ["data", "is_instance"],
    data: function() {
        return {
            loader: null,
            loading: false,
            loading2: false,
            loading3: false,
            loading4: false
        };
    },
    watch: {
        loader() {
            const l = this.loader;
            this[l] = !this[l];

            setTimeout(() => (this[l] = false), 3000);

            this.loader = null;
        }
    },
    components: {
        Response
    }
};
</script>
