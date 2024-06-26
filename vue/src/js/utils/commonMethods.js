import { ALERT } from '../store/mutations'

export default {
  $trans: function (key, defaultValue) {
    return this.$lodash.get(window[import.meta.env.VUE_APP_NAME]?.unusualLocalization.lang, key, defaultValue)
    // return get(window[import.meta.env.VUE_APP_NAME].unusualLocalization.lang, key, defaultValue)
  },
  $changeLocale: function (locale) {
    // this.$i18n.locale = locale
    this.$i18n.locale.value = locale
  },
  $call: function (functionName, ...args) {
    return this[functionName](...args)
  },
  $main: function () {
    return this.$root.$refs.main
  },
  $bindAttributes: function (attributes = null) {
    const _attributes = {}
    if (attributes) {
      Object.keys(attributes).forEach((v, i) => {
        if (parseInt(v) > -1) {
          _attributes[attributes[v]] = true
        } else {
          _attributes[v] = attributes[v]
        }
      })
    } else if (attributes == null) {
      return { ...this.$attrs }
    }
    // __log(_props)
    return _attributes
  },
  $can: function (permission) {
    if (this.$store.getters.isSuperAdmin) {
      return true
    }

    return false
  },
  $log: function (...args) {
    window.__log(...args)
  },
  $isset: function (...args) {
    return window.__isset(...args)
  },
  $notif: function (Obj) {
    this.$store.commit(ALERT.SET_ALERT, Obj)
  }
}
