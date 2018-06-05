<style>

.sidenav {
    height: 100%;
    width: 250px;
    position: fixed;
    z-index: 1;
    top: 0;
    left: 0;
    background-color: #2B2B2B;
    overflow-x: hidden;
    padding-top: 25px;
}

.sidenav a {
    padding: 10px;
    padding-top: 15px;
    padding-bottom: 15px;
    text-decoration: none;
    font-size: 18px;
    color: #818181;
    display: block;
}

.sidenav a:hover {
    color: #f1f1f1;
}

#sidenav-header{
    color: white;
}

</style>

<template>

<div id="sidenav">
    <div class="sidenav">
        <a id="sidenav-header" href = "#">TA Feedback System</a>
        <hr/>
        <a href = "#/courses">Courses and Sections</a>
        <a href = "#/surveys">Surveys</a>
        <a href = "#/results">Results</a>
        <a href = "#/settings">Settings</a>
    </div>
</div>

</template>

<script>
export default {
    name: "settings",
    data:function(){
        return{

        }
    }
};
</script>
