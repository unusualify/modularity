import { mapState, mapGetters } from 'vuex'
import { DATATABLE, FORM } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

export default {
  // props: {
  //   name: {
  //     type: String,
  //     default: 'Item'
  //   },
  //   titleKey: {
  //     type: String,
  //     default: 'name'
  //   },
  //   items: {
  //     type: Array
  //   },
  //   columns: {
  //     type: Array
  //   },
  //   hideDefaultHeader: Boolean,
  //   hideDefaultFooter: Boolean,
  //   isRowEditing: Boolean,
  //   createOnModal: Boolean,
  //   editOnModal: Boolean
  // },
  // data: function () {
  //   return {
  //     editedIndex: -1,
  //     createUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.create,
  //     editUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.edit,

  //     selectedItems: []
  //   }
  // },
  // watch: {
  //   editedItem (val) {
  //     this.editedIndex = this.elements.findIndex(o => { return o.id === val.id })
  //   },
  //   selectedItems (val) {
  //     __log('selectedItems watch', val)
  //   }
  // },
  // computed: {
  //   formTitle () {
  //     return this.$t((this.editedIndex === -1 ? 'new-item' : 'edit-item'), { item: this.name })
  //     return this.editedIndex === -1 ? `New ${this.name}` : `Edit ${this.name}`
  //   },
  //   options: {
  //     get () {
  //       return this.$store.state.datatable.options
  //     },
  //     set (value) {
  //       // __log('options set', value)
  //       this.$store.dispatch(ACTIONS.GET_DATATABLE, { payload: { options: value } })
  //       // this.$store.commit(DATATABLE.UPDATE_DATATABLE_OPTIONS, value)
  //     }
  //   },
  //   search: {
  //     get () {
  //       return this.$store.state.datatable.search
  //     },
  //     set (val) {
  //       this.$store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, val)
  //       this.$store.dispatch(ACTIONS.GET_DATATABLE)
  //     }
  //   },
  //   elements: {
  //     get () {
  //       return this.items ?? this.$store.state.datatable.data ?? []
  //     },
  //     set (val) {}
  //   },
  //   headers: {
  //     get () {
  //       return this.columns ?? this.$store.state.datatable.headers ?? []
  //     },
  //     set (val) {}
  //   },

  //   ...mapState({
  //     // datatable module
  //     // headers: state => state.datatable.headers,
  //     loading: state => state.datatable.loading,
  //     // elements: state => state.datatable.data,

  //     // form module
  //     inputs: state => state.form.inputs,
  //     editedItem: state => state.form.editedItem,
  //     formLoading: state => state.form.loading,
  //     formErrors: state => state.form.errors
  //   }),
  //   ...mapGetters([
  //     // datatable module
  //     'totalElements',
  //     'formatterColumns',
  //     'editableColumns',
  //     'rowEditables',
  //     'rowActionsType',
  //     'rowActions',
  //     'mainFilters',

  //     // form module
  //     'defaultItem'
  //   ])
  // },
  methods: {
    // handleFunctionCall (functionName, ...val) {
    //   return this[functionName](...val)
    // },
    // initialize () {
    //   this.$store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, '')
    //   this.$store.commit(
    //     DATATABLE.UPDATE_DATATABLE_OPTIONS,
    //     window[process.env.VUE_APP_NAME].STORE.datatable.options
    //   )
    //   this.$store.dispatch(ACTIONS.GET_DATATABLE)
    // },
    // editItem (item) {
    //   if (this.editOnModal) {
    //     this.setEditedItem(item)
    //     this.$refs.formModal.openModal()
    //   } else {
    //     const route = this.editUrl.replace(':id', item.id)
    //     window.open(route)
    //   }
    // },
    // resetEditedItem () {
    //   this.$nextTick(() => {
    //     this.$store.commit(FORM.SET_EDITED_ITEM, this.defaultItem)
    //   })
    // }
  }
}
