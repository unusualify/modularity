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
      miniStatus: false,
      hasRailMode : true,
      showIcon: true,
    }
  },
  computed: {
    isHoverable () {
      return (this.$store.state.config.sideBarOpt.isMini && this.isLgAndUp) || this.railMode
    },
    isMini: {
      get () {
        return this.miniStatus = this.$store.state.config.sideBarOpt.isMini && this.isLgAndUp
      },
      set (val) {
        this.miniStatus = val
      }
    },
    railMode: {
      get(){
        return this.hasRailMode = this.$vuetify.display.lgAndUp && this.$store.state.config.sideBarOpt.rail;;
      },
      // set(miniStatus){
      //   this.hasRailMode = miniStatus && this.$store.state.config.sideBarOpt.rail;
      // }
    },
    doShowIcon:{
      get(){
        return this.showIcon = this.$store.state.config.sideBarOpt.showIcon;
      }
    },
    showToggleButton () {
      return !this.isLgAndUp || (!this.$store.state.config.sideBarOpt.isMini)
    },
    isLgAndUp () {
      return this.$vuetify.display.lgAndUp
    },
    isXlAndUp () {
      return this.$vuetify.display.xlAndUp
    },
    isSmAndDown () {
      return this.$vuetify.display.smAndDown
    },
    ...mapState({
      isMiniSidebar: state => state.config.sideBarOpt.isMini
    })

  },
  watch: {
    isLgAndUp (val) {
      this.isMini = this.isMiniSidebar && val
    }
  },
  methods: {
    toggleSidebar () {
      this.sidebarToggle = !this.sidebarToggle
    },
    handleMethodCall (functionName, ...val) {
      return this[functionName](...val)
    },
    handleVmFunctionCall (functionName, ...val) {
      return this[functionName](...val)
    }
    // openMediaLibrary () {

    // }
  },
  mounted () {
    if (this.isMiniSidebar && this.isLgAndUp) {
      this.sidebarToggle = true
    } else if (!this.$store.state.config.sideBarOpt.isMini) {
      this.sidebarToggle = true
    }
  },
  created () {
    openMediaLibrary()
  }
}
