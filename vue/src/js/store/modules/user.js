import { USER } from '../mutations'

const state = {
  locale: window[import.meta.env.VUE_APP_NAME]?.LOCALE ?? 'en',
  timezone: window[import.meta.env.VUE_APP_NAME]?.TIMEZONE ?? 'Europe/London',
  authorization: window[import.meta.env.VUE_APP_NAME]?.AUTHORIZATION ?? {},

  isGuest: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.isGuest ?? false,

  profileDialog: false,
  profile: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.profile ?? {},
  profileRoute: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.profileRoute ?? '',
  profileShortcutModel: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.profileShortcutModel ?? {},
  profileShortcutSchema: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.profileShortcutSchema ?? {},

  showLoginModal: false,
  loginShortcutModel: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.loginShortcutModel ?? {},
  loginShortcutSchema: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.loginShortcutSchema ?? {},
  loginRoute: window[import.meta.env.VUE_APP_NAME]?.STORE?.user?.loginRoute ?? '',
}

const getters = {
  userProfile: state => {
    return state.profile
  },
  isGuest: state => {
    return state.isGuest
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
  [USER.OPEN_LOGIN_MODAL] (state) {
    state.showLoginModal = true
  },
  [USER.CLOSE_LOGIN_MODAL] (state) {
    state.showLoginModal = false
  }
}

export default {
  // namespaced: true,
  state,
  getters,
  actions,
  mutations
}
