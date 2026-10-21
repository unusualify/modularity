<script setup>
  import { watch, ref, computed } from 'vue'
  import axios from 'axios'

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
    },
    fetchEndpoint: {
      type: String,
      default: null
    },
    recent: {
      type: Array,
      default () {
        return []
      }
    },
    itemTitle: {
      type: String,
      default: 'name'
    },
    itemValue: {
      type: String,
      default: 'id'
    },
    density: {
      type: String,
      default: 'comfortable'
    },
    variant: {
      type: String,
      default: 'outlined'
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

  const submitting = ref(false)

  const impersonateRoute = computed(() => {
    return props.route.replace(':id', selected.value)
  })

  const postAndReload = async (url) => {
    if (submitting.value) return

    submitting.value = true
    try {
      await axios.post(url, {}, { headers: { Accept: 'application/json' } })
      window.location.reload()
    } catch (error) {
      submitting.value = false
      selected.value = null
      console.error('[ImpersonateToolbar]', error)
    }
  }

  const stopImpersonating = () => postAndReload(props.stopRoute)

  watch(selected, (newValue) => {
    if (newValue) {
      postAndReload(impersonateRoute.value)
    }
  })
</script>
<template>
  <v-list-item
      v-if="impersonated"
      class="font-weight-bold mt-2"
      color="primary"
      :ripple="false"
      :append="false"
      prepend-icon="mdi-account-off"
      title="Stop Impersonating"

      variant="flat"
      base-color="red"
      :disabled="submitting"

      @click="stopImpersonating"
  />
  <v-input-browser v-else
    v-model="selected"
    :endpoint="fetchEndpoint"
    :variant="variant"
    :density="density"
    :item-title="itemTitle"
    :item-value="itemValue"
    :recent-items="recent"
    >
    <template v-slot:activator="{ props }">
      <v-list-item
        @click="props.onClick"
        class="font-weight-bold"
        :ripple="false"
        :append="false"
        prepend-icon="mdi-account-switch"
        title="Impersonate User"
      />
    </template>
    <template v-slot:item="{ item }">
      <v-list-item :title="`${item.raw.name} (${item.raw.company_name})`" :subtitle="item.raw.email" />
    </template>
  </v-input-browser>

</template>

<style lang="scss" scoped>

</style>
