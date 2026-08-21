<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    value: {
      type: [Number, String],
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
    color: {
      type: String,
      default: 'primary',
    },
    labelColor: {
      type: String,
      default: 'grey-darken-1',
    },
    valueClass: {
      type: String,
      default: '',
    },
    labelClass: {
      type: String,
      default: '',
    },
    appendIcon: {
      type: String,
      default: null,
    },
    appendIconAttributes: {
      type: Object,
      default: () => ({}),
    },
    padDigits: {
      type: Number,
      default: 0,
    },
    variant: {
      type: String,
      default: 'flat',
    },
    elevation: {
      type: [Number, String],
      default: 1,
    },
    rounded: {
      type: [String, Number, Boolean],
      default: 'lg',
    },
    border: {
      type: [String, Number, Boolean],
      default: 'opacity-8',
    },
  })

  const displayValue = computed(() => {
    const raw = props.value
    const numeric = typeof raw === 'number' || (typeof raw === 'string' && /^\d+$/.test(raw))

    if (props.padDigits > 0 && numeric) {
      return String(raw).padStart(props.padDigits, '0')
    }

    return raw
  })

  const iconAttributes = computed(() => ({
    color: 'primary',
    size: 24,
    ...(props.appendIconAttributes ?? {}),
  }))

  const valueClasses = computed(() => [
    'ue-metric-card__value',
    'font-weight-bold',
    'text-headline-medium',
    props.color ? `text-${props.color}` : '',
    props.valueClass,
  ])

  const labelClasses = computed(() => [
    'ue-metric-card__label',
    'text-body-medium',
    'font-weight-medium',
    props.labelColor ? `text-${props.labelColor}` : '',
    props.labelClass,
  ])
</script>

<template>
  <v-card
    class="ue-metric-card w-100"
    :variant="variant"
    :elevation="elevation"
    :rounded="rounded"
    :border="border"
  >
    <v-card-text class="d-flex align-center px-3 py-6 ga-2">
      <v-sheet v-if="appendIcon"
        class="ue-metric-card__icon d-flex align-center justify-center flex-shrink-0"
        color="grey-lighten-6"
        rounded="lg"
        width="56"
        height="56"
      >
        <v-icon
          v-bind="iconAttributes"
          :icon="appendIcon"
        />
      </v-sheet>

      <div :class="[...labelClasses, 'flex-grow-1', 'min-width-0', 'text-truncate', 'text-center']">
        {{ label }}
      </div>

      <div class="d-flex align-center flex-grow-0 flex-shrink-0">
        <v-divider
          vertical
          inset
          length="28"
          thickness="1"
          color="grey-lighten-3"
          opacity="1"
          class="ue-metric-card__divider flex-grow-0 mx-1"
        />
      </div>

      <div :class="[...valueClasses, 'flex-grow-0', 'flex-shrink-0']">
        {{ displayValue }}
      </div>
    </v-card-text>
  </v-card>
</template>

<style scoped lang="sass">
  .ue-metric-card
    background-color: rgb(var(--v-theme-surface))

  .ue-metric-card__value
    line-height: 1
    min-width: 2ch
    text-align: end
    letter-spacing: -0.02em
</style>
