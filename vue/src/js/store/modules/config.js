const state = {
  sideBarOpt: window[import.meta.env.VUE_APP_NAME].STORE.config.sideBarOpt,
  secondarySideBar: window[import.meta.env.VUE_APP_NAME].STORE.config.secondarySideBar
}

// getters
const getters = {
  sideBarOpt: state => {
    return state.sideBarOpt
  },
  secondarySidebarOpt: state => {
    return state.secondarySideBar
  }
}

const mutations = {

}

export default {
  state,
  getters,
  mutations
}
