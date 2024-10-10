<template>
  <v-radio-group v-model="input" direction="horizontal"  class="v-input-radio-group">
    <v-row>
      <v-col v-for="(item, i) in items" :key="`col-${item[itemValue]}`">
        <v-btn
          block class="v-input-radio-group__btn"
          :variant="input == item[itemValue] ? 'elevated' : 'outlined'"
          @click="input=item[itemValue]"
          :disabled="$attrs.disabled ?? false"
        >
          <template #prepend>
            <v-radio :value="item[itemValue]" :disabled="$attrs.disabled ?? false"></v-radio>
          </template>
          {{ item[itemTitle].toUpperCase() }}
        </v-btn>
      </v-col>
    </v-row>
  </v-radio-group>
  <!-- <v-input
    v-model="selectedItems"
    hideDetails="auto"
    appendIcon="mdi-close"
    :variant="boundProps.variant"
    class="v-input-radio-group"
    >
    <template v-slot:default="defaultSlot">
    </template>
  </v-input> -->
</template>

<script>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
import Checklist from './Checklist.vue'

export default {
  name: 'v-input-radio-group',
  emits: [...makeInputEmits],
  components: {
    Checklist
  },
  props: {
    ...makeInputProps(),
    modelValue: {
      type: Object,
      default: () => ''
    },
    items: {
      type: Object,
      default: () => []
    },
    itemTitle: {
      type: String,
      default: 'name'
    },
    itemValue: {
      type: String,
      default: 'id'
    },
  },
  setup (props, context) {
    return {
      ...useInput(props, context)
    }
  },
  data: function () {
    return {

    }
  },
  computed: {

  },
  watch: {

  },
  methods: {

  },
  created() {
    // __log(this.schema)
  }
}
</script>

<style lang="sass">
  .v-input-radio-group
    .v-input__control
      display: block


    .v-input-radio-group__btn
      padding-left: 4px
      justify-content: flex-start !important

      .v-btn__content
        width: 100%
        white-space: normal


</style>

<style lang="scss">

</style>
