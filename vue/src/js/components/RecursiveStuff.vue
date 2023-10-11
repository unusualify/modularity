<template>
  <component
    :is="configuration.tag"
    v-bind="{...configuration.attributes}"
    >
    <template v-if="isArray(configuration.elements)">
      <ue-recursive-stuff
        v-for="(_configuration, i) in configuration.elements"
        :key="`tag-${level}-${i}`"
        :level="level+1"
        :configuration="_configuration"
      />
    </template>
    <template v-else-if="isString(configuration.elements)">
      {{ configuration.elements }}
    </template>
  </component>
</template>

<script>
import { withDirectives, h, resolveDirective } from 'vue'

export default {

  props: {
    configuration: {
      type: Object,
      default () {
        return {}
      }
    },
    level: {
      type: Number,
      default: 0
    }
  },
  setup (props, context) {
    // const vFitGrid = resolveDirective('fit-grid')
    // const directives = [vFitGrid];
    // const directives = props.configuration.directives ? props.configuration.directives.map((v) => resolveDirective(v)) : []
    // __log(directives)

    function isString (value) {
      // __log('isString', value, __isString(value))
      return __isString(value)
    }
    function isArray (value) {
      // __log('isArray', value, Array.isArray(value))
      return Array.isArray(value, (value))
    }

    return {
      // directives: props.configuration.directives ? props.configuration.directives.map((v) => resolveDirective(v)) : [],
      isString,
      isArray
    }
  },
  data () {
    return {

    }
  },
  created () {
    // __log(this.configuration)
  }
}
</script>

<style lang="sass" scoped>

</style>
