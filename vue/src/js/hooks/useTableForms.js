// hooks/useTable.js
import { computed, ref, nextTick, watch } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { FORM } from '@/store/mutations'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { useTableItem } from '@/hooks'

export const makeTableFormsProps = propsFactory({
  inputFields: {
    type: Array
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
  }
})

export default function useTableForms(props, context) {

  const store = useStore()
  const { t } = useI18n({ useScope: 'global' })
  const { setEditedItem, resetEditedItem, editedItem } = useTableItem()

  // Form refs and state
  const form = ref(null)
  const formActive = ref(false)
  const customFormModalActive = ref(false)

  // Form state
  const customFormAttributes = ref({})
  const customFormSchema = ref({})
  const customFormModel = ref({})

  // Computed properties
  const inputs = computed(() => props.inputFields ?? store.state.datatable.inputs ?? [])

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

  const formIsValid = computed(() =>
    form.value?.validModel ?? null
  )

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

  const confirmFormModal = () => {
    form.value.submit(null, (res) => {
      if (Object.prototype.hasOwnProperty.call(res, 'variant') &&
          res.variant.toLowerCase() === 'success') {
        closeForm()
      }
    })
  }

  // Watch effect for form active state
  watch(() => formActive.value, (newValue) => {
    if (!newValue) {
      form.value?.resetValidation()
      resetEditedItem()
    }

  })
  // watch(() => state.formActive, (newValue, oldValue) => {
  //   newValue || form.value.resetValidation() || methods.resetEditedItem()
  // })

  return {
    // Refs
    form,
    formActive,
    customFormModalActive,
    customFormAttributes,
    customFormSchema,
    customFormModel,

    // Computed
    inputs,
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
    confirmFormModal
  }
}
