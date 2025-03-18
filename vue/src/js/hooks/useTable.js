// hooks/useTable.js
import { watch, computed, nextTick, reactive, toRefs, ref, toRef} from 'vue'
import { useDisplay } from 'vuetify'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import api from '@/store/api/datatable'

import { DATATABLE, FORM } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

import { mapGetters } from '@/utils/mapStore'
import { getSubmitFormData } from '@/utils/getFormData.js'

import { useRoot, useFormatter } from '@/hooks'

import {
  makeTableNamesProps,
  makeTableEndpointsProps,
  makeTableFiltersProps,
  makeTableHeadersProps,
  makeTableFormsProps,
  makeTableItemActionsProps,
  makeTableActionsProps,

  useTableItem,
  useTableNames,
  useTableFilters,
  useTableHeaders,
  useTableForms,
  useTableItemActions,
  useTableModals,
  useTableEndpoints,
  useTableActions,
} from '@/hooks/table'

export const makeTableProps = propsFactory({
  ...makeTableNamesProps(),
  ...makeTableEndpointsProps(),
  ...makeTableFiltersProps(),
  ...makeTableHeadersProps(),
  ...makeTableFormsProps(),
  ...makeTableItemActionsProps(),
  ...makeTableActionsProps(),
  fillHeight: {
    type: Boolean,
    default: false,
  },
  items: {
    type: Array
  },
  hideFooter: {
    type: Boolean,
    default: false
  },

  tableOptions: {
    type: Object
  },
  tableClasses: {
    type: [String, Array],
    default: ''
  },

  iteratorType: {
    type: String,
    default: '',
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

  striped: Boolean,
  hideBorderRow: Boolean,
  roundedRows: Boolean,

  fullWidthWrapper: {
    type: Boolean,
    default: false
  },
  noFullScreen: {
    type: Boolean,
    default: false
  },
  tableDensity:{
    type:String,
    default: 'comfortable',
  },
  toolbarOptions:{
    type: Object,
    default: {},
  },
  bulkActions: {
    type: [Array, Object],
    default: []
  },
  paginationOptions: {
    type: [Array, Object],
    default: {}
  },

  formAttributes: {
    type: Object,
    default: {}
  },
  formModalAttributes: {
    type: Object,
    default: {}
  },

  showSelect: {
    type: Boolean,
    default: true,
  },
  sticky:{
    type: Boolean,
    default: true,
  },
  controlsPosition: {
    type: String,
    default: 'top',
  },

})

// by convention, composable function names start with "use"
export default function useTable (props, context) {
  // state encapsulated and managed by the composable
  // a composable can also hook into its owner component's
  // lifecycle to setup and teardown side effects.

  const store = useStore()
  const { smAndDown } = useDisplay()
  const { t, te, tm } = useI18n({ useScope: 'global' })

  const editedIndex = ref(-1)
  let items = ref(props.items ?? store.state.datatable?.data ?? [])
  const elements = computed({
    get(){
      return items.value;
    },
    set(val){
      items.value = val
    }
  });

  // Get item-related computeds
  const tableItem = useTableItem()
  // Get name-related computeds
  const tableNames = useTableNames(props, {
    editedIndex: editedIndex
  })
  // Get endpoints-related computeds
  const tableEndpoints = useTableEndpoints(props)
  // Get filter-related computeds
  const tableFilters = useTableFilters(props)
  // Get headers-related computeds
  const tableHeaders = useTableHeaders(props)
  // Get forms-related computeds
  const tableForms = useTableForms(props, {
    ...context,
    ...tableNames
  })
  const tableModals = useTableModals(props)

  const getters = mapGetters()

  const form = ref(null)
  const loading = ref(false)

  const searchModel = ref(store.state.datatable.search ?? '')

  const isStoreTable = computed(() => {
    return props.endpoints.index.replace(/\/$/, '').split('?')[0] === window.location.href.replace(/\/$/, '').split('?')[0]
  })

  const options = ref(props.tableOptions ?? store.state.datatable.options ?? {})

  const state = reactive({

    id: Math.ceil(Math.random() * 1000000) + '-table',
    hideTable: false,
    searchPlaceholder: t("Type to Search"),
    fillHeight: computed(() => props.fillHeight),
    windowSize: {
      x: 0,
      y: 0
    },
    isStoreTable,
    options,
    mobileTableLayout: computed(() => {
      return smAndDown.value
    }),
    enableCustomFooter: computed(() =>  props.paginationOptions.footerComponent === 'vuePagination'),
    footerProps: computed(() => {
      const footerProps = props.paginationOptions.footerProps
      if(state.enableInfiniteScroll){
        return {
          'hide-default-footer' : true,
        }
      }

      return footerProps
    }),
    // elements: computed(() => props.items ?? store.state.datatable.data ?? []),
    elements: elements,
    // datatable store
    loading: computed({
      get() {
        return isStoreTable.value ? store.state.datatable.loading : loading.value
      },
      set(val) {
        loading.value = val
      }
    }),
    editedIndex: editedIndex,
    selectedItems: [],

    activeTableItem: null,
    activeItemConfiguration: null,
    enableInfiniteScroll: computed(() => props.paginationOptions.footerComponent === 'infiniteScroll' && getters.totalElements.value > state.elements.length),
    draggableItems: computed(() => {
      const items = state.elements.reduce((prev, curr, currentIndex) => {
        const newItem = {
          "type": "item",
          "key": currentIndex+1,
          "value": curr.id, // Todo datatable ref item-key prop instead of static.id
          "index" : currentIndex,
          "selectable": props.showSelect,
          "columns": tableHeaders.headers.value.reduce((headersPrev, header) => {
            headersPrev[header.key] = curr[header.key]

            return headersPrev
          }, {})
        };

        // prev.push(newItem);
        prev[currentIndex] = newItem
        return prev;
      }, []); // Array of Objects

      return items;
    }),

    searchModel
  })

  const tableItemActions = useTableItemActions(props, {
    ...context,
    ...{
      tableForms,
      tableModals,
      tableEndpoints
    }

  })
  const methods = reactive({
    onResize () {
      state.windowSize = { x: window.innerWidth, y: window.innerHeight }
    },
    initialize: function () {

      if(!isStoreTable.value){
        store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, '')
      } else {
        state.loading = true
        store.dispatch(ACTIONS.GET_DATATABLE, { callback: (res) => {
          state.loading = false
        }})
      }
      // store.commit(
      //   DATATABLE.UPDATE_DATATABLE_OPTIONS,
      //   window[import.meta.env.VUE_APP_NAME].STORE.datatable.options
      // )
    },
    goNextPage () {
      if (state.options.page < store.getters.totalPage) { state.options.page += 1 }
    },
    goPreviousPage () {
      if (state.options.page > 1) { state.options.page -= 1 }
    },
    changeOptions(options){
      if(!_.isEqual(options, state.options)){
        state.options = options
      }
    },
    onIntersect(isIntersecting, entries, observer){
      if(isIntersecting && entries[0].intersectionRatio === 1){
        methods.goNextPage()
      }
    },
    loadItems(options = null){
      state.loading = true

      api.get(
        tableEndpoints.indexUrl.value, options ?? state.options, function(response){
          const incomingDataArray = response.resource.data
          if(state.enableInfiniteScroll){
            state.elements = state.elements.push(incomingDataArray)
          }else{
            state.elements = incomingDataArray
          }
          state.loading = false
        }
      )
    },

    setEditedItem: tableItem.setEditedItem,
    resetEditedItem: tableItem.resetEditedItem,

    setBulkItems(){
      store.commit(DATATABLE.REPLACE_DATATABLE_BULK, state.selectedItems)
    },
    async bulkAction(action){
      methods.setBulkItems()
      let studlyCase = _.snakeCase(action.name).toUpperCase()
      if(__isset(ACTIONS[studlyCase])){
        await store.dispatch(ACTIONS[studlyCase])
        state.selectedItems = []
      } else {
        // console.error(`${error}`)
        console.warn(`${action.name} may have not implemented yet on useTable.js hook`)
      }
    },
    // async bulkDelete(){
    //   await store.dispatch(ACTIONS.BULK_DELETE)
    //   state.selectedItems = []
    // },
    // async bulkRestore(){
    //   await store.dispatch(ACTIONS.BULK_RESTORE)
    //   state.selectedItems = []
    // },
    // async bulkForceDelete(){
    //   await store.dispatch(ACTIONS.BULK_DESTROY)
    //   state.selectedItems = []
    // },
    confirmAction(){
      methods.bulkAction(state.selectedAction)
      state.actionModalActive = false
    },
    closeActionModal(){
      state.actionModalActive = false
    },
    sortElements(list){
      // state.elements = list;

      api.reorder(
        tableEndpoints.reorderUrl.value,
        // For Optimistic UI approach, did not query for new list,
        // used response.status and new modelValue
        list.map((element) => element.id), function(response){
          if(response.status === 200){
            list.forEach((element, index) => element.position = index+1)
            state.elements = list
          }
        }
      )

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
              data[key] = getSubmitFormData(data.schema, item, store.state)
              if (data.actionUrl) {
                data.actionUrl = data.actionUrl.replace(`:${match}`, data[key].id)
              }
            } else if (item[match]) {
              data[key] = getSubmitFormData(data.schema, item[match], store.state)
              if (data.actionUrl) {
                data.actionUrl = data.actionUrl.replace(`:${match}`, data[key].id)
              }
            }
          }
        } else if (Array.isArray(data[key]) || _.isObject(data[key])) {
          data[key] = methods.hydrateNestedData(item, data[key])
        }
      }

      return data
    },
    searchItems(){
      if(tableFilters.search.value !== searchModel.value){
        tableFilters.search.value = searchModel.value
      }
    }
  })

  watch(() => tableItem.editedItem.value, (newValue, oldValue) => {
    state.editedIndex = state.elements.findIndex(o => { return o.id === newValue.id })
  })
  watch(() => state.activeTableItem, (newValue, oldValue) => {
    if (newValue) {
      // hydrate abstract fields
      state.activeItemConfiguration = methods.hydrateNestedData(newValue, JSON.parse(JSON.stringify(props.nestedData)))
      // __log(methods.hydrateNestedData(newValue, activeItemConfiguration))
    }
  }, { deep: true })
  // watch(() => state.formActive, (newValue, oldValue) => {
  //   newValue || form.value.resetValidation() || methods.resetEditedItem()
  // })
  watch(() => state.deleteModalActive, (newValue, oldValue) => {
    newValue || methods.resetEditedItem()
  })
  watch(() => state.options, (newValue, oldValue) => {
    if (isStoreTable.value) {
      store.dispatch(ACTIONS.GET_DATATABLE, { payload: { options: newValue, infiniteScroll: state.enableInfiniteScroll }, endpoint : props.endpoints.index ?? null})
    }else {
      newValue.replaceUrl = false
      methods.loadItems(newValue)
    }

  }, { deep: true })
  watch(() => state.elements, (newValue, oldValue) => {

    // Refresh edited item
    if(state.editedIndex > -1) {
      let refreshItem = newValue[state.editedIndex]
      tableItem.setEditedItem(refreshItem)
    }
  }, { deep: true })
  watch(() => store.state.datatable.data, (newValue, oldValue) => {
    state.elements = newValue;
  })

  const formatter = useFormatter(props, context, tableHeaders.headers.value)

  return {
    form,
    ...useTableActions(props, context),
    ...toRefs(state),
    ...toRefs(methods),
    ...tableNames,
    ...tableFilters,
    ...tableHeaders,
    ...tableForms,
    ...tableItemActions,
    ...tableModals,
    ...formatter,
    ...getters,
  }
}
