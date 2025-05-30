import { reactive, computed, onMounted, toRefs, ref, watch } from 'vue'
import { useDisplay } from 'vuetify'
import { useStore } from 'vuex'
import { CONFIG } from '@/store/mutations'
// import openMediaLibrary from '@/behaviors/openMediaLibrary'

export default function useSidebar () {
  const { lgAndUp, xlAndUp } = useDisplay()
  const isExpanded = ref(false)
  const activeMenuItem = ref('#profile')
  const store = useStore()

  const navigationDrawer = ref(null)

  const state = reactive({
    navigationDrawer,
    open: [],
    activeMenu: computed({
      get () {
        return activeMenuItem.value
      },
      set (val) {
        activeMenuItem.value = val
      }
    }),
    status: computed({
      get() {
        return store.state.config.sidebarStatus
      },
      set(value) {
        store.commit(CONFIG.SET_SIDEBAR, value)
      }
    }),

    options: store.state.config.sidebarOptions,

    width: computed(() => xlAndUp.value ? 320 : (state.options.width || 264)),
    hideIcons: computed(() => !state.rail && state.options.hideIcons),
    railManual: false,
    rail: computed(() => (state.options.rail || state.railManual) && lgAndUp.value),
    hasRail: computed(() => state.options.rail),
    isHoverable: computed(() => (lgAndUp.value || state.rail) && state.options.expandOnHover),

    secondaryOptions: store.state.config.secondarySidebarOptions,

    profileMenu: store.state.config.profileMenu,
    socialMediaLinks: [
      [
        'mdi-twitter',
        ''
      ],
      [
        'mdi-linkedin',
        ''
      ],
      [
        'mdi-facebook',
        ''
      ],
      [
        'mdi-instagram',
        ''
      ]
    ],

  })

  const methods = reactive({
    handleProfile(event){
      if(event.type === 'mouseenter' && state.profileMenu.expandOnHover) state.open.push('User')
    },
    handleMenu(title){
      state.activeMenu = `#${title}`
    }
  })

  watch(lgAndUp, () => {
    // state.expanded = !state.rail.value
  })
  onMounted(() => {
    // methods.initializeSidebar()
    // state.expanded = !state.rail.value
  })

  return {
    ...toRefs(state),
    ...toRefs(methods)
  }
}
