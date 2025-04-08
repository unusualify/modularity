<script setup>
  import { computed } from 'vue';

  const props = defineProps({
    title: {
      type: String,
      required: true,
    },
    metrics: {
      type: Array,
      default: () => [],
    },
    color: {
      type: String,
      default: null
    },
    cardColor: {
      type: String,
      default: null
    },
    filterColor: {
      type: String,
      default: null
    },
    bgHeaderColor: {
      type: String,
      default: null
    },
    noInline: {
      type: Boolean,
      default: false
    },
    dateLabel: {
      type: String,
      default: "Today"
    },
    date: {
      type: String,
      default: null
    },

    metricColor: {
      type: String,
      default: null
    },
    metricCardColor: {
      type: String,
      default: null
    },
    metricLabelColor: {
      type: String,
      default: null
    },
    metricValueClass: {
      type: String,
      default: null
    },
    metricLabelClass: {
      type: String,
      default: null
    },
    metricNoInline: {
      type: Boolean,
      default: false
    }
  });

  const cardClasses = computed(() => {
    return [
      'ue-metrics',
      !props.noInline ? 'd-inline-block' : '',
      'rounded-lg',
      'overflow-hidden',
    ];
  });

  const headerClasses = computed(() => {
    return [
      'ue-metrics__header',
      props.bgHeaderColor ? `bg-${props.bgHeaderColor}` : '',
      'd-flex justify-space-between align-center pa-4',
      'rounded-t-lg',
      'border-s-sm border-e-sm border-t-sm'
    ];
  });

  const titleClasses = computed(() => {
    return [
      props.color ? `text-${props.color}` : '',
      'font-weight-medium',
      'text-body-1'
    ];
  });

  const filterClasses = computed(() => {
    return [
      props.filterColor ? `text-${props.filterColor}` : '',
      'text-body-2',
      'text-medium-emphasis'
    ];
  });

  const defaultMetricAttributes = computed(() => {
    return {
      ...(props.metricColor ? {color: props.metricColor} : {}),
      ...(props.metricCardColor ? {cardColor: props.metricCardColor} : {}),
      ...(props.metricLabelColor ? {labelColor: props.metricLabelColor} : {}),
      ...(props.metricValueClass ? {valueClass: props.metricValueClass} : {}),
      ...(props.metricLabelClass ? {labelClass: props.metricLabelClass} : {}),
      ...(props.metricNoInline ? {noInline: props.metricNoInline} : {}),
    };
  });

  // __log(defaultMetricAttributes.value);
</script>

<template>
  <v-card
    :color="cardColor"
    :class="cardClasses"
    elevation="0"
  >
    <!-- Header with title and date -->
    <v-card-title :class="headerClasses">
      <div :class="titleClasses">{{ title }}</div>
      <div v-if="date" :class="filterClasses">
        {{ dateLabel }} {{ date }}
      </div>
    </v-card-title>

    <!-- Metrics row -->
    <div class="d-flex flex-wrap ue-metrics__row">
      <template
        v-for="(metric, index) in metrics"
        :key="index"
        >
        <ue-metric
          :class="[
            'ue-metrics__metric',
            'flex-grow-1',
          ]"
          :style="{ width: `${100 / metrics.length}%`, minWidth: '150px' }"
          elevation="0"
          rounded="0"
          dense
          no-inline
          v-bind="{
            ...defaultMetricAttributes,
            ...metric
          }"
        />
      </template>
    </div>
  </v-card>
</template>

<style scoped lang="scss">
  $border-width: 1px;
  $border-color: rgba(0, 0, 0, 0.92);

  .ue-metrics,
  .ue-metrics__header {
    border-color: $border-color !important;
    border-right-color: $border-color !important;

  }
  .ue-metrics {
    border-bottom: $border-width solid;

    .ue-metrics__row {
      border-left: $border-width solid $border-color !important;
    }

    .ue-metrics__metric {
      border-top: $border-width solid;
      border-block-start-color: $border-color !important;

      border-right: $border-width solid $border-color !important;
      border-block-end-color: $border-color !important;
    }
  }
</style>