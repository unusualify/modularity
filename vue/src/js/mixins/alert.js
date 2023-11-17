import { mapState, mapGetters } from 'vuex'
import { ALERT } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

export default {
  props: {

  },
  data: function () {
    return {

    }
  },
  computed: {
    show: {
      get () {
        return this.$store.state.alert.show
      },
      set (val) {
        this.$store.commit(ALERT.SET_ALERT_SHOW, val)
      }
    },
    defaultMessage () {
      return this.$t('messages.' + this.type)
    },
    ...mapState({
      type: state => state.alert.type,
      message: state => state.alert.message
    }),
    ...mapGetters([
    //   'defaultItem',
    ])
  },
  methods: {
    activate () {
      this.show = true
    },
    deactivate () {
      this.show = true
    },
    open (type = 'info', message = null, timeout = 3000) {
      this.type = type
      this.message = message
      this.timeout = timeout
      this.activate()
    },
    close () {
      this.deactivate()
      this.$nextTick(() => {
        this.type = 'info'
        this.message = null
        this.timeout = 3000
      })
    }
  }
}
