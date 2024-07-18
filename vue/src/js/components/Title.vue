<template>
  <div :class="[defaultClasses, (!paddingReset ? paddingClasses: []), classes]">
    <slot v-bind="{text}">
      {{ text }}
    </slot>
    <slot name="description" v-bind="{subTitle}">
      <div :class="[(!paddingReset ? paddingClasses: []), descriptionClasses]">
        {{ subTitle ?? ''}}
      </div>
    </slot>
  </div>
</template>

<script setup>
const props = defineProps({
  text: String,
  subTitle: String,
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
  },
  descriptionClasses: {
    type: [String, Array],
    default: 'font-weight-light text-subtitle-2 text-truncate'
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
