import { CONFIG } from '../mutations'


const state = {
  test: window[import.meta.env.VUE_APP_NAME]?.STORE.config.test ?? false,
  profileMenu: window[import.meta.env.VUE_APP_NAME]?.STORE.config.profileMenu ?? [],
  sidebarStatus: true,
  sidebarOptions: window[import.meta.env.VUE_APP_NAME]?.STORE.config.sidebarOptions ?? false,
  secondarySidebarOptions: window[import.meta.env.VUE_APP_NAME]?.STORE.config.secondarySidebarOptions ?? false,
  isRequestInProgress: false, // New state property to track async requests
  ongoingAxiosRequests: 0, // Counter for ongoing requests
}

// getters
const getters = {
  sidebarStatus: state => {
    return state.sidebarStatus
  },
  sidebarOptions: state => {
    return state.sidebarOptions
  },
  secondarySidebarOptions: state => {
    return state.secondarySidebarOptions
  },
  profileMenu: state => {
    return state.profileMenu
  },
  isRequestInProgress: state => state.isRequestInProgress, // New getter for request state
  ongoingAxiosRequests: state => state.ongoingAxiosRequests, // New getter for request state
}

const mutations = {
  [CONFIG.SIDEBAR_TOGGLE] (state) {
    state.sidebarStatus = !state.sidebarStatus; // Mutation to toggle sidebar
  },
  [CONFIG.SET_SIDEBAR] (state, status = true) {
    state.sidebarStatus = status; // Mutation to toggle sidebar
  },
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
