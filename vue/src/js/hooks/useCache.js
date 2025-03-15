// useCache.js
import { useStore } from 'vuex'
import { CACHE } from '@/store/mutations'

export default function useCache() {
  const store = useStore()

  const get = (key) => {
    return store.commit(CACHE.GET_CACHE, key)
  }

  const put = (key, value) => {
    store.commit(CACHE.PUT_CACHE, key, value)
  }

  const push = (key, value) => {
    store.commit(CACHE.PUSH_CACHE, key, value)
  }

  const last = (key) => {
    return store.commit(CACHE.GET_LAST_CACHE, key)
  }

  const forget = (key) => {
    store.commit(CACHE.FORGET_CACHE, key)
  }

  return {
    get,
    put,
    push,
    last,
    forget
  }
}
