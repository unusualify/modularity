export default {
  $trans: function (key, defaultValue) {
    return this.$lodash.get(window[process.env.VUE_APP_NAME].unusualLocalization.lang, key, defaultValue)
    // return get(window[process.env.VUE_APP_NAME].unusualLocalization.lang, key, defaultValue)
  },
  $changeLocale: function (locale) {
    // this.$i18n.locale = locale
    this.$i18n.locale.value = locale
  },
  $call: function (functionName, ...args) {
    return this[functionName](...args)
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
  }
}
