// hooks/table/useTableForms.js
import { computed, ref, nextTick, watch, toRefs, toRef, reactive } from 'vue'
import _ from 'lodash-es'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'

import ACTIONS from '@/store/actions'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeTableFormsProps = propsFactory({
  inputFields: {
    type: Array,
    default: () => []
  },
  formSchema: {
    type: Object,
    required: true,
    validator(value) {
      return value && typeof value === 'object'
    }
  },
  formWidth: {
    type: [String, Number],
    default: '60%'
  },
  createOnModal: {
    type: Boolean,
    default: true
  },
  editOnModal: {
    type: Boolean,
    default: true
  },
  embeddedForm: {
    type: Boolean,
    default: false
  },
  addBtnOptions: {
    type: Object,
    default: () => ({})
  },
  noForm: {
    type: Boolean,
    default: false
  },
  formActions: {
    type: [Array, Object],
    default: []
  }
})

export default function useTableForms(props, context) {

  const store = useStore()
  const { t } = useI18n({ useScope: 'global' })
  const { setEditedItem, resetEditedItem, editedItem } = context.TableItem

  // Form refs and state
  const UeForm = ref(null)
  const formActive = ref(false)
  const customFormModalActive = ref(false)

  // Form state
  const customFormModalAttributes = ref({})
  const customFormAttributes = ref({})
  const customFormSchema = ref({})
  const customFormModel = ref({})

  const states = reactive({
    UeForm,
    formActive,
    customFormModalActive,

    customFormModalAttributes,
    customFormAttributes,
    customFormSchema,
    customFormModel,
  })

  // Computed properties
  // const inputs = computed(() => props.inputFields ?? store.state.datatable.inputs ?? [])

  const formRef = computed(() =>
    state.id + '-form'
  )

  const formStyles = computed(() => ({
    width: props.formWidth
  }))

  const formLoading = computed(() =>
    store.state.form.loading ?? false
  )

  const formErrors = computed(() =>
    store.state.form.errors ?? {}
  )

  const formIsValid = computed(() => {
    return UeForm.value?.validModel ?? null
  })

  const addBtnTitle = computed(() => {
    if(props.createOnModal || props.editOnModal) {
      return props.addBtnOptions.text
        ? t(props.addBtnOptions.text)
        : t('fields.add-item', { item: context.transNameSingular.value })
    }
    return props.addBtnOptions.text ?? t('ADD NEW')
  })

  // Methods
  const openForm = () => {
    formActive.value = true
  }

  const closeForm = () => {
    formActive.value = false
  }

  const createForm = () => {
    resetEditedItem()
    openForm()
  }

  const handleFormSubmission = (data) => {
    if (Object.prototype.hasOwnProperty.call(data, 'variant')
      && data.variant.toLowerCase() === 'success') {
      closeForm()
      context.loadItems()
    }
  }

  // Watch effect for form active state
  watch(() => formActive.value, (newValue) => {
    if (!newValue) {
      // form.value?.resetValidation()
      resetEditedItem()
    }

  })
  // watch(() => state.formActive, (newValue, oldValue) => {
  //   newValue || form.value.resetValidation() || methods.resetEditedItem()
  // })

  return {
    // Refs
    ...toRefs(states),

    // Computed
    // inputs,
    formRef,
    formStyles,
    formLoading,
    formErrors,
    formIsValid,
    addBtnTitle,

    // Methods
    openForm,
    closeForm,
    createForm,
    handleFormSubmission,
  }
}
