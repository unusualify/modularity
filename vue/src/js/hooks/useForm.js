// hooks/useForm.js
import { ref, computed, watch, toRefs, reactive, nextTick } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { cloneDeep, isEqual, find, reduce, set, get } from 'lodash-es'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { useInputHandlers, useValidation, useLocale, useItemActions, useAuthorization } from '@/hooks'
import { FORM, ALERT } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'
import api from '@/store/api/form'
import { getModel, getSubmitFormData, getSchema, getFormEventSchema, } from '@/utils/getFormData.js'
import { handleEvents } from '@/utils/formEvents'
import { getTranslationInputsCount, processInputs } from '@/utils/schema.js'

import { redirector } from '@/utils/response'

export const makeFormProps = propsFactory({
  modelValue: {
    type: Object,
    // default () {
    //   return {}
    // }
  },
  formClass: {
    type: [Array, String],
    default: ''
  },
  actionUrl: {
    type: String
  },
  title: {
    type: [String, Object]
  },
  noTitle: {
    type: Boolean,
    default: false
  },
  subtitle: {
    type: String,
  },
  schema: {
    type: Object,
    // default () {
    //   return {}
    // }
  },
  async: {
    type: Boolean,
    default: true
  },
  buttonText: {
    type: String
  },
  hasSubmit: {
    type: Boolean,
    default: false
  },
  stickyFrame: {
    type: Boolean,
    default: false
  },
  stickyButton: {
    type: Boolean,
    default: false
  },
  rowAttribute: {
    type: Object,
    default () {
      return {
        noGutters: false,
        class: 'py-4',
        // justify:'center',
        // align:'center'
      }
    }
  },
  slots: {
    type: Object,
    default () {
      return {}
    }
  },
  valid: {
    type: Boolean,
    default: null
  },
  isEditing: {
    type: Boolean,
    default: false
  },
  hasDivider: {
    type: Boolean,
    default: false
  },
  fillHeight: {
    type: Boolean,
    default: false
  },
  scrollable: {
    type: Boolean,
    default: false
  },
  noDefaultFormPadding: {
    type: Boolean,
    default: false
  },
  noDefaultSurface: {
    type: Boolean,
    default: false
  },
  actions: {
    type: [Array, Object],
    default: []
  },
  actionsPosition: {
    type: String,
    default: 'top',
    validator(value) {
      return ['title-right', 'title-center', 'top', 'middle', 'bottom', 'right-top', 'right-middle', 'right-bottom'].includes(value)
    }
  },
  rightSlotGap: {
    type: Number,
    default: 12
  },
  pushButtonToBottom: {
    type: Boolean,
    default: false
  },
  rightSlotWidth: {
    type: [Number, String],
    default: null
  },
  rightSlotMinWidth: {
    type: Number,
    default: 300
  },
  rightSlotMaxWidth: {
    type: Number,
    default: 600
  },
  additionalSectionDialogTitle: {
    type: String,
    default: 'Additional Options'
  },

  clearOnSaved: {
    type: Boolean,
    default: false
  },
  refreshOnSaved: {
    type: Boolean,
    default: false
  },
  noWaitSourceLoading: {
    type: Boolean,
    default: false
  }
})

