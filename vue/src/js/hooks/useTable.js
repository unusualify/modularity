// hooks/useTable.js
import { watch, computed, nextTick, reactive, toRefs } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import _, { isObject, find } from 'lodash'

import { DATATABLE, FORM } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

import { mapGetters } from '@/utils/mapStore'
import { getSchemaModel } from '@/utils/getFormData.js'

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
  hideFooter: {
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
  nestedData: {
    type: Object,
    default () {
      return {}
    }
  },
  isRowEditing: Boolean,
  createOnModal: Boolean,
  editOnModal: Boolean,
  embeddedForm: Boolean,

  noForm: {
    type: Boolean,
    default: false
  },
  fullWidthWrapper: {
    type: Boolean,
    default: false
  },
  noFullScreen: {
    type: Boolean,
    default: false
  },
  noFooter: {
    type: Boolean,
    default: false
  }
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
    formRef: computed(() => {
      return state.id + '-form'
    }),
    formStyles: { width: props.formWidth },

    formActive: false,
    deleteModalActive: false,
    customModalActive: false,

    activeTableItem: null,
    hideTable: false,

    createUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.create ?? '',
    editUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.edit ?? '',
    editedIndex: -1,
    selectedItems: [],

    snakeName: _.snakeCase(props.name),
    transNameSingular: computed(() => te('modules.' + state.snakeName, 0) ? t('modules.' + state.snakeName, 0) : props.name),
    transNamePlural: computed(() => t('modules.' + state.snakeName, 1)),
    transNameCountable: computed(() => t('modules.' + state.snakeName, getters.totalElements.value)),
    tableTitle: computed(() => {
      return __isset(props.customTitle) ? props.customTitle : state.transNamePlural
    }),
    formTitle: computed(() => {
      return t((state.editedIndex === -1 ? 'new-item' : 'edit-item'), {
        item: te(`modules.${state.snakeName}`) ? t(`modules.${state.snakeName}`, 0) : props.name
      })
    }),
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

    activeItemConfiguration: null,

    options: props.tableOptions ?? store.state.datatable.options ?? {},
    headers: props.columns ?? store.state.datatable.headers ?? [],
    inputs: props.inputFields ?? store.state.datatable.inputs ?? [],

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

    // datatable store
    loading: computed(() => store.state.datatable.loading ?? false),
    filterActiveStatus: computed(() => store.state.datatable.filter.status ?? 'all'),
    filterActive: computed(() => find(store.state.datatable.mainFilters, { slug: state.filterActiveStatus })),
    // form store
    editedItem: computed(() => store.state.form.editedItem ?? {}),
    formLoading: computed(() => store.state.form.formLoading ?? false),
    formErrors: computed(() => store.state.form.formErrors ?? {})
  })

  const methods = reactive({
    // initialize: function () {
    //   store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, '')
    //   store.commit(
    //     DATATABLE.UPDATE_DATATABLE_OPTIONS,
    //     window[process.env.VUE_APP_NAME].STORE.datatable.options
    //   )
    //   store.dispatch(ACTIONS.GET_DATATABLE)
    // },
    setEditedItem: function (item) {
      store.commit(FORM.SET_EDITED_ITEM, item)
    },
    resetEditedItem: function () {
      nextTick(() => {
        store.commit(FORM.SET_EDITED_ITEM, getters.defaultItem.value)
      })
    },

    editItem: function (item) {
      __log(
        props,
        item
      )
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

    activateItem: function (item) {
      state.activeTableItem = find(state.elements, { id: item.id })
    },
    hydrateNestedData: function (item, data) {
      const valuePattern = /\$([A-Za-z]+)/
      // const urlPattern = /\/:([A-Za-z])+/
      for (const key in data) {
        if (__isString(data[key])) {
          const matches = data[key].match(valuePattern)
          if (matches) {
            const match = matches[1]
            // __log(item)
            if (state.snakeName === match) {
              data[key] = getSchemaModel(data.schema, item)
              if (data.actionUrl) {
                data.actionUrl = data.actionUrl.replace(`:${match}`, data[key].id)
              }
            } else if (item[match]) {
              data[key] = getSchemaModel(data.schema, item[match])
              if (data.actionUrl) {
                data.actionUrl = data.actionUrl.replace(`:${match}`, data[key].id)
              }
            }
          }
        } else if (Array.isArray(data[key]) || __isObject(data[key])) {
          data[key] = methods.hydrateNestedData(item, data[key])
        }
      }

      return data
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
    },

    filterStatus: function (slug) {
      if (this.navActive === slug) return
      store.commit(DATATABLE.UPDATE_DATATABLE_PAGE, 1)
      store.commit(DATATABLE.UPDATE_DATATABLE_FILTER_STATUS, slug)
      store.dispatch(ACTIONS.GET_DATATABLE)
    }
  })

  watch(() => state.editedItem, (newValue, oldValue) => {
    // __log('editedItem watch', newValue, oldValue, state.elements.findIndex(o => { return o.id === newValue.id }))
    state.editedIndex = state.elements.findIndex(o => { return o.id === newValue.id })
  })
  watch(() => state.activeTableItem, (newValue, oldValue) => {
    if (newValue) {
      // hydrate abstract fields
      state.activeItemConfiguration = methods.hydrateNestedData(newValue, JSON.parse(JSON.stringify(props.nestedData)))
      // __log(methods.hydrateNestedData(newValue, activeItemConfiguration))
    }
  }, { deep: true })
  watch(() => state.formActive, (newValue, oldValue) => {
    newValue || methods.resetEditedItem()
  })
  watch(() => state.deleteModalActive, (newValue, oldValue) => {
    newValue || methods.resetEditedItem()
  })
  watch(() => state.options, (newValue, oldValue) => {
    // state.options.page = newValue
    __log('options watch', newValue, oldValue)
    store.dispatch(ACTIONS.GET_DATATABLE, { payload: { options: newValue } })
  }, { deep: true })
  watch(() => state.elements, (newValue, oldValue) => {
    // __log('elements watch', newValue, oldValue)
  }, { deep: true })

  const formatter = useFormatter(props, context, state.headers)

  // expose managed state as return value
  return {
    ...toRefs(state),
    ...getters,
    ...toRefs(methods),
    ...formatter
  }
}
