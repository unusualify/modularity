// hooks/useTable.js
import { watch, computed, nextTick, reactive, toRefs } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'

import _ from 'lodash'

import { DATATABLE, FORM } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

import { mapGetters } from '@/utils/mapStore'

import { useFormatter } from '@/hooks'

// by convention, composable function names start with "use"
export default function useTable (props, context) {
  // state encapsulated and managed by the composable
  // a composable can also hook into its owner component's
  // lifecycle to setup and teardown side effects.

  const store = useStore()

  const { t, te } = useI18n({ useScope: 'global' })

  const state = reactive({
    createUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.create ?? '',
    editUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.edit ?? '',
    editedIndex: -1,
    selectedItems: [],
    formActive: false,
    dialogActive: false,

    options: props.tableOptions ?? store.state.datatable.options ?? {},
    headers: props.columns ?? store.state.datatable.headers ?? [],
    inputs: props.inputFields ?? store.state.datatable.inputs ?? [],

    snakeName: _.snakeCase(props.name),

    transName: computed(() => t('modules.' + state.snakeName)),
    tableHeader: computed(() => {
      return __isset(props.customHeader)
        ? _.upperCase(props.customHeader)
        : t(`modules.${state.snakeName}`, 1)
        // : this.$t(`modules.${this.$lodash.snakeCase(this.name)}`, { n: 3 })
        // : this.$lodash.upperCase(this.$t('list-of-item', [this.name, this.$t('modules.' + this.$lodash.snakeCase(this.name))]))
    }),

    // dialogDescription: computed(() => t('confirm-deletion', {
    //   route: state.transName.toLowerCase(),
    //   name: store.state.form.editedItem[props.titleKey] ?? ''
    // })),
    dialogDescription: computed(() => {
      // __log(store.state.form.editedItem, props.titleKey)
      return t('confirm-deletion', {
        route: state.transName.toLowerCase(),
        name: store.state.form.editedItem[props.titleKey] ?? ''
      })
    }),

    formTitle: computed(() => {
      return t((state.editedIndex === -1 ? 'new-item' : 'edit-item'), {
        item: te(`modules.${state.snakeName}`) ? t(`modules.${state.snakeName}`, 0) : props.name
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

    headers_: computed({
      get () {
        return props.columns ?? store.state.datatable.headers ?? []
      },
      set (val) {}
    }),
    inputs_: computed({
      get () {
        return props.inputFields ?? store.state.datatable.inputs ?? []
      },
      set (val) {}
    }),
    options_: computed({
      get () {
        return props.tableOptions ?? store.state.datatable.options ?? {}
      },
      set (val) {
        // __log('options set', val)
        store.dispatch(ACTIONS.GET_DATATABLE, { payload: { options: val } })
        // this.$store.commit(DATATABLE.UPDATE_DATATABLE_OPTIONS, value)
      }
    })

  })

  const getters = mapGetters()

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
      // __log(item)
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
      state.dialogActive = true
    },

    deleteRow: function () {
      store.dispatch(ACTIONS.DELETE_ITEM, {
        id: state.editedItem.id,
        callback: () => {
          state.dialogActive = false
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
  watch(() => state.dialogActive, (newValue, oldValue) => {
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
