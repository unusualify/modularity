// hooks/useRepeater.js
import { reactive, toRefs, computed, ref, watch } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { transform, cloneDeep, filter, omit, find, isEmpty, map, reduce, isArray, isObject, isEqual } from 'lodash-es'
import { getModel } from '@/utils/getFormData'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

import { makeInputProps } from '@/hooks/'

import {
  useValidation,
  useInput
} from '@/hooks'

import { isset } from '@/utils/helpers'
import { ALERT } from '@/store/mutations'

export const makeRepeaterProps = propsFactory({
  ...makeInputProps(),
  modelValue: {
    // The component natively handles both shapes: when `asObject:true`
    // (and `isUnique` + `uniqueField` are set) `useRepeater` keys the
    // model by `uniqueField` (see lines 167, 210, 226, 228), so the
    // parent legitimately passes an Object — e.g. PaymentCurrency's
    // `default_vat_rates` field. The validator was Array-only,
    // tripping a Vue warn for that exact case.
    type: [Array, Object],
    default: () => []
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
  },
  asObject: {
    type: Boolean,
    default: false
  },
  collapsible: {
    type: Boolean,
    default: false
  },
  collapsibleTitleField: {
    type: String,
    default: null
  },
  collapsibleDefaultOpen: {
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
    if (isUnique && isset(rawSchema.value) && Object.keys(rawSchema.value).length > 0) {
      return props.uniqueField ?? Object.values(rawSchema.value)[0].name
    }
    return null
  })

  const uniqueInput = computed(() => {
    if (isUnique && isset(rawSchema.value) && Object.keys(rawSchema.value).length > 0) {
      return rawSchema.value[uniqueField.value]
    }
    return null
  })

  const asObject = props.isUnique && props.uniqueField && props.asObject

  const uniqueInputItems = computed(() => {
    let uniqueInputValueType = uniqueInput.value.itemValueType ?? 'integer';

    return (uniqueInput.value?.items ?? []).filter((item) => {
      if(uniqueInputValueType === 'integer') {
        return item[uniqueValue] !== null && item[uniqueValue] > 0
      } else if(uniqueInputValueType === 'string') {
        return item[uniqueValue] !== null && item[uniqueValue] !== ''
      } else {
        return item[uniqueValue] !== null
      }
    })
  })

  const availableUniqueInputItems = computed(() => {
    return uniqueInputItems.value.filter((item) => !uniqueFilledValues.value.includes(item[uniqueValue]))
  })

  const usedUniqueInputItems = computed(() => {
    return uniqueInputItems.value.filter((item) => uniqueFilledValues.value.includes(item[uniqueValue]))
  })

  const uniqueInputValidItemsLength = computed(() => {
    return uniqueInputItems.value.length
  })

  function namingRepeaterField (index, name) {
    const id = `repeater${inputHook.id.value}`

    let pattern = new RegExp(`^${id}\\[${index}\\]`)

    if(pattern.test(name)){
      return name
    }

    return `${id}[${index}][${name}]`
  }

  function flattenModel (model) {
    let values = []

    if (asObject && isObject(model)) {
      Object.keys(model).forEach(key => {
        let value = model[key]
        values.push({
          [props.uniqueField]: key,
          ...value
        })
      })
    } else if (isArray(model)) {
      values = model
    }

    return values
  }

  function roughenModel (model) {
    let values = asObject ? {} : model

    if (asObject && isArray(model)) {
      values = model.reduce((acc, item) => {
        acc[item[props.uniqueField]] = omit(item, [props.uniqueField])

        return acc
      }, {})
    }

    return values
  }

  const REPEATER_UID_KEY = '_repeaterUid'

  function getSchemaFieldNames () {
    return new Set(
      Object.values(processedSchema.value ?? {})
        .map((input) => input?.name)
        .filter(Boolean)
    )
  }

  function getOrderBase (items) {
    if (!props.orderKey || !isArray(items) || items.length === 0) {
      return 1
    }

    const nums = items
      .map((item) => Number(item?.[props.orderKey]))
      .filter((n) => !Number.isNaN(n))

    return nums.includes(0) ? 0 : 1
  }

  function sanitizeRepeaterItem (item) {
    if (!isObject(item) || isArray(item)) {
      return item
    }

    const omitKeys = [REPEATER_UID_KEY]

    if (props.autoIdGenerator || item.id === undefined || item.id === null) {
      omitKeys.push('id')
    }

    return omit(item, omitKeys)
  }

  function collectPassthroughFields (item) {
    if (!isObject(item) || isArray(item)) {
      return {}
    }

    const schemaFieldNames = getSchemaFieldNames()
    const passthrough = {}

    Object.keys(item).forEach((key) => {
      if (schemaFieldNames.has(key)) {
        return
      }

      if (key === REPEATER_UID_KEY) {
        passthrough[key] = item[key]
        return
      }

      if (key === 'id') {
        if (!props.autoIdGenerator && item[key] !== undefined) {
          passthrough[key] = item[key]
        }
        return
      }

      if (item[key] === undefined) {
        return
      }

      passthrough[key] = item[key]
    })

    return passthrough
  }

  function itemContentFingerprint (item) {
    return omit(sanitizeRepeaterItem(item), props.orderKey ? [props.orderKey] : [])
  }

  function isReorderPermutation (parsedItems, currentItems) {
    if (!isArray(parsedItems) || !isArray(currentItems) || parsedItems.length !== currentItems.length || parsedItems.length === 0) {
      return false
    }

    const parsedFingerprints = parsedItems.map(itemContentFingerprint)
    const currentFingerprints = currentItems.map(itemContentFingerprint)

    if (isEqual(parsedFingerprints, currentFingerprints)) {
      return false
    }

    const sortKey = (value) => JSON.stringify(value)

    return isEqual(
      [...parsedFingerprints].sort((a, b) => sortKey(a).localeCompare(sortKey(b))),
      [...currentFingerprints].sort((a, b) => sortKey(a).localeCompare(sortKey(b)))
    )
  }

  function hydrateRepeaterModel (item, index) {
    const model = getModel(processedSchema.value, item)
    const passthrough = collectPassthroughFields(item)
    const extraFields = {}

    if (props.draggable && props.orderKey) {
      const existingOrder = passthrough[props.orderKey] ?? item?.[props.orderKey] ?? model[props.orderKey]

      if (existingOrder === undefined || existingOrder === null || existingOrder === '') {
        extraFields[props.orderKey] = index + 1
      } else if (passthrough[props.orderKey] === undefined) {
        extraFields[props.orderKey] = existingOrder
      }
    }

    if (!props.autoIdGenerator && !passthrough[REPEATER_UID_KEY]) {
      extraFields[REPEATER_UID_KEY] = `repeater-${inputHook.id.value}-${index}`
    }

    return {
      ...(props.autoIdGenerator ? { id: index } : {}),
      ...transform(omit(model, []), (o, v, k) => {
        o[namingRepeaterField(index, k)] = v
      }),
      ...passthrough,
      ...extraFields
    }
  }

  function hydrateRepeaterModels(model) {
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
          clonedSchema[uniqueField.value].items = uniqueInputItems.value.filter(item => !(uniqueFilledValues.value.includes(item[uniqueValue]) && selfValue !== item[uniqueValue]))
          clonedSchema[uniqueField.value].clearable = false
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

    const parsed = transform(object ?? {}, (o, v, k) => {
      const matches = typeof k === 'string' ? k.match(pattern) : null

      if (matches) {
        const keys = matches.map(match => match.replace(pattern, '$1'))
        o[keys.pop()] = v
        return
      }

      // Preserve bare passthrough keys (order, position, real id, uid, …)
      if (k === 'id' && (props.autoIdGenerator || v === undefined)) {
        return
      }

      if (v === undefined) {
        return
      }

      o[k] = v
    })

    if (props.draggable && props.orderKey) {
      const existingOrder = parsed[props.orderKey]

      if (existingOrder === undefined || existingOrder === null || existingOrder === '') {
        parsed[props.orderKey] = i + 1
      }
    }

    return sanitizeRepeaterItem(parsed)
  }

  function parseRepeaterModels (model) {

    return model.map((object, i) => {
      return parseRepeaterModel(object, i)
    })
  }

  function getInitialRepeaterModels() {
    if (isEmpty(rawSchema.value)) {
      return []
    }

    const initialValue = flattenModel(modelValue.value)
    const initialRepeats = hydrateRepeaterModels(initialValue)

    if (props.min > 0 && initialValue.length < props.min) {
      const schema = invokeRuleGenerator(rawSchema.value)
      initialRepeats.push(hydrateRepeaterModel(getModel(schema), 1))
    }

    if (initialRepeats.length > 0) {
      const parsedInitialRepeats = parseRepeaterModels(initialRepeats).map(sanitizeRepeaterItem)

      if (!isEqual(initialValue, parsedInitialRepeats)) {
        inputHook.updateModelValue.value(roughenModel(parsedInitialRepeats))
      }
    }

    if (isUnique) {
      uniqueFilledValues.value = reduce(cloneDeep(initialRepeats), (acc, _rawModel) => {
        const _model = parseRepeaterModel(_rawModel)
        let uniqueInputValueType = uniqueInput.value.itemValueType ?? 'integer';

        if (uniqueField.value && _model[uniqueField.value]) {
          let value = _model[uniqueField.value]
          if(uniqueInputValueType === 'integer') {
            if(!isNaN(value) && value > 0) {
              acc.push(value)
            }
          } else if(uniqueInputValueType === 'string') {
            if(value !== null && value !== '') {
              acc.push(value)
            }
          } else {
            if(value !== null) {
              acc.push(value)
            }
          }
        }

        return acc
      }, [])
    }

    return initialRepeats
  }

  const repeaterModels = ref(getInitialRepeaterModels())
  const openedPanels = ref([])

  function getPanelValue (model, index) {
    return props.autoIdGenerator ? (model?.id ?? index) : (model?.[REPEATER_UID_KEY] ?? index)
  }

  function getRepeaterDomKey (model, index) {
    if (props.autoIdGenerator) {
      return model?.id ?? index
    }

    return model?.[REPEATER_UID_KEY] ?? index
  }

  const draggableItemKey = computed(() => {
    if (props.autoIdGenerator) {
      return 'id'
    }

    return (item) => item?.[REPEATER_UID_KEY]
  })

  function syncOpenedPanels (models) {
    if (!props.collapsible) {
      return
    }

    const panelValues = models.map((model, index) => getPanelValue(model, index))
    openedPanels.value = openedPanels.value.filter(value => panelValues.includes(value))

    if (props.collapsibleDefaultOpen && openedPanels.value.length === 0 && panelValues.length > 0) {
      openedPanels.value = [...panelValues]
    }
  }

  function openPanel (model, index) {
    const value = getPanelValue(model, index)

    if (!openedPanels.value.includes(value)) {
      openedPanels.value = [...openedPanels.value, value]
    }
  }

  function togglePanel (model, index, open) {
    const value = getPanelValue(model, index)

    if (open) {
      openPanel(model, index)
      return
    }

    openedPanels.value = openedPanels.value.filter(panelValue => panelValue !== value)
  }

  function getRepeaterItemTitle (index) {
    const items = parseRepeaterModels(repeaterModels.value)
    const item = items[index]

    if (!item) {
      return ''
    }

    if (props.collapsibleTitleField && item[props.collapsibleTitleField]) {
      return String(item[props.collapsibleTitleField])
    }

    const schemaKeys = Object.keys(rawSchema.value ?? {})

    for (const key of schemaKeys) {
      const fieldName = rawSchema.value[key]?.name ?? key

      if (item[fieldName]) {
        return String(item[fieldName])
      }
    }

    const label = props.singularLabel || props.label || 'Item'

    return `${label} ${index + 1}`
  }

  syncOpenedPanels(repeaterModels.value)

  watch(() => modelValue.value, (newVal, oldVal) => {
    if (!isEqual(newVal, oldVal)) {
      repeaterModels.value = getInitialRepeaterModels()
      syncOpenedPanels(repeaterModels.value)
    }
  }, {
    deep: true
  })

  watch(() => repeaterModels.value, (newVal) => {
    let parsedItems = parseRepeaterModels(newVal).map(sanitizeRepeaterItem)
    const currentItems = flattenModel(modelValue.value)

    if (props.draggable && props.orderKey) {
      if (isReorderPermutation(parsedItems, currentItems)) {
        const base = getOrderBase(currentItems)
        parsedItems = parsedItems.map((item, index) => ({
          ...item,
          [props.orderKey]: index + base
        }))
      } else {
        parsedItems = parsedItems.map((item, index) => {
          if (item[props.orderKey] === undefined || item[props.orderKey] === null || item[props.orderKey] === '') {
            return {
              ...item,
              [props.orderKey]: currentItems[index]?.[props.orderKey] ?? (index + getOrderBase(currentItems))
            }
          }

          return item
        })
      }
    }

    if (isUnique) {
      uniqueFilledValues.value = reduce(parsedItems, (acc, _rawModel) => {
        // const _model = parseRepeaterModel(_rawModel)
        const _model = _rawModel

        let uniqueInputValueType = uniqueInput.value.itemValueType ?? 'integer';

        if (uniqueField.value && _model[uniqueField.value]) {
          let value = _model[uniqueField.value]
          if(uniqueInputValueType === 'integer') {
            if(!isNaN(value) && value > 0) {
              acc.push(value)
            }
          } else if(uniqueInputValueType === 'string') {
            if(value !== null && value !== '') {
              acc.push(value)
            }
          } else {
            if(value !== null) {
              acc.push(value)
            }
          }
        }

        return acc
      }, [])
    }

    const nextValue = roughenModel(parsedItems)

    if (!isEqual(nextValue, modelValue.value)) {
      inputHook.updateModelValue.value(nextValue)
    }
  }, {
    deep: true
  })

  const state = reactive({
    repeaterModels,
    repeaterSchemas: computed(() => hydrateSchemas(repeaterModels.value)),

    totalRepeats: computed(() => state.repeaterModels.length),
    hasRepeaterModels: computed(() => state.repeaterModels.length > 0),

    isRemainingAddible: computed(() => (!isUnique
      || (state.totalRepeats < uniqueInput.value.items.length
        && state.totalRepeats < uniqueInputValidItemsLength.value))
      ),
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
      const prev = state.repeaterModels[index] ?? {}
      const bracketPattern = /\[[^\]]+\]/
      const preserved = {}

      Object.keys(prev).forEach((key) => {
        if (bracketPattern.test(key)) {
          return
        }

        if (key === 'id' && props.autoIdGenerator) {
          preserved[key] = prev[key]
          return
        }

        if (prev[key] === undefined) {
          return
        }

        preserved[key] = prev[key]
      })

      const merged = {
        ...preserved,
        ...value,
        ...(props.autoIdGenerator ? { id: prev.id ?? index } : {})
      }

      const newVal = parseRepeaterModel(merged, index)
      const flattenedModel = flattenModel(modelValue.value)

      if(flattenedModel[index] && !isEqual(sanitizeRepeaterItem(flattenedModel[index]), newVal)) {
        if(props.idResetter && flattenedModel[index][props.idResetter] && flattenedModel[index][props.idResetter] !== newVal[props.idResetter]) {
          delete merged.id
          delete newVal.id
        }
      }

      state.repeaterModels[index] = merged
    },
    onUpdateRepeaterSchema (value, index) {
      const newSchema = parseRepeaterModel(value, index)
      const oldSchemaKeys = Object.keys(rawSchema.value)

      oldSchemaKeys.forEach(key => {
        rawSchema.value[key] = Object.assign({}, rawSchema.value[key], omit(newSchema[key], ['name']))
      })
    },
    onHoverContent (index) {},
    addRepeaterBlock: function () {
      if (state.isAddible) {
        const schema = invokeRuleGenerator(cloneDeep(rawSchema.value))
        const value = getModel(schema)

        if(isUnique) {
          value[props.uniqueField] = availableUniqueInputItems.value[0][uniqueValue] ?? null;
        }
        const newModel = hydrateRepeaterModel(value, state.totalRepeats)
        const models = cloneDeep(state.repeaterModels)

        models.push(newModel)
        state.repeaterModels = models
        openPanel(newModel, state.totalRepeats)
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
        syncOpenedPanels(state.repeaterModels)
      } else {
        store.commit(ALERT.SET_ALERT, { message: `You cannot delete, because the number of elements should be at least ${props.min}`, variant: 'warning', location: 'top' })
      }
    },
    duplicateRepeaterBlock: function (index) {
      if (state.isAddible) {
        const newModel = parseRepeaterModels(state.repeaterModels)
        newModel.push(newModel[index])
        state.repeaterModels = hydrateRepeaterModels(newModel)
        openPanel(state.repeaterModels[state.repeaterModels.length - 1], state.repeaterModels.length - 1)
      } else {
        store.commit(ALERT.SET_ALERT, { message: `You cannot add new item, because the number of elements should be at much ${props.max}`, variant: 'warning', location: 'top' })
      }
    }
  })

  // expose managed state as return value
  return {
    ...toRefs(methods),
    ...toRefs(state),
    openedPanels,
    getPanelValue,
    getRepeaterDomKey,
    draggableItemKey,
    getRepeaterItemTitle,
    togglePanel,
    invokeRuleGenerator,
    ...inputHook
  }
}
