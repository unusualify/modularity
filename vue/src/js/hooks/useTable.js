// hooks/useTable.js
import { watch, computed, nextTick, reactive, toRefs, ref, watchEffect } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import { isObject, find, omit, snakeCase, kebabCase } from 'lodash-es'

import { DATATABLE, FORM } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

import { mapGetters } from '@/utils/mapStore'
import { getSubmitFormData } from '@/utils/getFormData.js'

import { useFormatter, useRoot } from '@/hooks'

export const makeTableProps = propsFactory({
  name: {
    type: String
  },
  customTitle: {
    type: String
  },
  fillHeight: {
    type: Boolean,
    default: false,
  },
  titlePrefix: {
    type: String,
    default: ''
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
  hideSearchField: {
    type: Boolean,
    default: false,
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
  tableDensity:{
    type:String,
    default: 'comfortable',
  },
  tableSubtitle:{
    type:String,
    default: '',
  },
  toolbarOptions:{
    type: Object,
    default: {},
  },
  rowActionsIcon:{
    type: String,
    default: 'mdi-cog-outline'
  },

  addBtnOptions:{
    type: Object,
    default: {},
  },
  filterBtnOptions:{
    type:Object,
    default: {},
  },
  customRowComponent: {
    type: Object,
    default: {}
  },
  bulkActions: {
    type: [Array, Object],
    default: []
  },
  paginationOptions: {
    type: [Array, Object],
    default: {}
  } ,
})

// by convention, composable function names start with "use"
export default function useTable (props, context) {
  // state encapsulated and managed by the composable
  // a composable can also hook into its owner component's
  // lifecycle to setup and teardown side effects.

  const store = useStore()

  const { isSmAndDown } = useRoot()

  const { t, te } = useI18n({ useScope: 'global' })

  const getters = mapGetters()

  const form = ref(null)

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
    fillHeight: computed(() => props.fillHeight),
    createUrl: window[import.meta.env.VUE_APP_NAME].ENDPOINTS.create ?? props.endpoints.create ?? '',
    editUrl: computed(()=> {
      return window[import.meta.env.VUE_APP_NAME].ENDPOINTS.edit ?? props.endpoints.edit ?? ''
    }),
    editedIndex: -1,
    selectedItems: [],
    windowSize: {
      x: 0,
      y: 0
    },
    snakeName: snakeCase(props.name),
    permissionName: kebabCase(props.name),
    transNameSingular: computed(() => te('modules.' + state.snakeName, 0) ? t('modules.' + state.snakeName, 0) : props.name),
    transNamePlural: computed(() => t('modules.' + state.snakeName, 1)),
    transNameCountable: computed(() => t('modules.' + state.snakeName, getters.totalElements.value)),
    tableTitle: computed(() => {
      const prefix = props.titlePrefix ? props.titlePrefix : ''
      return prefix + (__isset(props.customTitle) ? props.customTitle : state.transNamePlural)
    }),
    formTitle: computed(() => {
      return t((state.editedIndex === -1 ? 'new-item' : 'edit-item'), {
        item: te(`modules.${state.snakeName}`) ? t(`modules.${state.snakeName}`, 0) : props.name
      })
    }),
    deleteQuestion: computed(() => {
      // __log(store.state.form.editedItem, props.titleKey)
      const langKey = state.isSoftDeletableItem
        ? 'confirm-soft-deletion'
        : 'confirm-deletion'
      return t(langKey, {
        // route: state.transName.toLowerCase(),
        route: state.transNameSingular,
        name: (state.editedItem[props.titleKey]
          ? (isObject(state.editedItem[props.titleKey]) ? state.editedItem[props.titleKey][store.state.user.locale] : state.editedItem[props.titleKey])
          : '').toLocaleUpperCase()
      })
    }),
    isSoftDeletableItem: computed(() => {
      return methods.isSoftDeletable(state.editedItem)
    }),
    activeItemConfiguration: null,
    options:  props.tableOptions ?? store.state.datatable.options ?? {},
    headers: props.columns ?? store.state.datatable.headers ?? [],
    headersWithKeys: computed(() => {
      let collection = {};
       Object.values(state.headers).forEach((header, i) => {
        // let k =

        // let newObject = Object.create({ })
         collection[header['key']] = header
       })

       return collection
    }),
    inputs: props.inputFields ?? store.state.datatable.inputs ?? [],
    // elements: computed(() => props.items ?? store.state.datatable.data ?? []),
    elements: computed(() => {
      return props.items ?? store.state.datatable.data ?? []

    }),
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
    formLoading: computed(() => store.state.form.loading ?? false),
    formErrors: computed(() => store.state.form.errors ?? {}),

    formIsValid: computed(function () {
      // __log(form?.value?.valid, form?.value)
      return form?.value?.valid ?? null
    }),
    mobileTableLayout: computed(() => {
      return isSmAndDown
    }),
    hideSearchField: computed(()=> {return props.hideSearchField}),
    tableSubtitle: computed(() => {
      return __isset(props.tableSubtitle) ? t(props.tableSubtitle) : ''
    }),
    searchText: computed(() => t("Type to Search")),
    addBtnTitle: computed(() => {
      if(props.createOnModal || props.editOnModal){
        return props.addBtnOptions.text ? t(props.addBtnOptions.text) : t('add-item', {'item' : state.transNameSingular})
      }else{
        return props.addBtnOptions.text ?? t('ADD NEW')
      }
    }),
    filterBtnTitle: computed(() => {
      return {
        text: `${state.filterActive.name} (${state.filterActive.number})`,
      }
    }),
    enableIterators: computed(() => Object.keys(props.customRowComponent).length),
    hideHeaders: computed(() => {
      return props.hideHeaders || state.enableIterators
    }),
    enableCustomFooter: computed(() =>  props.paginationOptions.footerComponent === 'vuePagination'),
    defaultFooterProps: computed(() => {
      const footerProps = props.paginationOptions.footerProps
      if(props.paginationOptions.footerComponent === 'default'){
        return {
          'items-per-page-options': footerProps.itemsPerPageOptions,
          'items-per-page-text': footerProps.itemsPerPageText,
          'items-per-page': footerProps.itemsPerPage,
          'first-icon' : footerProps.firstIcon,
          'last-icon': footerProps.lastIcon,
          'next-icon' : footerProps.nextIcon,
          'prev-icon' : footerProps.prevIcon,
          'show-current-page': footerProps.showCurrentPage,
        }
      }else{
        return {
          'hide-default-footer' : true
        }
      }
    }),
    customFooterProps: computed(() => {
      const customComponent = props.paginationOptions.footerComponent

      if(state.enableCustomFooter){
        const footerProps = props.paginationOptions[customComponent]
        if(customComponent === 'vuePagination'){
          return {
            'variant' : footerProps.variant, //'flat' | 'elevated' | 'tonal' | 'outlined' | 'text' | 'plain' -- 'text' in default
            'border' : footerProps.border,
            'active-color' : footerProps.activeColor,
            'color' : footerProps.color, // utility colors or rgba(x,x,x,a),
            'density' : footerProps.density, // default | comfortable | compact
            'elevation' : footerProps.elevation, // string | number or undefined in default
            'ellipsis': footerProps.ellipsis, // string '...' in default
            'first-icon' : footerProps.firstIcon,
            'last-icon' : footerProps.lastIcon,
            'next-icon' : footerProps.nextIcon,
            'prev-icon' : footerProps.prevIcon,
            'rounded' : footerProps.rounded, // string|number or boolean 0.xs.sm.true,lg,xl,pill, circle, and shaped
            'show-first-last-page' : footerProps.showFirstLastPage, // boolean,
            'size' : footerProps.size, // string | number  Sets the height and width of the component. Default unit is px. Can also use the following predefined sizes: x-small, small, default, large, and x-large.
            'total-visible' : footerProps.totalVisible === 'auto' ? store.getters.totalPage : footerProps.totalVisible,
          }
        }
      }


    }),

  })

  const methods = reactive({
    onResize () {
      state.windowSize = { x: window.innerWidth, y: window.innerHeight }
    },
    initialize: function () {
      store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, '')
      store.commit(
        DATATABLE.UPDATE_DATATABLE_OPTIONS,
        window[import.meta.env.VUE_APP_NAME].STORE.datatable.options
      )
      store.dispatch(ACTIONS.GET_DATATABLE)
    },
    setEditedItem: function (item) {
      store.commit(FORM.SET_EDITED_ITEM, item)
    },
    resetEditedItem: function () {
      // __log('resetEditedItem')
      nextTick(() => {
        store.commit(FORM.RESET_EDITED_ITEM)
      })
    },
    itemHasAction: function (item, action) {
      let hasAction = true
      switch (action.name) {
        case 'edit':
          if (methods.isSoftDeletable(item)) {
            hasAction = false
          } else {
            hasAction = hasAction = methods.canItemAction(action)
          }
          break
        case 'delete':
          if (methods.isSoftDeletable(item)) {
            hasAction = false
          } else {
            hasAction = methods.canItemAction(action)
          }
          break
        case 'forceDelete':
          if (methods.isSoftDeletable(item)) {
            hasAction = methods.canItemAction(action)
          } else {
            hasAction = false
          }
          break
        case 'restore':
          if (methods.isSoftDeletable(item)) {
            hasAction = methods.canItemAction(action)
          } else {
            hasAction = false
          }
          break
        case 'duplicate':
          if (methods.isSoftDeletable(item)) {
            hasAction = false
          }
          break
        case 'switch':
          if (methods.isSoftDeletable(item)) {
            hasAction = false
          }
          break
        case 'activate':
          if (methods.isSoftDeletable(item)) {
            hasAction = false
          }
          break
        default:
          break
      }

      return hasAction
    },
    canItemAction: function (action) {
      // __log(store.getters.userPermissions)
      if (__isset(action.can) && action.can) {
        // if (store.getters.isSuperAdmin) {
        //   return true
        // }
        // __log(
        //   action.can
        // )
        // __log(
        //   permission,
        //   this.$store.getters.isSuperAdmin,
        //   this.$store.getters.userPermissions
        // )
        // if (this.$store.getters.isSuperAdmin) {
        //   return true
        // }

        // return false
      }

      return true
    },
    itemAction: function (item, action, ...args) {
      let _action = {}
      if (__isString(action)) { _action.name = action } else { _action = action }

      switch (_action.name) {
        case 'edit':
          if (props.editOnModal || props.embeddedForm) {
            methods.setEditedItem(item)
            methods.openForm()
            // this.$refs.formModal.openModal()
          } else {
            const route = state.editUrl.replace(':id', item.id)
            window.open(route)
          }
          break
        case 'delete':
          methods.setEditedItem(item)
          // this.$refs.dialog.openModal()
          methods.openDeleteModal()
          break
        case 'forceDelete':
          methods.setEditedItem(item)
          // this.$refs.dialog.openModal()
          methods.openDeleteModal()
          break
        case 'restore':
          methods.setEditedItem(item)
          methods.restoreRow(item.id)
          // this.$refs.dialog.openModal()
          break
        case 'duplicate':
          methods.setEditedItem(omit(item, 'id'))
          methods.openForm()

          // methods.duplicateRow(item.id)
          // this.$refs.dialog.openModal()
          // methods.openDeleteModal()
          break
        case 'link':
          window.open(
            _action.url.replace(':id', item.id),
            '_blank'
          )
          break
        case 'switch':
          var value = args[0]
          var key = args[1]
          store.dispatch(ACTIONS.SAVE_FORM, {
            plain: true,
            item: {
              id: item.id,
              ...{ [key]: value }
            },
            callback: function () {
              item[key] = value

              if (state.editedItem.id === item.id) {
                methods.setEditedItem({
                  ...state.editedItem,
                  ...{ [key]: value }
                })
              }
            },
            errorCallback: function () {}
          })
          break
        case 'activate':
          state.activeTableItem = find(state.elements, { id: item.id })
          break
        default:
          break
      }
    },

    can (permission) {
      const name = state.permissionName + '_' + permission

      if (store.getters.isSuperAdmin) {
        return true
      } else {
        return store.getters.userPermissions[name]
      }
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
    duplicateItem: function (item) {
      methods.setEditedItem(item)
      methods.duplicateRow(item.id)
    },
    switchItem: function (item, value, key) {
      // __log(value, key)
      // item[key] = value
      // methods.setEditedItem(item)

      store.dispatch(ACTIONS.SAVE_FORM, {
        plain: true,
        item: {
          id: item.id,
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
        } else if (Array.isArray(data[key]) || __isObject(data[key])) {
          data[key] = methods.hydrateNestedData(item, data[key])
        }
      }

      return data
    },

    deleteRow: function () {
      if (state.isSoftDeletableItem) {
        store.dispatch(ACTIONS.DESTROY_ITEM, {
          id: state.editedItem.id,
          callback: () => {
            methods.closeDeleteModal()
          },
          errorCallback: () => {

          }
        })
      } else {
        store.dispatch(ACTIONS.DELETE_ITEM, {
          id: state.editedItem.id,
          callback: () => {
            methods.closeDeleteModal()
          },
          errorCallback: () => {

          }
        })
      }
    },
    duplicateRow: function () {
      // __log(state.editedItem)
      store.dispatch(ACTIONS.DUPLICATE_ITEM, {
        id: state.editedItem.id,
        callback: () => {

        },
        errorCallback: () => {

        }
      })
    },
    restoreRow: function () {
      store.dispatch(ACTIONS.RESTORE_ITEM, {
        id: state.editedItem.id,
        callback: () => {

        },
        errorCallback: () => {

        }
      })
    },
    forceDeleteRow: function () {
      // __log(state.editedItem)
      store.dispatch(ACTIONS.DESTROY_ITEM, {
        id: state.editedItem.id,
        callback: () => {

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
    confirmFormModal () {
      form.value.submit(null, (res) => {
        if (Object.prototype.hasOwnProperty.call(res, 'variant') && res.variant.toLowerCase() === 'success') { methods.closeForm() }
      })
    },

    openDeleteModal: function () {
      state.deleteModalActive = true
    },
    closeDeleteModal: function () {
      state.deleteModalActive = false
    },

    isSoftDeletable (item) {
      return !!(__isset(item.deleted_at) && item.deleted_at)
    },
    goNextPage () {
      if (state.options.page < store.getters.totalPage) { state.options.page += 1 }
    },
    goPreviousPage () {
      if (state.options.page > 1) { state.options.page -= 1 }
    },
    filterStatus(slug){
      if (this.navActive === slug) return
      store.commit(DATATABLE.UPDATE_DATATABLE_PAGE, 1)
      store.commit(DATATABLE.UPDATE_DATATABLE_FILTER_STATUS, slug)
      store.dispatch(ACTIONS.GET_DATATABLE)
    },
    changeOptions(options){
      state.options = options
    },
    setBulkItems(){
      store.commit(DATATABLE.REPLACE_DATATABLE_BULK, state.selectedItems)
    },
    bulkAction(action){
      methods.setBulkItems()
      switch (action.name) {
        case 'delete':
          methods.bulkDelete()
          break;
        case 'forceDelete':
          break
        case 'restore':

        default:
          break;
      }

    },
    bulkDelete(){
      store.dispatch(ACTIONS.BULK_DELETE)
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
    newValue || form.value.resetValidation() || methods.resetEditedItem()
  })
  watch(() => state.deleteModalActive, (newValue, oldValue) => {
    newValue || methods.resetEditedItem()
  })
  watch(() => state.options, (newValue, oldValue) => {
    // state.options.page = newValue

    store.dispatch(ACTIONS.GET_DATATABLE, { payload: { options: newValue } })
  }, { deep: true })
  watch(() => state.elements, (newValue, oldValue) => {
    // __log('elements watch', newValue, oldValue)
  }, { deep: true })

  const formatter = useFormatter(props, context, state.headers)

  // expose managed state as return value
  return {
    form,
    ...toRefs(state),
    ...getters,
    ...toRefs(methods),
    ...formatter,

    isSmAndDown
  }
}
