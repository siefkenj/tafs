<style>
</style>

<template>
        <v-card>
            <v-card-title class="headline mb-0 orange--text">Edit Name</v-card-title>
            <p>Please edit your name to allow your students to identify you.</p>
            <v-layout>
                <v-flex>
                    <v-text-field
                        label="New Name"
                        v-model="new_name">
                    </v-text-field>
                </v-flex>
            </v-layout>

            <v-card-actions>
                <v-btn
                  flat
                  @click="$emit('cancel')"
                  class="grey--text"
                >
                  Cancel
                </v-btn>

              <v-spacer></v-spacer>

              <v-btn
                @click="$emit('saveName',new_name)"
                color="orange white--text"
                >
                Save
              </v-btn>

            </v-card-actions>
        </v-card>
</template>

<script>
export default {
    name: "NameModal",
    props: {
        user_package: Object
    },
    watch: {
        user_package: function() {
            this.new_name = this.user_package.name;
        }
    },
    data() {
        return {
            new_name: null
        };
    }
};
</script>
