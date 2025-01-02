import { CONFIG } from '../mutations'

const state = {
  isHot: window[import.meta.env.VUE_APP_NAME]?.STORE.ambient.isHot ?? false,
  appEnv: window[import.meta.env.VUE_APP_NAME]?.STORE.ambient.appEnv ?? '',
  appName: window[import.meta.env.VUE_APP_NAME]?.STORE.ambient.appName ?? '',
  appEmail: window[import.meta.env.VUE_APP_NAME]?.STORE.ambient.appEmail ?? '',
  appDebug: window[import.meta.env.VUE_APP_NAME]?.STORE.ambient.appDebug ?? false,

  test: window[import.meta.env.VUE_APP_NAME]?.STORE.ambient.test ?? false,
  systemPackageVersions: window[import.meta.env.VUE_APP_NAME]?.STORE.ambient.systemPackageVersions ?? {},
}

// getters
const getters = {
  isHot: state => {
    return state.isHot
  },
  appName: state => {
    return state.appName
  },
  appEmail: state => {
    return state.appEmail
  },
  appEnv: state => {
    return state.appEnv
  },
  appDebug: state => {
    return state.appDebug
  },
  versions: state => {
    return state.systemPackageVersions
  },
}

const mutations = {

}

export default {
  state,
  getters,
  mutations
}
