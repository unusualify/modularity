// hooks/table/useTableItemActions.js
import { computed, reactive, toRefs } from 'vue'
import { useDisplay } from 'vuetify'
import _ from 'lodash-es'

import formApi from '@/store/api/form'
import datatableApi from '@/store/api/datatable'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { useAuthorization, useDynamicModal, useCastAttributes } from '@/hooks'
import { useTableItem, useTableNames } from '@/hooks/table'

import { checkItemConditions } from '@/utils/itemConditions';
import useGenerate from '@/hooks/utils/useGenerate.js'

export const makeTableItemActionsProps = propsFactory({
  isRowEditing: Boolean,
  hideMobileActions: {
    type: Boolean,
    default: false
  },
  rowActionsIcon:{
    type: String,
    default: 'mdi-cog-outline'
  },
  rowActions: {
    type: [Array, Object],
    default: () => []
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

export default function useTableItemActions(props, { tableForms, loadItems }) {
  const tableItem = useTableItem()
  const tableNames = useTableNames(props)
  const { can } = useAuthorization()
  const DynamicModal = useDynamicModal()
  const { castObjectAttributes } = useCastAttributes()
  const { generateButtonProps } = useGenerate()
  const { xs, smAndDown, mdAndDown, lgAndDown, xlAndDown } = useDisplay()

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
        hasAction = !tableItem.isDeleted(item) && can(action.name, tableNames.permissionName.value)
        break
      case 'forceDelete':
      case 'restore':
        hasAction = tableItem.isDeleted(item) && can(action.name, tableNames.permissionName.value)
        break
      default:
        break
    }

    return hasAction && checkItemConditions(action.conditions, item)

  }

  // Action Handlers
  const handleDefaultAction = async (item, action) => {
    let url = action.url ?? action.endpoint ?? action.href

    if(url){
      if(url.includes(':id')){
        url = url.replace(':id', item.id)
      }

      if(action.hasDialog){

        const callback = async (callback, errorCallback) => {
          window.open(url, action.target ?? '_self')
        }

        actionEvents.event = 'dialog'
        actionEvents.payload = { description: action.dialogQuestion ?? null, callback }
      }else{
        window.open(url, action.target ?? '_self')
      }
    }

    if(action.modalService && _.isObject(action.modalService)){
      DynamicModal.open(action.modalService.component ?? null, _.omit(castObjectAttributes(action.modalService, item), ['component']))
    }
  }

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

    if(action.hasOwnProperty('modalAttributes')){
      tableForms.customFormModalAttributes.value = _.cloneDeep(action.modalAttributes)
    } else {
      tableForms.customFormModalAttributes.value = {}
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

  const runPreProcess = (preProcess, item) => {
    let type = null
    let payload = null
    let conditions = null

    if(_.isArray(preProcess)) {
      type = preProcess[0]
      payload = preProcess[1]
    }else if(_.isObject(preProcess)) {
      type = preProcess.type
      payload = preProcess.payload
      conditions = preProcess.conditions
    }

    if(!checkItemConditions(conditions, item)) return

    if (type === 'post') {
      if(props.endpoints.store) {
        // return formApi.post(props.endpoints.store, preProcess.params)
      }
    } else if (type === 'put') {
      if(props.endpoints.update && item && item.id) {
        return formApi.put(props.endpoints.update.replace(':id', item.id), castObjectAttributes(payload, item), function(response) {
          if(response.status === 200 && response.data.variant === 'success') {
            loadItems()
          }
        }, function(error) {
          console.error(error)
        })
      }
    }
  }

  const preProcessAction = (action, item) => {
    if (action.preProcesses) {
      action.preProcesses.forEach(preProcess => {
        runPreProcess(preProcess, item)
      })
    }
  }
  // Main Action Handler
  const itemAction = (item = null, action = null, ...args) => {
    const _action = __isString(action) ? { name: action } : action

    preProcessAction(_action, item)

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
        let target = item.target ?? _action.target ?? '_blank'

        if(!['_self', '_blank', '_parent', '_top'].includes(target)){
          target = '_blank'
        }

        let link = item.href ?? (_action.url ? _action.url.replace(':id', item.id) : props.endpoints.show ? props.endpoints.show.replace(':id', item.id) : null)

        if(!link){
          console.error('No link found in item or action', item, _action)
          return
        }

        window.open(link, target)

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
        handleDefaultAction(item, _action)
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
        if (actionsLength > 6) return xlAndDown.value // desktop if actions greater than 6
        if (actionsLength > 5) return lgAndDown.value // desktop if actions greater than 5
        if (actionsLength > 4) return mdAndDown.value // desktop if actions greater than 4
        if (actionsLength > 3) return smAndDown.value // tablet if actions greater than 3
        if (actionsLength > 2) return xs.value // mobile

        return false
      }

      // Get the length of actions (handle both array and object)
      let actionsLength = 0
      if (_.isArray(props.rowActions)) {
        actionsLength = props.rowActions.length
      } else if (_.isObject(props.rowActions)) {
        actionsLength = Object.keys(props.rowActions).length
      }

      return props.rowActionsType === 'dropdown' || shouldShowDropdown(actionsLength)
        ? 'dropdown'
        : 'inline'
    }),
    visibleRowActions: computed(() => {
      // Convert object to array if needed
      let actionsArray = props.rowActions


      if (_.isObject(props.rowActions) && !_.isArray(props.rowActions)) {

        // If it's an object, convert to array using Object.values()
        actionsArray = Object.values(props.rowActions)
      }

      return actionsArray.reduce((acc, action) => {

        if(Object.prototype.hasOwnProperty.call(action, 'href')){
          console.error('href is not supported in row actions', action)

          return acc
        }

        let component = 'v-icon'
        let componentProps = {}
        let hasTooltip = true

        let icon = action.icon ? action.icon : '$' + action.name
        let iconColor = action.iconColor ?? action.color ?? 'primary'

        if(__isset(action.is) && action.is === 'v-btn'){
          component = 'v-btn'
          hasTooltip = false
          componentProps = {
            variant: 'plain',
            class: 'pa-0 ma-2',
            ...(action.componentProps ?? {}),
            color: action.color,
          }

          if(action.icon){
            componentProps.prependIcon = action.icon
          }
        }else{
          componentProps = {
            small: true,
            class: 'mr-2',
            ...(action.componentProps ?? {}),
            color: action.color ?? 'grey-darken-2',
          }
        }

        action.componentProps = componentProps
        action.label = action.label ?? window.__headline(action.name)

        acc.push({
          ...action,
          is: component,
          icon,
          iconColor,
          hasTooltip,
        })

        return acc
      }, [])
    })
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
