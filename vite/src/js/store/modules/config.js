
const state = {
  isMiniSidebar: window[process.env.JS_APP_NAME].STORE.config.isMiniSidebar,
}

// getters
const getters = {
    // isMiniSidebar: state => {
    //     return state.isMiniSidebar
    // },
}

const mutations = {

}

export default {
  state,
  getters,
  mutations
}
