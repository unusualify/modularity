<template>
  <v-menu
    :ref="`${id}-menu`"
    v-model="menuActive"
    :close-on-content-click="false"
    :nudge-right="40"
    @update:return-value="value"
    transition="scale-transition"
    offset-y
    max-width="290px"
    min-width="290px"
    >
    <template v-slot:activator="{ on, attrs }">
      <v-text-field
        v-model="value"
        :label="label"

        readonly
        v-bind="{
            ...attrs,
            ...props
        }"
        v-on="on"
      ></v-text-field>
    </template>
    <v-time-picker
      v-if="menuActive"
      v-model="value"
      full-width
      @click:minute="$refs[`${id}-menu`].save(value)"
      v-bind="pickerProps"
    ></v-time-picker>
  </v-menu>
</template>

<script>
import { InputMixin } from '@/mixins'

export default {
  mixins: [InputMixin],
  data () {
    return {
      menuActive: false
    }
  },
  computed: {
    pickerProps () {
      if (this.attributes.picker_props) {
        return this.configureProps(this.attributes.picker_props)
      } else {
        return {}
      }
    }
  }
}
</script>
