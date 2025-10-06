<script setup>
import { computed } from 'vue'

const props = defineProps({
  items: {
    type: Object,
    default () {
      return {}
    }
  }
})

const blocks = computed(() => {
  return props.items
})
</script>


<template>
  <v-row class="h-100">
    <ue-recursive-stuff
      v-for="(block, i) in blocks"
      :key="`tag-0-${i}`"
      :configuration="block"
    />
  </v-row>
</template>

<style lang="sass" scoped>

</style>
