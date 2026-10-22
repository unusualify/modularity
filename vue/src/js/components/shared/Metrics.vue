<script setup>
  import { computed, ref, watch } from 'vue';
  import { isObject } from 'lodash-es';

  const props = defineProps({
    title: {
      type: String,
      required: true,
    },
    subtitle: {
      type: String,
      default: null
    },
    // forwarded to the title's ue-title (type, weight, transform, …); empty keeps the ue-title defaults
    titleAttributes: {
      type: Object,
      default: () => ({})
    },
    // full-width divider between the header (title + date filter) and the metrics
    headerDivider: {
      type: Boolean,
      default: false
    },
    items: {
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
    hasGutter: {
      type: Boolean,
      default: false
    },
    gutterStep: {
      type: Number,
      default: 1
    },
    rowClass: {
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

    metricWidth: {
      type: [String, Number],
      default: null
    },
    minMetricWidth: {
      type: [String, Number],
      default: 130
    },
    // horizontal layout only: max equal-width columns per row; fewer when a column would drop under minMetricWidth
    metricColumns: {
      type: [String, Number],
      default: null
    },

    metricAttributes: {
      type: Object,
      default: () => ({})
    },
    // forwarded to every ue-metric as `layout`; 'horizontal' also switches the row to an auto-fill grid
    metricLayout: {
      type: String,
      default: 'stacked',
      validator: (value) => ['stacked', 'horizontal'].includes(value),
    },

    elevation: {
      type: [Number, String],
      default: 0
    },
    rounded: {
      type: [String, Number, Boolean],
      default: undefined
    },
    border: {
      type: [String, Number, Boolean],
      default: undefined
    },

    compactFilter: {
      type: Boolean,
      default: false
    },
    filterPlaceholder: {
      type: String,
      default: null
    },

    endpoint: {
      type: String,
      default: null
    }
  });

  const METRIC_GRID_GAP = 16;

  const isHorizontalLayout = computed(() => props.metricLayout === 'horizontal');

  const cardClasses = computed(() => {
    return [
      !props.noInline ? 'd-inline-block' : '',
      'd-flex flex-column flex-grow-1 min-height-0',
    ];
  });

  const headerClasses = computed(() => {
    return [
      props.bgHeaderColor ? `bg-${props.bgHeaderColor}` : '',
      'd-flex justify-space-between align-center',
      props.headerDivider ? 'px-4 pt-4 pb-5' : 'pa-4',
      props.compactFilter ? 'ga-3' : '',
    ];
  });

  const titleClasses = computed(() => {
    return [
      props.color ? `text-${props.color}` : '',
      'text-wrap',
    ];
  });

  const filterClasses = computed(() => {
    return [
      props.filterColor ? `text-${props.filterColor}` : '',
      // 'text-body-medium text-medium-emphasis',
      'd-flex align-center',
      props.compactFilter ? 'ue-metrics__filter--compact' : '',
    ];
  });

  const filterStyle = computed(() => props.compactFilter ? null : { width: '280px' });

  const rowClasses = computed(() => {
    return [
      isHorizontalLayout.value ? 'ue-metrics__grid' : 'd-flex flex-wrap ga-4',
      props.rowClass ? props.rowClass : '',
      props.hasGutter ? `mx-n${props.gutterStep}` : ''
    ];
  });

  const rowStyle = computed(() => {
    if (!isHorizontalLayout.value) {
      return null;
    }

    const minWidth = props.minMetricWidth ? `${parseInt(props.minMetricWidth, 10)}px` : '0px';
    const columns = parseInt(props.metricColumns, 10);
    const columnWidth = columns > 0
      ? `max(${minWidth}, calc((100% - ${(columns - 1) * METRIC_GRID_GAP}px) / ${columns}))`
      : minWidth;

    return {
      gap: `${METRIC_GRID_GAP}px`,
      gridTemplateColumns: `repeat(auto-fill, minmax(${columnWidth}, 1fr))`,
    };
  });

  const metricStyle = computed(() => {
    if (isHorizontalLayout.value) {
      return null;
    }

    return [
      props.metricWidth ? `width: ${props.metricWidth}` : '',
      props.minMetricWidth ? `min-width: ${props.minMetricWidth}px` : '',
    ];
  });

  const defaultMetricAttributes = computed(() => {
    return {
      layout: props.metricLayout,
      ...(props.metricAttributes ?? {})
    };
  });

  const metrics = ref(props.items);

  const filterableMetrics = computed(() => {
    return metrics.value.filter(metric => {
      return metric.connectorFilter
        && isObject(metric.connectorFilter)
        && metric.connectorFilter.name
        && metric.connectorFilter.args;
    });
  });

  const hasFilterableMetric = computed(() => {
    return filterableMetrics.value.length > 0;
  });

  const dateRangeModel = ref(null);
  const dateRangeLoading = ref(false);

  const refreshMetrics = () => {
    dateRangeLoading.value = true;

    axios.post(props.endpoint, {
      date_range: dateRangeModel.value,
      items: metrics.value
    }).then(response => {
      if(response.data.variant === 'success') {
        metrics.value = response.data.data;
      }
    }).catch(error => {
      console.log(error);
    }).finally(() => {
      dateRangeLoading.value = false;
    });
  }
  watch(dateRangeModel, (newValue, oldValue) => {
    if(Array.isArray(newValue) && newValue.length > 1 || (!newValue && oldValue && oldValue.length > 1)) {
      refreshMetrics();
    }
  });
</script>

<template>
  <v-card
    :color="cardColor"
    :class="cardClasses"
    :elevation="elevation"
    :rounded="rounded"
    :border="border"
  >
    <!-- Header with title and date -->
    <v-card-title :class="headerClasses">
      <div :class="['py-2', { 'flex-shrink-0': compactFilter }]">
        <ue-title padding="a-0" v-bind="titleAttributes" :text="title" :class="titleClasses" />
        <ue-title padding="a-0" :text="subtitle" type="caption" weight="medium" color="grey-darken-1" transform="none"/>
      </div>
      <!-- <div :class="titleClasses">{{ title }}</div> -->
      <div :class="filterClasses" :style="filterStyle" v-if="hasFilterableMetric && endpoint">
        <v-date-input
          v-model="dateRangeModel"
          :Xlabel="$t('')"
          :validate-on="`submit blur`"
          :placeholder="filterPlaceholder ?? undefined"
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
        >
          <!-- <template v-slot:actions="{ save, cancel, isPristine }">
            sss
          </template> -->
        </v-date-input>
        <!-- {{ dateLabel }} {{ date }} -->
      </div>
    </v-card-title>

    <v-divider v-if="headerDivider" class="ue-metrics__header-divider" />

    <v-card-text>
      <!-- Metrics row -->
      <div :class="rowClasses" :style="rowStyle">
        <template
          v-for="(metric, index) in metrics"
          :key="index"
          >
          <ue-metric
            :style="metricStyle"

            dense
            no-inline
            v-bind="{
              ...defaultMetricAttributes,
              ...metric
            }"
          >
            <template v-if="metric.filtered" v-slot:value="valueScope">
              <v-badge
                color="grey-lighten-4"
                class="text-white"
                icon="mdi-filter-check"
                inline
                Xdot
                X-floating
              >
                <div :class="valueScope.classes">
                  {{ valueScope.value }}
                </div>
              </v-badge>
            </template>
          </ue-metric>
        </template>
      </div>
    </v-card-text>

  </v-card>
</template>

<style scoped lang="scss">
  .ue-metrics__grid {
    display: grid;
  }

  .ue-metrics__filter--compact {
    flex: 0 1 220px;
    min-width: 132px;

    :deep(.v-field) {
      --v-field-input-padding-top: 4px;
      --v-field-input-padding-bottom: 4px;
      --v-input-control-height: 32px;
      font-size: 0.75rem;
    }

    :deep(.v-field__input) {
      min-height: 32px;
      text-overflow: ellipsis;
    }
  }
</style>
