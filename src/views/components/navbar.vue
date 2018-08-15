<style>
.sidenav {
    height: 100%;
    width: 250px;
    position: fixed;
    z-index: 1;
    top: 0;
    left: 0;
    background-color: #2b2b2b;
    overflow-x: hidden;
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

.nav-element {
    background: none;
    border: none;
    font-size: 20px;
    color: white;
    margin: auto;
    margin-top: 30px;
    margin-bottom: 30px;
    display: block;
}

button:hover {
    color: black;
}
</style>

<template>

<div id="nav">
    <div class="sidenav">
        <button class="nav-element" v-on:click="change_route(`dashboard`)">TA Feedback System</button>
        <hr/>
        <button class="nav-element" v-on:click="change_route(`courses`)">Courses and Sections</button>
        <button class="nav-element" v-on:click="change_route(`surveys`)">Surveys</button>
    </div>
</div>

</template>

<script>
export default {
    name: "NavBar",
    methods: {
        change_route(component) {
            this.$router.push({
                path: component,
                query: {
                    user_id: this.$route.query.user_id,
                    term: this.$route.query.term
                }
            });
        }
    }
};
</script>
