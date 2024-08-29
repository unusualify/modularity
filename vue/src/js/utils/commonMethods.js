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
  $castValueMatch: function (value, ownerItem) {
    let matches

    let returnValue = value

    if(__isString(value) && (matches = value.match(/\$([\w\.\*]+)/))){
      let notation = matches[1]
      let quoted = __preg_quote(matches[0])
      let parts = notation.split('.')
      // __log(parts)

      let newParts = []
      for(const j in parts){
        let part = parts[j]
        if(part === '*'){
          // let searchedValue =
          let _id = ownerItem.id
          // parts[j] = `*id=${_id}`
        }else{
          newParts.push(part)
        }
      }

      notation = newParts.join('.')

      let newValue = __data_get(ownerItem, notation)

      if(newValue){
        let _value
        if(Array.isArray(newValue) && newValue.length > 0){
          _value = newValue.join(',')
        }else if(__isString(newValue)){
          _value = newValue

          let snakeCased = snakeCase(_value)

          if(this.$te(`modules.${snakeCased}`)){
            _value = this.$t(`modules.${snakeCased}`)
          }
        }

        if(_value){
          let remainingQuote = '\\w\\s' + __preg_quote('çşıİğüö.,;?')
          let pattern = new RegExp( String.raw`^([${remainingQuote}]+)?(${quoted})([${remainingQuote}]+)?$`)

          if(value.match(pattern)){
            returnValue = value.replace(pattern, '$1' + _value + '$3')
          }else{
            __log(
              'Not matched sentence',
              remainingQuote,
              pattern,
              value,
              value.match(pattern)
            )
          }
        }
      }
    }

    return returnValue
  },
  $log: function (...args) {
    window.__log(...args)
  },
  $isset: function (...args) {
    return window.__isset(...args)
  },
  $notif: function (payload) {
    this.$store.commit(ALERT.SET_ALERT, payload)
  },
  $dialog: function (payload) {
    this.$store.commit(ALERT.SET_DIALOG, payload)
  }
}
