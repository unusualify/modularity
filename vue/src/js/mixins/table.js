import { mapState, mapGetters } from 'vuex'
import { DATATABLE, FORM } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

export default {
  props: {
    name: {
      type: String,
      default: 'Item'
    },
    titleKey: {
      type: String,
      default: 'name'
    },
    items: {
      type: Array
    },
    columns: {
      type: Array
    },
    inputFields: {
      type: Array
    },
    hideDefaultHeader: Boolean,
    hideDefaultFooter: Boolean,
    isRowEditing: Boolean,
    createOnModal: Boolean,
    editOnModal: Boolean
  },
  data: function () {
    return {
      createUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.create,
      editUrl: window[process.env.VUE_APP_NAME].ENDPOINTS.edit
    }
  },
  watch: {
    editedItem (val) {
      this.editedIndex = this.elements.findIndex(o => { return o.id === val.id })
    },
    selectedItems (val) {
      __log('selectedItems watch', val)
    }
  },
  computed: {
    formTitle () {
      return this.$t((this.editedIndex === -1 ? 'new-item' : 'edit-item'), { item: this.name })
      return this.editedIndex === -1 ? `New ${this.name}` : `Edit ${this.name}`
    },
    elements: {
      get () {
        return this.items ?? this.$store.state.datatable.data ?? []
      },
      set (val) {}
    },
    headers: {
      get () {
        return this.columns ?? this.$store.state.datatable.headers ?? []
      },
      set (val) {}
    },
    inputs: {
      get () {
        return this.inputFields ?? this.$store.state.datatable.inputs ?? []
      },
      set (val) {}
    },

    ...mapState({
      // datatable module
      // headers: state => state.datatable.headers,
      loading: state => state.datatable.loading,
      // elements: state => state.datatable.data,

      // form module
      inputs: state => state.form.inputs,
      editedItem: state => state.form.editedItem,
      formLoading: state => state.form.loading,
      formErrors: state => state.form.errors
    }),
    ...mapGetters([
      // datatable module
      'totalElements',
      'formatterColumns',
      'editableColumns',
      'rowEditables',
      'rowActionsType',
      'rowActions',
      'mainFilters',

      // form module
      'defaultItem'
    ])
  },
  methods: {
    handleFunctionCall (functionName, ...val) {
      return this[functionName](...val)
    },
    initialize () {
      this.$store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, '')
      this.$store.commit(
        DATATABLE.UPDATE_DATATABLE_OPTIONS,
        window[process.env.VUE_APP_NAME].STORE.datatable.options
      )
      this.$store.dispatch(ACTIONS.GET_DATATABLE)
    },
    editItem (item) {
      if (this.editOnModal) {
        this.setEditedItem(item)
        this.$refs.formModal.openModal()
      } else {
        const route = this.editUrl.replace(':id', item.id)
        window.open(route)
      }
    },
    deleteItem (item) {
      this.setEditedItem(item)
      this.$refs.dialog.openModal()
    },
    formatDate (value) {
      return this.$d(new Date(value), 'long')
      // return new Date(value).toLocaleString()
    },

    setEditedItem (item) {
      this.$store.commit(FORM.SET_EDITED_ITEM, item)
    },
    resetEditedItem () {
      this.$nextTick(() => {
        this.$store.commit(FORM.SET_EDITED_ITEM, this.defaultItem)
      })
    }
  }
}
