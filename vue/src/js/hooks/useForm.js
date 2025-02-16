// hooks/useForm.js
import { ref, computed, watch, toRefs, reactive, nextTick } from 'vue'
import { useStore } from 'vuex'
import { FORM, ALERT } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'
import api from '@/store/api/form'
import { useI18n } from 'vue-i18n'
import { useInputHandlers, useValidation, useLocale, useItemActions } from '@/hooks'
import { getModel, getSubmitFormData, getSchema, handleEvents, getTranslationInputsCount, getTopSchema } from '@/utils/getFormData.js'
import { redirector } from '@/utils/response'
import { cloneDeep } from 'lodash-es'

export default function useForm(props, context) {
  const store = useStore()
  const { t, te } = useI18n({ useScope: 'global' })

  // Composables
  const inputHandlers = useInputHandlers()
  const validations = useValidation(props)
  const locale = useLocale()

  // Data refs
  const formLoading = ref(false)
  const formErrors = ref({})
  const rawSchema = ref(props)
  const defaultItem = ref(null)
  const issetModel = ref(Object.keys(props.modelValue).length > 0)
  const issetSchema = ref(Object.keys(props.schema).length > 0)

  const editedItem = computed(() => store.state.form.editedItem)

  const reference = computed(() => 'ref-' + id.value)

  const states = reactive({
    id: Math.ceil(Math.random() * 1000000) + '-form',
    VForm: null,
    model: issetModel.value ? props.modelValue : store.state.form.editedItem,
    formItem: computed(() => issetModel.value ? props.modelValue : store.state.form.editedItem),
    // issetModel: Object.keys(props.modelValue).length > 0,
    issetModel,
    issetSchema,
    hasStickyFrame: props.stickyFrame || props.stickyButton,
    inputSchema: null,
    topSchema: null,
    // formLoading: false,
    // formErrors: {},
    manualValidation: false,
    extraValids: [],
    buttonDefaultText: computed(() =>
      props.buttonText ? (te(props.buttonText) ? t(props.buttonText) : props.buttonText) : t('submit')
    ),
    editedItem,
    // editedItem: computed(() => store.state.form.editedItem),
    serverValid: computed(() => store.state.form.serverValid ?? true),
    loading: computed(() =>
      props.actionUrl ? formLoading.value : store.state.form.loading
    ),
    errors: computed(() =>
      props.actionUrl ? formErrors.value : store.state.form.errors
    ),
    reference: computed(() => 'ref-' + states.id),
    hasTraslationInputs: computed(() => getTranslationInputsCount(states.inputSchema) > 0),
    flattenedActions: computed(() => __isObject(props.actions) ? Object.values(props.actions) : props.actions),
    hasActions: computed(() => states.flattenedActions.length > 0),
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

          context.emit('submitted', response.data)
          redirector(response.data)
          if (callback && typeof callback === 'function') callback(response.data)
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
        store.dispatch(ACTIONS.SAVE_FORM, { item: null, callback, errorCallback })
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

    document.body.appendChild(form)
    form.submit()
  }

  const handleEvent = (obj) => {
    handleEvents(states.model, states.inputSchema, obj.schema, true)
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
    __log(VForm)
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
      if( states.inputSchema[name]) states.inputSchema[name].errorMessages = _errors[name]
    }
  }

  const resetSchemaError = (key) => {
    states.inputSchema[key].errorMessages = []
  }

  const resetSchemaErrors = () => {
    for (const key in states.inputSchema) {
      resetSchemaError(key)
    }
  }

  // Initialize
  const initialize = () => {
    rawSchema.value = issetSchema.value ? props.schema : store.state.form.inputs
    defaultItem.value = issetSchema.value ? getModel(rawSchema.value) : store.getters.defaultItem

    states.model = getModel(
      rawSchema.value,
      // issetModel.value ? props.modelValue : editedItem.value,
      issetModel.value ? props.modelValue : store.state.form.editedItem,
      store.state,
    )

    states.inputSchema = validations.invokeRuleGenerator(getSchema(rawSchema.value, states.model, props.isEditing))
    states.topSchema = getTopSchema(rawSchema.value, props.isEditing)
    states.extraValids = props.actions.length ? props.actions.map(() => true) : []

    // __log('initialize', states.model)
    resetSchemaErrors()
  }

  const methods = reactive({
    async validate () {
      const result = await states.VForm.value.validate()

      return result
    },
    createModel: (schema) => {
      return getModel(schema, states.model.value, store.state)
    },
    handleInput: (v, s) => {
      const { on, key, obj } = v

      if (on === 'input' && !!key) {
        if (!states.serverValid) {
          resetSchemaError(key)
        }

        handleEvent(obj)
      }
      context.emit('input', v)
    },
    handleClick: (e) => {
      const { on, obj, params } = e
      // check 'click' is from prependInner Icon (Print) at key 'subgroups.content'
      // if (on === 'click' && key === 'subgroups.content' && (params && params.tag) === 'prepend-inner') {
      //   window.print()
      // }
      // check 'click' is from from appendIcon at key password

      // for click slot handlers
      // __log(params, val)
      if (on === 'click' && params && params.tag) {
        // toggle visibility of password control
        inputHandlers.invokeInputClickHandler(obj, params.tag)
      }
    },
    handleUpdate: (e) => {
      // __log('handleUpdate', e)
    },
    handleResize: (e) => {
      // __log('handleResize', e)
    },
    handleBlur: (e) => {
      // __log('handleBlur', e)
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
      __log(states.model, value, inputName)
    },
    regenerateInputSchema: (newItem) => {
      // #TODO regenerate inputschema for prefix regex pattern
      // for (const key in this.rawSchema) {
      //   if (__isset(this.rawSchema[key].event)) {

    },
    getTopInputActiveLabel: (topInput) => {
      const item = topInput.items.find(item => item[topInput.itemValue] ===  (window.__isset(states.model[topInput.name]) ? states.model[topInput.name] : -1))
      return item ? item[topInput.itemTitle] : topInput.label
    }
  })


  watch(states.model, (value) => {
    context.emit('update:modelValue', value)
  }, { deep: true })

  // Watch editedItem
  watch(editedItem, (newValue, oldValue) => {
    if (!issetModel.value) {
      methods.regenerateInputSchema(newValue)
      states.model = getModel(rawSchema.value, newValue, store.state)
    }
  })

  // Watch errors
  watch(() => states.errors, (newValue) => {
    setSchemaErrors(newValue)
  })

  // Watch validModel
  watch(() => validations.validModel, (newValue) => {
    context.emit('update:valid', newValue)
  })

  // Watch schema
  watch(() => props.schema, (value, oldValue) => {
    if (JSON.stringify(value) !== JSON.stringify(oldValue)) {
      initialize()
    }
  }, { deep: true })

  // Watch inputSchema
  watch(() => states.inputSchema, (value) => {
    // You can add any specific handling needed when inputSchema changes
  }, { deep: true })

  // Watch rawSchema
  watch(rawSchema, (value, oldValue) => {
    let oldModel = cloneDeep(states.model)
    let model = getModel(value, states.model, store.state)
    if (JSON.stringify(Object.keys(__dot(model))) !== JSON.stringify(Object.keys(__dot(oldModel)))) {
      states.model = model
      states.inputSchema = validations.invokeRuleGenerator(getSchema(value, states.model, props.isEditing))
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
