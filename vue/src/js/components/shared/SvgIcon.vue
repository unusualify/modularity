<template>
  <span
    v-svg
    class="ue-svg-icon"
    :symbol="symbol"
    :style="wrapperStyle"
  ></span>
</template>

<script>
import { computed } from 'vue'

export default {
  props: {
    symbol: {
      type: String,
      required: true
    },
    height: {
      type: String
    },
    width: {
      type: String
    }
  },
  setup(props) {
    const symbol = computed(() => {
      if (props.symbol) {
        return props.symbol
      }
    })
    const wrapperStyle = computed(() => {
      if (!props.height && !props.width) {
        return undefined
      }
      const style = {
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center'
      }
      if (props.width) {
        style.width = `${props.width}px`
      }
      if (props.height) {
        style.height = `${props.height}px`
      }
      return style
    })
    return {
      symbol,
      wrapperStyle
    }
  }
}
</script>

<style scoped>
.ue-svg-icon :deep(svg) {
  display: block;
  width: 100%;
  height: 100%;
  max-width: 100%;
  max-height: 100%;
}
</style>
