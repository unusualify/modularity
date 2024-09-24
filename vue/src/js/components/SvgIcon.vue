<template>
  <div :style="style">
    <span v-svg :symbol="symbol"></span>
  </div>
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

    },
    style: {
      type: String,
      default : ""
    }
  },
  setup(props) {
    const symbol = computed(() => {
      if (props.symbol) {
        console.log(props.symbol)
        return props.symbol
      }
    })
    const style = computed(() => {
      if(props.height || props.width){
        return "height:" + props.height + "px;" + "width:" + props.width + "px;"
      }
    })
    // You can add any additional logic here if needed
    return {
      symbol,
      style
    }
  }
}
</script>

<style>

div span svg{
  max-width: 100%;
  max-height: 100%;
}


</style>
