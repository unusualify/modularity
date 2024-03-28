<template>
  <ue-modal
    v-model="dialog"

    v-bind="$bindAttributes()"
    width-type="md"
    >
    <template v-slot:activator="{ props }">
      <slot name="activator" :props="props">
        <v-btn
          class="w-66 m-auto"
          color="red lighten-2"
          dark
          v-bind="props"
          >
          {{ $t('authentication.logout') }}
        </v-btn>
      </slot>
    </template>

    <template v-slot:body="props">
      <v-card>
        <!-- <v-card-title class="text-h5 grey lighten-2">
          Logout Form
        </v-card-title> -->
        <v-card-text class="pt-2">
          {{ $t('Are you sure logout?') }}
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" text @click="dialog=false">
            {{ $t('No') }}
          </v-btn>
          <v-form method="post" action="/logout">
            <input type="hidden" name="_token" :value="csrf">
            <v-btn color="error" type="submit">
              {{ $t('Yes') }}
            </v-btn>
          </v-form>
        </v-card-actions>
      </v-card>
    </template>

    </ue-modal>
</template>

<script>
export default {
  emits: ['update:modelValue'],
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
