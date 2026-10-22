<script setup>
  import { computed } from 'vue';

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
      default: null,
    },
    cardColor: {
      type: String,
      default: null,
    },
    labelColor: {
      type: String,
      default: null,
    },
    valueClass: {
      type: String,
      default: '',
    },
    labelClass: {
      type: String,
      default: '',
    },
    dense: {
      type: Boolean,
      default: false,
    },
    noInline: {
      type: Boolean,
      default: false,
    },
    center: {
      type: Boolean,
      default: false,
    },
    icon: {
      type: String,
      default: null,
    },
    appendIcon: {
      type: String,
      default: null,
    },
    appendIconAttributes: {
      type: Object,
      default: () => ({}),
    },
    // 'stacked' = value over label; 'horizontal' = label | divider | value
    layout: {
      type: String,
      default: 'stacked',
      validator: (value) => ['stacked', 'horizontal'].includes(value),
    },
    variant: {
      type: String,
      default: undefined,
    },
    elevation: {
      type: [Number, String],
      default: undefined,
    },
    rounded: {
      type: [String, Number, Boolean],
      default: undefined,
    },
    border: {
      type: [String, Number, Boolean],
      default: undefined,
    },
  });

  const isHorizontal = computed(() => props.layout === 'horizontal');

  // Compute classes based on provided props
  const cardClasses = computed(() => {
    return {
      'd-inline-block': !props.noInline,
      'ue-metric': true,
      'ue-metric--horizontal d-flex flex-column': isHorizontal.value,
      'text-center': props.center,
      'py-1 px-1': !props.dense && !isHorizontal.value,
      // 'py-2 px-2': !props.dense
    };
  });

  const valueClasses = computed(() => {
    if (isHorizontal.value) {
      return [
        'ue-metric__value',
        'font-weight-regular',
        'text-headline-large',
        props.color ? `text-${props.color}` : '',
        props.valueClass,
      ];
    }

    return [
      'font-weight-bold',
      props.dense ? 'text-headline-large' : 'text-display-medium',
      props.color ? `text-${props.color}` : '',
      props.valueClass,
    ];
  });

  const labelClasses = computed(() => {
    if (isHorizontal.value) {
      const labelColor = props.labelColor ?? props.color;

      return [
        'ue-metric__label',
        'text-body-small',
        'font-weight-medium',
        labelColor ? `text-${labelColor}` : '',
        props.labelClass,
      ];
    }

    return [
      props.color ? `text-${props.color}` : '',
      props.labelColor ? `text-${props.labelColor}` : '',
      props.dense ? 'text-body-small' : 'text-subtitle',
      props.labelClass
    ];
  });

</script>

<template>
  <v-card
    :color="cardColor"
    :class="cardClasses"
    :variant="variant"
    :elevation="elevation"
    :rounded="rounded"
    :border="border"
  >
    <v-card-text v-if="isHorizontal" class="ue-metric__body d-flex align-center flex-grow-1 ga-3 px-4 py-4">
      <v-icon
        v-if="appendIcon"
        class="flex-shrink-0"
        v-bind="appendIconAttributes"
        :icon="appendIcon"
      />
      <slot name="label" v-bind="{ label, classes: labelClasses, icon }">
        <div :class="[...labelClasses, 'flex-grow-1']">
          <v-icon
            v-if="icon"
            :icon="icon"
          />
          {{ label }}
        </div>
      </slot>
      <v-divider
        vertical
        thickness="1"
        class="ue-metric__divider flex-grow-0"
      />
      <div class="d-flex align-center justify-end flex-shrink-0">
        <slot name="value" v-bind="{ value, classes: valueClasses }">
          <div :class="valueClasses">
            {{ value }}
          </div>
        </slot>
      </div>
    </v-card-text>
    <v-card-text v-else class="pa-3 pr-0">
      <div class="d-flex">
        <div v-if="appendIcon" class="d-flex align-center justify-center me-2">
          <v-icon
          v-bind="appendIconAttributes"
          :icon="appendIcon"
          />
        </div>
        <div>
          <slot name="value" v-bind="{ value, classes: valueClasses }">
            <div :class="valueClasses">
              {{ value }}
            </div>
          </slot>
          <slot name="label" v-bind="{ label, classes: labelClasses, icon }">
            <div :class="labelClasses">
              <v-icon
                v-if="icon"
                :icon="icon"
              />
              {{ label }}
            </div>
          </slot>
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>

<style scoped lang="sass">
  .ue-metric
    transition: all 0.2s

  // fixed floor so tiles with one- and two-line labels line up across panels
  .ue-metric__body
    min-height: 96px

  // Vuetify sizes vertical dividers from `length`; stretch keeps it proportional to the tile instead
  .ue-metric__divider
    align-self: stretch
    height: auto
    max-height: none
    margin-block: 4px
    // same token as the card's border-* utility
    border-color: rgba(var(--v-border-color), var(--v-border-opacity))
    opacity: 1

  // long single words wrap instead of pushing the value out of the tile
  .ue-metric__label
    min-width: 0
    overflow-wrap: anywhere

  .ue-metric__value
    line-height: 1
    min-width: 2ch
    text-align: end
    letter-spacing: -0.02em

  .ue-metric--horizontal
    container-type: inline-size

  @container (max-width: 180px)
    .ue-metric__value
      font-size: 1.75rem

  @container (max-width: 150px)
    .ue-metric__body
      padding-inline: 8px
      gap: 6px

    .ue-metric__value
      font-size: 1.25rem
</style>
