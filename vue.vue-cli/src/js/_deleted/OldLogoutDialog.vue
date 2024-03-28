<template>
    <v-dialog
        v-model="dialog"

        >
        <template v-slot:activator="{ props }">
            <slot
              name="activator"
              :props="{
                  ...props
              }"
            >
              <v-btn
                  class="w-66 m-auto"
                  color="red lighten-2"
                  dark
                  v-bind="props"
                  >
                  Logout
              </v-btn>
            </slot>
        </template>

        <v-card>
            <v-card-title class="text-h5 grey lighten-2">
                Logout Form
            </v-card-title>

            <v-card-text class="pt-2">
                Are you sure logout?
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    color="secondary"
                    text
                    @click="dialog = false"
                >
                    Cancel
                </v-btn>
                <v-form method="post" action="/logout">

                    <input type="hidden" name="_token" :value="csrf">

                    <v-btn
                        color="error"
                        type="submit"
                    >
                        Yes
                    </v-btn>
                </v-form>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
  props: {
    csrf: {
      type: String
    }
  },
  data () {
    return {
      dialog: false
    }
  }
}
</script>

<style>

</style>
