// hooks/table/useTableNames.js
import { computed, toRefs } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { useModule } from '@/hooks'
import { useTableItem } from '@/hooks/table'

export const makeTableNamesProps = propsFactory({
  isModuleRoute: {
    type: Boolean,
    default: false
  },
  name: {
    type: String
  },
  moduleName: {
    type: String
  },
  routeName: {
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

  const { editedItem, isSoftDeletableItem } = context.TableItem

  const Module = useModule(props, context)

  const transNameCountable = computed(() =>
    t(Module.tableTranslationNotation.value, store.getters.totalElements)
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

    // NOTE: Build a fresh object — never mutate `props.form{Edit,Create}TitleInterpolations`.
    // The source map holds *paths* (e.g. `{ item: 'id' }`); writing the resolved value back into
    // it would poison the next compute, since the loop would then look up the resolved value
    // itself (e.g. a UUID) instead of the original path — pinning the modal title to the id of
    // the first ticket opened in the session.
    const interpolationSource = isEditing ? props.formEditTitleInterpolations : props.formCreateTitleInterpolations
    const interpolation = {}

    for(let key in interpolationSource) {
      const path = interpolationSource[key]
      interpolation[key] = __isset(Module[path])
        ? Module[path].value
        : __isset(editedItem.value[path])
          ? editedItem.value[path]
          : path
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
      route: Module.transNameSingular.value,
      name: itemName.toLocaleUpperCase()
    })
  })
  const deleteDialogTitle = computed(() => {
    const deletionSpecifierKey = isSoftDeletableItem.value
      ? 'confirm-soft-deletion-title'
      : 'confirm-deletion-title'

    const langKey = te(`messages.${Module.snakeName.value}.${deletionSpecifierKey}`)
      ? `messages.${Module.snakeName.value}.${deletionSpecifierKey}`
      : `fields.${deletionSpecifierKey}`

    const itemName = editedItem.value[props.titleKey]
      ? (_.isObject(editedItem.value[props.titleKey])
          ? editedItem.value[props.titleKey][store.state.user.locale]
          : editedItem.value[props.titleKey])
      : ''

    return t(langKey, {
      route: Module.transNameSingular.value,
      name: itemName.toLocaleUpperCase()
    })
  })

  const deleteDialogDescription = computed(() => {
    const deletionSpecifierKey = isSoftDeletableItem.value
      ? 'confirm-soft-deletion-description'
      : 'confirm-deletion-description'

    const langKey = te(`messages.${Module.snakeName.value}.${deletionSpecifierKey}`)
      ? `messages.${Module.snakeName.value}.${deletionSpecifierKey}`
      : `fields.${deletionSpecifierKey}`

    const itemName = editedItem.value[props.titleKey]
      ? (_.isObject(editedItem.value[props.titleKey])
          ? editedItem.value[props.titleKey][store.state.user.locale]
          : editedItem.value[props.titleKey])
      : ''

    return t(langKey, {
      route: Module.transNameSingular.value,
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
    deleteDialogTitle,
    deleteDialogDescription,

    // Form
    formTitle,
    formSubtitle,
  }
}
