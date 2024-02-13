const state = {
  sideBarOpt : window[process.env.VUE_APP_NAME].STORE.config.sideBarOpt,
  secondarySideBar : window[process.env.VUE_APP_NAME].STORE.config.secondarySideBar,
}



// getters
const getters = {
  sideBarOpt: state => {
    return state.sideBarOpt;
  },
  secondarySidebarOpt: state => {
    return state.secondarySideBar;
  }
}

const mutations = {

}

export default {
  state,
  getters,
  mutations
}
