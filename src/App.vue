<style>

@import './css/app.css';

</style>

<template>

<div id="app">
    <div>
        <sidenav></sidenav>
        <h1>app</h1>
        <router-view></router-view>
    </div>
</div>

</template>

<script>
import Sidenav from './views/components/navbar.vue'
export default {
    name: "app",
    components:{
        Sidenav
    }
};

</script>
