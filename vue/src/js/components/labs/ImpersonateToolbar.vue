<script setup>
import { watch, ref, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  active: {
    type: Boolean,
    default: false
  },
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
})
const emit = defineEmits(['update:modelValue'])

const selected = ref(null)

const show = computed({
  get () {
    return props.modelValue
  },
  set (value) {
    emit('update:modelValue', value)
  }
})

watch(selected, function (newValue, oldValue) {
  __log(newValue, oldValue)
})
const impersonateRoute = computed(() => {
  return props.route.replace(':id', selected.value)
})
</script>
<template>
    <!-- <v-icon v-if="active && !show" icon="$close" /> -->
  <v-navigation-drawer
      v-model="show"
      location="right"
    >
    <div class="pa-3">
      <v-btn v-if="impersonated" color="red" :href="stopRoute">
          Stop Impersonating
      </v-btn>
      <!-- <div v-else>

      </div> -->

      <v-select
        v-if="!impersonated" class="mt-5"
        v-model="selected" :items="users"
        variant="outlined" density="compact"
        item-title="name" item-value="id"
        clearable
        >
      </v-select>
      <v-btn v-if="!impersonated" :href="impersonateRoute" :disabled="!selected">Impersonate</v-btn>
    </div>
  </v-navigation-drawer>
</template>

<script>
export default {
  data () {
    return {
      // selected: null,
      // show: true
    }
  },
  // computed: {
  //   impersonateRoute () {
  //     return this.route.replace(':id', this.selected)
  //   }
  // },
  created () {
    __log(this.users)
  }
}
</script>

<style lang="scss" scoped>

</style>
