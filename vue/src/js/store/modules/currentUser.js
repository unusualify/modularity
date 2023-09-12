const state = {
  locale: window[process.env.VUE_APP_NAME].LOCALE ?? 'en',
  timezone: window[process.env.VUE_APP_NAME].TIMEZONE ?? ''
}

const getters = {

}

const actions = {

}

const mutations = {

}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}
