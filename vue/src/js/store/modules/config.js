const state = {
  test: window[import.meta.env.VUE_APP_NAME]?.STORE.config.test ?? true,
  sideBarOpt: window[import.meta.env.VUE_APP_NAME]?.STORE.config.sideBarOpt ?? false,
  secondarySideBar: window[import.meta.env.VUE_APP_NAME]?.STORE.config.secondarySideBar ?? false,
  profileMenu: window[import.meta.env.VUE_APP_NAME]?.STORE.config.profileMenu ?? [],
  currentUser: window[import.meta.env.VUE_APP_NAME]?.STORE.config.currentUser ?? {}
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
  }
}

const mutations = {

}

export default {
  state,
  getters,
  mutations
}
