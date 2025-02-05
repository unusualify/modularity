// hooks/useTableItemActions.js
import { computed } from 'vue'
import { useStore } from 'vuex'
import _ from 'lodash-es'
import ACTIONS from '@/store/actions'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { useTableItem, useTableNames } from '@/hooks'

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

  const can = (permission) => {
    const name = tableNames.permissionName.value + '_' + permission
    return store.getters.isSuperAdmin || store.getters.userPermissions[name]
  }

  // Helper method to get nested object values using dot notation
  const getNestedValue = (obj, path) => {
    return path.split('.').reduce((current, part) => {
      return current && current[part] !== undefined ? current[part] : undefined;
    }, obj);
  }

  // Action Permissions
  const itemHasAction = (item, action) => {
    let hasAction = true

    switch (action.name) {
      case 'edit':
      case 'delete':
      case 'switch':
      case 'duplicate':
      case 'activate':
        hasAction = !tableItem.isSoftDeletable(item) && can(action.name)
        break
      case 'forceDelete':
      case 'restore':
        hasAction = tableItem.isSoftDeletable(item) && can(action.name)
        break
      default:
        break
    }

    if(action.conditions) {
      return hasAction && action.conditions.every(condition => {
        const [path, operator, value] = condition;
        const actualValue = getNestedValue(item, path);

        switch (operator) {
          case '=':
          case '==':
            return actualValue === value;
          case '!=':
            return actualValue !== value;
          case '>':
            __log(value, actualValue, actualValue > value)
            return actualValue > value;
          case '<':
            return actualValue < value;
          case '>=':
            return actualValue >= value;
          case '<=':
            return actualValue <= value;
          case 'in':
            __log(value, actualValue, Array.isArray(value) && value.includes(actualValue))
            return Array.isArray(value) && value.includes(actualValue);
          case 'not in':
            return Array.isArray(value) && !value.includes(actualValue);
          case 'exists':
            return actualValue !== undefined && actualValue !== null;
          default:
              console.warn(`Unknown operator: ${operator}`);
              return false;
        }
      })
    }



    return hasAction
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
  }

  return {
    // methods
    itemAction,
    itemHasAction,
    can
  }
}
