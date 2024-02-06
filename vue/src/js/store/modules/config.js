const state = {
  sideBarOpt : window[process.env.VUE_APP_NAME].STORE.config.sideBarOpt,
}



// getters
const getters = {
  sideBarOpt: state => {
    return state.sideBarOpt;
  },
}

const mutations = {

}

export default {
  state,
  getters,
  mutations
}
