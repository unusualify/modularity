<template>
  <div :class="[
    titleClasses,
    classes
  ]">
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
  import { transform } from 'lodash';
  import { computed } from 'vue';

  const props = defineProps({
    text: String,
    subTitle: String,
    type: {
      type: String,
      default: 'body-1',
      validator: (value) => value === null || ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'subtitle-1', 'subtitle-2', 'body-1', 'body-2', 'button', 'caption', 'overline'].includes(value)
    },
    weight: {
      type: String,
      default: 'bold',
      validator: (value) => value === null || ['black', 'bold', 'medium', 'regular', 'light', 'thin'].includes(value)
    },
    transform: {
      type: String,
      default: 'uppercase',
      validator: (value) => value === null || ['none', 'capitalize', 'lowercase', 'uppercase'].includes(value)
    },
    color: {
      type: String,
      default: 'primary'
    },
    padding: {
      type: String,
      default: 'a-6',
      // validator: (value) => value === null || [
      //   'a-0', 'a-2', 'a-3', 'a-4', 'a-5', 'a-6', 'a-7', 'a-8', 'a-9', 'a-10', 'a-11', 'a-12', 'a-13', 'a-14', 'a-15', 'a-16',
      //   'x-0', 'x-2', 'x-3', 'x-4', 'x-5', 'x-6', 'x-7', 'x-8', 'x-9', 'x-10', 'x-11', 'x-12', 'x-13', 'x-14', 'x-15', 'x-16',
      //   'y-0', 'y-2', 'y-3', 'y-4', 'y-5', 'y-6', 'y-7', 'y-8', 'y-9', 'y-10', 'y-11', 'y-12', 'y-13', 'y-14', 'y-15', 'y-16',
      //   't-0', 't-2', 't-3', 't-4', 't-5', 't-6', 't-7', 't-8', 't-9', 't-10', 't-11', 't-12', 't-13', 't-14', 't-15', 't-16',
      //   'b-0', 'b-2', 'b-3', 'b-4', 'b-5', 'b-6', 'b-7', 'b-8', 'b-9', 'b-10', 'b-11', 'b-12', 'b-13', 'b-14', 'b-15', 'b-16',
      //   'r-0', 'r-2', 'r-3', 'r-4', 'r-5', 'r-6', 'r-7', 'r-8', 'r-9', 'r-10', 'r-11', 'r-12', 'r-13', 'r-14', 'r-15', 'r-16',
      //   'l-0', 'l-2', 'l-3', 'l-4', 'l-5', 'l-6', 'l-7', 'l-8', 'l-9', 'l-10', 'l-11', 'l-12', 'l-13', 'l-14', 'l-15', 'l-16',
      //   's-0', 's-2', 's-3', 's-4', 's-5', 's-6', 's-7', 's-8', 's-9', 's-10', 's-11', 's-12', 's-13', 's-14', 's-15', 's-16',
      //   'e-0', 'e-2', 'e-3', 'e-4', 'e-5', 'e-6', 'e-7', 'e-8', 'e-9', 'e-10', 'e-11', 'e-12', 'e-13', 'e-14', 'e-15', 'e-16',
      // ].includes(value)
    },
    margin: {
      type: String,
      default: 'a-0',
      // validator: (value) => value === null || [
      //   'a-0', 'a-2', 'a-3', 'a-4', 'a-5', 'a-6', 'a-7', 'a-8', 'a-9', 'a-10', 'a-11', 'a-12', 'a-13', 'a-14', 'a-15', 'a-16',
      //   'x-0', 'x-2', 'x-3', 'x-4', 'x-5', 'x-6', 'x-7', 'x-8', 'x-9', 'x-10', 'x-11', 'x-12', 'x-13', 'x-14', 'x-15', 'x-16',
      //   'y-0', 'y-2', 'y-3', 'y-4', 'y-5', 'y-6', 'y-7', 'y-8', 'y-9', 'y-10', 'y-11', 'y-12', 'y-13', 'y-14', 'y-15', 'y-16',
      //   't-0', 't-2', 't-3', 't-4', 't-5', 't-6', 't-7', 't-8', 't-9', 't-10', 't-11', 't-12', 't-13', 't-14', 't-15', 't-16',
      //   'b-0', 'b-2', 'b-3', 'b-4', 'b-5', 'b-6', 'b-7', 'b-8', 'b-9', 'b-10', 'b-11', 'b-12', 'b-13', 'b-14', 'b-15', 'b-16',
      //   'r-0', 'r-2', 'r-3', 'r-4', 'r-5', 'r-6', 'r-7', 'r-8', 'r-9', 'r-10', 'r-11', 'r-12', 'r-13', 'r-14', 'r-15', 'r-16',
      //   'l-0', 'l-2', 'l-3', 'l-4', 'l-5', 'l-6', 'l-7', 'l-8', 'l-9', 'l-10', 'l-11', 'l-12', 'l-13', 'l-14', 'l-15', 'l-16',
      //   's-0', 's-2', 's-3', 's-4', 's-5', 's-6', 's-7', 's-8', 's-9', 's-10', 's-11', 's-12', 's-13', 's-14', 's-15', 's-16',
      //   'e-0', 'e-2', 'e-3', 'e-4', 'e-5', 'e-6', 'e-7', 'e-8', 'e-9', 'e-10', 'e-11', 'e-12', 'e-13', 'e-14', 'e-15', 'e-16',
      // ].includes(value)
    },

    defaultClasses: {
      type: [String, Array],
      default: 'ue-title'
    },

    // noUpperCase: {
    //   type: Boolean,
    //   default: false
    // },
    // noBold: {
    //   type: Boolean,
    //   default: false
    // },
    // paddingClasses: {
    //   type: [String, Array],
    //   default: ''
    // },
    classes: {
      type: [String, Array]
    },
    // paddingReset: {
    //   type: Boolean,
    //   default: false
    // },
    // descriptionClasses: {
    //   type: [String, Array],
    //   default: 'font-weight-light text-subtitle-2 text-truncate'
    // },
  })

  const titleClasses = computed(() => {
    return [
      `text-${props.type}`,
      `text-${props.color}`,
      `text-${props.transform}`,
      `font-weight-${props.weight}`,
      `p${props.padding}`,
      `m${props.margin}`
    ]
  })
</script>

<script>
export default {
  setup () {

  }
}
</script>

<style lang="sass" scoped>
  .ue-title
    // padding-top: .75rem
    // padding-bottom: .75rem
    // padding-left: 12 * $spacer
    // padding-right: 12 * $spacer

</style>
