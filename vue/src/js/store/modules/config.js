import { CONFIG } from '../mutations'


const state = {
  test: window[import.meta.env.VUE_APP_NAME]?.STORE.config.test ?? true,
  sideBarOpt: window[import.meta.env.VUE_APP_NAME]?.STORE.config.sideBarOpt ?? false,
  secondarySideBar: window[import.meta.env.VUE_APP_NAME]?.STORE.config.secondarySideBar ?? false,
  profileMenu: window[import.meta.env.VUE_APP_NAME]?.STORE.config.profileMenu ?? [],
  currentUser: window[import.meta.env.VUE_APP_NAME]?.STORE.config.currentUser ?? {},
  isRequestInProgress: false, // New state property to track async requests
  ongoingAxiosRequests: 0, // Counter for ongoing requests

}

// getters
const getters = {
  sideBarOpt: state => {
    return state.sideBarOpt
  },
  secondarySidebarOpt: state => {
    return state.secondarySideBar
  },
  profileMenu: state => {
    return state.profileMenu
  },
  currentUser: state => {
    return state.currentUser
  },
  isRequestInProgress: state => state.isRequestInProgress, // New getter for request state
  ongoingAxiosRequests: state => state.ongoingAxiosRequests, // New getter for request state
}

const mutations = {
  [CONFIG.SET_REQUEST_IN_PROGRESS] (state, isInProgress = true) {
    state.isRequestInProgress = isInProgress; // Mutation to set request state
  },
  [CONFIG.INCREASE_AXIOS_REQUEST] (state) {
    state.ongoingAxiosRequests += 1; // Mutation to set request state

    state.isRequestInProgress = state.ongoingAxiosRequests > 0
  },
  [CONFIG.DECREASE_AXIOS_REQUEST] (state) {
    state.ongoingAxiosRequests -= 1; // Mutation to set request state

    state.isRequestInProgress = state.ongoingAxiosRequests > 0
  },
}

export default {
  state,
  getters,
  mutations
}
