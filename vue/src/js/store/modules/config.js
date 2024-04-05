const state = {
  sideBarOpt: window[import.meta.env.VUE_APP_NAME].STORE.config.sideBarOpt,
  secondarySideBar: window[import.meta.env.VUE_APP_NAME].STORE.config.secondarySideBar,
  profileMenu: window[import.meta.env.VUE_APP_NAME].STORE.config.profileMenu
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
  }
}

const mutations = {

}

export default {
  state,
  getters,
  mutations
}
