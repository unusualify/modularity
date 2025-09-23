// useCache.js
import { useStore } from 'vuex'
import { CACHE } from '@/store/mutations'
import store from '@/store'

export default function useCache() {
  const store = useStore()

  const get = (key, defaultValue = null) => {
    return store.getters[CACHE.GET_CACHE](key) ?? defaultValue
  }

  const put = (key, value) => {
    store.commit(CACHE.PUT_CACHE, {key, value})
  }

  const push = (key, value) => {
    store.commit(CACHE.PUSH_CACHE, {key, value})
  }

  const last = (key, defaultValue = null) => {
    return store.getters[CACHE.GET_LAST_CACHE](key) ?? defaultValue
  }

  const forget = (key) => {
    store.commit(CACHE.FORGET_CACHE, key)
  }

  const has = (key) => {
    return store.getters[CACHE.HAS_CACHE](key)
  }

  return {
    get,
    put,
    push,
    last,
    forget,

    states: computed(() => store.state.cache),
  }
}
