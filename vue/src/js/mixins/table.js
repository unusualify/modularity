import { mapState, mapGetters } from 'vuex'
import { DATATABLE, FORM } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

export default {
  props: {
    name: {
      type: String,
      default: 'Item'
    },
    customHeader: {
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
    columns: {
      type: Array
    },
    inputFields: {
      type: Array
    },
    tableOptions: {
      type: Object
    },
    tableClasses: {
      type: [String, Array],
      default: ''
    },
    slots: {
      type: Object,
      default () {
        return {}
      }
    },
    hideDefaultHeader: Boolean,
    hideDefaultFooter: Boolean,
    isRowEditing: Boolean,
    createOnModal: Boolean,
    editOnModal: Boolean,
    embeddedForm: Boolean
  },
  methods: {

  }
}
