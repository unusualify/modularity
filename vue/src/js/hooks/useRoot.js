import { reactive, computed, onUpdated, onMounted, toRefs, getCurrentInstance } from 'vue'
import { useStore } from 'vuex'
import openMediaLibrary from '@/behaviors/openMediaLibrary'
import { propsFactory } from 'vuetify/lib/util/propsFactory.mjs'

export const props = propsFactory({
  isLgAndUp:{
    type:Boolean,
    default:false,
  },
  isXlAndUp:{
    type:Boolean,
    default:false,
  },
  isSmAndDown:{
    type:Boolean,
    default:false,
  },
})

export default function useRoot(){
  let vuetifyInstance;
  let rootInstance;

  onMounted(()=> {
    vuetifyInstance = getCurrentInstance().proxy.$vuetify;
    rootInstance = getCurrentInstance().root;
    methods.initializeStates();
  })

  const state = reactive({
    root: null,
    isLgAndUp: false,
    isXlAndUp: false,
    isSmAndDown: false,
  })

  const methods = reactive({
    initializeStates: () => {
      state.isLgAndUp = computed(() => vuetifyInstance.display.lgAndUp),
      state.isXlAndUp = computed(() => vuetifyInstance.display.xlAndUp),
      state.isSmAndDown = computed(() => vuetifyInstance.display.smAndDown),
      state.root =  rootInstance;
    },
    openMediaLibrary: () => {
      state.root.ctx.openFreeMediaLibrary();
    }
  })

  return {
    ...toRefs(state),
    ...methods,
  }
}
