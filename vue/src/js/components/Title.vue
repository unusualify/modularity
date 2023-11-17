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
    default: 'text-h8 text-primary font-weight-bold ue-title'
  },
  paddingClasses: {
    type: [String, Array],
    default: ''
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

<style lang="sass" scoped>
  .ue-title
    padding-top: .75rem
    padding-bottom: .75rem
    padding-left: $theme-space
    padding-right: $theme-space
    text-transform: uppercase!important

</style>
