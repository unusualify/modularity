import htmlClasses from '@/utils/htmlClasses'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

const makeModalProps = propsFactory({
  modelValue: {
    type: Boolean,
    default: false
  }
})
export default {
  emits: ['update:modelValue', 'confirm', 'cancel'],
  props: {
    // modelValue: {
    //   type: Boolean,
    //   default: false
    // },
    cancelText: {
      type: String,
      default: ''
    },
    confirmText: {
      type: String,
      default: ''
    },
    ...makeModalProps()
  },
  data () {
    return {
      id: Math.ceil(Math.random() * 1000000) + '-modal'
    }
  },

  computed: {
    show: {
      get () {
        return this.modelValue
      },
      set (value) {
        this.$emit('update:modelValue', value)
      }
    },
    textCancel () {
      return this.cancelText !== '' ? this.cancelText : this.$t('cancel')
    },
    textConfirm () {
      return this.confirmText !== '' ? this.confirmText : this.$t('confirm')
    }
  },
  methods: {
    setValue (e) {
      __log('setValue', e.target.name)
      this[e.target.name] = e.target.value
    },
    openModal () {
      this.show = true
    },
    closeModal () {
      this.show = false
    },
    cancelModal (callback = null) {
      if (typeof this.cancelCallback === 'undefined') {
        this.closeModal()
      } else {
        this.cancelCallback()
      }
      if (callback) {
        callback()
      }
      this.$emit('cancel')
    },
    confirmModal (callback = null) {
      if (typeof this.confirmCallback === 'undefined') {
        if (callback) {
          callback()
        } else {
          this.closeModal()
        }
      } else {
        this.confirmCallback()
      }

      this.$emit('confirm')
    }
  },
  created () {
    // setInterval((self) => {
    //   __log('show', self.show)
    // }, 5000, this)
  }
}
