const state = {
  locale: window[import.meta.env.VUE_APP_NAME].LOCALE ?? 'en',
  timezone: window[import.meta.env.VUE_APP_NAME].TIMEZONE ?? '',
  authorization: window[import.meta.env.VUE_APP_NAME].AUTHORIZATION ?? {}
}

const getters = {
  isSuperAdmin: state => {
    return state.authorization.isSuperAdmin ?? false
  },
  isClient: state => {
    return state.authorization.isClient ?? false
  },
  userLocale: state => {
    return state.locale
  },

  hasRestorable: state => {
    return state.authorization.hasRestorable ?? false
  },
  hasBulkable: state => {
    return state.authorization.hasBulkable ?? false
  },
  userPermissions: state => {
    return state.authorization.permissions ?? []
  }
}

const actions = {

}

const mutations = {

}

export default {
  // namespaced: true,
  state,
  getters,
  actions,
  mutations
}
