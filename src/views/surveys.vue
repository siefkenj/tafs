<template>

<div id="question-select">
    <router-view @error="sendError"></router-view>
</div>

</template>

<script>
export default {
    name: "question-select",
    methods: {
        sendError: function(value) {
            this.$emit("error", value);
        }
    }
};
</script>
