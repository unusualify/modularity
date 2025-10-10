// useConfig.js
import { computed, toRefs, reactive } from 'vue'
import { useStore } from 'vuex'
import store from '@/store'
import { CONFIG } from '@/store/mutations'

export default function useConfig() {
  const store = useStore()

  const shouldUseInertia = computed(() => store.state.config.isInertia ?? false)
  const isRequestInProgress = computed(() => store.state.config.isRequestInProgress ?? false)

  const setRequestInProgress = (value) => {
    store.commit(CONFIG.SET_REQUEST_IN_PROGRESS, value)
  }

  const increaseAxiosRequest = () => {
    store.commit(CONFIG.INCREASE_AXIOS_REQUEST)
  }

  const decreaseAxiosRequest = () => {
    store.commit(CONFIG.DECREASE_AXIOS_REQUEST)
  }

  const states = reactive({
    shouldUseInertia,
    isRequestInProgress,
  })

  const methods = reactive({
    setRequestInProgress,
    increaseAxiosRequest,
    decreaseAxiosRequest,
  })

  return {
    ...toRefs(states),
    ...toRefs(methods),
  }
}
