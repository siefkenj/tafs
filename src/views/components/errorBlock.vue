<style>

#error-block {
    background-color: #F23A3A;
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
    <p>Error: {{error_message}}</p>
</div>

</template>

<script>

export default {
    name: "edit-courses",
    props: ["error_message"]
};

</script>
