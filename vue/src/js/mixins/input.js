import { mapState, mapGetters } from 'vuex'

export default {
  props: ['modelValue', 'obj'],
  emits: ['update:modelValue'],

  computed: {
    // input: {
    //   get () { return this.modelValue },
    //   set (val) { this.$emit('update:modelValue', val) } // listen to @input="handler"
    // },

    ...mapState({
      // errors: state => state.form.errors
    })

    // props () {
    //   if (this.attributes.props) {
    //     return this.configureProps(this.attributes.props)
    //   } else {
    //     return {}
    //   }
    // },
    // secondaryProps () {
    //   if (!!this.attributes.props && !!this.attributes.props.props) {
    //     return this.configureProps(this.attributes.props.props)
    //   } else {
    //     return {}
    //   }
    // }
  },
  methods: {
    // update (value) {
    //   // __log('update:modelValue', value)
    //   this.$emit('input', value)
    //   this.$emit('update:modelValue', value)
    // },
    errorMessages (name) {
      return this.errors[name]
    }
  }
}
