// hooks/useTable.js
import { watch, computed, nextTick, reactive, toRefs, ref, toRef} from 'vue'
import { useDisplay } from 'vuetify'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'

import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import api from '@/store/api/datatable'

import ACTIONS from '@/store/actions'

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
  defaultTableOptions: {
    type: Object,
    default: () => ({
      itemsPerPage: 10,
      page: 1,
      search: '',
      sortBy: [],
      groupBy: [],
    }),
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
  isModuleTable: {
    type: Boolean,
    default: false,
  },
  endpoints: {
    type: Object,
    default: {},
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
  let elements = ref(props.items ?? [])

  const isStoreTable = computed(() => {
    return props.endpoints.index.replace(/\/$/, '').split('?')[0] === window.location.href.replace(/\/$/, '').split('?')[0]
  })

  const form = ref(null)
  const loading = ref(false)
  const options = ref(_.pick({
    ...(props.defaultTableOptions ?? {}),
    ...(props.tableOptions ?? {}),
  }, ['itemsPerPage', 'page', 'sortBy', 'groupBy', 'search']))

  const totalNumberOfElements = ref(props.total ?? -1)
  const totalNumberOfPages = computed(() => Math.ceil(totalNumberOfElements.value / options.value.itemsPerPage))

  const setElements = (data) => {
    elements.value = data
  }

  const pushElements = (data) => {
    elements.value = elements.value.push(data)
  }

  const updateResponseFields = (response) => {
    totalNumberOfElements.value = response.resource.total
    tableFilters.setMainFilters(response.mainFilters)
    // tableFilters.advancedFilters.value = resource.advancedFilters

    if(state.enableInfiniteScroll){
      // state.elements = state.elements.push(response.resource.data)
      pushElements(response.resource.data)
    }else{
      // state.elements = response.resource.data
      setElements(response.resource.data)
    }
  }

  const loadItems = async (customOptions = null) => {
    state.loading = true

    const payload = {
      ...(customOptions ?? options.value),
      ...{ replaceUrl: false }
    }

    if(tableFilters.search.value !== ''){
      payload.search = tableFilters.search.value
    }

    if(tableFilters.activeFilterSlug.value !== 'all'){
      payload.filter = {
        ...(payload.filter ?? {}),
        status: tableFilters.activeFilterSlug.value,
      }
    }

    if(!_.isEmpty(tableFilters.activeAdvancedFilters.value)){
      payload.filter = {
        ...(payload.filter ?? {}),
        ...tableFilters.activeAdvancedFilters.value,
      }
    }

    if(_.isObject(props.endpoints) && props.endpoints.index) {
      await api.get( props.endpoints.index, payload,
        function(response){

          updateResponseFields(response)

          state.loading = false
        },
        function(errorResponse){
          state.loading = false
          console.error(errorResponse)
        }
      )
    } else {
      console.error(`No index endpoint found in endpoints of props`)
    }

    // state.isLoading = false
  }

  const tableItem = useTableItem()
  const tableNames = useTableNames(props, {
    editedIndex: editedIndex
  })
  const tableFilters = useTableFilters(props)
  const tableHeaders = useTableHeaders(props)
  const tableForms = useTableForms(props, {
    ...context,
    ...tableNames
  })
  const tableModals = useTableModals(props)
  const tableItemActions = useTableItemActions(props, {
    ...context,
    ...{
      tableForms
    }
  })

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
    loading,
    totalNumberOfElements,
    options,
    elements,


    // datatable store
    editedIndex: editedIndex,
    selectedItems: [],

    activeTableItem: null,
    activeItemConfiguration: null,
    enableInfiniteScroll: computed(() => props.paginationOptions.footerComponent === 'infiniteScroll' && totalNumberOfElements.value > elements.value.length),
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

  })

  const methods = reactive({
    onResize () {
      if(!props.noFullScreen){
        state.windowSize = { x: window.innerWidth, y: window.innerHeight }
      }
    },
    onIntersect(isIntersecting, entries, observer){
      if(isIntersecting && entries[0].intersectionRatio === 1){
        methods.goNextPage()
      }
    },
    initialize: function () {
      loadItems()
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

    setEditedItem: tableItem.setEditedItem,
    resetEditedItem: tableItem.resetEditedItem,
    sortElements(list){
      // state.elements = list;

      if(_.isObject(props.endpoints) && props.endpoints.reorder) {
        api.reorder(
          props.endpoints.reorder,
          // For Optimistic UI approach, did not query for new list,
          // used response.status and new modelValue
          list.map((element) => element.id), function(response){
            if(response.status === 200){
              list.forEach((element, index) => element.position = index+1)
              state.elements = list
            }
          }
        )
      } else {
        console.error(`No reorder endpoint found in endpoints of props`)
      }

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
    // filter
    searchItems(newSearchValue){
      if(!tableFilters.setSearchValue()) return

      loadItems()
    },
    changeFilter(slug){
      if(!tableFilters.setFilterSlug(slug)) return

      options.value.page = 1
      loadItems()
    },
    changeAdvancedFilter(slug){
      options.value.page = 1
      loadItems()
    },
    resetAdvancedFilter(){
      tableFilters.clearAdvancedFilter()
      options.value.page = 1
      loadItems()
    },

    handleFormActionComplete(payload) {
      // payload.action
      // payload.response
      const action = payload.action
      const response = payload.response

      if(action.type === 'request') {
        loadItems()
      }

      if(action.type === 'modal') {
        loadItems()
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
  // watch(() => state.deleteModalActive, (newValue, oldValue) => {
  //   __log('deleteModalActive', newValue)
  //   newValue || methods.resetEditedItem()
  // })
  watch(() => state.options, (newValue, oldValue) => {
    loadItems()
  }, { deep: true })
  watch(() => state.elements, (newValue, oldValue) => {
    // Refresh edited item
    if(state.editedIndex > -1) {
      let refreshItem = newValue[state.editedIndex]
      tableItem.setEditedItem(refreshItem)
    }
  }, { deep: true })

  // Set up watchers to handle action events
  watch(() => tableItemActions.actionEvents.event, (event) => {
    if (event) {
      const payload = tableItemActions.actionEvents.payload

      let callbackParameters = []
      let hasCallback = false
      let runCallback = false
      let updateItem = false
      let resetItem = true

      let runAlert = true
      let isLoadItems = true

      switch (event) {
        case 'dialog':
          if(payload.callback && typeof payload.callback === 'function') {
            hasCallback = tableModals.modals.value.dialog

            const attributes = {
              confirmClosing: false,
            }

            switch(payload.type){
              case 'delete':
              case 'forceDelete':
                attributes.description = tableNames.deleteQuestion.value
                break
              case 'bulkPublish':
              case 'bulkDelete':
              case 'bulkRestore':
              case 'bulkDestroy':
                callbackParameters.push(state.selectedItems)

                const kebabCase = _.kebabCase(payload.type)
                const langKey = `fields.confirm-${kebabCase}`
                attributes.description = t(langKey, {
                  count: state.selectedItems.length,
                  route: tableNames.transNamePlural.value
                })
                break
            }

            // callbackParameters.push(successCallback)
            // tableModals.modals.value.dialog.setConfirmCallback(() => payload.callback(...callbackParameters))
            tableModals.modals.value.dialog.set(attributes)
            // tableModals.modals.value.dialog.open()
          }
          break
        case 'process':

          if(payload.callback && typeof payload.callback === 'function') {
            runCallback = true
            // callbackParameters.push(successCallback)
          }

          if(payload.type === 'publish') {
            resetItem = false
            updateItem = true
          }

          break

        case 'showCustomForm':
          tableForms.customFormModalActive.value = true
          break

        case 'showData':
          tableModals.modals.value.show.loadData(payload.data)
          tableModals.modals.value.show.set(payload.action)
          tableModals.modals.value.show.open()
          break
      }

      let successCallback = (res) => {
        if(res.status === 200) {

          if(runAlert && res.data.variant && res.data.message){
            store.dispatch(ACTIONS.SHOW_ALERT, res.data)
          }

          if(isLoadItems) {
            loadItems()
          }

          if(hasCallback) {
            try {
              hasCallback.close()
              hasCallback.reset()
            } catch (error) {
              // console.error(error)
            }
          }

          // tableModals.modals.value.dialog.close()
          // tableModals.modals.value.dialog.reset()

          if(resetItem) {
            tableItem.resetEditedItem()
            state.selectedItems = []
          }

          if(updateItem && payload.item) {
            tableItem.setEditedItem(payload.item)
          }

        }
      }

      if(runCallback) {
        callbackParameters.push(successCallback)
        payload.callback(...callbackParameters)
      }

      if(hasCallback) {
        callbackParameters.push(successCallback)
        hasCallback.setConfirmCallback(() => payload.callback(...callbackParameters))
        hasCallback.open()
      }

      tableItemActions.actionEvents.reset()
      // Reset the event after handling it
    }
  }, { immediate: true })

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
  }
}
