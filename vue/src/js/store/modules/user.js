import { USER } from '../mutations'


const state = {
  locale: window[import.meta.env.VUE_APP_NAME]?.LOCALE ?? 'en',
  timezone: window[import.meta.env.VUE_APP_NAME]?.TIMEZONE ?? 'Europe/London',
  authorization: window[import.meta.env.VUE_APP_NAME]?.AUTHORIZATION ?? {},

  profileDialog: false,
  profile: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.profile ?? {},
  profileRoute: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.profileRoute ?? '',
  profileShortcutModel: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.profileShortcutModel ?? {},
  profileShortcutSchema: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.profileShortcutSchema ?? {}
}

const getters = {
  userProfile: state => {
    return state.profile
  },
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
  },
  userRoles: state => {
    return state.authorization.roles ?? []
  }
}

const actions = {

}

const mutations = {
  [USER.SET_PROFILE_DATA] (state, data) {
    state.profile = data
  },
}

export default {
  // namespaced: true,
  state,
  getters,
  actions,
  mutations
}
