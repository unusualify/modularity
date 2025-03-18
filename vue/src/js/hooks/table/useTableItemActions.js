// hooks/table/useTableItemActions.js
import { computed, reactive, toRefs } from 'vue'
import { useStore } from 'vuex'
import { useDisplay } from 'vuetify'
import _ from 'lodash-es'
import ACTIONS from '@/store/actions'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { useAuthorization } from '@/hooks'
import { useTableItem, useTableNames } from '@/hooks/table'

import { checkItemConditions } from '@/utils/itemConditions';

export const makeTableItemActionsProps = propsFactory({
  isRowEditing: Boolean,
  rowActionsIcon:{
    type: String,
    default: 'mdi-cog-outline'
  },
  rowActions: {
    type: Array,
    default: []
  },
  rowActionsType: {
    type: String,
    default: 'inline'
  },
  iteratorType: {
    type: String,
    default: '',
  },
  formActions: {
    type: Array,
    default: []
  }
})

export default function useTableItemActions(props, { tableForms, tableModals, tableEndpoints }) {
  const store = useStore()
  const tableItem = useTableItem()
  const tableNames = useTableNames(props)
  const { can } = useAuthorization()

  const { smAndDown, mdAndDown, lgAndDown } = useDisplay()

  // Action Permissions
  const itemHasAction = (item, action) => {
    let hasAction = true

    switch (action.name) {
      case 'edit':
      case 'delete':
      case 'switch':
      case 'duplicate':
      case 'activate':
        hasAction = !tableItem.isSoftDeletable(item) && can(action.name, tableNames.permissionName.value)
        break
      case 'forceDelete':
      case 'restore':
        hasAction = tableItem.isSoftDeletable(item) && can(action.name, tableNames.permissionName.value)
        break
      default:
        break
    }

    return hasAction && checkItemConditions(action.conditions, item)

  }

  // Action Handlers
  const handleEditAction = (item) => {
    if (props.editOnModal || props.embeddedForm) {
      tableItem.setEditedItem(item)
      tableForms.openForm()
    } else {
      const route = tableEndpoints.editUrl.value.replace(':id', item.id)
      window.open(route)
    }
  }

  const handleDeleteAction = (item) => {
    tableItem.setEditedItem(item)
    tableModals.activeModal.value = 'delete'
    tableModals.customModalActive.value = true
  }

  const handleRestoreAction = (item) => {
    tableItem.setEditedItem(item)
    store.dispatch(ACTIONS.RESTORE_ITEM, {
      id: item.id,
      callback: () => {},
      errorCallback: () => {}
    })
  }

  const handleDuplicateAction = (item) => {
    tableItem.setEditedItem(_.omit(item, 'id'))
    tableForms.openForm()
  }

  const handleSwitchAction = (item, value, key) => {

    store.dispatch(ACTIONS.SAVE_FORM, {
      plain: true,
      item: {
        id: item.id,
        ...{ [key]: value }
      },
      callback: () => {
        item[key] = value
        if (tableItem.editedItem.id === item.id) {
          tableItem.setEditedItem({
            ...tableItem.editedItem,
            ...{ [key]: value }
          })
        }
      },
      errorCallback: () => {}
    })
  }

  const handleBulkAction = (action) => {
    tableModals.activeModal.value = 'action'
    tableModals.customModalActive.value = true
    tableModals.selectedAction.value = action
  }

  const handleCustomFormAction = (action, item) => {
    tableForms.customFormSchema.value = _.cloneDeep(action.form.attributes.schema)
    tableForms.customFormAttributes.value = _.cloneDeep(action.form.attributes)

    if (action.form.hasOwnProperty('model_formatter')) {
      for (let key in action.form.model_formatter) {
        let attr = _.get(item, action.form.model_formatter[key], '')
        _.set(tableForms.customFormModel.value, key, attr)
      }
    }

    if (action.form.hasOwnProperty('schema_formatter')) {
      for (let key in action.form.schema_formatter) {
        let attr = _.get(item, action.form.schema_formatter[key], '')
        _.set(tableForms.customFormAttributes.value.schema, key, attr)
      }
    }

    tableForms.customFormModalActive.value = true
  }

  const getOnlyShowData = (data, only = null) => {
    if(!only) return data

    if(Array.isArray(data)) {
      data = data.map(item => {
        return getOnlyShowData(item, only)
      })
    } else if (__isObject(data)) {
      if(Array.isArray(only)) {
        let newData = {}
        only.forEach((key) => {
          let value = __data_get(data, key, null)
          newData[key] = value
        })
      } else if (__isObject(only)) {
        let newData = {}
        Object.keys(only).forEach((key) => {
          let label = only[key]
          let value = __data_get(data, key, null)

          newData[label] = value
        })
        data = _.cloneDeep(newData)
      }
    }

    return data
  }

  const getExceptShowData = (data, except = null) => {
    if(!except) return data

    if(Array.isArray(data)) {
      data = data.map(item => {
        return getExceptShowData(item, except)
      })
    } else if (__isObject(data) && Array.isArray(except)) {
      let newData = _.cloneDeep(data)
      except.forEach((key) => {
        let value = __data_get(data, key, null)
        delete newData[key]
      })
      data = _.cloneDeep(newData)
    }

    return data
  }

  const handleShowAction = (action, item) => {
    let data = null

    if(action.show  === true ) {
      data = item
    } else {
      data = __data_get(item, action.show, null);
    }


    if(data) {
      if(action.only) {
        data = getOnlyShowData(data, action?.only)
      } else if (action.except) {
        data = getExceptShowData(data, action?.except)
      }
      tableModals.modals.value.show.loadData(data)
      tableModals.modals.value.show.set(action)
      tableModals.modals.value.show.open()
    }
    // tableModals.modals.value.show.description = action.show.description
    // tableModals.activeModal.value = 'showModal'
    // tableModals.showModalActive.value = true
  }

  // Main Action Handler
  const itemAction = (item = null, action = null, ...args) => {
    const _action = __isString(action) ? { name: action } : action

    switch (_action.name) {
      case 'edit':
        handleEditAction(item)
        break
      case 'delete':
      case 'forceDelete':
        handleDeleteAction(item)
        break
      case 'restore':
        handleRestoreAction(item)
        break
      case 'duplicate':
        handleDuplicateAction(item)
        break
      case 'link':
        window.open(_action.url.replace(':id', item.id), '_blank')
        break
      case 'switch':
        handleSwitchAction(item, args[0], args[1])
        break
      case 'activate':
        state.activeTableItem = _.find(state.elements, { id: item.id })
        break
      case 'bulkDelete':
      case 'bulkForceDelete':
      case 'bulkRestore':
        handleBulkAction(_action)
        break
      default:
        break
    }

    if (_action.form) {
      handleCustomFormAction(_action, item)
    }

    if (_action.show) {
      handleShowAction(_action, item)
    }
  }

  const states = reactive({
    actionShowingType: computed(() => {
      const shouldShowDropdown = (actionsLength) => {
        if (actionsLength > 7) return lgAndDown.value
        if (actionsLength > 4) return mdAndDown.value
        return smAndDown.value
      }

      return props.rowActionsType === 'dropdown' || shouldShowDropdown(props.rowActions.length)
        ? 'dropdown'
        : 'inline'
    }),
  })

  return {
    ...toRefs(states),
    // methods
    itemAction,
    itemHasAction
  }
}
