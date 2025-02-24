<template>
  <v-radio-group v-model="input" direction="horizontal"  class="v-input-radio-group" hide-details>
    <ue-title v-if="label" :text="label" color="grey-darken-5" padding="b-4" margin="b-0" transform="none"/>
    <v-row>
      <v-col v-for="(item, i) in items" :key="`col-${item[itemValue]}`" :cols="12" :md="6">
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

        <div v-if="item[descriptionTitle]" class="text-caption pa-2" v-html="item[descriptionTitle]"></div>
      </v-col>
    </v-row>
  </v-radio-group>
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
    descriptionTitle: {
      type: String,
      default: 'description'
    }
  },
  setup (props, context) {

    const initializeInput = (val) => {
      return val
    }
    return {
      ...useInput(props, {...context, ...{initializeInput}})
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
