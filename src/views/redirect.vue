<template>

<div id="redirect">
    <h1>Redirecting</h1>
</div>

</template>

<script>
import generate_query_string from "./components/generate_query_string";
export default {
    name: "Redirect",
    created: function() {
        this.getData();
    },
    methods: {
        getData: function() {
            let url = {
                what: "get_auth_info"
            };
            fetch("get_info.php?" + generate_query_string(url))
                .then(res => res.json())
                .then(data => this.setData(data))
                .catch(err => {
                    this.$emit("error", err.toString());
                });
        },
        setData: function(data) {
            var user_id = "woods13";
            var term = 201809;
            if (data.DATA[0].auth.utorid != null) {
                user_id = data.DATA[0].auth.utorid;
            }
            this.$router.replace({
                path: "surveys",
                query: { user_id: user_id, term: term }
            });
        }
    }
};
</script>
