<template>
  <component
    :is="tag"
    :class="[
      titleClasses,
      classes,
      'd-flex'
    ]"
  >
    <slot v-bind="{text}">
      <span v-html="text" />
    </slot>
    <slot name="right">

    </slot>
  </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  text: String,
  subTitle: String,
  tag: {
    type: String,
    default: 'div',
    validator: (value) => ['div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(value)
  },
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
  },
  bg: {
    type: String
  },
  padding: {
    type: String,
    default: 'a-3',
  },
  margin: {
    type: String,
    default: 'a-0',
  },
  align: {
    type: String,
    default: 'start',
    // Accept both flexbox tokens (start/center/end) and the CSS
    // text-align aliases (left/center/right) that callers commonly
    // pass — config-driven `<ue-title>` previews in module configs
    // (e.g. SystemPayment) sometimes spell `align="left"` when they
    // mean `align-self: flex-start`. The aliases are normalised to
    // start/end inside `titleClasses` so the emitted CSS class stays
    // a real Vuetify utility.
    validator: (value) => ['start', 'center', 'end', 'left', 'right'].includes(value)
  },
  textPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'center', 'right'].includes(value)
  },
  justify: {
    type: String,
    default: 'start',
    validator: (value) => ['start', 'center', 'end', 'space-between'].includes(value)
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
  },
});

// Map text-align style tokens to flexbox tokens so callers that pass
// `align="left" | "right"` still produce a real `align-start | align-end`
// utility class instead of a dead `align-left`.
const ALIGN_ALIAS = { left: 'start', right: 'end' }

const titleClasses = computed(() => [
  'ue-title',
  `text-${props.type}`,
  props.bg ? `bg-${props.bg}` : '',
  props.color ? `text-${props.color}` : '',
  `text-${props.transform}`,
  `font-weight-${props.weight}`,
  props.padding ? `p${props.padding}` : '',
  props.margin ? `m${props.margin}` : '',
  `text-${props.textPosition}`,
  `align-${ALIGN_ALIAS[props.align] ?? props.align}`,
  `justify-${props.justify}`
]);

</script>

<style lang="sass" scoped>

</style>
