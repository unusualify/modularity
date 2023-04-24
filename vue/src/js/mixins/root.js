import { mapState, mapGetters } from 'vuex'
import { ALERT } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

import openMediaLibrary from '@/behaviors/openMediaLibrary'

export default {
  props: {

  },
  data () {
    return {
      csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      sidebarToggle: false,
      miniStatus: false

    }
  },
  computed: {
    isHoverable () {
      return this.$store.state.config.isMiniSidebar && this.isLgAndUp
    },
    isMini: {
      get () {
        return this.miniStatus = this.$store.state.config.isMiniSidebar && this.isLgAndUp
      },
      set (val) {
        this.miniStatus = val
        // __log('miniStatus form isMini setter', val, this.miniStatus)
      }
    },
    showToggleButton () {
      return !this.isLgAndUp || (!this.$store.state.config.isMiniSidebar)
    },
    isLgAndUp () {
      return this.$vuetify.display.lgAndUp
    },
    ...mapState({
      isMiniSidebar: state => state.config.isMiniSidebar
    })

  },
  watch: {
    isLgAndUp (val) {
      // __log(
      //     this.isMiniSidebar
      // )
      this.isMini = this.isMiniSidebar && val
      // this.sidebarToggle = false;
    }
  },
  methods: {
    toggleSidebar () {
      this.sidebarToggle = !this.sidebarToggle
    },
    handleMethodCall (functionName, ...val) {
      // __log(functionName)
      return this[functionName](...val)
    },
    handleVmFunctionCall (functionName, ...val) {
      // __log(
      //   // window.vm,
      //   this.$app._instance,
      //   functionName
      // )
      // this.openFreeMediaLibrary()
      this[functionName](...val)
      // return window.vm[functionName](...val)
    },
    openMediaLibrary () {

    },
    bindProps (vue) {
      __log(vue)
    }
  },
  mounted () {
    if (this.isMiniSidebar && this.isLgAndUp) {
      this.sidebarToggle = true
    } else if (!this.$store.state.config.isMiniSidebar) {
      this.sidebarToggle = true
    }
    // __log(
    //     'root mixin mounted',
    //     this.miniStatus,
    //     this.sidebarToggle
    // )
  },
  created () {
    openMediaLibrary()
  }
}
