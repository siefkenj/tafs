<style>
#error-block {
    background-color: #f23a3a;
    color: white;
    text-align: center;
    border-radius: 10px;
    padding-top: 15px;
    padding-bottom: 15px;
    margin-top: 5px;
}
</style>

<template>

<div id="error-block">
    <p>Error: {{formatted_error}}</p>
</div>

</template>

<script>
export default {
    name: "ErrorBlock",
    props: ["error_message"],
    computed: {
        formatted_error: function() {
            if (this.error_message.indexOf("CORS") > -1) {
                return "Error getting data. Please refresh the page.";
            }
            if (this.error_message.indexOf("Failed to fetch") > -1) {
                return "Error getting data. Please refresh the page.";
            }
            return this.error_message;
        }
    }
};
</script>
