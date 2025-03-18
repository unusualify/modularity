// hooks/table/useTableModals.js
import { ref, computed, toRefs, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'

import ACTIONS from '@/store/actions'
import { useTableItem, useTableNames } from '@/hooks/table'

export default function useTableModals(props, context) {
  const store = useStore()
  const { t } = useI18n({ useScope: 'global' })
  const tableItem = useTableItem()
  const tableNames = useTableNames(props)

  // Modal States
  const deleteModalActive = ref(false)
  const customModalActive = ref(!(_.isEmpty(store._state.data.datatable.customModal)))
  const actionModalActive = ref(false)
  const activeModal = ref('custom')
  const selectedAction = ref(null)
  const deleteModal = ref(null)

  // Computed Properties
  const actionDialogQuestion = computed(() => {
    return t('confirm-action', {
      route: tableNames.transNameSingular.value,
      action: t(selectedAction.value?.name ?? ''),
    })
  })

  // Modal Definitions
  const modals = ref({
    'delete': {
      content: tableNames.deleteQuestion,
      confirmAction() {
        if (tableItem.isSoftDeletableItem.value) {
          store.dispatch(ACTIONS.DESTROY_ITEM, {
            id: tableItem.editedItem.value.id,
            callback: () => {
              customModalActive.value = false
            },
            errorCallback: () => {}
          })
        } else {
          store.dispatch(ACTIONS.DELETE_ITEM, {
            id: tableItem.editedItem.value.id,
            callback: () => {
              customModalActive.value = false
            },
            errorCallback: () => {}
          })
        }
      },
      closeAction() {
        customModalActive.value = false
      }
    },
    'action': {
      content: actionDialogQuestion,
      confirmAction() {
        bulkAction(selectedAction.value)
        customModalActive.value = false
      },
      openAction(action) {
        selectedAction.value = action
        customModalActive.value = true
      },
      closeAction() {
        customModalActive.value = false
      }
    },
    'custom': {
      content: computed(() => store._state.data.datatable.customModal.description),
      confirmText: 'Done',
      cancelText: ' ',
      img: 'https://cdn2.iconfinder.com/data/icons/greenline/512/check-1024.png',
      icon: computed(() => store._state.data.datatable.customModal.icon),
      iconSize: 72,
      title: 'Payment Complete',
      color: 'success',
      closeAction() {
        customModalActive.value = false
        resetCustomModal()
      },
      confirmAction() {
        customModalActive.value = false
        resetCustomModal()
      }
    },
    'show': {
      ref: ref(null),
      active: false,
      data: null,

      transition: 'dialog-bottom-transition',
      widthType: 'lg',
      fullscreen: false,
      persistent: false,
      title: t('Display Details'),
      confirmText: t('Confirm'),
      cancelText: t('Cancel'),

      _old: {},

      toggle(state = null) {
        this.active = state ?? !this.active
      },
      close() {
        this.toggle(false)
      },
      open() {
        this.toggle(true)
      },
      set(attributes) {
        let feasibles = Object.keys(attributes).reduce((acc, attribute) => {
          if (this[attribute] !== undefined && !['active', 'ref', 'data', '_old'].includes(attribute)) {
            acc[attribute] = attributes[attribute]
          }
          return acc
        }, {})
        let assignables = _.reduce(feasibles, (acc, value, attribute) => {
          acc[attribute] = value
          this._old[attribute] = _.cloneDeep(this[attribute])
          return acc
        }, {})
        Object.assign(this, assignables)
      },
      loadData(data) {
        this.data = data
      },
      resetData() {
        this.data = null
      },

      cancel() {
        this.toggle(false)
        this.reset()
      },
      confirm() {
        // this.toggle(false)
        // this.reset()
      },
      reset() {
        this.resetData()

        _.each(this._old, (value, attribute) => {
          this[attribute] = value
         }
        )
        this._old = {}
      },
    }
  })

  // Helper Methods
  const resetCustomModal = () => {
    modals.custom.confirmText = ''
    modals.custom.cancelText = ''
    modals.custom.img = ''
    modals.custom.icon = ''
    modals.custom.iconSize = null
    modals.custom.title = ''
    modals.custom.color = ''
  }

  const bulkAction = async (action) => {
    const studlyCase = _.snakeCase(action.name).toUpperCase()
    if (__isset(ACTIONS[studlyCase])) {
      await store.dispatch(ACTIONS[studlyCase])
      selectedItems.value = []
    } else {
      console.warn(`${action.name} may have not implemented yet on useTable.js hook`)
    }
  }

  // Modal Control Methods
  const openDeleteModal = (modal) => {
    modal.open()
  }

  const closeDeleteModal = () => {
    customModalActive.value = false
  }

  const closeActionModal = () => {
    actionModalActive.value = false
  }

  const openActionModal = (action) => {
    customModalActive.value = true
  }

  // Cleanup Method
  const cleanupModals = () => {
    if (store._state.data.datatable.customModal) {
      __removeQueryParams(['customModal[description]', 'customModal[color]', 'customModal[icon]', 'customModal[hideModalCancel]'])
    }
  }

  watch(() => modals.value.show.active, (value) => {
    if (!value) {
      modals.value.show.reset()
    }
  })

  onMounted(() => {
    cleanupModals()
  })

  return {
    // States
    ...toRefs({
    }),
    deleteModalActive,
    customModalActive,
    actionModalActive,
    activeModal,
    selectedAction,
    deleteModal,
    modals,

    // Computed
    actionDialogQuestion,

    // Methods
    openDeleteModal,
    closeDeleteModal,
    closeActionModal,
    openActionModal,
    cleanupModals,
    bulkAction
  }
}
