<template>
  <div :class="[defaultClasses, (!paddingReset ? paddingClasses: []), classes]">
    <slot v-bind="{text}">
      {{ text }}
    </slot>
  </div>
</template>

<script setup>
const props = defineProps({
  text: String,
  defaultClasses: {
    type: [String, Array],
    default: 'text-h8 text-primary font-weight-bold'
  },
  paddingClasses: {
    type: [String, Array],
    default: 'pt-5 pb-5'
  },
  classes: {
    type: [String, Array]
  },
  paddingReset: {
    type: Boolean,
    default: false
  }
})
</script>

<script>
export default {
  setup () {
    // ...
  }
}
</script>

<style lang="scss" scoped>

</style>
