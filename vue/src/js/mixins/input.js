import { mapState } from 'vuex'

export default {
  props: ['modelValue', 'obj'],
  emits: ['update:modelValue'],

  computed: {

    ...mapState({
      // errors: state => state.form.errors
    })

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
