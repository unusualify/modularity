import { reactive, computed, onMounted, toRefs, ref, watch, getCurrentInstance } from 'vue'
import { useStore } from 'vuex'
// import openMediaLibrary from '@/behaviors/openMediaLibrary'
import { propsFactory } from 'vuetify/lib/util/propsFactory.mjs'
import { useRoot } from '@/hooks'

const props = propsFactory({
  miniStatus:{
    type: Boolean,
    default: false,
  },
  hasRailMode:{
    type: Boolean,
    default: false,
  },
  sidebarToggle:{
    type: Boolean,
    default: false,
  },
  isExpanded:{
    type: Boolean,
    default: true,
  },
});

export default function useSidebar() {

  let isExpanded = ref(false);
  const store = useStore();
  const root = useRoot();
  const state = reactive({
    csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    sidebarToggle: props.sidebarToggle,
    mainSidebar: store.state.config.sideBarOpt,
    secondarySidebar : store.state.config.secondarySideBar,
    mainLocation : computed(() => state.mainSidebar.mainLocation),
    secondarySidebarExists: computed(() => state.secondarySidebar.exists),
    secondaryLocation : computed(() => state.secondarySidebar.location),
    contentDrawer : computed(() => state.mainSidebar.contentDrawer.exists),
    rail: computed(() => state.mainSidebar.rail && root.isLgAndUp),
    isMini: computed(() => state.mainSidebar.isMini && root.isLgAndUp),
    isHoverable: computed(() => ((state.isMini && root.isLgAndUp) || state.rail) && state.mainSidebar.expandOnHover),
    showToggleBtn: computed(() => !state.isMini.value),
    width: computed(() => root.isXlAndUp.value ? 320 : 256),
    expanded: computed({
      get(){
        return isExpanded.value;
      },
      set(val){
        isExpanded.value = val;
      }
    }),
    showIcon: computed(() => state.rail.value ? (state.expanded ? state.mainSidebar.showIcon : true) : state.mainSidebar.showIcon),
    socialMediaLinks:[
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
    ]
  });

  const methods = reactive({
    toggleSideBar: function() {
      state.sidebarToggle = !state.sidebarToggle;
    },
    handleMethodCall: function(functionName, ...val) {
      return this[functionName](...val)
    },
    handleVmFunctionCall: function(functionName, ...val) {
      return this[functionName](...val)
    },
    initializeSidebar : () => {
      if (state.isMini.value && root.isLgAndUp.value) {
        state.sidebarToggle = true
      } else if (!state.isMini.value) {
        state.sidebarToggle = false
      }
    },
    handleExpanding: function(event) {
      if(state.rail.value){
        state.expanded = !event;
      }
    },
    openFreeMediaLibrary: function(){
      root.openMediaLibrary();
    }
  });

  watch(root.isLgAndUp,()=>{
    state.expanded = !state.rail.value;
  })
  onMounted(() => {
    methods.initializeSidebar()
    state.expanded = !state.rail.value;
  });

  return {
    ...toRefs(state),
    root,
    methods,
  }
}
