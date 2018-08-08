<template>

<v-card>
    <v-card-title>Survey Token(Please Save This)</v-card-title>

    <v-layout row>
        <v-flex xs2>
            <v-subheader>Token: </v-subheader>
        </v-flex>
        <v-flex xs8>
            <v-text-field v-if="token" :value="token.override_token" readonly>
                {{token.override_token}}
            </v-text-field>
        </v-flex>

    </v-layout>

    <v-card-actions>
        <v-btn color="error" flat @click="$emit('cancel')">
            Cancel
        </v-btn>
    </v-card-actions>
</v-card>

</template>

<script>
export default {
    name: "Token",
    props: {
        token_data: Object
    },
    data: function() {
        return {
            token: null
        };
    },
    watch: {
        token_data: function() {
            this.token = this.token_data;
        }
    }
};
</script>
