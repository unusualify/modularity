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
  fillHeight: {
    type: Boolean,
    default: false,
  },
  items: {
    type: Array
  },
  total: {
    type: Number,
    default: null,
  },

  hideFooter: {
    type: Boolean,
    default: false
  },

  fixedHeader: {
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
  disableSort: {
    type: Boolean,
    default: false,
  },
  mustSort: {
    type: Boolean,
    default: true,
  },
  multiSort: {
    type: Boolean,
    default: false,
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

  const isStoreTable = computed(() => {
    return props.endpoints.index.replace(/\/$/, '').split('?')[0] === window.location.href.replace(/\/$/, '').split('?')[0]
  })
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

  // __log(props.tableOptions)
  const optionsSelf = ref({
    itemsPerPage: 10,
    page: 1,
    search: '',
    sortBy: [],
    groupBy: [],
    // mustSort: props.mustSort,
    // multiSort: props.multiSort,
    ...(props.tableOptions ?? {}),
  })

  // const options = ref(props.tableOptions ?? store.state.datatable.options ?? {})
  const options = ref(isStoreTable.value
    ? store.state.datatable.options
    : optionsSelf.value
  )
  // const options = computed(() => isStoreTable.value
  //   ? store.state.datatable.options
  //   : optionsSelf.value
  // )

  const numberOfElementsSelf = ref(props.total ?? 0)
  const numberOfElements = computed(() => !isStoreTable.value
     ? numberOfElementsSelf.value
     : store.state.datatable.total
  )
  const numberOfPages = computed(() => Math.ceil(numberOfElements.value / options.value.itemsPerPage))

  const loadItems = async (customOptions = null) => {
    state.isLoading = true

    await api.get(
      tableEndpoints.indexUrl.value, {
        ...(customOptions ?? options.value),
        ...{
          replaceUrl: false,
        }
      },
      function(response){
        numberOfElementsSelf.value = response.resource.total

        if(state.enableInfiniteScroll){
          state.elements = state.elements.push(response.resource.data)
        }else{
          state.elements = response.resource.data
        }
        state.isLoading = false
      },
      function(errorResponse){
        state.isLoading = false
        console.error(errorResponse)
      }
    )
    // state.isLoading = false
  }

  const state = reactive({

    id: Math.ceil(Math.random() * 1000000) + '-table',
    isStoreTable,
    hideTable: false,
    searchPlaceholder: t("Type to Search"),
    fillHeight: computed(() => props.fillHeight),
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
    windowSize: {
      x: 0,
      y: 0
    },
    mobileTableLayout: computed(() => {
      return smAndDown.value
    }),

    numberOfElements,
    options,

    elements,

    // datatable store
    isLoading: computed({
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
    enableInfiniteScroll: computed(() => props.paginationOptions.footerComponent === 'infiniteScroll' && numberOfElements.value > elements.value.length),
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

    searchModel,
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
      if(!props.noFullScreen){
        state.windowSize = { x: window.innerWidth, y: window.innerHeight }
      }
    },
    initialize: function () {

      if(!isStoreTable.value){
        // store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, '')
        loadItems(state.optionsSelf)
      } else {
        state.isLoading = true
        store.dispatch(ACTIONS.GET_DATATABLE, { callback: (res) => {
          state.isLoading = false
        }})
      }
      // store.commit(
      //   DATATABLE.UPDATE_DATATABLE_OPTIONS,
      //   window[import.meta.env.VUE_APP_NAME].STORE.datatable.options
      // )
    },
    goNextPage () {
      if (state.options.page < numberOfPages.value) { state.options.page += 1 }
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
    } else {
      loadItems(newValue)
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
