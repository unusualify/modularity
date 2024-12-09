<template>
  <template v-if="!configuration.tag && isTextable(configuration.elements)">
    {{ configuration.elements }}
  </template>
  <template v-else-if="!configuration.tag && isArray(configuration.elements)">

    <component
    v-for="(element, index) in configuration.elements"
    :is="element.tag"
    v-bind="element.attributes"
    >{{ element.elements }}</component>
  </template>
  <component
    v-else-if="configuration.tag"
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

    <template v-else-if="isTextable(configuration.elements)">
      {{ applyCasting(configuration.elements) }}
    </template>


      <template v-for="(slotConf,slotName) in slots"
        :key="`tag-${level}-slot-${slotName}`"
        v-slot:[`${slotName}`]>
        <!-- {{ $log(slotName, slotConf) }} -->
          <ue-recursive-stuff
            :level="level+1"
            :configuration="slotConf"
          />
      </template>
      <!-- <ue-recursive-stuff
        v-for="(_configuration, i) in configuration.slots"
        :key="`tag-${level}-${i}`"
        :level="level+1"
        :configuration="_configuration"
      /> -->


  </component>
</template>

<script>
import { reduce, get, cloneDeep } from 'lodash-es'
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
    },
  },
  setup (props, context) {
    // const vFitGrid = resolveDirective('fit-grid')
    // const directives = [vFitGrid];
    // const directives = props.configuration.directives ? props.configuration.directives.map((v) => resolveDirective(v)) : []
    // __log(directives)
    const slots = computed(() => {
      // console.log(props.configuration);
      if(props.configuration.hasOwnProperty('slots'))
        return props.configuration.slots
      else
        return {}
      })
    function isTextable (value) {
      return __isString(value) || __isNumber(value)
    }
    function isArray (value) {
      return Array.isArray(value, (value))
    }
    function hasSlot(value) {
      return value.hasOwnProperty('slots');
    }
    function isObject (value) {
      return __isObject(value)
    }
    function applyCasting(value, funcs = []) {
      const matches = value.match(castPattern)
      if (matches) {
        let result = get(props.bindData, matches[1])
        funcs.forEach((func) => {
          result = window[func](result)
        })
        return result
      }
      return null
    }
    function castAttribute(object, key, value) {
      let _value = value
      let funcs = []

      if (isArray(value) && __isString(value[0])) {
        _value = value.shift()
        funcs = value
      }

      if (isObject(value)) {
        // Do nothing
      } else {
        const result = applyCasting(_value, funcs)
        if (result) {
          object[key] = result
          delete filteredAttributes[key]
        }
      }
    }
    const castPattern = /\$([\w|.]+)/
    const filteredAttributes = cloneDeep(props.configuration.attributes)
    const castedAttributes = computed(() => {
      const attrs = cloneDeep(props.configuration.attributes)
      return reduce(attrs, (o, v, k) => {
        if (!(isArray(v) || __isString(v) || __isObject(v))) {
          return o
        }

        castAttribute(o, k, v)

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
            return __data_get(props.bindData, key)
          }
        }
      }

      return {}
    })

    return {
      // directives: props.configuration.directives ? props.configuration.directives.map((v) => resolveDirective(v)) : [],
      isTextable,
      isArray,
      applyCasting,
      hasSlot,
      filteredAttributes,
      castedAttributes,
      bindAttributes,
      slots
    }
  },
  data () {
    return {

    }
  },
  created () {
    // console.log(this.configuration)
  }
}
</script>

<style lang="sass" scoped>

</style>
