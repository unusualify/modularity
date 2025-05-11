import { ALERT } from '../mutations'
import ACTIONS from '@/store/actions'

const state = {
  success: null,
  info: null,
  warning: null,
  error: null,

  show: false,
  type: 'info',
  message: null,
  location: 'bottom',

  dialog: false,
  dialogMessage: null,

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
  },

  [ALERT.SET_DIALOG_SHOW] (state, show) {
    state.dialog = show
  },
  [ALERT.SET_DIALOG] (state, dialog) {
    state.dialogMessage = dialog.message ?? null
    state.dialog = true
  },
  [ALERT.CLEAR_DIALOG] (state) {
    state.dialog = false
    state.dialogMessage = null
  },
}

const actions = {
  [ACTIONS.SHOW_ALERT] ({ commit }, payload) {
    commit(ALERT.SET_ALERT, payload)
  },
}

export default {
  state,
  getters,
  mutations,
  actions
}
