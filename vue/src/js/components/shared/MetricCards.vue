<script setup>
  import { computed, ref, watch } from 'vue'
  import { isObject } from 'lodash-es'

  const props = defineProps({
    title: {
      type: String,
      required: true,
    },
    subtitle: {
      type: String,
      default: null,
    },
    items: {
      type: Array,
      default: () => [],
    },
    metricCol: {
      type: Object,
      default: () => ({
        cols: 12,
        sm: 6,
        lg: 3,
      }),
    },
    metricAttributes: {
      type: Object,
      default: () => ({}),
    },
    endpoint: {
      type: String,
      default: null,
    },
    filterColor: {
      type: String,
      default: null,
    },
  })

  const metrics = ref(props.items)

  const defaultMetricAttributes = computed(() => ({
    ...(props.metricAttributes ?? {}),
  }))

  const filterableMetrics = computed(() => {
    return metrics.value.filter((metric) => {
      return metric.connectorFilter
        && isObject(metric.connectorFilter)
        && metric.connectorFilter.name
        && metric.connectorFilter.args
    })
  })

  const hasFilterableMetric = computed(() => filterableMetrics.value.length > 0)

  const filterClasses = computed(() => [
    props.filterColor ? `text-${props.filterColor}` : '',
    'd-flex align-center',
  ])

  const dateRangeModel = ref(null)
  const dateRangeLoading = ref(false)

  const refreshMetrics = () => {
    dateRangeLoading.value = true

    axios.post(props.endpoint, {
      date_range: dateRangeModel.value,
      items: metrics.value,
    }).then((response) => {
      if (response.data.variant === 'success') {
        metrics.value = response.data.data
      }
    }).catch((error) => {
      console.log(error)
    }).finally(() => {
      dateRangeLoading.value = false
    })
  }

  watch(dateRangeModel, (newValue, oldValue) => {
    if ((Array.isArray(newValue) && newValue.length > 1) || (!newValue && oldValue && oldValue.length > 1)) {
      refreshMetrics()
    }
  })
</script>

<template>
  <div class="ue-metric-cards">
    <div class="d-flex flex-wrap justify-space-between align-end ga-4 mb-3">
      <div class="py-0">
        <ue-title
          padding="a-0"
          :text="title"
          type="title-medium"
          weight="bold"
          color="grey"
        />
        <ue-title
          v-if="subtitle"
          padding="a-0"
          :text="subtitle"
          type="body-small"
          weight="regular"
          color="grey-lighten-1"
          transform="none"
          class="mt-1"
        />
      </div>
      <div
        v-if="hasFilterableMetric && endpoint"
        :class="filterClasses"
        style="width: 280px;"
      >
        <v-date-input
          v-model="dateRangeModel"
          variant="outlined"
          density="compact"
          prepend-icon=""
          append-inner-icon="$calendar"
          persistent-placeholder
          show-adjacent-months
          required
          hide-details
          multiple="range"
          clearable
          :disabled="dateRangeLoading"
          class="w-100"
        />
      </div>
    </div>

    <v-row class="ue-metric-cards__row">
      <v-col
        v-for="(metric, index) in metrics"
        :key="index"
        class="d-flex"
        v-bind="metricCol"
      >
        <ue-metric-card
          v-bind="{
            ...defaultMetricAttributes,
            ...metric,
          }"
        />
      </v-col>
    </v-row>
  </div>
</template>

