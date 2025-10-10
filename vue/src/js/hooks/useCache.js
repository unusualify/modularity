// useCache.js
import { computed } from 'vue'
import { CACHE } from '@/store/mutations'
import Store from '@/store'

export default function useCache() {
  const get = (key, defaultValue = null) => {
    return Store.getters[CACHE.GET_CACHE](key) ?? defaultValue
  }

  const put = (key, value) => {
    Store.commit(CACHE.PUT_CACHE, {key, value})
  }

  const push = (key, value) => {
    Store.commit(CACHE.PUSH_CACHE, {key, value})
  }

  const last = (key, defaultValue = null) => {
    return Store.getters[CACHE.GET_LAST_CACHE](key) ?? defaultValue
  }

  const forget = (key) => {
    Store.commit(CACHE.FORGET_CACHE, key)
  }

  const has = (key) => {
    return Store.getters[CACHE.HAS_CACHE](key)
  }

  return {
    get,
    put,
    push,
    last,
    forget,

    states: computed(() => Store.state.cache),
  }
}
