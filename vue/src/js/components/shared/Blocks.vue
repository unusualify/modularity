<script setup>
import { computed } from 'vue'

const AUTO_HEIGHT_CLASS = 'ue-blocks__col--auto'

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

const isAutoHeight = (block) => {
  const classes = block?.attributes?.class
  const tokens = Array.isArray(classes) ? classes : String(classes ?? '').split(/\s+/)

  return tokens.includes(AUTO_HEIGHT_CLASS)
}

// Each line of a wrapping row shares leftover height, so auto-height blocks get their own non-growing row.
// Tagless blocks (e.g. role-filtered dashboard configs) render nothing and must not form an empty growing row.
const segments = computed(() => {
  return Object.entries(blocks.value ?? {}).reduce((acc, [key, block]) => {
    if (!block?.tag) {
      return acc
    }

    const autoHeight = isAutoHeight(block)
    const last = acc[acc.length - 1]

    if (last && last.autoHeight === autoHeight) {
      last.blocks.push({ key, block })
    } else {
      acc.push({ autoHeight, blocks: [{ key, block }] })
    }

    return acc
  }, [])
})

const hasAutoHeightBlocks = computed(() => segments.value.some(segment => segment.autoHeight))
</script>


<template>
  <div
    v-if="hasAutoHeightBlocks"
    class="ue-blocks-stack"
  >
    <v-row
      v-for="(segment, s) in segments"
      :key="`segment-${s}`"
      :class="['ue-blocks', { 'ue-blocks--auto': segment.autoHeight }]"
    >
      <ue-recursive-stuff
        v-for="{ key, block } in segment.blocks"
        :key="`tag-0-${key}`"
        :configuration="block"
      />
    </v-row>
  </div>
  <v-row
    v-else
    class="ue-blocks flex-grow-1 min-height-0"
  >
    <ue-recursive-stuff
      v-for="(block, i) in blocks"
      :key="`tag-0-${i}`"
      :configuration="block"
    />
  </v-row>
</template>

<style lang="sass">
.ue-blocks-stack
  display: flex
  flex-direction: column
  flex: 1 1 auto
  min-height: 0

.ue-blocks
  flex: 1 1 auto
  min-height: 0
  align-content: stretch

  &--auto
    flex: 0 0 auto

  > .v-col
    display: flex
    flex-direction: column
    min-height: 0

    &:has(.ue-metric-cards)
      overflow: visible
</style>
