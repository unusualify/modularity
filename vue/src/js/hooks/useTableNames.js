// hooks/useTableNames.js
import { computed } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { useModule, useTableItem } from '@/hooks'

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
})

export default function useTableNames(props, context) {
  const store = useStore()
  const { t, te } = useI18n({ useScope: 'global' })

  const { editedItem, isSoftDeletableItem } = useTableItem()

  const { snakeName, permissionName, transNameSingular, transNamePlural } = useModule(props, context)

  const transNameCountable = computed(() =>
    t(`modules.${snakeName}`, store.getters.totalElements)
  )

  // Titles
  const tableTitle = computed(() => {
    const prefix = props.titlePrefix ? props.titlePrefix : ''
    return prefix + (__isset(props.customTitle) ? props.customTitle : transNamePlural.value)
  })

  const tableSubtitle = computed(() => {
    return __isset(props.subtitle) ? t(props.subtitle) : ''
  })

  const formTitle = computed(() => {
    const translationKey = store.state.form.editedIndex === -1
      ? 'fields.new-item'
      : 'fields.edit-item'

    return t(translationKey, { item: transNameSingular.value })
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
    snakeName,
    permissionName,

    // Translations
    transNameSingular,
    transNamePlural,
    transNameCountable,

    // Titles
    tableTitle,
    tableSubtitle,
    formTitle,
    deleteQuestion
  }
}
