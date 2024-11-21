// hooks/useTable.js
import { watch, computed, nextTick, reactive, toRefs, ref, toRef, onMounted} from 'vue'
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
  striped: Boolean,
  hideBorderRow: Boolean,
  roundedRows: Boolean,

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
  navActive: {
    type: String,
    default: 'all'
  },
  endpoints: {
    type: Object,
    default : {}
  },
  showSelect: {
    type: Boolean,
    default: true,
  },
  sticky:{
    type: Boolean,
    default: true,
  },
  cellOptions: {
    type: [Array, Object],
    default: {}
  },
  headerOptions: {
    type: [Array, Object],
    default: {}
  }
})

// by convention, composable function names start with "use"
export default function useTable (props, context) {
  // state encapsulated and managed by the composable
  // a composable can also hook into its owner component's
  // lifecycle to setup and teardown side effects.

  const store = useStore()
  const { smAndDown } = useDisplay()
  const { t, te, tm } = useI18n({ useScope: 'global' })

  const getters = mapGetters()

  const form = ref(null)
  let loading = ref(false)
  let items = ref(props.items ?? store.state.datatable.data)
  let headers = props.columns ?? store.state.datatable.headers ?? []

  const state = reactive({

    id: Math.ceil(Math.random() * 1000000) + '-table',
    formRef: computed(() => {
      return state.id + '-form'
    }),
    formStyles: { width: props.formWidth },
    formActive: false,
    deleteModalActive: false,
    customModalActive: !(_.isEmpty(store._state.data.datatable.customModal)),
    activeModal: 'custom',
    customFormModalActive : false,
    customFormAttributes: {},
    customFormSchema: {},
    customFormModel: {},
    activeTableItem: null,
    hideTable: false,
    fillHeight: computed(() => props.fillHeight),
    createUrl: computed(() => props.endpoints.create ?? null),
    editUrl: computed(() => props.endpoints.edit ??  null ),
    reorderUrl: computed(() => props.endpoints.reorder ?? null),
    editedIndex: -1,
    selectedItems: [],
    windowSize: {
      x: 0,
      y: 0
    },
    snakeName: _.snakeCase(props.name),
    permissionName: _.kebabCase(props.name),
    transNameSingular: computed(() => te('modules.' + state.snakeName, 0) ? t('modules.' + state.snakeName, 0) : props.name),
    transNamePlural: computed(() => t('modules.' + state.snakeName, 1)),
    transNameCountable: computed(() => t('modules.' + state.snakeName, getters.totalElements.value)),
    tableTitle: computed(() => {
      const prefix = props.titlePrefix ? props.titlePrefix : ''
      return prefix + (__isset(props.customTitle) ? props.customTitle : state.transNamePlural)
    }),
    formTitle: computed(() => {
      let translationKey = state.editedIndex === -1 ? 'fields.new-item' : 'fields.edit-item'
      return t(translationKey, { item: state.transNameSingular})
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
          ? (_.isObject(state.editedItem[props.titleKey]) ? state.editedItem[props.titleKey][store.state.user.locale] : state.editedItem[props.titleKey])
          : '').toLocaleUpperCase()
      })
    }),
    isSoftDeletableItem: computed(() => {
      return methods.isSoftDeletable(state.editedItem)
    }),
    activeItemConfiguration: null,
    options:  props.tableOptions ?? store.state.datatable.options ?? {},

    headers: headers,
    headersModel: _.cloneDeep(headers),
    headersModel_: computed({
      get(){
        return headers;
      },
      set(val){
        // items.value = val
      }
    }),
    selectedHeaders: computed(() => {
      return state.headers.filter(header => !!header.visible && header.visible == true)
    }),

    inputs: props.inputFields ?? store.state.datatable.inputs ?? [],
    // elements: computed(() => props.items ?? store.state.datatable.data ?? []),
    elements: computed({
      get(){
        return items.value;
      },
      set(val){
        items.value = val
      }
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
    loading: computed({
      get() {
        return props.endpoints.index ? loading.value : store.state.datatable.loading
      },
      set(val) {
        loading.value = val
      }
    }),
    filterActiveStatus: computed(() => store.state.datatable.filter.status ?? 'all'),
    filterActive: computed(() => _.find(store.state.datatable.mainFilters, { slug: state.filterActiveStatus })),
    // form store
    editedItem: computed(() => store.state.form.editedItem ?? {}),
    formLoading: computed(() => store.state.form.loading ?? false),
    formErrors: computed(() => store.state.form.errors ?? {}),

    formIsValid: computed(function () {
      // __log(form?.value?.valid, form?.value)
      return form?.value?.validModel ?? null
    }),
    mobileTableLayout: computed(() => {
      return smAndDown.value
    }),
    hideSearchField: computed(()=> {return props.hideSearchField}),
    tableSubtitle: computed(() => {
      return __isset(props.tableSubtitle) ? t(props.tableSubtitle) : ''
    }),
    searchText: computed(() => t("Type to Search")),
    addBtnTitle: computed(() => {
      if(props.createOnModal || props.editOnModal){
        return props.addBtnOptions.text ? t(props.addBtnOptions.text) : t('fields.add-item', {'item' : state.transNameSingular})
      }else{
        return props.addBtnOptions.text ?? t('ADD NEW')
      }
    }),
    filterBtnTitle: computed(() => {
      return {
        text: `${state.filterActive?.name} (${state.filterActive?.number})`,
      }
    }),
    enableIterators: computed(() => Object.keys(props.customRowComponent).length),
    hideHeaders: computed(() => {
      return props.hideHeaders || state.enableIterators
    }),
    enableInfiniteScroll: computed(() => props.paginationOptions.footerComponent === 'infiniteScroll' && getters.totalElements.value > state.elements.length),
    navActive: computed(() => state.filterActive?.slug ?? 'all'),
    mainFilters: computed(() => store.state.datatable.mainFilters ?? null),
    indexUrl: computed(() => props.endpoints.index ?? null),
    actionModalActive: false,
    selectedAction: null,
    actionDialogQuestion: computed(() => {
      return t('confirm-action', {
        // route: state.transName.toLowerCase(),
        route: state.transNameSingular,
        action: t(state.selectedAction?.name ?? ''),
      })
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
    advancedFilters: computed(() => store.state.datatable.advancedFilters ?? null),
    draggableItems: computed(() => {
      const items = state.elements.reduce((prev, curr, currentIndex) => {
        const newItem = {
          "type": "item",
          "key": currentIndex+1,
          "value": curr.id, // Todo datatable ref item-key prop instead of static.id
          "index" : currentIndex,
          "selectable": props.showSelect,
          "columns": state.headers.reduce((headersPrev, header) => {
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
    deleteModal: ref(null),
    modals: {
      'delete': {
        // title: computed(() => t('confirm-deletion')),
        content: computed(() => state.deleteQuestion),
        confirmAction () {

          if (state.isSoftDeletableItem) {
            store.dispatch(ACTIONS.DESTROY_ITEM, {
              id: state.editedItem.id,
              callback: () => {
                state.customModalActive = false
              },
              errorCallback: () => {

              }
            })
          } else {
            store.dispatch(ACTIONS.DELETE_ITEM, {
              id: state.editedItem.id,
              callback: () => {
                state.customModalActive = false
              },
              errorCallback: () => {

              }
            })
          }
        },
        closeAction () {
          state.customModalActive = false
          // this.active = false
        }
      },
      'action': {
        content: computed(() => state.actionDialogQuestion),
        confirmAction() {
          methods.bulkAction(state.selectedAction)
          state.customModalActive = false;
        },
        openAction(action) {
          // state.actionModalActive = true
          state.selectedAction = action
          state.customModalActive = true;
        },
        closeAction() {
          state.customModalActive = false;
        }
      },
      'custom': {
        content: computed(() => store._state.data.datatable.customModal.description),
        closeAction() {
          state.customModalActive = false;
          state.modals.custom.confirmText = '';
          state.modals.custom.cancelText = '';
          state.modals.custom.img = '';
          state.modals.custom.icon = '';
          state.modals.custom.iconSize = null;
          state.modals.custom.title = '';
          state.modals.custom.color = '';
          console.log(state.modals.custom)
        },
        confirmAction() {
          state.customModalActive = false;
          state.modals.custom.confirmText = '';
          state.modals.custom.cancelText = '';
          state.modals.custom.img = '';
          state.modals.custom.icon = '';
          state.modals.custom.iconSize = null;
          state.modals.custom.title = '';
          state.modals.custom.color = '';
          console.log(state.modals.custom)
        },
        confirmText: 'Done',
        cancelText: ' ',
        img: 'https://cdn2.iconfinder.com/data/icons/greenline/512/check-1024.png',
        icon: store._state.data.datatable.customModal.icon,
        iconSize: 72,
        title: 'Payment Complete',
        color: 'success'
      }
    }
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
          // __log(methods.isSoftDeletable(item), action)
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
        // __log(action, action.can)
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
    itemAction: function (item = null, action = null, name ,...args) {

      let _action = {}

      if (__isString(action)) {
        _action.name = action
        // if (_action.name.includes('bulk')) { store.commit(DATATABLE.REPLACE_DATATABLE_BULK, state.selectedItems) }
      } else { _action = action }

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
          // this.$refs.dialog.openModal()
          // methods.openDeleteModal()
          methods.setEditedItem(item)
          state.activeModal = 'delete';
          state.customModalActive = true;
          break
        case 'forceDelete':
          methods.setEditedItem(item)
          state.activeModal = 'delete';
          state.customModalActive = true;
          break
        case 'restore':
          methods.setEditedItem(item)
          methods.restoreRow(item.id)
          // this.$refs.dialog.openModal()
          break
        case 'duplicate':
          methods.setEditedItem(_.omit(item, 'id'))
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
          state.activeTableItem = _.find(state.elements, { id: item.id })
          break
        case 'bulkDelete':
          state.activeModal = 'action';
          state.customModalActive = true;
          state.selectedAction = _action;
          break
        case 'bulkForceDelete':
          state.activeModal = 'action';
          state.customModalActive = true;
          state.selectedAction = _action;
          break
        case 'bulkRestore':
          state.activeModal = 'action';
          state.customModalActive = true;
          state.selectedAction = _action;
          break
        // case 'pay':
        //   methods.setEditedItem(item);
        //   // state.payModalData.schema['payment-service'].price = item._price;
        //   // state.payModalData.schema = methods.preparePaySchema(_action.schema,item);
        //   // state.payModalData.active = true
        //   // console.log(_action, action);
        //   // return;
        //   state.customFormModalSchema = _action.schema;
        //   state.customFormModalActive = true;



        //   // state.payModalData.active = true;
        //   // state.payModalData.schema = _action.schema;

        //   break;
        default:
          break
      }
      if(_action.form){
        //use clone_dip
        // console.log(_action.form)
        state.customFormSchema = _.cloneDeep(_action.form.attributes.schema);
        state.customFormAttributes = _.cloneDeep(_action.form.attributes);

        // console.log(state.customFormSchema);
        if(_action.form.hasOwnProperty('model_formatter')){
          for(let key in _action.form.model_formatter){
            let attr = _.get(item,_action.form.model_formatter[key], '')
            _.set(state.customFormModel,key, attr);
          }
        }
        if(_action.form.hasOwnProperty('schema_formatter')){
          for(let key in _action.form.schema_formatter){
            let attr = _.get(item,_action.form.schema_formatter[key], '');
            _.set(state.customFormAttributes.schema,key, attr)
          }
        }
        // console.log(state.customFormModel, state.customFormAttributes)
        // state.customFormAttributes.actionUrl = state.customFormAttributes.actionUrl.replace(':id', item.price.id);
        // console.log(item)

        // console.log( state.customFormAttributes)
        // console.log(state.customFormActionUrl);
        state.customFormModalActive = true;
        return;
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
      state.activeTableItem = _.find(state.elements, { id: item.id })
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
      // state.modals.form.active = true;
    },
    closeForm: function () {
      state.formActive = false
    },
    confirmFormModal () {
      form.value.submit(null, (res) => {
        if (Object.prototype.hasOwnProperty.call(res, 'variant') && res.variant.toLowerCase() === 'success') { methods.closeForm() }
      })
    },
    _openDeleteModal: function (modal) {
      modal.open()
      // state.deleteModal.open()
      // state.deleteModalActive = true
    },
    _closeDeleteModal: function () {
      state.modals[0].active.value = false
      // state.modals[0].ref.close()
      // state.deleteModalActive = false
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
      if (state.navActive === slug) return
      store.commit(DATATABLE.UPDATE_DATATABLE_PAGE, 1)
      store.commit(DATATABLE.UPDATE_DATATABLE_FILTER_STATUS, slug)
      state.selectedItems = []
      store.dispatch(ACTIONS.GET_DATATABLE)
    },
    changeOptions(options){
      if(!_.isEqual(options, state.options)){
        state.options = options
      }
    },
    canBulkAction(action){
      // if(methods.canItemAction(action)){
      //   switch (action.name) {
      //     case 'forceDelete':
      //       return state.navActive === 'trash'
      //     case 'restore':
      //       return state.navActive === 'trash'
      //     case 'delete':
      //       return state.navActive !== 'trash'
      //     default:
      //       console.error('Action not defined in conditional pipe line - useTable:709')
      //       return false
      //   }
      // }

      return true
    },
    setBulkItems(){
      store.commit(DATATABLE.REPLACE_DATATABLE_BULK, state.selectedItems)
    },
    bulkAction(action){
      methods.setBulkItems()
      try {
        methods[action.name]()
      } catch (error) {
        console.error(`${error}`)
        console.warn(`${action.name} may have not implemented yet on useTable.js hook`)
      }
    },
    async bulkDelete(){
      await store.dispatch(ACTIONS.BULK_DELETE)
      state.selectedItems = []
    },
    async bulkRestore(){
      await store.dispatch(ACTIONS.BULK_RESTORE)
      state.selectedItems = []

    },
    async bulkForceDelete(){
      await store.dispatch(ACTIONS.BULK_DESTROY)
      state.selectedItems = []
    },
    onIntersect(isIntersecting, entries, observer){
      if(isIntersecting && entries[0].intersectionRatio === 1){
        methods.goNextPage()
      }
    },
    loadItems(options = null){
      state.loading = true
      api.get(
        state.indexUrl, options ?? state.options, function(response){
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
    closeActionModal(){
      state.actionModalActive = false
    },
    _openActionModal(action, name){
      return;
      state.customModalActive = true;
      openAction=true;
      // state.actionModalActive = true
      // state.selectedAction = action
    },
    confirmAction(){
      methods.bulkAction(state.selectedAction)
      state.actionModalActive = false
    },
    submitAdvancedFilter(){
      store.commit(DATATABLE.UPDATE_DATATABLE_ADVANCED_FILTER, state.advancedFilters)
      store.commit(DATATABLE.UPDATE_DATATABLE_PAGE, 1)
      store.dispatch(ACTIONS.GET_DATATABLE)
    },
    clearAdvancedFilter(){
      store.commit(DATATABLE.RESET_DATATABLE_ADVANCED_FILTER)
      store.commit(DATATABLE.UPDATE_DATATABLE_PAGE, 1)
      store.dispatch(ACTIONS.GET_DATATABLE)
    },
    sortElements(list){
      // state.elements = list;

      api.reorder(
        state.reorderUrl,
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
    applyHeaders(){
      state.headers = _.cloneDeep(state.headersModel)

      __pushQueryParams({
        columns: state.headersModel.reduce((acc, h) => {
          if(h.visible){
            acc.push(h.key)
          }
          return acc
        }, [])
      })
      // store.commit(DATATABLE.UPDATE_DATATABLE_HEADERS, state.headersModel)
    }
  })

  watch(() => state.editedItem, (newValue, oldValue) => {
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

    if( state.indexUrl ){
      newValue.replaceUrl = false
      methods.loadItems(newValue)

    } else {
      store.dispatch(ACTIONS.GET_DATATABLE, { payload: { options: newValue, infiniteScroll: state.enableInfiniteScroll }, endpoint : props.endpoints.index ?? null})

    }

  }, { deep: true })
  watch(() => state.elements, (newValue, oldValue) => {
  }, { deep: true })
  watch(() => store.state.datatable.data, (newValue, oldValue) => {
    state.elements = newValue;
  })
  const formatter = useFormatter(props, context, state.headers)

  // expose managed state as return value

  onMounted(() => {
    console.log('Component using useTable is mounted')
    // methods.initialize()
    if(store._state.data.datatable.customModal){
      __removeQueryParams(['customModal[description]', 'customModal[color]', 'customModal[icon]']);
    }
  })

  return {
    form,
    ...toRefs(state),
    ...getters,
    ...toRefs(methods),
    ...formatter
  }
}
