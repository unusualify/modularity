// hooks/table/useTableModals.js
import { ref, computed, toRefs, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'

import datatableApi from '@/store/api/datatable'

import ACTIONS from '@/store/actions'
import { useTableNames } from '@/hooks/table'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeTableModalsProps = propsFactory({
  openCustomModal: {
    type: Boolean,
    default: false,
  },
})

export default function useTableModals(props, context) {
  const store = useStore()
  const { t } = useI18n({ useScope: 'global' })
  const { editedItem } = context.TableItem
  const TableNames = context.TableNames

  // Modal States
  const deleteModalActive = ref(false)
  const customModalActive = ref(props.openCustomModal)
  const actionModalActive = ref(false)

  const activeModalType = ref('custom')

  const selectedAction = ref(null)
  const deleteModal = ref(null)

  const activeModal = computed(() => {
    return modals.value[activeModalType.value]
  })

  // Modal Control Methods
  const openCustomModal = () => {
    customModalActive.value = true
  }
  const closeCustomModal = () => {
    customModalActive.value = false
  }
  const cleanupModals = () => {
    // if (store._state.data.datatable.customModal) {
    //   __removeQueryParams(['customModal[description]', 'customModal[color]', 'customModal[icon]', 'customModal[hideModalCancel]'])
    // }
  }
  const setModalType = (type) => {
    activeModalType.value = type
  }

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
    const camelCase = _.camelCase(action.name)
    if(datatableApi[camelCase]) {
      datatableApi[camelCase](props.endpoints[camelCase], tableItem.editedItem.value.id, () => {

      })
    }
    // if (__isset(ACTIONS[studlyCase])) {
    //   await store.dispatch(ACTIONS[studlyCase])
    //   selectedItems.value = []
    // } else {
    //   console.warn(`${action.name} may have not implemented yet on useTable.js hook`)
    // }
  }

  // Computed Properties
  const actionDialogQuestion = computed(() => {
    return t('confirm-action', {
      route: TableNames.transNameSingular.value,
      action: t(selectedAction.value?.name ?? ''),
    })
  })

  // Modal Definitions
  const modals = ref({
    'dialog': {
      ref: ref(null),
      active: false,

      // confirmCallback: null,
      // rejectCallback: null,
      modalAttributes: {
        title: null,
        description: null,
        transition: 'dialog-bottom-transition',
        widthType: 'md',
        fullscreen: false,
        persistent: false,
        confirmText: t('Confirm'),
        cancelText: t('Cancel'),

        confirmClosing: false,
        rejectClosing: true,

        confirmCallback: null,
        rejectCallback: null,
      },
      _oldModalAttributes: {},
      toggle(state = null) {
        this.active = state ?? !this.active
      },
      close() {
        this.toggle(false)
      },
      open() {
        this.toggle(true)
      },
      setConfirmCallback(callback) {
        if(callback && typeof callback === 'function') {
          this._oldModalAttributes.confirmCallback = this.modalAttributes.confirmCallback
          this.modalAttributes.confirmCallback = callback
        }
      },
      setRejectCallback(callback) {
        if(callback && typeof callback === 'function') {
          this._oldModalAttributes.rejectCallback = this.modalAttributes.rejectCallback
          this.modalAttributes.rejectCallback = callback
        }
      },
      setQuestion(question) {
        this._oldModalAttributes.description = this.modalAttributes.description
        this.modalAttributes.description = question
      },
      setTitle(title) {
        this._oldModalAttributes.title = this.modalAttributes.title
        this.modalAttributes.title = title
      },
      set(attributes) {
        let feasibles = Object.keys(attributes).reduce((acc, attribute) => {
          acc[attribute] = attributes[attribute]
          // if (this[attribute] !== undefined && !['active', 'ref', 'data', '_old'].includes(attribute)) {
          //   acc[attribute] = attributes[attribute]
          // }
          return acc
        }, {})
        let assignables = _.reduce(feasibles, (acc, value, attribute) => {
          acc[attribute] = value
          this._oldModalAttributes[attribute] ??= _.cloneDeep(this.modalAttributes[attribute])
          return acc
        }, {})

        Object.assign(this.modalAttributes, assignables)
      },
      reset() {
        const self = this
        _.each(this._oldModalAttributes, (value, attribute) => {
          self.modalAttributes[attribute] = value
        })
        this._oldModalAttributes = {}
      },
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
        closeCustomModal()
        resetCustomModal()
      },
      confirmAction() {
        closeCustomModal()
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
    deleteModalActive,
    customModalActive,
    actionModalActive,
    selectedAction,
    deleteModal,

    activeModal,

    modals,

    // Computed
    actionDialogQuestion,

    // Methods
    openCustomModal,
    closeCustomModal,
    cleanupModals,
    setModalType,
    bulkAction,
  }
}
