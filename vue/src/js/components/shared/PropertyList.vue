<template>
  <div :class="[
    'ue-property-list',
    !noPadding ? 'ue-property-list--padding' : ''
  ]">
    <div v-if="!items || items.length === 0">No data available</div>
    <template v-else>
      <div v-for="(item, index) in items" :key="index" class="ue-property-list__item d-flex flex-wrap">
        <span class="text-caption font-weight-bold pr-1" v-html="item[0].trim() + (item.slice(1).length > 0 ? ':' : '')"></span>
        <span class="text-caption" v-html="formatValue(item.slice(1))">
        </span>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  data: {
    type: [Array, Object],
    default: () => [],
  },
  noPadding: {
    type: Boolean,
    default: false
  }
});

const items = computed(() => {
  if (Array.isArray(props.data)) {
    return props.data;
  } else if (typeof props.data === 'object' && props.data !== null) {
    return Object.entries(props.data).map(([key, value]) => [key, value]);
  }
  return [];
});

const formatValue = (values) => {
  if (!values) return null
  if (values.length === 0) return null;

  let formattedValues = values.map(value => {
    if (Array.isArray(value)) {
      return value.join(', ');
    }
    return value;
  });
  return formattedValues.join(', ');
};

</script>

<style lang="sass" scoped>
  .ue-property-list
    &--padding
      padding-top: calc(12 * $spacer / 2)
      padding-bottom: calc(12 * $spacer / 2)
    .ue-property-list__item:last-child
      border-bottom: none

</style>
