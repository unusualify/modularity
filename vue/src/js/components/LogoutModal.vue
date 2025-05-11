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
      <v-card class="text-center">
        <v-card-title class="">
          <ue-title type="h5" weight="medium" justify="center" color="primary" padding="t-3">
            {{ $t('Log Out') }}
          </ue-title>
        </v-card-title>
        <v-card-text>
          {{ $t('Are you sure you want to logout?') }}
        </v-card-text>

        <!-- <v-divider></v-divider> -->

        <v-card-actions class="justify-center">
          <!-- <v-spacer></v-spacer> -->
          <v-btn class="" variant="outlined" @click="dialog=false" density="compact">
            {{ $t('Cancel') }}
          </v-btn>
          <v-form method="post" action="/logout">
            <input type="hidden" name="_token" :value="$csrf()">
            <v-btn variant="elevated" type="submit" density="compact">
              {{ $t('Log Out') }}
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
