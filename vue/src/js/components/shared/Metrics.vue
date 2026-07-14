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

    metricAttributes: {
      type: Object,
      default: () => ({})
    },

    endpoint: {
      type: String,
      default: null
    }
  });

  const cardClasses = computed(() => {
    return [
      !props.noInline ? 'd-inline-block' : '',
      // 'rounded-lg',
      // 'overflow-hidden',
    ];
  });

  const headerClasses = computed(() => {
    return [
      props.bgHeaderColor ? `bg-${props.bgHeaderColor}` : '',
      'd-flex justify-space-between align-center pa-4',
    ];
  });

  const titleClasses = computed(() => {
    return [
      props.color ? `text-${props.color}` : '',
      'font-weight-medium text-body-1 text-wrap',
    ];
  });

  const filterClasses = computed(() => {
    return [
      props.filterColor ? `text-${props.filterColor}` : '',
      // 'text-body-2 text-medium-emphasis',
      'd-flex align-center'
    ];
  });

  const rowClasses = computed(() => {
    return [
      'd-flex flex-wrap ga-4',
      props.rowClass ? props.rowClass : '',
      props.hasGutter ? `mx-n${props.gutterStep}` : ''
    ];
  });

  const defaultMetricAttributes = computed(() => {
    return {
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
    elevation="0"
  >
    <!-- Header with title and date -->
    <v-card-title :class="headerClasses">
      <div class="py-2">
        <ue-title padding="a-0" :text="title" :class="titleClasses" />
        <ue-title padding="a-0" :text="subtitle" type="caption" weight="medium" color="grey-darken-1" transform="none"/>
      </div>
      <!-- <div :class="titleClasses">{{ title }}</div> -->
      <div :class="filterClasses" style="width: 280px;" v-if="hasFilterableMetric && endpoint">
        <v-date-input
          v-model="dateRangeModel"
          :Xlabel="$t('')"
          :validate-on="`submit blur`"
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

    <v-card-text>
      <!-- Metrics row -->
      <div :class="rowClasses">
        <template
          v-for="(metric, index) in metrics"
          :key="index"
          >
          <ue-metric
            :style="[
              metricWidth ? `width: ${metricWidth}` : '',
              minMetricWidth ? `min-width: ${minMetricWidth}px` : '',
            ]"

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

</style>