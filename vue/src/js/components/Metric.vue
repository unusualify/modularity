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
  });

  // Compute classes based on provided props
  const cardClasses = computed(() => {
    return {
      'd-inline-block': !props.noInline,
      'metric-card': true,
      'text-center': props.center,
      'py-1 px-1': !props.dense,
      // 'py-2 px-2': !props.dense
    };
  });

  const valueClasses = computed(() => {
    return [
      'font-weight-bold',
      props.dense ? 'text-h4' : 'text-h3',
      props.color ? `text-${props.color}` : '',
      props.valueClass,
    ];
  });

  const labelClasses = computed(() => {
    return [
      props.color ? `text-${props.color}` : '',
      props.labelColor ? `text-${props.labelColor}` : '',
      props.dense ? 'text-caption' : 'text-subtitle',
      props.labelClass
    ];
  });

</script>

<template>
  <v-card
    :color="cardColor"
    :class="cardClasses"
  >
    <v-card-text>
      <div :class="valueClasses">
        {{ value }}
      </div>
      <div :class="labelClasses">
        <v-icon
          v-if="icon"
          :icon="icon"

        />
        {{ label }}
      </div>
    </v-card-text>
  </v-card>
</template>

<style scoped lang="sass">
  .metric-card
    transition: all 0.2s
</style>
