// hooks/useTable.js
import { watch, computed, nextTick, reactive, toRefs } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import _, { isObject } from 'lodash'

import { DATATABLE, FORM } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

import { mapGetters } from '@/utils/mapStore'
import { useFormatter } from '@/hooks'

export const makeTableProps = propsFactory({
  name: {
    type: String
  },
  customTitle: {
    type: String
  },
  titleKey: {
    type: String,
    default: 'name'
  },
  items: {
    type: Array
  },
  hideHeaders: {
    type: Boolean,
    default: false
  },
  columns: {
    type: Array
  },
  inputFields: {
    type: Array
  },
  formWidth: {
    type: [String, Number],
    default: '60%'
  },
  tableOptions: {
    type: Object
  },
  tableClasses: {
    type: [String, Array],
    default: ''
  },
  rowActions: {
    type: Array,
    default: []
  },
  rowActionsType: {
    type: String,
    default: 'inline'
  },
  slots: {
    type: Object,
    default () {
      return {}
    }
  },
  hideDefaultHeader: Boolean,
  hideDefaultFooter: Boolean,
  isRowEditing: Boolean,
  createOnModal: Boolean,
  editOnModal: Boolean,
  embeddedForm: Boolean
})

// by convention, composable function names start with "use"
export default function useTable (props, context) {
  // state encapsulated and managed by the composable
  // a composable can also hook into its owner component's
  // lifecycle to setup and teardown side effects.

  const store = useStore()

  const { t, te } = useI18n({ useScope: 'global' })

  const getters = mapGetters()

  const state = reactive({
    id: Math.ceil(Math.random() * 1000000) + '-table',
    formStyles: {
      width: props.formWidth
    },
    createUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.create ?? '',
    editUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.edit ?? '',
    editedIndex: -1,
    selectedItems: [],
    formActive: false,
    deleteModalActive: false,

    options: props.tableOptions ?? store.state.datatable.options ?? {},
    headers: props.columns ?? store.state.datatable.headers ?? [],
    inputs: props.inputFields ?? store.state.datatable.inputs ?? [],

    snakeName: _.snakeCase(props.name),

    transNameSingular: computed(() => te('modules.' + state.snakeName, 0) ? t('modules.' + state.snakeName, 0) : props.name),
    transNamePlural: computed(() => t('modules.' + state.snakeName, 1)),
    transNameCountable: computed(() => t('modules.' + state.snakeName, getters.totalElements.value)),
    tableTitle: computed(() => {
      return __isset(props.customTitle)
        ? _.upperCase(props.customTitle)
        : state.transNamePlural
        // : this.$t(`modules.${this.$lodash.snakeCase(this.name)}`, { n: 3 })
        // : this.$lodash.upperCase(this.$t('list-of-item', [this.name, this.$t('modules.' + this.$lodash.snakeCase(this.name))]))
    }),
    formTitle: computed(() => {
      return t((state.editedIndex === -1 ? 'new-item' : 'edit-item'), {
        item: te(`modules.${state.snakeName}`) ? t(`modules.${state.snakeName}`, 0) : props.name
      })
    }),
    // dialogDescription: computed(() => {
    //   // __log(store.state.form.editedItem, props.titleKey)
    //   return t('confirm-deletion', {
    //     // route: state.transName.toLowerCase(),
    //     route: state.transNameSingular.toLocaleLowerCase(),
    //     name: (store.state.form.editedItem[props.titleKey]
    //       ? (isObject(store.state.form.editedItem[props.titleKey]) ? store.state.form.editedItem[props.titleKey][store.state.currentUser.locale] : store.state.form.editedItem[props.titleKey])
    //       : '').toLocaleUpperCase()
    //   })
    // }),
    deleteQuestion: computed(() => {
      // __log(store.state.form.editedItem, props.titleKey)
      return t('confirm-deletion', {
        // route: state.transName.toLowerCase(),
        route: state.transNameSingular,
        name: (store.state.form.editedItem[props.titleKey]
          ? (isObject(store.state.form.editedItem[props.titleKey]) ? store.state.form.editedItem[props.titleKey][store.state.currentUser.locale] : store.state.form.editedItem[props.titleKey])
          : '').toLocaleUpperCase()
      })
    }),

    elements: computed(() => props.items ?? store.state.datatable.data ?? []),
    search: computed({
      get () {
        return store.state.datatable.search
      },
      set (val) {
        store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, val)
        store.dispatch(ACTIONS.GET_DATATABLE)
      }
    }),

    // map setters
    // datatable store
    loading: computed(() => store.state.datatable.loading),
    // form store
    editedItem: computed(() => store.state.form.editedItem),
    formLoading: computed(() => store.state.form.formLoading),
    formErrors: computed(() => store.state.form.formErrors),

    // headers_: computed({
    //   get () {
    //     return props.columns ?? store.state.datatable.headers ?? []
    //   },
    //   set (val) {}
    // }),
    // inputs_: computed({
    //   get () {
    //     return props.inputFields ?? store.state.datatable.inputs ?? []
    //   },
    //   set (val) {}
    // }),
    // options_: computed({
    //   get () {
    //     return props.tableOptions ?? store.state.datatable.options ?? {}
    //   },
    //   set (val) {
    //     // __log('options set', val)
    //     store.dispatch(ACTIONS.GET_DATATABLE, { payload: { options: val } })
    //     // this.$store.commit(DATATABLE.UPDATE_DATATABLE_OPTIONS, value)
    //   }
    // }),

    formRef: computed(() => {
      return state.id + '-form'
    })
  })

  const methods = reactive({
    initialize: function () {
      store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, '')
      store.commit(
        DATATABLE.UPDATE_DATATABLE_OPTIONS,
        window[process.env.VUE_APP_NAME].STORE.datatable.options
      )
      store.dispatch(ACTIONS.GET_DATATABLE)
    },
    setEditedItem: function (item) {
      store.commit(FORM.SET_EDITED_ITEM, item)
    },
    resetEditedItem: function () {
      nextTick(() => {
        store.commit(FORM.SET_EDITED_ITEM, getters.defaultItem.value)
      })
    },

    editItem: function (item) {
      if (props.editOnModal || props.embeddedForm) {
        methods.setEditedItem(item)
        methods.openForm()
        // this.$refs.formModal.openModal()
      } else {
        const route = state.editUrl.replace(':id', item.id)
        window.open(route)
      }
    },
    deleteItem: function (item) {
      methods.setEditedItem(item)
      // this.$refs.dialog.openModal()
      methods.openDeleteModal()
    },
    switchItem: function (value, key, item) {
      // __log(value, key)
      // item[key] = value
      // methods.setEditedItem(item)

      store.dispatch(ACTIONS.SAVE_FORM, {
        item: {
          ...item,
          ...{ [key]: value }
        },
        callback: function () {
          item[key] = value

          if (state.editedItem.id == item.id) {
            methods.setEditedItem({
              ...state.editedItem,
              ...{ [key]: value }
            })
          }
        },
        errorCallback: function () {}
      })

      // // this.$refs.dialog.openModal()
      // methods.openDeleteModal()
    },

    deleteRow: function () {
      store.dispatch(ACTIONS.DELETE_ITEM, {
        id: state.editedItem.id,
        callback: () => {
          methods.closeDeleteModal()
        },
        errorCallback: () => {

        }
      })
    },

    createForm () {
      methods.resetEditedItem()
      methods.openForm()
    },
    openForm: function () {
      state.formActive = true
    },
    closeForm: function () {
      state.formActive = false
    },

    openDeleteModal: function () {
      state.deleteModalActive = true
    },
    closeDeleteModal: function () {
      state.deleteModalActive = false
    },

    goNextPage () {
      if (state.options.page < store.getters.totalPage) { state.options.page += 1 }
    },
    goPreviousPage () {
      if (state.options.page > 1) { state.options.page -= 1 }
    }
  })

  watch(() => state.editedItem, (newValue, oldValue) => {
    // __log('editedItem watch', newValue, oldValue, state.elements.findIndex(o => { return o.id === newValue.id }))
    state.editedIndex = state.elements.findIndex(o => { return o.id === newValue.id })
  })
  watch(() => state.formActive, (newValue, oldValue) => {
    newValue || methods.resetEditedItem()
  })
  watch(() => state.deleteModalActive, (newValue, oldValue) => {
    newValue || methods.resetEditedItem()
  })
  watch(() => state.options, (newValue, oldValue) => {
    // state.options.page = newValue
    // __log('options watch', newValue)
    store.dispatch(ACTIONS.GET_DATATABLE, { payload: { options: newValue } })
  }, { deep: true })
  watch(() => state.elements, (newValue, oldValue) => {
    // __log('elements watch', newValue, oldValue)
  }, { deep: true })

  const formatter = useFormatter(state.headers)

  // expose managed state as return value
  return {
    ...toRefs(state),
    ...getters,
    ...toRefs(methods),
    ...formatter
  }
}
