// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { reactive, toRefs, computed } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { transform, cloneDeep, filter, omit, find, isEmpty } from 'lodash'
import { getModel } from '@/utils/getFormData'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

import { makeInputProps } from '@/hooks/'

import {
  useValidation,
  useInput
} from '@/hooks'

import { ALERT } from '@/store/mutations'

export const makeRepeaterProps = propsFactory({
  ...makeInputProps(),
  modelValue: {
    type: Array,
    default: []
  },
  max: {
    type: Number,
    default: -1
  },
  min: {
    type: Number,
    default: -1
  },
  label: {
    type: String,
    default: ''
  },
  singularLabel: {
    type: String
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
  },
  buttonHasLabel: {
    type: Boolean,
    default: false
  },
  withGutter: {
    type: Boolean,
    default: false
  },
  autoIdGenerator: {
    type: Boolean,
    default: true
  }
})

// by convention, composable function names start with "use"
export default function useRepeater (props, context) {
  const store = useStore()

  const { invokeRuleGenerator } = useValidation(props, context)
  const inputHook = useInput(props, context)

  const { modelValue } = toRefs(props)

  function namingRepeaterField (index, name) {
    return `repeater${inputHook.id.value}[${index}][${name}]`
  }

  function hydrateRepeaterInput (item, index) {
    const model = getModel(props.schema, item)

    return {
      ...(props.autoIdGenerator ? { id: index } : {}),
      ...transform(omit(model, []), (o, v, k) => {
        o[namingRepeaterField(index, k)] = v
      })
    }
  }

  function hydrateRepeaterInputs (model) {
    // return model; // Burayı açman gerekebilir.
    return model.map((item, i) => {
      return hydrateRepeaterInput(item, i)
    })
  }

  function hydrateSchemas (inputs) {
    const schemas = []
    inputs.forEach((item, i) => {
      const schema = invokeRuleGenerator(cloneDeep(props.schema))
      schemas[i] = transform(schema, (schema, input, key) => {
        const _input = cloneDeep(input)
        const newName = namingRepeaterField(i, input.name)
        _input.name = newName
        schema[newName] = _input
      })

      Object.keys(schemas[i]).forEach(inputName => {
        const input = schemas[i][inputName]
        if (Object.prototype.hasOwnProperty.call(input, 'cascade')) {
          const cascadedName = namingRepeaterField(i, input.cascade)
          schemas[i][cascadedName][input.cascadeKey] = find(input.items, [input.itemValue, item[inputName]])?.schema ?? []
        }
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
    repeaterInputs: computed({
      get: () => {
        // return hydrateRepeaterInputs(modelValue.value ?? [])
        if (isEmpty(props.schema)) {
          return []
        }
        const initialRepeats = hydrateRepeaterInputs(Array.isArray(modelValue.value) ? modelValue.value : [])
        if (props.min > 0 && initialRepeats.length < props.min) {
          const schema = invokeRuleGenerator(cloneDeep(props.schema))
          initialRepeats.push(hydrateRepeaterInput(getModel(schema), 1))
        }

        return initialRepeats
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

    totalRepeats: computed(() => state.repeaterInputs.length),
    isAddible: computed(() => (props.max < 1) || state.totalRepeats < props.max),
    isDeletable: computed(() => (props.min < 1) || state.totalRepeats > props.min),
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
    }),

    addButtonContent: computed(() => {
      return props.addButtonText + (props.buttonHasLabel && __isset(props.singularLabel) ? ` ${props.singularLabel}` : '')
    })
  })

  const methods = reactive({
    onUpdateRepeaterInput (value, index) {
      modelValue.value[index] = parseRepeaterInput(value, index)
    },
    onHoverContent (index) {
      // __log('onHoverContent', index)
    },
    addRepeaterBlock: function () {
      if (state.isAddible) {
        const schema = invokeRuleGenerator(cloneDeep(props.schema))
        modelValue.value.push(hydrateRepeaterInput(getModel(schema), state.totalRepeats))
      } else {
        store.commit(ALERT.SET_ALERT, { message: `You cannot add new item, because the number of elements should be at much ${props.max}`, variant: 'warning', location: 'top' })
      }
    },
    deleteRepeaterBlock: function (index) {
      if (state.isDeletable) {
        const newModel = parseRepeaterInputs(state.repeaterInputs)
        newModel.splice(index, 1)
        state.repeaterInputs = hydrateRepeaterInputs(newModel)
      } else {
        store.commit(ALERT.SET_ALERT, { message: `You cannot delete, because the number of elements should be at least ${props.min}`, variant: 'warning', location: 'top' })
      }
    },
    duplicateRepeaterBlock: function (index) {
      if (state.isAddible) {
        const newModel = parseRepeaterInputs(state.repeaterInputs)
        newModel.push(newModel[index])
        state.repeaterInputs = hydrateRepeaterInputs(newModel)
      } else {
        store.commit(ALERT.SET_ALERT, { message: `You cannot add new item, because the number of elements should be at much ${props.max}`, variant: 'warning', location: 'top' })
      }
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
