<template>
    <v-menu
      v-model="menuActive"
      :close-on-content-click="false"
      :nudge-right="40"
      transition="scale-transition"
      offset-y
      max-width="600px"
      min-width="290px"
      >
      <template v-slot:activator="{ on }">
          <v-text-field
              v-on="on"
              v-model="dateFormattedLocale"
              v-bind="obj.schema"
              readonly

          ></v-text-field>
      </template>

      <v-date-picker
          v-model="input"
          :locale="$i18n.locale"
          v-bind="$bindAttributes(obj.schema.picker_props)"
          >
      </v-date-picker>

    </v-menu>
</template>

<script>
import { CustomInputMixin } from '@/mixins'

export default {
  mixins: [CustomInputMixin],
  name: 'ue-custom-input-color',
  data () {
    return {
      menuActive: false
    }
  },
  computed: {
    computedDateFormattedMomentjs () {
      return this.input ? moment(this.input).format('dddd, MMMM Do YYYY') : ''
    },
    dateFormattedLocale () {
      // __log(this.input, !!this.input)
      return this.input ? this.$d(new Date(this.input), 'short') : ''
      return this.input ? this.$d(new Date(this.input), 'long') : ''
    }
  }
}
</script>
