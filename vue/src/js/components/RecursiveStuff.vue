<template>
  <template v-if="!configuration.tag && isTextable(configuration.elements)">
    {{ applyCasting(configuration.elements) }}
  </template>
  <template v-else-if="!configuration.tag && isArray(configuration.elements)">
    <component
      v-for="(element, index) in configuration.elements"
      :key="`tag-${level}-${index}`"
      :is="element.tag"
      v-bind="element.attributes"
    >
      {{ element.elements }}
    </component>
  </template>
  <component v-else-if="configuration.tag"
      :is="configuration.tag"
      v-bind="{
        ...filteredAttributes,
        ...bindAttributes,
        ...castedAttributes
      }"
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
import { computed, ref } from 'vue'
import { reduce, get, cloneDeep, isArray, isString, isNumber } from 'lodash-es'

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
        return {}
      }
    },
  },
  setup (props, context) {
    // const vFitGrid = resolveDirective('fit-grid')
    // const directives = [vFitGrid];
    // const directives = props.configuration.directives ? props.configuration.directives.map((v) => resolveDirective(v)) : []
    // __log(directives)
    // console.log(props.configuration.value);
    const FuncPattern = /^\{(.*)\}$/
    const CastPattern = /\$([\w|.|\_|\-]+)/

    const slots = computed(() => {
      // console.log(props.configuration);
      if(props.configuration.hasOwnProperty('slots'))
        return props.configuration.slots
      else
        return {}
    })

    function isTextable (value) {
      return isString(value) || isNumber(value)
    }
    // function isArray (value) {
    //   return Array.isArray(value, (value))
    // }

    function hasSlot(value) {
      return value.hasOwnProperty('slots');
    }

    function isObject (value) {
      return __isObject(value)
    }

    function applyCasting(value, funcs = []) {
      if(!window.__isString(value))
        return value

      if(FuncPattern.test(value)) {
        const matches = value.match(FuncPattern)
        const evalText = matches[1]

        let evalParts = evalText.split(' ').map((v) => {
          if(CastPattern.test(v)) {
            let evalPartMatches = v.match(CastPattern)
            let evalPart = evalPartMatches[1]

            let evalPartCastedValue = __data_get(props.bindData, evalPart, undefined)

            if(evalPartCastedValue !== undefined) {
              return evalPartCastedValue
            }
          }
          return v
        })

        try {
          return eval(evalParts.join(' '))
        } catch (e) {
          console.error(e)
        }

        // return eval(evalParts.join(' '))
      }

      if(CastPattern.test(value)) {
        const matches = value.match(CastPattern)

        if (matches) {
          let result = get(props.bindData, matches[1])

          if(result !== undefined) {
            try {
              funcs.forEach((func) => {
                if(window[func] && typeof window[func] === 'function')
                  result = window[func](result)
              })
            } catch (e) {
              console.error(e)
            }
          }
          return result
        }
      }

      return value
    }

    function castAttribute(object, key, value) {
      let _value = value
      let funcs = []

      if (isArray(value) && isString(value[0])) {
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

    const filteredAttributes = cloneDeep(props.configuration.attributes)

    const castedAttributes = computed(() => {
      const attrs = cloneDeep(props.configuration.attributes)
      return reduce(attrs, (o, v, k) => {
        if (!(isArray(v) || isString(v) || isObject(v))) {
          return o
        }

        castAttribute(o, k, v)

        return o
      }, {})
    })

    const bindAttributes = computed(() => {
      const bindData = props.bindData
      let boundAttributes = {}

      if(!!bindData && isObject(bindData)) {

        let configurationBindKeys = props.configuration.bind

        if (configurationBindKeys) {

          if(isString(configurationBindKeys)) {
            configurationBindKeys = [configurationBindKeys]
          }

          if(isArray(configurationBindKeys)) {

            if (!!bindData) {

              configurationBindKeys.forEach((key) => {
                let attributeName = key
                let matches = key.match(CastPattern)

                if(matches) {
                  attributeName = matches[1]
                }

                let boundAttribute
                if (Object.prototype.hasOwnProperty.call(bindData, attributeName)) {
                  boundAttribute = __data_get(bindData, attributeName, undefined)
                }else if (Object.prototype.hasOwnProperty.call(bindData, key)) {
                  boundAttribute = __data_get(bindData, key, undefined)
                }

                if(boundAttribute !== undefined) {
                  boundAttributes = {...boundAttributes, ...boundAttribute}
                }
              })
            }
          }

        }
      }

      return boundAttributes
    })

    // const stringValue = computed(() => {
    //   let bindData = props.bindData

    //   return props.configuration.elements
    // })
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
