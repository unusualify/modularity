<template>
  <v-snackbar
    v-model="show"
    :timeout="timeout"
    :color="type"
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
    {{ text }}
  </v-snackbar>
</template>

<script>
import { AlertMixin } from '@/mixins'
export default {
  mixins: [AlertMixin],
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
    }
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
    }
  }
}
</script>
