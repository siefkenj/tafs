<template>

<v-card>
    <v-card-title>Survey Token (Please Save This)</v-card-title>

    <v-layout row>
        <v-flex xs2>
            <v-subheader>Token: </v-subheader>
        </v-flex>
        <v-flex xs8>
            <v-text-field v-if="token" :value="token" readonly>
                {{token}}
            </v-text-field>
        </v-flex>

    </v-layout>

    <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="orange white--text" @click="$emit('ok')">
            OK
        </v-btn>
    </v-card-actions>
</v-card>

</template>

<script>
export default {
    name: "TokenDisplay",
    props: {
        token: String
    }
};
</script>
