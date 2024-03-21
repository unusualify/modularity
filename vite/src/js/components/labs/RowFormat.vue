<template>
  <v-row>
    <v-col v-for="(element,i) in elements" :key="i" v-bind="element.col" >
      <label
        :class="[`${textColor}`, element.class]"
        :style="element.style">
        {{ element.text }}
      </label>
    </v-col>
  </v-row>
</template>

<script >

export default {
  props: {
    elements: {
      type: Array,
      default () {
        return []
      }
    },
    color: {
      type: String,
      default: ''
    }

  },
  data () {
    return {
      defaultCols: [

      ]
    }
  },
  computed: {
    textColor () {
      // `this` points to the component instance
      return this.color !== '' ? `text-${this.color}` : ''
    }
  }
}
</script>

<style lang="sass" scoped>

</style>
