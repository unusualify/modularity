<template>
  <div>
    <ue-title v-if="label" :classes="['pl-0 pt-0']">
      {{ label }}
    </ue-title>
    <v-row align="start" justify="start" noGutters>
      <v-checkbox
        v-for="(item, index) in items"
        :key="`checkbox-${index}`"
        v-model="input"
        :label="item[`${itemTitle}`]"
        :value="item[`${itemValue}`]"
        :color="checkboxColor"
        hide-details
        >
      </v-checkbox>
    </v-row>
    <!-- <v-row>
      <v-col v-for="(item, index) in items"
          :key="`checkbox-${index}`" cols="4">
          <v-checkbox
            v-model="input"
            :label="item[`${itemTitle}`]"
            :value="item[`${itemValue}`]"
            color="success"
            hide-details
            >
          </v-checkbox>
      </v-col>
    </v-row> -->
  </div>
</template>

<script>
import { InputMixin } from '@/mixins' // for props
import { useInput } from '@/hooks'

export default {
  setup (props, context) {
    return {
      ...useInput(props, context)
    }
  },
  mixins: [InputMixin],
  name: 'v-custom-input-checklist',
  props: {
    label: {
      type: String,
      default: ''
    },
    itemValue: {
      type: String,
      default: 'id'
    },
    itemTitle: {
      type: String,
      default: 'name'
    },
    items: {
      type: Array,
      default: () => []
    },
    checkboxColor: {
      type: String,
      default: 'success'
    }
  },

  created () {

  }
}
</script>

<style lang="scss" scoped>

</style>