export default function useForm(props, context) {
  const store = useStore()
  const { t, te } = useI18n({ useScope: 'global' })

  // Composables
  const inputHandlers = useInputHandlers()
  const validations = useValidation(props)
  const locale = useLocale()
  const { hasRoles } = useAuthorization()

  // Data refs
  const VForm = ref(null)
  const id = Math.ceil(Math.random() * 1000000) + '-form'
  const formBaseId = id + '-base'

  // const issetModel = ref(props.modelValue ? Object.keys(props.modelValue).length > 0 : false)
  // const issetSchema = ref(props.schema ? Object.keys(props.schema).length > 0 : false)
  const issetModel = ref(props.modelValue ? true : false)
  const issetSchema = ref(props.schema ? true : false)

  const formLoading = ref(false)
  const formErrors = ref({})

  const rawSchema = ref(issetSchema.value
    ? props.schema
    : store.state.form.inputs)

  const chunkedRawSchema = computed(() => processInputs(rawSchema.value))

  const defaultItem = ref(issetSchema.value
    ? getModel(rawSchema.value)
    : store.getters.defaultItem)

  const storeEditedItem = computed(() => store.state.form.editedItem)

  const model = ref(getModel(
    rawSchema.value,
    issetModel.value ? props.modelValue : storeEditedItem.value,
    store.state,
  ))

  const formItem = computed(() => issetModel.value ? props.modelValue : storeEditedItem.value)

  const inputSchema = ref(validations.invokeRuleGenerator(getSchema(rawSchema.value, { ...model.value, ...formItem.value }, props.isEditing)))
  const formEventSchema = ref(getFormEventSchema(rawSchema.value, { ...model.value, ...formItem.value }, props.isEditing))
  const extraValids = ref(props.actions.length ? props.actions.map(() => true) : [])

  const checkSubmittable = (item) => {
    let result = false

    if(!(Object.prototype.hasOwnProperty.call(item, 'noSubmit') && item.noSubmit)) {
      result = true
    }

    if(result === true) {
      if(__isset(item.editable)
        && (item.editable === false || item.editable === 'hidden')
        && props.isEditing) {
        result = false
      }else if(__isset(item.creatable)
          && (item.creatable === false || item.creatable === 'hidden')
          && !props.isEditing) {
        result = false
      }else if( Object.prototype.hasOwnProperty.call(item, 'allowedRoles') && Array.isArray(item.allowedRoles)) {
        if(!hasRoles(item.allowedRoles)) {
          result = false
        }
      }
    }

    return result
  }

  const isSubmittable = computed(() => {
    return find(inputSchema.value, (input) => {
      let result = false

      if(['wrap', 'group'].includes(input.type)) {
        result = find(input.schema, checkSubmittable)
      } else {
        result = checkSubmittable(input)
      }
      return result
    }) || find(formEventSchema.value, checkSubmittable) ? true : false
  })

  const hasAdditionalSection = computed(() => context.slots.right
    || context.slots['right.top']
    || context.slots['right.bottom']
    || context.slots['right.middle']
    || ['right-top', 'right-middle', 'right-bottom'].includes(props.actionsPosition)
  )

  const states = reactive({
    id,
    formBaseId,
    VForm,

    issetModel,
    issetSchema,

    model,
    // storeEditedItem,
    formItem,

    chunkedRawSchema,
    inputSchema,
    formEventSchema,
    isSubmittable,
    extraValids,

    hasStickyFrame: props.stickyFrame || props.stickyButton,
    // formLoading: false,
    // formErrors: {},
    manualValidation: false,
    buttonDefaultText: computed(() =>
      props.buttonText ? (te(props.buttonText) ? t(props.buttonText) : props.buttonText) : t('submit')
    ),
    // editedItem: computed(() => store.state.form.editedItem),
    serverValid: computed(() => store.state.form.serverValid ?? true),
    loading: computed(() =>
      props.actionUrl ? formLoading.value : store.state.form.loading
    ),
    errors: computed(() =>
      props.actionUrl ? formErrors.value : store.state.form.errors
    ),
    reference: computed(() => 'ref-' + states.id),
    hasTraslationInputs: computed(() => getTranslationInputsCount(inputSchema.value) > 0),

    hasAdditionalSection,

    showAdditionalSectionDialog: false,

    hasSchemaInputSourceLoading: computed(() => {
      return Object.values(rawSchema.value).some(schema => Object.prototype.hasOwnProperty.call(schema, 'sourceLoading') && schema.sourceLoading === true
        || (Object.prototype.hasOwnProperty.call(schema, 'type') && (schema.type === 'wrap' || schema.type === 'group') && Object.values(schema.schema).some(schema => Object.prototype.hasOwnProperty.call(schema, 'sourceLoading') && schema.sourceLoading === true)))
    })
  })
  // Methods

  const createSchema = (schema, modelValue) => {
    return validations.invokeRuleGenerator(getSchema(schema, modelValue, true))
  }

  const saveForm = (callback = null, errorCallback = null) => {

    if (props.actionUrl) {
      formErrors.value = {}
      formLoading.value = true

      const formData = getSubmitFormData(rawSchema.value, states.model, store._state.data)
      const method = Object.prototype.hasOwnProperty.call(formData, 'id') ? 'put' : 'post'

      api[method](props.actionUrl, formData,
        (response) => {
          formLoading.value = false
          if (Object.prototype.hasOwnProperty.call(response.data, 'errors')) {
            store.commit(FORM.SET_SERVER_VALID, false)
            formErrors.value = response.data.errors
          } else if (Object.prototype.hasOwnProperty.call(response.data, 'variant')) {
            store.commit(FORM.SET_SERVER_VALID, false)
            store.commit(ALERT.SET_ALERT, { message: response.data.message, variant: response.data.variant })
          }

          if(props.clearOnSaved) {
            states.model = getModel(rawSchema.value)
            resetValidation()
            VForm.value && VForm.value.reset()
          }

          context.emit('submitted', response.data)

          let callbackFunction = callback

          if(!props.refreshOnSaved || (Object.prototype.hasOwnProperty.call(response.data, 'forceRedirect') && response.data.forceRedirect)) {
            redirector(response.data)
          } else {
            let __reload = () => {
              window.location.reload(true)
            }
            callbackFunction = (data) => {
              if(callback && typeof callback === 'function') callback(data)
              __reload()
            }
          }
          if (callbackFunction && typeof callbackFunction === 'function') callbackFunction(response.data)
        },
        (response) => {
          formLoading.value = false
          if (Object.prototype.hasOwnProperty.call(response.data, 'exception')) {
            store.commit(ALERT.SET_ALERT, { message: 'Your submission could not be processed.', variant: 'error' })
          } else {
            store.dispatch(ACTIONS.HANDLE_ERRORS, response.response.data)
            store.commit(ALERT.SET_ALERT, { message: 'Your submission could not be validated, please fix and retry', variant: 'error' })
          }

          if (errorCallback && typeof errorCallback === 'function') errorCallback(response.data)
        }
      )
    } else {
      nextTick(() => {
        let __reload = () => {
          window.location.reload(true)
        }

        let callbackFunction = callback

        if(props.refreshOnSaved) {
          callbackFunction = (data) => {
            context.emit('submitted', data)
            if(callback && typeof callback === 'function') callback(data)
            if(Object.prototype.hasOwnProperty.call(data, 'forceRedirect') && data.forceRedirect) {
              redirector(data)
            }else{
              __reload()
            }
          }
        } else {
          callbackFunction = (data) => {
            context.emit('submitted', data)
            redirector(data)
            if(callback && typeof callback === 'function') callback(data)
          }
        }

        store.dispatch(ACTIONS.SAVE_FORM, { item: null, callback: callbackFunction, errorCallback })

        // if(props.refreshOnSaved) {
        //   window.location.reload()
        // }
      })
    }
  }

  const sendSync = (e) => {
    e && e.preventDefault()

    const form = document.createElement('form')
    form.method = 'POST'
    form.action = props.actionUrl
    form.enctype = 'multipart/form-data'

    let formData = convertToNestedFormData(states.model)

    for (const [key, value] of formData.entries()) {
      const input = document.createElement('input')
      input.type = 'hidden'
      input.name = key
      input.value = value
      form.appendChild(input)
    }

    const input = document.createElement('input')
    input.type = 'hidden'
    input.name = '_token'
    input.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    form.appendChild(input)

    if(props.isEditing) {
      const input = document.createElement('input')
      input.type = 'hidden'
      input.name = '_method'
      input.value = 'PUT'
      form.appendChild(input)
    }

    document.body.appendChild(form)

    form.submit()
  }

  const handleEvent = (obj) => {
    handleEvents(model.value, inputSchema.value, obj.schema, true)
  }

  const convertToNestedFormData = (obj, parentKey = '') => {
    const formData = new FormData()
    for (const [key, value] of Object.entries(obj)) {
      const formKey = parentKey ? `${parentKey}[${key}]` : key

      if (value === null || value === undefined) {
        continue
      } else if (typeof value === 'object') {
        if (Array.isArray(value)) {
          value.forEach((item, index) => {
            if (typeof item === 'object' && item !== null) {
              const nestedFormData = convertToNestedFormData(item, `${formKey}[${index}]`)
              for (const [nestedKey, nestedValue] of nestedFormData.entries()) {
                formData.append(nestedKey, nestedValue)
              }
            } else {
              formData.append(`${formKey}[${index}]`, item)
            }
          })
        } else {
          const nestedFormData = convertToNestedFormData(value, formKey)
          for (const [nestedKey, nestedValue] of nestedFormData.entries()) {
            formData.append(nestedKey, nestedValue)
          }
        }
      } else {
        formData.append(formKey, value)
      }
    }
    return formData
  }

  const resetValidation = () => {
    VForm.value.resetValidation()
  }

  const setSchemaErrors = (errors) => {
    const _errors = {}
    for (const name in errors) {
      const pattern = /(\w+)\.(\w+)/
      const matches = name.match(pattern)
      if (matches) {
        const _name = matches[1]
        const _locale = matches[2]
        if (!__isset(_errors[_name])) {
          _errors[_name] = []
        }
        _errors[_name][_locale] = errors[name]
      } else {
        _errors[name] = errors[name]
      }
    }

    for (const name in _errors) {
      if( inputSchema.value[name]) inputSchema.value[name].errorMessages = _errors[name]
      else if (find(chunkedRawSchema.value, chunk => chunk.name === name)) {
        const wrapInputs = reduce(inputSchema.value, (acc, input, key) => {
          if(input.type === 'wrap') {
            acc.push(key)
          }
          return acc
        }, [])

        wrapInputs.forEach(wrapKey => {
          if(inputSchema.value[wrapKey]['schema'][name]) {
            inputSchema.value[wrapKey]['schema'][name].errorMessages = _errors[name]
          }
        })
      }
    }
  }

  const resetSchemaError = (key) => {
    if(get(inputSchema.value, `${key}`)) {
      set(inputSchema.value, `${key}.errorMessages`, [])
      set(inputSchema.value, `${key}.error`, false)
    }else{
      for (const wrapKey in inputSchema.value) {

        if(inputSchema.value[wrapKey]['schema']) {

          for(const nestedKey in inputSchema.value[wrapKey]['schema']) {
            if(nestedKey === key) {
              set(inputSchema.value, `${wrapKey}.schema.${nestedKey}.errorMessages`, [])
              set(inputSchema.value, `${wrapKey}.schema.${nestedKey}.error`, false)
            }
          }
        }
      }
    }
  }

  const resetSchemaErrors = () => {
    for (const key in inputSchema.value) {
      resetSchemaError(key)

      if(inputSchema.value[key]['schema']) {

        for(const nestedKey in inputSchema.value[key]['schema']) {
          if(inputSchema.value[key]['schema'][nestedKey]) {
            resetSchemaError(`${key}.schema.${nestedKey}`)
          }
        }
      }
    }
  }

  // Initialize
  const initialize = () => {
    resetSchemaErrors()
  }

  const methods = reactive({
    async validate () {
      const result = await states.VForm.validate()

      return result
    },
    createModel: (schema) => {
      return getModel(schema, states.model.value, store.state)
    },
    handleInput: (event) => {
      const { on, key, obj } = event
      if (on === 'input' && !!key) {
        if (!states.serverValid) {
          resetSchemaError(key)
        }

        handleEvent(obj)
      }

      context.emit('input', event)
    },
    handleClick: (e) => {
      const { on, obj, params } = e
      // check 'click' is from prependInner Icon (Print) at key 'subgroups.content'
      // if (on === 'click' && key === 'subgroups.content' && (params && params.tag) === 'prepend-inner') {
      //   window.print()
      // }
      // check 'click' is from from appendIcon at key password

      // for click slot handlers
      if (on === 'click' && params && params.tag) {
        // toggle visibility of password control
        inputHandlers.invokeInputClickHandler(obj, params.tag)
      }
    },
    handleUpdate: (e) => {

    },
    handleResize: (e) => {

    },
    handleBlur: (e) => {

    },
    submit: (e, callback = null, errorCallback = null) => {
      if (validations.validModel.value) {
        if (props.async) {
          e && e.preventDefault()
          if (!props.actionUrl) {
            store.commit(FORM.SET_EDITED_ITEM, states.model)
            nextTick(() => {
              saveForm(callback, errorCallback)
            })
          } else {
            saveForm(callback, errorCallback)
          }
        } else {
          sendSync(e)
        }
      } else {
        e && e.preventDefault()
      }
    },
    updatedSlotModel: (value, inputName) => {
    },
    regenerateInputSchema: (newItem) => {
      // #TODO regenerate inputschema for prefix regex pattern
      // for (const key in this.rawSchema) {
      //   if (__isset(this.rawSchema[key].event)) {

    },
    updatedCustomFormBaseModelValue: (value) => {
      model.value = value
    },
  })

  // Add watch to sync with modelValue when it exists
  watch(() => props.modelValue, (newVal, oldVal) => {

    if(oldVal === undefined) return

    if (issetModel.value) {
      if(isEqual(newVal, oldVal) && isEqual(newVal, model.value)) return

      model.value = getModel(rawSchema.value, newVal, store.state)
    }
  })

  // Watch editedItem
  watch(() => storeEditedItem.value, (newVal, oldVal) => {
    if (!issetModel.value) {
      // methods.regenerateInputSchema(newValue)
      // model.value = getModel(rawSchema.value, newValue, store.state)
    }
  })

  watch(() => model.value, (newVal, oldVal) => { // ✅ Proper ref watching
    if (issetModel.value) {

      if(isEqual(newVal, oldVal) && isEqual(newVal, props.modelValue)) return

      context.emit('update:modelValue', newVal)
    } else {

    }
  }, { deep: true })

  // Watch errors
  watch(() => states.errors, (newValue) => {
    setSchemaErrors(newValue)
  })

  // Watch validModel
  watch(() => validations.validModel.value, (newValue) => {
    context.emit('update:valid', newValue)
  })

  // Watch schema
  watch(() => props.schema, (newValue, oldValue) => {
    if (!isEqual(newValue, oldValue) && issetSchema.value && !isEqual(newValue, inputSchema.value)) {
      rawSchema.value = newValue
      defaultItem.value = getModel(rawSchema.value)

      // model.value = getModel(
      //   rawSchema.value,
      //   issetModel.value ? props.modelValue : storeEditedItem.value,
      //   store.state,
      // )

      inputSchema.value = validations.invokeRuleGenerator(getSchema(rawSchema.value, model.value, props.isEditing))
      formEventSchema.value = getFormEventSchema(rawSchema.value, formItem.value, props.isEditing)
      // states.extraValids = props.actions.length ? props.actions.map(() => true) : []
      initialize()
      // context.emit('update:schema' )
    }
  }, { deep: true })

  // Watch inputSchema
  watch(() => inputSchema.value, (newValue, oldValue) => {

    // if(isEqual(newValue, oldValue) && isEqual(newValue, rawSchema.value)) return

    // if(issetSchema.value) {

    //   context.emit('update:schema', newValue)
    // }
    // You can add any specific handling needed when inputSchema changes
  }, { deep: true })

  // Watch rawSchema
  watch(() => rawSchema.value, (value, oldValue) => {
    if(oldValue === undefined) {
      return
    }
    if( !isEqual(value, oldValue)) {
      const oldModel = cloneDeep(model.value)
      // Changed variable name to avoid conflict
      const newModel = getModel(value, model.value, store.state)

      if (!isEqual(newModel, oldModel)) {
        model.value = newModel
        inputSchema.value = validations.invokeRuleGenerator(
          getSchema(value, model.value, props.isEditing)
        )
      }
    }

  }, { deep: true })

  initialize()

  // Add resetSchemaErrors to the returned methods
  return {
    ...toRefs(states),
    ...toRefs(methods),
    ...inputHandlers,
    ...validations,
    ...locale,
    // ...itemActions,
    // handleInput,
    // createModel,
    // createSchema,
    // submit,
    // resetValidation,
    // initialize,
    // resetSchemaError,
    // setSchemaErrors,
  }
}
