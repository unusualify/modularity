<template>
  <v-text-field
    ref="inputRef"
    v-model="formattedValue"
    v-bind="{...$bindAttributes($lodash.pick($attrs, ['label', 'error']))}"
    :errorMessages="errorMessages"
    >
    <!-- <slot v-for="(_, name) in $slots" :name="name" :slot="name" /> -->
    <!-- <template v-for="(_, name) in $scopedSlots" :slot="name" slot-scope="slotData"><slot :name="name" v-bind="slotData" /></template> -->
    <template v-for="(_, name) in $slots" v-slot:[name]="slotData"><slot :name="name" v-bind="slotData" /></template>
  </v-text-field>
</template>

<script>
import { useCurrencyNumber } from '@/hooks'

export default {
  name: 'ue-currency-number',
  props: {
    modelValue: Number,
    errorMessages: {
      type: Array,
      default: () => []
    }
  },
  setup(props,context) {

    return {
      ...useCurrencyNumber(props, context)
    }
  },
  created(){

  }
}
</script>
