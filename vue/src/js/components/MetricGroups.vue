<script setup>
  import { computed } from 'vue';
  import { map, cloneDeep, reduce } from 'lodash-es';

  const props = defineProps({
    title: {
      type: String,
      default: '',
    },
    metrics: {
      type: Array,
      required: true,
    },
    defaultCol: {
      type: Object,
      default: () => ({
        cols: 12,
      }),
    },

    metricsBgHeaderColor: {
      type: String,
      default: null,
    },
    metricsNoInline: {
      type: Boolean,
      default: null,
    },


    metricColor: {
      type: String,
      default: null,
    },
    metricCardColor: {
      type: String,
      default: null,
    },
    metricLabelColor: {
      type: String,
      default: null,
    },
    metricValueClass: {
      type: String,
      default: null,
    },
    metricLabelClass: {
      type: String,
      default: null,
    },
    metricNoInline: {
      type: Boolean,
      default: false,
    },
  });

  const defaultMetricAttributes = computed(() => {
    return {
      ...(props.metricColor ? {metricColor: props.metricColor} : {}),
      ...(props.metricCardColor ? {metricCardColor: props.metricCardColor} : {}),
      ...(props.metricLabelColor ? {metricLabelColor: props.metricLabelColor} : {}),
      ...(props.metricValueClass ? {metricValueClass: props.metricValueClass} : {}),
      ...(props.metricLabelClass ? {metricLabelClass: props.metricLabelClass} : {}),
      ...(props.metricNoInline ? {metricNoInline: props.metricNoInline} : {}),
    };
  });

  const castedMetricGroups = computed(() => {
    return props.metrics.map(metricGroup => {


      return {
        ...(props.metricsBgHeaderColor ? {bgHeaderColor: props.metricsBgHeaderColor} : {}),
        ...(props.metricsNoInline ? {noInline: props.metricsNoInline} : {}),
        ...(defaultMetricAttributes.value),
        ...metricGroup,
      };
    });
  });

</script>

<template>
  <v-card>
    <v-card-title>
      {{ title }}
    </v-card-title>
    <v-card-text>
      <v-row>
        <v-col v-for="(metricGroup, index) in castedMetricGroups"
          :key="index"
           v-bind="{...defaultCol, ...(metricGroup.col ?? {})}"
        >
          <ue-metrics
            v-bind="{...($lodash.omit(metricGroup, 'col'))}"
          />
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>