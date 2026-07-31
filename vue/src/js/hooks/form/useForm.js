// hooks/useForm.js
import { ref, computed, watch, toRefs, reactive, nextTick, onMounted, provide } from 'vue'
import { router } from '@inertiajs/vue3'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { cloneDeep, isEqual, find, reduce, set, get, isArray } from 'lodash-es'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { useConfig, useInputHandlers, useValidation, useLocale, useItemActions, useAuthorization, useUser, useEditPresence } from '@/hooks'
import useFormResponseStatus from './useFormResponseStatus'
import { LANGUAGE } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'
import api from '@/store/api/form'
import { getModel, getSubmitFormData, getSchema, getFormEventSchema, getSecondaryInputs } from '@/utils/getFormData.js'
import { handleEvents } from '@/utils/formEvents'
import { getTranslationInputsCount, processInputs } from '@/utils/schema.js'

import { redirector } from '@/utils/response'

export const makeFormProps = propsFactory({
  modelValue: {
    type: Object,
    required: true,
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
    required: true,
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
  editPresenceModelType: {
    type: String,
    default: null
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
    type: [Number, String],
    default: 300
  },
  rightSlotMaxWidth: {
    type: [Number, String],
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
  forceRefresh: {
    type: Boolean,
    default: false
  },
  noWaitSourceLoading: {
    type: Boolean,
    default: false
  },
  noSchemaUpdatingProgressBar: {
    type: Boolean,
    default: false
  },
  reloadOnly: {
    type: Array,
    default: () => []
  },
  languages: {
    type: Array,
    default: null,
  },
  noValidation: {
    type: Boolean,
    default: false
  },
  /**
   * CMS public page permalinks (locale + absolute URL). When set, Form shows links under the title.
   * @see localizedPublicPermalinksForFormItem in ManageUtilities (HasParentSegment + HasSlug + Page until a CMS submodule trait exists).
   */
  localizedPublicPermalinks: {
    type: Array,
    default: null,
  },
  /**
   * CMS signed public preview: { fetchUrl, expiresInMinutes } from ManageUtilities (HasParentSegment submodules).
   */
  signedPublicPreview: {
    type: Object,
    default: null,
  },
})

export default function useForm(props, context) {
  const store = useStore()
  const { t, te } = useI18n({ useScope: 'global' })
  const { shouldUseInertia } = useConfig()
  const { isSuperAdmin } = useUser()
  if(props.languages && isArray(props.languages) && props.languages.length > 0) {
    store.commit(LANGUAGE.SET_LANGUAGES, props.languages)
  }

  // Composables
  const inputHandlers = useInputHandlers()
  const validations = useValidation(props)
  const locale = useLocale()
  const { hasRoles } = useAuthorization()
  const { handleSuccessResponse, handleErrorResponse } = useFormResponseStatus()

  const presenceEnabled = computed(() => props.isEditing && !!props.editPresenceModelType && !!(props.modelValue?.id))
  const presenceModelType = computed(() => props.editPresenceModelType)
  const presenceModelId = computed(() => props.modelValue?.id ?? null)
  // Keep presence computeds as refs — do not bury them in reactive()+toRefs
  // (that can break template updates for the second concurrent editor).
  const {
    hasOtherEditors,
    lockMessage: editPresenceLockMessage,
  } = useEditPresence({
    enabled: presenceEnabled,
    modelType: presenceModelType,
    modelId: presenceModelId,
  })

  // Data refs
  const VForm = ref(null)
  const id = Math.ceil(Math.random() * 1000000) + '-form'
  const formBaseId = id + '-base'

  // const issetModel = ref(props.modelValue ? Object.keys(props.modelValue).length > 0 : false)
  // const issetSchema = ref(props.schema ? Object.keys(props.schema).length > 0 : false)
  const issetModel = ref(props.modelValue ? true : false)
  const issetSchema = ref(props.schema ? true : false)

  const validModel = computed(() => {
    return props.noValidation || validations.validModel.value
  })
  const formLoading = ref(false)
  const serverValid = ref(true)
  const formErrors = ref({})

  const rawSchema = ref(props.schema)
  const chunkedRawSchema = computed(() => processInputs(rawSchema.value))

  const defaultItem = ref(getModel(rawSchema.value))

  const model = ref(getModel(
    rawSchema.value,
    props.modelValue,
  ))

  const initialModel = ref(cloneDeep(model.value))

  const schemaUpdating = ref(false)

  const setSchemaUpdating = (value) => {
    if(value && props.noSchemaUpdatingProgressBar) {
      schemaUpdating.value = false
      return
    }

    schemaUpdating.value = value
  }

  // const formItem = computed(() => issetModel.value ? props.modelValue : store.state.form.editedItem)
  const formItem = computed(() => props.modelValue)

  const inputSchema = ref(validations.invokeRuleGenerator(getSchema(rawSchema.value, { ...model.value, ...formItem.value }, props.isEditing, ({ ...model.value, ...formItem.value }).id)))
  // const inputSchema = ref(getSchema(rawSchema.value, { ...model.value, ...formItem.value }, props.isEditing))

  const formEventSchema = ref(getFormEventSchema(rawSchema.value, { ...model.value, ...formItem.value }, props.isEditing))
  const secondaryInputs = ref(getSecondaryInputs(rawSchema.value, { ...model.value, ...formItem.value }, props.isEditing))
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
    }) || find(formEventSchema.value, checkSubmittable) || find(secondaryInputs.value, checkSubmittable) ? true : false
  })

  const currentRevisions = ref(props.revisions || [])
  const restoringRevisionId = ref(null)

  // the authorized user select or the status (stateable) event in the
  // header, both of which are only persisted on Submit.
  const dirtyWatchKeys = computed(() => {
    const keys = []

    const collect = (input) => {
      if (['wrap', 'group'].includes(input.type)) {
        Object.values(input.schema ?? {}).forEach(collect)
        return
      }

      if ((checkSubmittable(input) || input.dirtyCheck === true) && input.name) {
        keys.push(input.name)
      }
    }

    Object.values(inputSchema.value).forEach(collect)

    Object.values(formEventSchema.value).forEach((event) => {
      if (event && event.dirtyCheck === true && event.name) {
        keys.push(event.name)
      }
    })

    return keys
  })

  const isEmptyValue = (v) => v === null || v === undefined || v === ''

  const isDirty = computed(() =>
    dirtyWatchKeys.value.some((key) => {
      const current = model.value[key]
      const initial = initialModel.value[key]

      if (isEmptyValue(current) && isEmptyValue(initial)) {
        return false
      }

      return !isEqual(current, initial)
    })
  )


  const hasAdditionalSection = computed(() => context.slots.right
    || context.slots['right.top']
    || context.slots['right.bottom']
    || context.slots['right.middle']
    || ['right-top', 'right-middle', 'right-bottom'].includes(props.actionsPosition)
    || (secondaryInputs.value && secondaryInputs.value.length > 0)
    || (currentRevisions.value && currentRevisions.value.length > 0)
  )

  const states = reactive({
    id,
    reference: computed(() => 'ref-' + states.id),
    formBaseId,
    VForm,

    issetModel,
    issetSchema,

    model,
    formItem,

    schemaUpdating,
    formActionsActive: computed(() => !schemaUpdating.value && props.isEditing),
    isDirty,
    chunkedRawSchema,
    inputSchema,
    formEventSchema,
    secondaryInputs,
    isSubmittable,
    extraValids,

    hasStickyFrame: props.stickyFrame || props.stickyButton,
    // formLoading: false,
    // formErrors: {},
    manualValidation: false,
    buttonDefaultText: computed(() =>
      props.buttonText ? (te(props.buttonText) ? t(props.buttonText) : props.buttonText) : t('submit')
    ),

    serverValid,
    loading: formLoading,
    // errors: computed(() =>
    //   props.actionUrl ? formErrors.value : store.state.form.errors
    // ),
    errors: formErrors,
    hasTraslationInputs: computed(() => getTranslationInputsCount(inputSchema.value) > 0),

    hasAdditionalSection,

    showAdditionalSectionDialog: false,

    hasSchemaInputSourceLoading: computed(() => {
      const schema = rawSchema.value
      if (!schema || typeof schema !== 'object') return false
      return Object.values(schema).some(s => Object.prototype.hasOwnProperty.call(s, 'sourceLoading') && s.sourceLoading === true
        || (Object.prototype.hasOwnProperty.call(s, 'type') && (s.type === 'wrap' || s.type === 'group') && s.schema && Object.values(s.schema).some(nested => Object.prototype.hasOwnProperty.call(nested, 'sourceLoading') && nested.sourceLoading === true)))
    }),
  })
  // Methods

  const createSchema = (schema, modelValue) => {
    return validations.invokeRuleGenerator(getSchema(schema, modelValue, true, modelValue?.id))
  }

  const saveForm = (callback = null, errorCallback = null) => {
    const submitRequest = (method, endpoint, data) => {
      api[method](endpoint, data,
        (response) => {
          handleSuccessResponse({
            response,
            onRetry: () => {
              formLoading.value = true
              submitRequest(method, endpoint, data)
            },
            setLoading: (value) => { formLoading.value = value },
            setServerValid: (value) => { serverValid.value = value },
            setFormErrors: (value) => { formErrors.value = value },
            props,
            states,
            rawSchema,
            getModel,
            resetValidation,
            VForm,
            emitSubmitted: (payload) => context.emit('submitted', payload),
            callback,
            shouldUseInertia,
            router,
            redirector,
          })
        },
        (response) => {
          handleErrorResponse({
            response,
            onRetry: () => {
              formLoading.value = true
              submitRequest(method, endpoint, data)
            },
            setLoading: (value) => { formLoading.value = value },
            errorCallback,
          })
        }
      )
    }

    if (props.actionUrl) {
      formErrors.value = {}
      formLoading.value = true

      const formData = getSubmitFormData(rawSchema.value, states.model, store._state.data)
      const method = Object.prototype.hasOwnProperty.call(formData, 'id') ? 'put' : 'post'

      submitRequest(method, props.actionUrl, formData)
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
    updateFormValid: (value) => {
      validations.validModel.value = value
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
    saveForm,
    submit: (e, callback = null, errorCallback = null) => {
      if (props.noValidation || validations.validModel.value) {
        if (props.async) {
          e && e.preventDefault()
          if (!props.actionUrl) {
            // store.commit(FORM.SET_EDITED_ITEM, states.model)

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
    /**
     * Merge Laravel-style dotted keys into server error map (e.g. translated `field.locale`).
     * Triggers the same `formErrors` → `setSchemaErrors` path as backend validation.
     */
    mergeFormFieldErrors: (partial) => {
      if (!partial || typeof partial !== 'object') {
        return
      }
      formErrors.value = { ...formErrors.value, ...partial }
    },
    clearFormFieldErrorKey: (dotKey) => {
      if (!dotKey) {
        return
      }
      const next = { ...formErrors.value }
      delete next[dotKey]
      formErrors.value = next
    },
    resetFieldSchemaErrors: (fieldKey) => {
      resetSchemaError(fieldKey)
    },
  })


  // Add watch to sync with modelValue when it exists
  watch(() => props.modelValue, (newVal, oldVal) => {
    if(oldVal === undefined) return

    if (issetModel.value) {
      let newModelValue = getModel(rawSchema.value, newVal, store.state)
      if(isEqual(newVal, oldVal) && isEqual(newModelValue, model.value)) return

      model.value = getModel(rawSchema.value, newVal, store.state)
      // Re-baseline the dirty snapshot to the freshly saved data (e.g. after a
      // refreshOnSaved partial reload)
      initialModel.value = cloneDeep(model.value)

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
      setSchemaUpdating(true)

      rawSchema.value = newValue
      defaultItem.value = getModel(rawSchema.value)

      inputSchema.value = validations.invokeRuleGenerator(getSchema(rawSchema.value, { ...model.value, ...formItem.value }, props.isEditing, ({ ...model.value, ...formItem.value }).id))
      formEventSchema.value = getFormEventSchema(rawSchema.value, { ...model.value, ...formItem.value }, props.isEditing)
      secondaryInputs.value = getSecondaryInputs(rawSchema.value, { ...model.value, ...formItem.value }, props.isEditing)
      // states.extraValids = props.actions.length ? props.actions.map(() => true) : []
      initialize()
      // context.emit('update:schema' )

      setTimeout(() => {
        setSchemaUpdating(false)
      }, 500)
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
      const newModel = getModel(value, props.modelValue, store.state)

      if (!isEqual(newModel, oldModel)) {
        model.value = newModel
        inputSchema.value = validations.invokeRuleGenerator(
          getSchema(value, props.modelValue, props.isEditing, props.modelValue?.id)
        )
      }
    }

  }, { deep: true })

  initialize()


  if(isSuperAdmin.value) {
    console.info('useForm', {
      states,
      methods,
      inputHandlers,
      validations,
      locale,
      validModel,
      inputSchema,
      formEventSchema,
      secondaryInputs,
      rawSchema,
    })
  }

  // Add resetSchemaErrors to the returned methods
  return {
    ...toRefs(states),
    ...toRefs(methods),
    ...inputHandlers,
    ...validations,
    ...locale,
    validModel,
    hasOtherEditors,
    editPresenceLockMessage,
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
