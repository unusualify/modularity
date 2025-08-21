<template>
  <template v-if="!configuration.tag && isTextable(castedElements)">
    {{ applyCasting(castedElements) }}
  </template>
  <template v-else-if="!configuration.tag && isArray(castedElements)">
    <component
      v-for="(element, index) in castedElements"
      :key="`tag-${level}-${index}`"
      :is="element.tag"
      v-bind="element.attributes"
    >
      {{ castObjectAttributes(element.elements, bindData) }}
    </component>
  </template>
  <!-- Use render function for components with directives -->
  <component v-else-if="configuration.tag && hasDirectives" :is="renderComponentWithDirectives" />
  <!-- Use template for components without directives -->
  <component v-else-if="configuration.tag"
      :is="configuration.tag"
      v-bind="{
        ...filteredAttributes,
        ...bindAttributes,
        ...castedAttributes
      }"
    >
    <template v-if="isArray(castedElements)">
      <ue-recursive-stuff
        v-for="(_configuration, i) in castedElements"
        :key="`tag-${level}-${i}`"
        :level="level+1"
        :configuration="_configuration"
        :bind-data="bindData ?? {}"
      />
    </template>
    <template v-if="isObject(castedElements)">
      <ue-recursive-stuff
        :key="`tag-${level}-${i}`"
        :level="level+1"
        :configuration="castObjectAttributes(configuration.elements, bindData)"
        :bind-data="bindData ?? {}"
      />
    </template>

    <template v-else-if="isTextable(castedElements)">
      {{ applyCasting(castedElements) }}
    </template>

    <template v-for="(slotConf,slotName) in slots"
      :key="`tag-${level}-slot-${slotName}`"
      v-slot:[`${slotName}`]="slotScope">
      <ue-recursive-stuff
        :level="level+1"
        :configuration="slotConf"
        :bind-data="{...bindData, ...slotScope}"
      />
    </template>
  </component>
</template>

<script>
import { computed, ref, onMounted, resolveDirective, withDirectives, h, getCurrentInstance, vShow } from 'vue'
import { reduce, get, cloneDeep, isArray, isString, isNumber } from 'lodash-es'

import { useCastAttributes } from '@/hooks'

export default {
  props: {
    configuration: {
      type: [Object, Array, String],
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
    const { castObjectAttributes } = useCastAttributes()
    const componentElement = ref(null)
    const instance = getCurrentInstance()

    const FuncPattern = /^\{(.*)\}$/
    const CastPattern = /\$([\w|.|\_|\-]+)/

    const slots = computed(() => {
      if(props.configuration.hasOwnProperty('slots'))
        return props.configuration.slots
      else
        return {}
    })

    function isTextable (value) {
      return isString(value) || isNumber(value)
    }

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
      const attributes = props.configuration.attributes ?? {}
      const attrs = cloneDeep(attributes)

      return castObjectAttributes(attrs, props.bindData)

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

    const castedElements = computed(() => {
      let elements = props.configuration.elements ?? ''

      if(!elements)
        return elements

      return castObjectAttributes(elements, props.bindData)
    })

    // Check if component has directives
    const hasDirectives = computed(() => {
      return props.configuration.directives && Object.keys(props.configuration.directives).length > 0
    })

    // Built-in Vue directives that don't need resolveDirective
    const builtInDirectives = {
      'html': true,
      'text': true,
      'show': true,
      'if': true,
      'else': true,
      'else-if': true,
      'for': true,
      'on': true,
      'bind': true,
      'model': true,
      'slot': true,
      'pre': true,
      'cloak': true,
      'once': true
    }

    // Get built-in directives that can be applied with withDirectives
    const builtInDirectivesForWithDirectives = computed(() => {
      if (!props.configuration.directives) return []

      const directives = []
      Object.entries(props.configuration.directives).forEach(([directiveName, value]) => {
        if (builtInDirectives.hasOwnProperty(directiveName)) {
          if (directiveName === 'html') {
            // For v-html, we need to create a custom directive-like object
            directives.push([{
              beforeMount(el, binding) {
                el.innerHTML = binding.value
              },
              updated(el, binding) {
                el.innerHTML = binding.value
              }
            }, applyCasting(value)])
          } else if (directiveName === 'text') {
            directives.push([{
              beforeMount(el, binding) {
                el.textContent = binding.value
              },
              updated(el, binding) {
                el.textContent = binding.value
              }
            }, applyCasting(value)])
          } else if (directiveName === 'show') {
            directives.push([vShow, applyCasting(value)])
          }
          // Add other built-in directives as needed
        }
      })
      return directives
    })

    // Get only custom directives for withDirectives
    const customDirectives = computed(() => {
      if (!props.configuration.directives) return []

      return Object.entries(props.configuration.directives)
        .filter(([directiveName]) => !builtInDirectives.hasOwnProperty(directiveName))
        .map(([directiveName, value]) => {

          try {
            const directive = resolveDirective(directiveName)
            let val = applyCasting(value)
            return [directive, val]
          } catch (error) {
            console.error(`Custom directive '${directiveName}' could not be resolved:`, error)
            return null
          }

        }).filter(Boolean)
    })

    // Combine all directives
    const allDirectives = computed(() => {
      return [...builtInDirectivesForWithDirectives.value, ...customDirectives.value]
    })

    // Separate built-in directives that should be handled as attributes (deprecated approach)
    const builtInDirectiveAttributes = computed(() => {
      return {} // We're now handling all directives properly with withDirectives
    })

    // Render component with directives using render function
    const renderComponentWithDirectives = () => {
      const componentAttributes = {
        ...filteredAttributes,
        ...bindAttributes.value,
        ...castedAttributes.value,
        ...builtInDirectiveAttributes.value
      }

      // Create child elements
      const children = []

      // Handle array elements
      if (isArray(castedElements.value)) {
        castedElements.value.forEach((_configuration, i) => {
          children.push(
            h('ue-recursive-stuff', {
              key: `tag-${props.level}-${i}`,
              level: props.level + 1,
              configuration: _configuration,
              'bind-data': props.bindData ?? {}
            })
          )
        })
      }
      // Handle object elements
      else if (isObject(castedElements.value)) {
        children.push(
          h('ue-recursive-stuff', {
            key: `tag-${props.level}-object`,
            level: props.level + 1,
            configuration: castObjectAttributes(props.configuration.elements, props.bindData),
            'bind-data': props.bindData ?? {}
          })
        )
      }
      // Handle text elements
      else if (isTextable(castedElements.value)) {
        children.push(applyCasting(castedElements.value))
      }

      // Handle slots
      const slotElements = {}
      Object.entries(slots.value).forEach(([slotName, slotConf]) => {
        slotElements[slotName] = (slotScope = {}) =>
          h('ue-recursive-stuff', {
            level: props.level + 1,
            configuration: slotConf,
            'bind-data': {...props.bindData, ...slotScope}
          })
      })

      // Create the base component
      const component = h(
        props.configuration.tag,
        componentAttributes,
        children.length > 0 ? children : slotElements
      )

      // Apply custom directives if they exist
      if (allDirectives.value.length > 0) {
        return withDirectives(component, allDirectives.value)
      }

      return component
    }

    return {
      isTextable,
      isArray,
      isObject,

      applyCasting,
      castObjectAttributes,
      hasSlot,
      filteredAttributes,
      castedAttributes,
      castedElements,
      bindAttributes,
      slots,
      componentElement,
      hasDirectives,
      renderComponentWithDirectives
    }
  },
  data () {
    return {

    }
  },
  created () {
  }
}
</script>

<style lang="sass" scoped>

</style>
