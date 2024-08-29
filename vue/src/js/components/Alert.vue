<template>
  <v-snackbar
    v-model="show"
    :timeout="timeout"
    :color="type"
    :location="location"
    >
    <template v-slot:actions>
      <!-- <v-icon
          :color="closeButtonColor"
          icon="$close"
          @click="show = false"
      /> -->
      <v-btn
        :color="closeButtonColor"
        variant="plain"
        density="compact"
        @click="show = false"
      >
        <v-icon
          :color="closeButtonColor"
          icon="$close"
        />
      </v-btn>
    </template>
    <!-- {{ text }} -->
    <div v-html="text"/>
  </v-snackbar>
</template>

<script>
// import { AlertMixin } from '@/mixins'
import { ALERT } from '@/store/mutations/index'
import { mapState, mapGetters } from 'vuex'

export default {
  // mixins: [AlertMixin],
  data: () => ({
    timeout: 3000
  }),
  computed: {
    closeButtonColor () {
      switch (this.type) {
        case 'success':
        case 'warning':
        case 'info':
        case 'error':
          return 'white'
        default:
          return 'white'
      }
    },
    text () {
      return this.message || this.defaultMessage
    },
    defaultMessage () {
      return this.$t('messages.' + this.type)
    },
    show: {
      get () {
        return this.$store.state.alert.show
      },
      set (val) {
        this.$store.commit(ALERT.SET_ALERT_SHOW, val)
      }
    },
    ...mapState({
      type: state => state.alert.type,
      message: state => state.alert.message,
      location: state => state.alert.location
    }),
    ...mapGetters([
    //   'defaultItem',
    ])
  },
  methods: {
    info (message = null, timeout = 3000) {
      this.open('info', message, timeout)
    },
    success (message = null, timeout = 3000) {
      this.open('success', message, timeout)
    },
    warning (message = null, timeout = 3000) {
      this.open('warning', message, timeout)
    },
    error (message = null, timeout = 3000) {
      this.open('error', message, timeout)
    },
    activate () {
      this.show = true
    },
    deactivate () {
      this.show = true
    },
    open (type = 'info', message = null, timeout = 3000) {
      this.type = type
      this.message = message ?? this.message
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
</script>
