// hooks/table.js

import { ref, watch, computed, nextTick, reactive, toRefs } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'

import { DATATABLE, FORM } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

import { mapGetters } from '@/utils/mapStore'

import { useFormatter } from '@/hooks/formatter.js'
// by convention, composable function names start with "use"
export function useTable (props, context) {
  // state encapsulated and managed by the composable
  const store = useStore()

  const { t } = useI18n({ useScope: 'global' })

  const state = reactive({
    createUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.create ?? '',
    editUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.edit ?? '',
    editedIndex: -1,
    selectedItems: []
  })

  const formTitle = computed(() => t((state.editedIndex === -1 ? 'new-item' : 'edit-item'), { item: props.name }))

  const options = computed({
    get () {
      return props.tableOptions ?? store.state.datatable.options ?? {}
    },
    set (val) {
      // __log('options set', value)
      store.dispatch(ACTIONS.GET_DATATABLE, { payload: { options: val } })
      // this.$store.commit(DATATABLE.UPDATE_DATATABLE_OPTIONS, value)
    }
  })
  const search = computed({
    get () {
      return store.state.datatable.search
    },
    set (val) {
      store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, val)
      store.dispatch(ACTIONS.GET_DATATABLE)
    }
  })
  const elements = computed({
    get () {
      return props.items ?? store.state.datatable.data ?? []
    },
    set (val) {}
  })
  const headers = computed({
    get () {
      return props.columns ?? store.state.datatable.headers ?? []
    },
    set (val) {}
  })
  const inputs = computed({
    get () {
      return props.inputFields ?? store.state.datatable.inputs ?? []
    },
    set (val) {}
  })

  watch(() => store.state.datatable.editedItem, (newValue, oldValue) => {
    state.editedIndex = elements.value.findIndex(o => { return o.id === newValue.id })
  })

  const getters = mapGetters()

  // a composable can also hook into its owner component's
  // lifecycle to setup and teardown side effects.
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
    }
  })

  const formatter = useFormatter(headers.value)

  // expose managed state as return value
  return {
    // createUrl,
    // editUrl,
    // editedIndex,
    // selectedItems,
    ...toRefs(state),

    formTitle,
    options,
    search,
    elements,
    headers,
    inputs,

    ...getters,

    ...toRefs(methods),
    ...formatter
  }
}
