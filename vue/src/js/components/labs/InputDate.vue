<template>
    <v-sheet>
      <v-text-field
        v-model="dateHtmlFormat"
        v-bind="{
          ...$lodash.omit(boundProps, ['offset', 'order', 'type']),
        }"
        type="date"

        >
      </v-text-field>
    </v-sheet>
</template>

<script>
import { InputMixin } from '@/mixins' // for props
import { useInput } from '@/hooks'

export default {
  mixins: [InputMixin],
  name: 'v-input-date',
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
    dateHtmlFormat: {
      get () {
        return this.input ? (new Date(this.input)).toISOString().split('T')[0] : ''
      },
      set (val) {
        this.input = val
        // context.emit('update:modelValue', val)
      }
    },
    dateFormattedLocale () {
      // return this.input ? this.$d(new Date(this.input), 'short') : ''
      return this.input ? this.$d(new Date(this.input), 'medium') : ''
      return this.input ? this.$d(new Date(this.input), 'long') : ''
    }
  }
}
</script>
