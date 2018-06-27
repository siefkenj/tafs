<template>

<div id="settings">
    <h1>User Information</h1>
    <h3>{{this.name}}</h3>
    <h3>{{this.photo}}</h3>
    <div v-if="display">
        <input v-model="temp_name" placeholder="New Name">
        <input v-model="temp_photo" placeholder="New Photo">
        <button v-on:click="saveData(true)"><h4>Edit Information</h4></button>
        <button v-on:click="saveData(false)"><h4>Cancel</h4></button>
    </div>

    <div v-else>
        <button v-on:click="display=true"><h4>Edit Information</h4></button>
    </div>
</div>

</template>

<script>
import generate_query_string from "./components/generate_query_string";
export default {
    data: function() {
        return {
            name: null,
            photo: null,
            temp_name: null,
            temp_photo: null,
            display: false
        };
    },
    created: function() {
        this.getData();
    },
    methods: {
        /**
         * Getting user information for user.
         */
        getData: function() {
            let url = {
                what: "user_info",
                user_id: this.$route.params.user_id
            };
            fetch("get_info.php?" + generate_query_string(url))
                .then(res => res.json())
                .then(data => {
                    this.name = data.DATA[0].name;
                    this.photo = data.DATA[0].photo;
                })
                .catch(err => {
                    this.$emit("error", err.toString());
                });
        },
        /**
         * If save is true, values entered within fields are saved as new
         * name and photo if not null.
         *
         * @param save Set true if data within temporary fields should be saved
         */
        saveData: function(save) {
            if (save) {
                this.name = this.temp_name != null && this.temp_name;
                this.photo = this.temp_photo != null && this.temp_photo;
            }
            this.temp_name = null;
            this.temp_photo = null;
            this.display = false;
        }
    }
};
</script>
