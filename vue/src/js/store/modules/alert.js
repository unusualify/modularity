import { ALERT } from '../mutations'

const state = {
  success: null,
  info: null,
  warning: null,
  error: null,

  show: false,
  type: 'info',
  message: null,
  location: 'bottom'
}

// getters
const getters = {
  notifByVariant: state => {
    return variant => state[variant]
  },
  notified: state => {
    return Object.keys(state).filter(key => state[key] !== null).length === 0
  }
}

const mutations = {
  [ALERT.SET_ALERT_SHOW] (state, show) {
    state.show = show
  },
  [ALERT.SET_ALERT] (state, alert) {
    state.type = alert.variant ?? state.type
    state.location = alert.location ?? state.location
    state.message = alert.message ?? null

    state.show = true
  },
  [ALERT.CLEAR_ALERT] (state, variant) {
    state.show = false
    state.type = 'info'
    state.message = null
  }
}

export default {
  state,
  getters,
  mutations
}
