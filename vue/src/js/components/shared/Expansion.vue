<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: true
  },
  title: {
    type: String,
    default: ''
  },
  multiple: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  hasActions: {
    type: Boolean,
    default: false
  },
  expandedIcon: {
    type: String,
    default: 'mdi-chevron-down'
  },
  collapsedIcon: {
    type: String,
    default: 'mdi-chevron-up'
  }
});

const model = ref(props.modelValue ? ['one'] : []);

</script>

<template>
  <v-expansion-panels
    v-model="model"
    :multiple="multiple"
    :readonly="readonly"
  >
    <v-expansion-panel value="one">
      <v-expansion-panel-title v-if="title"
        >
        {{ title }}
        <template v-if="hasActions" v-slot:actions="{ expanded }">
          <v-icon :color="!expanded ? 'teal' : ''" :icon="expanded ? expandedIcon : collapsedIcon"></v-icon>
        </template>
      </v-expansion-panel-title>
      <v-expansion-panel-text>
        <slot></slot>
      </v-expansion-panel-text>
    </v-expansion-panel>
  </v-expansion-panels>
</template>