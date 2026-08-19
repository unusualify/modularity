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
  <v-row class="ue-blocks flex-grow-1 min-height-0">
    <ue-recursive-stuff
      v-for="(block, i) in blocks"
      :key="`tag-0-${i}`"
      :configuration="block"
    />
  </v-row>
</template>

<style lang="sass">
.ue-blocks
  flex: 1 1 auto
  min-height: 0
  align-content: stretch

  > .v-col
    display: flex
    flex-direction: column
    min-height: 0
    flex: 1 1 auto
    overflow: auto
</style>
