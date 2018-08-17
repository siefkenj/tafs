<style>
</style>

<template>
<div>
    <v-toolbar dark color="primary">
        <v-toolbar-title class="white--text">{{this.name}} ({{this.user_id}})</v-toolbar-title>

        <v-spacer></v-spacer>

        <v-toolbar-items>
            <v-btn flat @click="$emit('editName')">Edit Name</v-btn>
        </v-toolbar-items>
    </v-toolbar>
</div>
</template>

<script>
export default {
    props: {
        user_id: String,
        name: String
    }
};
</script>
