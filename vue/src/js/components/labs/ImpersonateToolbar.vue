<template>
  <v-app-bar title='Impersonate Control' :elevation="2">
      <v-btn v-if="impersonated" color="red" :href="stopRoute">
          Stop Impersonating
      </v-btn>
      <!-- <div v-else>

      </div> -->

      <v-select v-if="!impersonated" class="mt-5"
        variant="outlined" density="compact"
        item-title="name" item-value="id"
        v-model="selected" :items="users"
        clearable
        >
      </v-select>
      <v-btn v-if="!impersonated" :href="impersonateRoute" :disabled="!selected">Impersonate</v-btn>

  </v-app-bar>
</template>

<script>
export default {
  props: {
    users: {
      type: Array,
      default () {
        return []
      }
    },
    impersonated: {
      type: Boolean,
      default: false
    },
    route: {
      type: String,
      default: '/users/impersonate/:id'
    },
    stopRoute: {
      type: String,
      default: '/users/impersonate/stop'
    }
  },
  data () {
    return {
      selected: null
    }
  },
  computed: {
    impersonateRoute () {
      return this.route.replace(':id', this.selected)
    }
  },
  created () {
    __log(this.users)
  }
}
</script>

<style lang="scss" scoped>

</style>
