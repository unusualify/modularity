import _ from 'lodash-es'
import pluralize from 'pluralize'

import { ALERT, CONFIG } from '../store/mutations'

export default {
  $csrf: function () {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  },
  $log: function (...args) {
    return window.__log(...args)
  },
  $isset: function (...args) {
    return window.__isset(...args)
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
  $main: function () {
    return this.$root.$refs.main
  },
  $profileDialog: function () {
    return this.$root.$refs.sidebar.$refs.profileDialog
  },
  $openProfileDialog: function () {
    return this.$store.state.user.profileDialog = true
  },
  $closeProfileDialog: function () {
    return this.$store.state.user.profileDialog = false
  },
  $can: function (permission) {
    if (this.$store.getters.isSuperAdmin) {
      return true
    }

    return false
  },
  $hasRoles: function (roles) {
    if(window.__isString(roles)){
      roles = roles.split(',').map(role => role.trim())
    }

    return this.$store.getters.userRoles.some(role => roles.includes(role))
  },
  $toggleSidebar: function () {
    this.$store.commit(CONFIG.SIDEBAR_TOGGLE)
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
        }else if(__isNumber(newValue)){
          _value = newValue.toString()
        }

        if(_value){
          let remainingQuote = '\\w\\s' + __preg_quote('çşıİğüö.,;?|:_')
          let pattern = new RegExp( String.raw`^([${remainingQuote}]+)?(${quoted})([${remainingQuote}]+)?$`)

          if(value.match(pattern)){
            returnValue = value.replace(pattern, '$1' + _value + '$3')
          }else{
            __log(
              'Not matched sentence',
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
  $notif: function (payload) {
    this.$store.commit(ALERT.SET_ALERT, payload)
  },
  $dialog: function (payload) {
    this.$store.commit(ALERT.SET_DIALOG, payload)
  },
  $hasRequestInProgress: function () {
    return this.$store.getters.isRequestInProgress
  },
  $trans: function (key, defaultValue) {
    return this.$lodash.get(window[import.meta.env.VUE_APP_NAME]?.modularityLocalization.lang, key, defaultValue)
    // return get(window[import.meta.env.VUE_APP_NAME].modularityLocalization.lang, key, defaultValue)
  },
  $changeLocale: function (locale) {
    // this.$i18n.locale = locale
    this.$i18n.locale.value = locale
  },
  $localization: function (str) {

    let hasWhitespace = str.indexOf(' ') > -1

    if(hasWhitespace){
      return this.$t(str)
    }

    let segments = _.split(str, '.');
    if(segments.length > 1){ // is a nested searching
      return this.$te(str) ? this.$t(str) : _.join(segments, ' ')
    }

    let isPlural = false
    let singular = str
    if(pluralize.isPlural(singular)){
      isPlural = true
      singular = pluralize.singular(singular)
    }

    let kebabCase = _.kebabCase(singular)
    let snakeCase = _.snakeCase(singular)

    return this.$translation(`modules.${snakeCase}`, isPlural ? 1 : 0) ||
      this.$translation(`fields.${kebabCase}`) ||
      this.$translation(`fields.${snakeCase}`) ||
      window.__snakeToHeadline(str)
  },
  $moduleTranslationName: function (str) {
    // const { t, te } = useI18n({ useScope: 'global' })
    let original = str
    let isPlural = false
    let name = str

    let snakeNameFromForeignKey = window.__snakeNameFromForeignKey(name)

    if(snakeNameFromForeignKey){ // is foreign key
      name = snakeNameFromForeignKey
      str = snakeNameFromForeignKey
    }

    if(pluralize.isPlural(name)){
      isPlural = true
      name = pluralize.singular(name)
    }

    name = _.snakeCase(name)
    str = _.snakeCase(str)

    return this.$te(`modules.${name}`) ? this.$t(`modules.${name}`, isPlural ? 1 : 0) : window.__snakeToHeadline(str)
  },
  $translation: function (key, ...args) {
    return this.$te(key) ? this.$t(key, ...args) : false
  },
  $headline: function (str) {
    return window.__headline(str)
  },
  $getDisplayData: function (schema, model) {
    let displayData = {};

    for (const key in schema) {
      const input = schema[key];
      const value = model[key];

      let displayLabel = input.displayLabel || input.label || input.name || key;
      displayLabel = this.$localization(displayLabel)
      // __log(displayLabel, input)

      if(!input.type || input.type == 'hidden') continue;

      if (!value && value !== 0 && value !== false && displayLabel !== null && input.type !== 'wrap') continue;

      displayData[key] = {
        _title: displayLabel,
        _type: input.type
      };

      switch (input.type) {
        case 'wrap':
          // Wrap only affects schema, not model
          if (input.schema) {

            delete displayData[key]
            displayData = Object.assign(displayData, this.$getDisplayData(input.schema, model))
            // console.log(input.schema, model, getDisplayData(input.schema, model))
            // displayData[key].value = getDisplayData(input.schema, model);
          }
        break;
        case 'group':
          // Group adds a nested level to the model
          if (input.schema && typeof value === 'object') {
            displayData[key] = {
              ...displayData[key],
              ...this.$getDisplayData(input.schema, value)
            };
          } else {
            // displayData[key]._value = value;
          }
        break;
        case 'input-repeater':
          // Repeater adds a nested level to the model as an array
          if (Array.isArray(value) && input.schema) {
            displayData[key]._value = value.map(item => this.$getDisplayData(input.schema, item));
          } else {
            displayData[key]._value = value;
          }
        break;
        case 'input-filepond':
          // Repeater adds a nested level to the model as an array
          if (Array.isArray(value)) {
            displayData[key]._value = value.map(item => item.file_name);
          } else {
            displayData[key]._value = value.file_name;
          }
        break;
        case 'input-form-tabs':
          // Repeater adds a nested level to the model as an array
          displayData[key]._value = _.reduce(value, (acc, obj, id) => {
            id = __isString(id) ? parseInt(id) : id
            const item = input.items.find((i) => i.id == id);
            const _displayLabel = item.title || item.name
            // __log(item)
            // acc[_key] = {
            //   title: _key,
            //   items: {}
            // }
            let _displayData = {
              _title: _displayLabel,
              _model: item.id ?? null,
              _value: {}
            }
            _.each(input.tabFields, ( _map, _key) => {
              let _input = input.schema[_key]
              let __displayLabel = this.$localization(_input.displayLabel || _input.label || _input.name || _key)
              let __displayData = {
                _title: __displayLabel,
                _type: input.type,
                _value: null
              }
              // let _name = __moduleTranslationName(_key)
              let _value = obj[_key];
              let _haystack = item[_map];
              if (Array.isArray(_value) && _haystack) {
                __displayData._value = _value.map(id => {
                  let _item = _haystack.find(i => i.id === id);
                  return _item ? _item.title || _item.name : 'N/A';
                })
              } else {
                if(!!_haystack){
                  let item = _haystack.find(i => i.id === _value);
                  _value = item ? item.title || item.name : _value;
                  // __log(id, _key, _map, _displayData, _value)

                  __displayData._value = _value;
                  let __displayKeys = this.$getDisplayKeys(item);
                  for(const displayKey in __displayKeys){
                    if(!__displayData[displayKey]){
                      __displayData[__displayKeys[displayKey]] = item[displayKey]
                    }
                  }

                }
              }
              // acc[_key].items[_name] = _value
              _displayData[_key] = __displayData
            })

            acc.push(_displayData)

            return acc;
          }, [])
        break;
        case 'input-price':
          // const { n, locale, numberFormats, t, te } = useI18n({ useScope: 'global' })
          const currencyInfo = this.$numberFormats[this.$getLocale].currency
          const displayCurrency = input.items.find(c => c.iso === currencyInfo.currency)
          __log(this.$n(100, { style: 'currency', currency: currencyInfo.currency }))

          displayData[key]._value = this.$n(
            value.find(priceItem => priceItem.currency_id === displayCurrency.id).display_price,
            { style: 'currency', currency: currencyInfo.currency }
          )
        break;
        default:
          if (__isObject(value)) {
            // __log('getDisplayData is object', input.type, value, input)
            displayData[key]._value = value;
          } else if (Array.isArray(value) && input.items) {
            // __log('getDisplayData is array', input.type, key, value, input)
            displayData[key]._value = value.map(id => {
              const item = input.items.find(i => i[input.itemValue] === id);
              return item ? item[input.itemTitle] : id;
            });
          } else {
            if (!!input.items) {
              const item = input.items.find(i => i[input.itemValue] === value);
              displayData[key]._value = item ? item[input.itemTitle] : value;
            } else {
              displayData[key]._value = value;
            }
          }
        break;
      }
    }

    return displayData;
  },
  $getDisplayKeys: function (item) {
    return _.reduce(Object.keys(item ?? {}), (acc, key) => {
      let matches = key.match(/^([a-zA-Z0-9]+)(_show)$/)
      if(matches){
        acc[key] = matches[1]
      }
      return acc
    }, {})
  },
  $copy: function(text) {
    window.navigator.clipboard.writeText(text);
  }
}
