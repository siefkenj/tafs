

<template>

    <div v-if="!display_b">
        <!-- Change Question choice from &ensp; -->
        <!-- The original selected questions -->
        <!-- <p>{{question.content.title}}</p>
        &ensp;to &ensp; -->
        <!-- The back up questions to choose from -->
        <v-select
        v-model="selected_question"
        :items="backup_questions"
        item-text="title"
        return-object
        single-line>
        </v-select>
        <!-- <button type="submit" v-on:click="replace()">
            Change</button>
        &ensp; -->
        <!-- <button type="submit" v-on:click="cancel">Cancel</button> -->
        <!-- </br> -->
    </div>
    <div v-if="display_b">
        "try it!"
        <v-list-tile-title>"try it again!"</v-list-tile-title>
        <v-list-tile-sub-title class="text--primary">{{ question.content.title }}</v-list-tile-sub-title>
        <v-list-tile-sub-title>{{ question.content.name }}</v-list-tile-sub-title>
    </div>

</template>

<script>
export default {
    name: "SelectionBoxPanel",
    props: ["display_box", "question", "backup_questions"],
    methods: {


    },
    data: function () {
        return {
            display_b: this.display_box,
            selected_question: null
        }
    },
    watch: {
        display_box: function () {
            this.display_b = this.display_box;
        }
    }
};
</script>
