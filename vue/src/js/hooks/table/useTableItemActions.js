// hooks/table/useTableItemActions.js
import { computed, reactive, toRefs } from 'vue'
import { useDisplay } from 'vuetify'
import _ from 'lodash-es'

import formApi from '@/store/api/form'
import datatableApi from '@/store/api/datatable'

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
  }
})

export default function useTableItemActions(props, { tableForms }) {
  const tableItem = useTableItem()
  const tableNames = useTableNames(props)
  const { can } = useAuthorization()

  const { smAndDown, mdAndDown, lgAndDown } = useDisplay()

  // Create a reactive object to store action events
  const actionEvents = reactive({
    // Will contain events like: showModal, confirmAction, etc.
    event: null,
    payload: null,
    // Reset the current event
    reset: () => {
      actionEvents.event = null
      actionEvents.payload = null
    }
  })

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

      if(_.isObject(props.endpoints) && props.endpoints.edit) {
        const route = props.endpoints.edit.replace(':id', item.id)
        window.open(route)
      } else {
        console.error(`No edit endpoint found in endpoints of props`)
      }

    }
  }

  const handleRestoreAction = (item) => {
    tableItem.setEditedItem(item)

    const type = 'restore'
    const callback = async (callback, errorCallback) => {
      return await datatableApi[type](props.endpoints[type], item.id, callback, errorCallback)
    }

    actionEvents.event = 'process'
    actionEvents.payload = { type, item, callback }

  }

  const handleDuplicateAction = (item) => {
    tableItem.setEditedItem(_.omit(item, 'id'))
    tableForms.openForm()
  }

  const handleSwitchAction = (item, value, key) => {

    const type = 'publish'

    const callback = async (callback, errorCallback) => {
      let payload = {
        id: item.id,
        [key]: value
      }
      return await formApi.put(props.endpoints.update.replace(':id', item.id), payload, callback, errorCallback)
    }

    item[key] = value

    actionEvents.event = 'process'
    actionEvents.payload = { type, item, callback }
  }

  const handleDeleteAction = (item) => {
    tableItem.setEditedItem(item)

    let id = tableItem.editedItem.value.id
    let type = tableItem.isSoftDeletableItem.value ? 'forceDelete' : 'delete'

    const callback = async (callback, errorCallback) => {
      if (type === 'forceDelete') {
        return await datatableApi.forceDelete(props.endpoints.forceDelete, id, callback, errorCallback)
      } else {
        return await datatableApi.delete(props.endpoints.destroy, id, callback, errorCallback)
      }
    }

    actionEvents.event = 'dialog'
    actionEvents.payload = { type, id, callback }
  }

  const handleBulkAction = (action) => {
    const type = action.name
    const callback = async (selectedItems, callback, errorCallback) => {
      let actionName = _.camelCase(type)
      if(datatableApi[actionName]){
        return await datatableApi[actionName](props.endpoints[actionName], selectedItems, callback, errorCallback)
      }
    }
    actionEvents.event = 'dialog'
    actionEvents.payload = { type, callback }
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
    actionEvents.event = 'showCustomForm'
    actionEvents.payload = { action, item }
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

      actionEvents.event = 'showData'
      actionEvents.payload = { action, data }
    }
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
      case 'bulkPublish':
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
    itemHasAction,
    // Add the action events to the return value
    actionEvents
  }
}
