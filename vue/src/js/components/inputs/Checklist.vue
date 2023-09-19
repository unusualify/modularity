<template>
  <div>
    <ue-title v-if="label">
      {{ label }}
    </ue-title>
    <v-row>
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
    </v-row>
  </div>
</template>

<script>
import { InputMixin } from '@/mixins' // for props
import { useInput } from '@/hooks'

export default {
  setup (props, context) {
    __log(
      useInput(props, context)
    )
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
      default: 'title'
    },
    items: {
      type: Array,
      default: () => []
    }
  },

  created () {
    __log(this.input)
  }
}
</script>

<style lang="scss" scoped>

</style>
