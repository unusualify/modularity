// hooks/table/useTableNames.js
import { computed, toRefs } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { useModule } from '@/hooks'
import { useTableItem } from '@/hooks/table'

export const makeTableNamesProps = propsFactory({
  name: {
    type: String
  },
  customTitle: {
    type: String
  },
  titlePrefix: {
    type: String,
    default: ''
  },
  titleKey: {
    type: String,
    default: 'name'
  },
  subtitle:{
    type:String,
    default: '',
  },
  // Form
  formTitle: {
    type: String,
  },
  formCreateTitleTranslationKey: {
    type: String,
    default: 'fields.new-item'
  },
  formEditTitleTranslationKey: {
    type: String,
    default: 'fields.edit-item'
  },
  formCreateTitleInterpolations: {
    type: Object,
    default: () => ({
      item: 'transNameSingular'
    })
  },
  formEditTitleInterpolations: {
    type: Object,
    default: () => ({
      item: 'transNameSingular'
    })
  },
  createFormTitle: {
    type: String,
  },
  editFormTitle: {
    type: String,
  },

  formSubtitle: {
    type: String,
  },
  formCreateSubtitle: {
    type: String,
  },
  formEditSubtitle: {
    type: String,
  },
})

export default function useTableNames(props, context) {
  const store = useStore()
  const { t, te } = useI18n({ useScope: 'global' })

  const { editedItem, isSoftDeletableItem } = useTableItem()

  const Module = useModule(props, context)

  const transNameCountable = computed(() =>
    t(`modules.${Module.snakeName.value}`, store.getters.totalElements)
  )

  // Titles
  const tableTitle = computed(() => {
    const prefix = props.titlePrefix ? props.titlePrefix : ''
    return prefix + (__isset(props.customTitle) ? props.customTitle : Module.transNamePlural.value)
  })

  const tableSubtitle = computed(() => {
    return __isset(props.subtitle) ? t(props.subtitle) : ''
  })

  const formTitle = computed(() => {
    // let title = props.formTitle
    let isEditing = context.editedIndex.value !== -1
    let translationKey = props.formCreateTitleTranslationKey

    if (isEditing) {
      translationKey = props.formEditTitleTranslationKey
    }

    if (__isset(props.createFormTitle)) {
      translationKey = props.createFormTitle
    }
    if (__isset(props.editFormTitle)) {
      translationKey = props.editFormTitle
    }

    let interpolation = isEditing ? props.formEditTitleInterpolations : props.formCreateTitleInterpolations

    for(let key in interpolation) {
      let value = interpolation[key]
      interpolation[key] = __isset(Module[interpolation[key]])
        ? Module[interpolation[key]].value
        : __isset(editedItem.value[interpolation[key]])
          ? editedItem.value[interpolation[key]]
          : value
    }

    return te(translationKey) ? t(translationKey, interpolation) : translationKey
  })

  const formSubtitle = computed(() => {
    let subtitle = props.formSubtitle

    let isEditing = context.editedIndex.value !== -1

    if (__isset(props.formCreateSubtitle) && !isEditing) {
      subtitle = props.formCreateSubtitle
    }
    if (__isset(props.formEditSubtitle) && isEditing) {
      subtitle = props.editFormSubtitle
    }

    return te(subtitle) ? t(subtitle) : subtitle
  })

  // Delete question text
  const deleteQuestion = computed(() => {
    const langKey = isSoftDeletableItem.value
      ? 'fields.confirm-soft-deletion'
      : 'fields.confirm-deletion'

    const itemName = editedItem.value[props.titleKey]
      ? (_.isObject(editedItem.value[props.titleKey])
          ? editedItem.value[props.titleKey][store.state.user.locale]
          : editedItem.value[props.titleKey])
      : ''

    return t(langKey, {
      route: transNameSingular.value,
      name: itemName.toLocaleUpperCase()
    })
  })

  return {
    // Base names
    snakeName: Module.snakeName,
    permissionName: Module.permissionName,

    // Translations
    transNameSingular: Module.transNameSingular,
    transNamePlural: Module.transNamePlural,
    transNameCountable,

    // Titles
    tableTitle,
    tableSubtitle,

    deleteQuestion,

    // Form
    formTitle,
    formSubtitle,
  }
}
