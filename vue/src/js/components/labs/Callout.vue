<template>
  <v-sheet class="rounded-0">
    <v-alert
      class="rounded-0 v-callout"
      border="start"
      :color="bgColor"
      :border-color="color"
      elevation="2"
    >
      <RowFormat :elements="[
          {
            text: title,
            col: {'cols': 8, class: 'd-flex align-center'},
          },
          {
            text: value,
            col: {'cols': 4},
            style: {
              'font-size': '1.5rem'
            }
          }
        ]"
        :color="_textColor"
      >

      </RowFormat>
      <!-- <v-row>
        <v-col cols="12" sm="8" >
          <span :class="`text-${_textColor}`">
            {{ title }}
          </span>
        </v-col>
        <v-col cols="12" sm="4" >
          <span :class="`text-${_textColor}`">
            {{ value }}
          </span>
        </v-col>
      </v-row> -->
    </v-alert>
  </v-sheet>
</template>

<script>
import RowFormat from './RowFormat.vue'

export default {
  components: {
    RowFormat
  },
  props: {
    bgColor: {
      type: String,
      default: 'white'
    },
    color: {
      type: String,
      default: 'success'
    },
    textColor: {
      type: String,
      default: ''
    },
    title: {
      type: String,
      default: ''
    },
    value: {
      type: String,
      default: ''
    }
  },
  data () {
    return {

    }
  },
  computed: {
    _textColor () {
      // `this` points to the component instance
      return this.textColor !== '' ? this.textColor : this.color
    }
  }
}
</script>

<style lang="sass" >
  .v-callout
    border: unset
    &.v-alert.v-alert--border
        --v-border-opacity: 1
    &.v-alert--border-start
      .v-alert__border
        border-inline-start-width: 4px

</style>
