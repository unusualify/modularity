// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { reactive, toRefs, computed, watch } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { transform, cloneDeep, filter } from 'lodash'
import { getModel } from '@/utils/getFormData'
import { useI18n } from 'vue-i18n'

import {
  useValidation,
  useInput
} from '@/hooks'

export const makeRepeaterProps = propsFactory({
  modelValue: {
    type: Array
  },
  label: {
    type: String,
    default: ''
  },
  schema: {
    type: Object,
    default: () => {}
  },
  rowAttribute: {
    type: Object,
    default () {
      return {
        noGutters: false,
        class: 'ml-theme'
        // justify:'center',
        // align:'center'
      }
    }
  },
  addButtonText: {
    type: String,
    default () {
      return useI18n().t('ADD NEW')
    }
  }
})

// by convention, composable function names start with "use"
export default function useRepeater (props, context) {
  const { invokeRuleGenerator } = useValidation(props, context)
  const inputHook = useInput(props, context)

  const { modelValue } = toRefs(props)

  function namingRepeaterField (index, name) {
    return `repeater${inputHook.id.value}[${index}][${name}]`
  }
  function hydrateRepeaterInput (item, index) {
    return {
      id: index,
      ...transform(item, (o, v, k) => {
        o[namingRepeaterField(index, k)] = v
      })
    }
  }
  function hydrateRepeaterInputs (model) {
    return model.map((item, i) => {
      return hydrateRepeaterInput(item, i)
    })
  }
  function hydrateSchemas (inputs) {
    const schemas = []

    inputs.forEach((item, i) => {
      // const schema = JSON.parse(JSON.stringify(this.schema))
      const schema = invokeRuleGenerator(cloneDeep(props.schema))
      schemas[i] = transform(schema, (o, v, k) => {
        const name = namingRepeaterField(i, v.name)
        v.name = name
        o[name] = v
      })
    })

    return schemas
  }
  function parseRepeaterInput (object, i) {
    // let pattern = /repeater${this.id}[(\w+)]/
    const pattern = /\[(.*?)\]/gi

    const extraFields = {}

    if (props.draggable) {
      extraFields[props.orderKey] = i + 1
    }

    return {
      ...transform(object, (o, v, k) => {
        const matches = k.match(pattern)
        if (matches) {
          const keys = matches.map(match => match.replace(pattern, '$1'))
          o[keys.pop()] = v
        }
      }),
      ...extraFields
    }
  }
  function parseRepeaterInputs (model) {
    return model.map((object, i) => {
      return parseRepeaterInput(object, i)
    })
  }

  const state = reactive({
    repeaterInputs_: hydrateRepeaterInputs(modelValue.value),
    repeaterInputs__: computed(() => hydrateRepeaterInputs(modelValue.value)),
    repeaterInputs: computed({
      get: () => {
        // __log('repeaterInputs getter')
        return hydrateRepeaterInputs(modelValue.value)
      },
      set: (val, old) => {
        __log(
          'repeaterInputs setter',
          val
          // state.repeaterInputs

        )
        inputHook.updateModelValue.value(parseRepeaterInputs(val))
      }
    }),
    // repeaterSchemas_: computed({
    //   get: () => {
    //     // __log('repeaterSchemas getter', hydrateSchemas(state.repeaterInputs))
    //     return hydrateSchemas(state.repeaterInputs)
    //   },
    //   set: (val, old) => {
    //     // __log('repeaterSchemas setter', value)
    //   }
    // }),
    repeaterSchemas: computed(() => hydrateSchemas(state.repeaterInputs)),
    selectFieldSlots: computed(() => {
      const slotableSchemas = []
      filter(props.schema, function (schema, key) {
        return Object.prototype.hasOwnProperty.call(schema, 'slots') && Object.keys(schema.slots).length > 0
      }).forEach((schema, index) => {
        const _schema = cloneDeep(schema)
        state.repeaterInputs.forEach((input, i) => {
          const element = []
          // [input_name, slot_name, slotObject]
          for (const name in _schema.slots) {
            element.push({
              inputName: namingRepeaterField(i, _schema.name),
              name,
              context: _schema.slots[name]
            })
          }
          slotableSchemas.push(element)
        })
      })
      return slotableSchemas
    })
  })

  const methods = reactive({
    onUpdateRepeaterInput (value, index) {
      modelValue.value[index] = parseRepeaterInput(value, index)
      // __log('onUpdateRepeaterInput', value, index)
    },

    onHoverContent (index) {
      // __log('onHoverContent', index)
    },
    addRepeaterBlock: function () {
      const schema = invokeRuleGenerator(cloneDeep(props.schema))

      const repeaterCount = state.repeaterInputs.length

      modelValue.value.push(hydrateRepeaterInput(getModel(schema), repeaterCount))
    },
    deleteRepeaterBlock: function (index) {
      const newModel = parseRepeaterInputs(state.repeaterInputs)

      newModel.splice(index, 1)

      state.repeaterInputs = hydrateRepeaterInputs(newModel)
    },
    duplicateRepeaterBlock: function (index) {
      const newModel = parseRepeaterInputs(state.repeaterInputs)

      newModel.push(newModel[index])

      state.repeaterInputs = hydrateRepeaterInputs(newModel)
    }
  })

  // watch(() => state.repeaterInputs, (newValue, oldValue) => {
  //   __log('repeaterInputs watch', newValue)
  //   // inputHook.updateModelValue.value(parseRepeaterInputs(newValue))
  // }, { deep: true })
  // watch(() => props.modelValue, (newValue, oldValue) => {
  //   __log('props.modelValue watch', props.modelValue, newValue)
  //   state.repeaterInputs = hydrateRepeaterInputs(newValue)
  // }, { deep: true })

  // expose managed state as return value
  return {
    // ...toRefs(_props),
    ...toRefs(methods),
    ...toRefs(state),
    invokeRuleGenerator,
    ...inputHook
  }
}