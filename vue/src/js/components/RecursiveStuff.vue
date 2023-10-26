<template>
  <component
    :is="configuration.tag"
    v-bind="{...filteredAttributes, ...bindAttributes, ...castedAttributes}"
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
import { reduce, get, cloneDeep } from 'lodash'
import { computed, ref } from 'vue'

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
    },
    bindData: {
      type: Object,
      default () {
        return {

        }
      }
    }
  },
  setup (props, context) {
    // const vFitGrid = resolveDirective('fit-grid')
    // const directives = [vFitGrid];
    // const directives = props.configuration.directives ? props.configuration.directives.map((v) => resolveDirective(v)) : []
    // __log(directives)

    function isString (value) {
      return __isString(value)
    }
    function isArray (value) {
      return Array.isArray(value, (value))
    }

    const castPattern = /\$([\w|.]+)/
    const filteredAttributes = cloneDeep(props.configuration.attributes)

    const castedAttributes = computed(() => {
      const attrs = cloneDeep(props.configuration.attributes)
      return reduce(attrs, (o, v, k) => {
        if (!(isArray(v) || isString(v))) {
          return o
        }

        let value = v
        let funcs = []

        if (isArray(v) && isString(v[0])) {
          value = v.shift()
          funcs = v
        }

        const matches = value.match(castPattern)

        if (matches) {
          let result = get(props.bindData, matches[1])
          funcs.forEach((func) => {
            result = global[func](result)
          })

          if (result) {
            o[k] = result
            delete filteredAttributes[k]
          }
        }

        return o
      }, {})
    })

    const bindAttributes = computed(() => {
      const bindKey = props.configuration.bind
      if (bindKey) {
        const matches = bindKey.match(castPattern)

        if (matches && !!props.bindData) {
          const key = matches[1]
          if (Object.prototype.hasOwnProperty.call(props.bindData, key)) {
            return props.bindData[key]
          }
        }
      }

      return {}
    })

    return {
      // directives: props.configuration.directives ? props.configuration.directives.map((v) => resolveDirective(v)) : [],
      isString,
      isArray,
      filteredAttributes,
      castedAttributes,
      bindAttributes
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
