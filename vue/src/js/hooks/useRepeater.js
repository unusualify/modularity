// hooks/formatter .js
import { reactive, toRefs, computed, ref } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { transform, cloneDeep, filter, omit, find, isEmpty, map, reduce } from 'lodash-es'
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
  subtitle: {
    type: String
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
      }
    }
  },
  formRowAttribute: {
    type: Object,
    default () {
      return {
        noGutters: false,
        class: 'ml-12'
        // justify:'center',
        // align:'center'
      }
    }
  },
  noAddButton: {
    type: Boolean,
    default: false
  },
  addButtonText: {
    type: String,
    default () {
      return useI18n().t('ADD NEW')
    }
  },
  hasButtonLabel: {
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
  },
  noHeaders: {
    type: Boolean,
    default: false
  },
  isUnique: {
    type: Boolean,
    default: false
  },
  uniqueValue: {
    type: String,
    default: 'id'
  },
  uniqueField: {
    type: String,
    default: null
  },
  disableAddButton: {
    type: Boolean,
    default: true
  },
  formCol: {
    type: Object,
    default: () => {
      return { cols: 12 }
    }
  },
  idResetter: {
    type: String,
    default: null
  },
  noWaitSourceLoading: {
    type: Boolean,
    default: false
  }
})

// by convention, composable function names start with "use"
export default function useRepeater (props, context) {
  const store = useStore()

  const { invokeRuleGenerator } = useValidation(props, context)
  const inputHook = useInput(props, context)

  const rawSchema = ref(props.schema)

  const processedSchema = computed(() => {
    if (!props.noHeaders) {
      return reduce(cloneDeep(rawSchema.value ?? {}), (acc, input, name) => {
        acc[name] = omit(input, ['label'])

        return acc
      }, {})
    }

    return cloneDeep(rawSchema.value ?? {})
  })

  const { modelValue } = toRefs(props)

  const isUnique = props.isUnique
  const uniqueValue = props.uniqueValue
  const uniqueFilledValues = ref([])
  const uniqueField = computed(() => {
    if (isUnique && window.__isset(rawSchema.value) && Object.keys(rawSchema.value).length > 0) {
      return props.uniqueField ?? Object.values(rawSchema.value)[0].name
    }
    return null
  })
  const uniqueInput = computed(() => {
    if (isUnique && window.__isset(rawSchema.value) && Object.keys(rawSchema.value).length > 0) {
      return rawSchema.value[uniqueField.value]
    }
    return null
  })

  function namingRepeaterField (index, name) {
    const id = `repeater${inputHook.id.value}`

    let pattern = new RegExp(`^${id}\\[${index}\\]`)

    if(pattern.test(name)){
      return name
    }

    return `${id}[${index}][${name}]`
  }

  function hydrateRepeaterModel (item, index) {
    const model = getModel(processedSchema.value, item)
    const extraFields = {}

    if (props.draggable && !model[props.orderKey]) {
      extraFields[props.orderKey] = index + 1
    }

    return {
      ...(props.autoIdGenerator ? { id: index } : {}),
      ...transform(omit(model, []), (o, v, k) => {
        o[namingRepeaterField(index, k)] = v
      }),
      ...extraFields
    }
  }

  function hydrateRepeaterModels(model) {
    // return model; // Burayı açman gerekebilir.
    return model.map((item, i) => {
      return hydrateRepeaterModel(item, i)
    })
  }

  function hydrateSchemas (inputs) {
    const schemas = []

    inputs.forEach((item, i) => {
      const clonedSchema = cloneDeep(processedSchema.value)

      // remove the items selected at other repeats
      if (isUnique) {
        if (clonedSchema[uniqueField.value]) {
          const _model = parseRepeaterModel(inputs[i])
          const selfValue = _model[uniqueField.value]
          clonedSchema[uniqueField.value].items = uniqueInput.value.items.filter(item => !(uniqueFilledValues.value.includes(item[uniqueValue]) && selfValue !== item[uniqueValue]))
        }
      }

      const schema = invokeRuleGenerator(clonedSchema)

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

  function parseRepeaterModel (object, i) {
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

  function parseRepeaterModels (model) {
    return model.map((object, i) => {
      return parseRepeaterModel(object, i)
    })
  }

  const repeaterModels = computed({
    get: () => {
      if (isEmpty(rawSchema.value)) {
        return []
      }
      // let clonedSchema = cloneDeep(rawSchema.value)
      const initialValue = Array.isArray(modelValue.value) ? modelValue.value : []
      const initialRepeats = hydrateRepeaterModels(initialValue)


      if (props.min > 0 && initialValue.length < props.min) {
        const schema = invokeRuleGenerator(rawSchema.value)
        initialRepeats.push(hydrateRepeaterModel(getModel(schema), 1))
      }

      if (initialRepeats.length > 0) {
        const parsedInitialRepeats = parseRepeaterModels(initialRepeats).map(item => {
          const omitKeys = props.autoIdGenerator ? ['id'] : []
          return omit(item, omitKeys)
        })
        if (JSON.stringify(modelValue.value) !== JSON.stringify(parsedInitialRepeats)) {
          parsedInitialRepeats.forEach((item, index) => {
            modelValue.value[index] = item
          })
        }
      }

      if (isUnique) {
        uniqueFilledValues.value = reduce(cloneDeep(initialRepeats), (acc, _rawModel) => {
          const _model = parseRepeaterModel(_rawModel)
          if (uniqueField.value && _model[uniqueField.value]) {
            acc.push(_model[uniqueField.value])
          }
          return acc
        }, [])

      }

      return initialRepeats
    },
    set: (val, old) => {
      // working on only deleting a repeat
      inputHook.updateModelValue.value(parseRepeaterModels(val))
    }
  })

  const state = reactive({
    repeaterModels,
    repeaterSchemas: computed(() => hydrateSchemas(repeaterModels.value)),

    totalRepeats: computed(() => state.repeaterModels.length),
    hasRepeaterModels: computed(() => state.repeaterModels.length > 0),

    isRemainingAddible: computed(() => (!isUnique || (state.totalRepeats < uniqueInput.value.items.length && state.totalRepeats < uniqueInput.value.items.filter(item => item[uniqueValue] > 0).length))),
    isAddible: computed(() => ((props.max < 1) || state.totalRepeats < props.max) && state.isRemainingAddible),
    isDeletable: computed(() => (props.min < 1) || state.totalRepeats > props.min),
    addButtonIsActive: computed(() => !props.disableAddButton || state.isAddible),

    selectFieldSlots: computed(() => {
      const slotableSchemas = []

      filter(rawSchema.value, function (schema, key) {
        return Object.prototype.hasOwnProperty.call(schema, 'slots') && Object.keys(schema.slots).length > 0
      }).forEach((schema, index) => {
        const _schema = cloneDeep(schema)
        state.repeaterModels.forEach((input, i) => {
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

    headers: reduce(rawSchema.value ?? [], (acc, input) => {
      if(!['hidden'].includes(input.type)) {
        acc.push({
          title: input.label || __headline(input.name),
          col: input.col
        })
      }
      return acc
    }, []),
    addButtonContent: computed(() => {
      return props.addButtonText + (props.hasButtonLabel && __isset(props.singularLabel) ? ` ${props.singularLabel}` : '')
    }),
    hasSchemaInputSourceLoading: computed(() => {
      return Object.values(rawSchema.value).some(schema => Object.prototype.hasOwnProperty.call(schema, 'sourceLoading') && schema.sourceLoading === true)
    })
  })

  const methods = reactive({
    onUpdateRepeaterModel (value, index) {
      const newVal = parseRepeaterModel(value, index)

      if(modelValue.value[index] && JSON.stringify(modelValue.value[index]) !== JSON.stringify(newVal)) {

        if(props.idResetter && modelValue.value[index][props.idResetter] && modelValue.value[index][props.idResetter] !== newVal[props.idResetter]) {
          delete newVal['id']
        }
      }
      modelValue.value[index] = newVal
    },
    onUpdateRepeaterSchema (value, index) {
      const newSchema = parseRepeaterModel(value, index)
      const oldSchemaKeys = Object.keys(rawSchema.value)

      oldSchemaKeys.forEach(key => {
        rawSchema.value[key] = Object.assign({}, rawSchema.value[key], omit(newSchema[key], ['name']))
      })
    },
    onHoverContent (index) {

    },
    addRepeaterBlock: function () {
      if (state.isAddible) {
        const schema = invokeRuleGenerator(cloneDeep(rawSchema.value))
        modelValue.value.push(hydrateRepeaterModel(getModel(schema), state.totalRepeats))
      } else {
        let message = `You cannot add new item, because the number of elements should be at much ${props.max}`
        if (!state.isRemainingAddible) {
          message = `You cannot add new item, because there are no more items to add`
        }
        store.commit(ALERT.SET_ALERT, { message, variant: 'warning', location: 'top' })
      }
    },
    deleteRepeaterBlock: function (index) {
      if (state.isDeletable) {
        const newModel = parseRepeaterModels(state.repeaterModels)
        newModel.splice(index, 1)
        state.repeaterModels = hydrateRepeaterModels(newModel)
      } else {
        store.commit(ALERT.SET_ALERT, { message: `You cannot delete, because the number of elements should be at least ${props.min}`, variant: 'warning', location: 'top' })
      }
    },
    duplicateRepeaterBlock: function (index) {
      if (state.isAddible) {
        const newModel = parseRepeaterModels(state.repeaterModels)
        newModel.push(newModel[index])
        state.repeaterModels = hydrateRepeaterModels(newModel)
      } else {
        store.commit(ALERT.SET_ALERT, { message: `You cannot add new item, because the number of elements should be at much ${props.max}`, variant: 'warning', location: 'top' })
      }
    }
  })

  // expose managed state as return value
  return {
    ...toRefs(methods),
    ...toRefs(state),
    invokeRuleGenerator,
    ...inputHook
  }
}
