import { CACHE } from '../mutations'
import _ from 'lodash'

const state = {

}

// getters
const getters = {
  [CACHE.GET_CACHE]: (state) => (key) => {
    return state[key] ?? null
  },
  [CACHE.GET_LAST_CACHE]: (state) => (key) => {
    const value = state[key] ? (Array.isArray(state[key]) ? _.last(state[key]) : state[key]) : null

    return value
  }
}

const mutations = {
  [CACHE.PUT_CACHE] (state, obj) {
    const {key, value} = obj
    state[key] = value
  },
  [CACHE.PUSH_CACHE] (state, obj) {
    const {key, value} = obj
    if(!state[key]){
      state[key] = []
    }else if(!Array.isArray(state[key])){
      state[key] = [state[key]]
    }
    state[key] = [
      ...state[key],
      value
    ]
  },
  [CACHE.FORGET_CACHE] (state, key) {
    delete state[key]
  },
}

export default {
  state,
  getters,
  mutations
}
