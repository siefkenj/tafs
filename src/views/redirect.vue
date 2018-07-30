<template>

<div id="redirect">
    <h1>Redirecting</h1>
</div>

</template>

<script>
export default {
    name: "redirect",
    created: function() {
        var user_id = "butler84";
        this.$router.replace({ path: `/user_id/${user_id}/dashboard` });
    }
};
</script>
