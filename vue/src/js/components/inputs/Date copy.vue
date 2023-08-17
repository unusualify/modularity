<template>
    <v-sheet>
      <v-menu
        v-model="menuActive"
        :close-on-content-click="false"
        transition="scale-transition"
        max-width="600"
        min-width="290"
        >
        <template v-slot:activator="{ props }">
            <v-text-field
                v-model="dateFormattedLocale"
                v-bind="{
                  ...$lodash.omit(boundProps, ['offset', 'order']),
                  ...props
                }"
                readonly

            ></v-text-field>
        </template>

        <v-locale-provider :locale="$i18n.locale">
          <v-date-picker
            v-bind="boundProps.picker_props"
            >
          </v-date-picker>
        </v-locale-provider>
        <!-- <v-date-picker
            v-model="input"
            :locale="$i18n.locale"
            v-bind="boundProps.picker_props"
            >
        </v-date-picker> -->

      </v-menu>
    </v-sheet>
</template>

<script>
import { InputMixin } from '@/mixins' // for props
import { useInput } from '@/hooks'

import { VDatePicker } from 'vuetify/labs/VDatePicker'

export default {
  mixins: [InputMixin],
  components: {
    VDatePicker
  },
  name: 'ue-custom-input-date',
  setup (props, context) {
    return {
      ...useInput(props, context)
    }
  },
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
      __log(this.input)
      // __log(this.input, !!this.input)
      // return this.input ? this.$d(new Date(this.input), 'short') : ''
      return this.input ? this.$d(new Date(this.input), 'medium') : ''
      return this.input ? this.$d(new Date(this.input), 'long') : ''
    }
  }
}
</script>
