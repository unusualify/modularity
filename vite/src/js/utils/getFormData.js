import { isEmpty, find, filter, omitBy, forOwn, reduce, cloneDeep } from 'lodash-es'

const isArrayable = 'custom-input-treeview|treeview|custom-input-checklist|custom-input-repeater|custom-input-file|custom-input-image'
// const isMediableTypes = 'custom-input-file|custom-input-image'
// const isMediableFields = 'files|medias'

const chunkInputs = (inputs) => {
  const _inputs = cloneDeep(inputs)

  inputs = {}
  for (const key in _inputs) {
    if (_inputs[key].type === 'wrap' && _inputs[key].schema) {
      Object.keys(_inputs[key].schema).forEach((name, i) => {
        inputs[name] = _inputs[key].schema[name]
      })
    } else if (_inputs[key].type === 'groupx' && _inputs[key].name && _inputs[key].schema) {
      // inputs[_inputs[key].name] = reduce(_inputs[key].schema, function (acc, item) {
      //   acc[item.name] = item.default ?? ''
      //   return acc
      // }, {})
    } else {
      inputs[key] = _inputs[key]
    }
  }
  return inputs
}

export const getSchema = (inputs) => {
  const _inputs = omitBy(inputs, (value, key) => {
    return Object.prototype.hasOwnProperty.call(value, 'slotable')
  })

  if (find(_inputs, (input) => Object.prototype.hasOwnProperty.call(input, 'wrap'))) {
    // reduce(_inputs, (acc, input, key) => {
    //   if(Object.prototype.hasOwnProperty.call(input, 'group')){
    //     if(acc[input.group])
    //   }else{
    //     acc[key] = input
    //   }
    //   return acc
    // }, {})
  }

  return _inputs
}

export const getModel = (inputs, item = null, rootState = null) => {
  const languages = window[import.meta.env.VUE_APP_NAME].STORE.languages.all
  const editing = __isset(item)

  inputs = chunkInputs(inputs)

  const values = Object.keys(inputs).reduce((fields, k) => {
    const input = inputs[k]
    const name = input.name
    const isTranslated = Object.prototype.hasOwnProperty.call(input, 'translated') && input.translated

    // default model value
    let _default = Object.prototype.hasOwnProperty.call(input, 'default') ? input.default : ''
    const _prefillAvaliable = Object.prototype.hasOwnProperty.call(input, 'autofillable') && Object.prototype.hasOwnProperty.call(input, 'prefillValue');
    const _prefillValue = _prefillAvaliable && (input.prefillValue && input.prefillValue.length !== 0 ) ? input.prefillValue : null;

    if (isArrayable.includes(input.type)) {
      _default = []
    }

    if (isTranslated) {
      _default = reduce(languages, function (acc, language, k) {
        acc[language.value] = _default
        return acc
      }, {})
    }

    // if (isMediableTypes.includes(input.type)) {
    //   // if (editing) { __log(name, item, input) }
    //   return fields
    // }
    // __log(name, _default, item)
    const value = editing ? (__isset(item[name]) ? item[name] : _prefillValue) : _prefillValue || _default

    if (__isObject(input)) {
      if (isTranslated) { // translations
        if (editing) {
          const hasTranslations = Object.prototype.hasOwnProperty.call(item, 'translations')
          if (hasTranslations && item.translations[name]) {
            fields[name] = languages.reduce(function (map, lang) {
              map[lang.value] = find(item.translations, { locale: lang.value })
                ? find(item.translations, { locale: lang.value })[name]
                : item.translations[name][lang.value]
              return map
            }, {})
          } else {
            fields[name] = value
          }
        }
      } else {
        if (!value &&
          editing &&
          Object.prototype.hasOwnProperty.call(item, 'translations') &&
          Object.prototype.hasOwnProperty.call(item.translations, name)
        ) {
          const locale = Object.keys(item.translations[name])[0]
          fields[name] = item.translations[name][locale]
        } else {
          fields[name] = value
        }
      }
    }

    return fields
  }, {})

  if (editing) {
    values.id = item.id
  }

  // if (rootState) {
  //   return Object.assign(values, {
  //     medias: gatherSelected(rootState.mediaLibrary.selected)
  //   })
  // }
  if (rootState) {
    // hydrateSelected(item, rootState)
  }

  return values
}

export const getSubmitFormData = (inputs, item = null, rootState = null) => {
  inputs = chunkInputs(inputs)

  const isArrayable = 'custom-input-treeview|treeview|custom-input-checklist'

  const values = Object.keys(inputs).reduce((fields, k) => {
    const input = inputs[k]
    // if (isMediableTypes.includes(input.type)) {
    //   return fields
    // }

    const name = input.name
    // default model value
    if (!__isset(item[name])) {
      let value = input.default ?? ''
      if (isArrayable.includes(input.type)) {
        value = []
      }

      fields[name] = value

      return fields
    }
    const value = item[name]

    if (__isObject(input)) {
      if (Object.prototype.hasOwnProperty.call(input, 'translated') && input.translated) { // translations
        fields[name] = window[import.meta.env.VUE_APP_NAME].STORE.languages.all.reduce(function (map, lang) {
          if (__isObject(value)) {
            map[lang.value] = __isset(value[lang.value]) ? value[lang.value] : ''
          } else {
            map[lang.value] = value
          }
          return map
        }, {})
      } else {
        fields[name] = item[name]
      }
    }

    return fields
  }, {})

  if (item.id) {
    values.id = item.id
  }

  // if (rootState) {
  //   return Object.assign(values, {
  //     // medias: gatherSelected(rootState.mediaLibrary.selected)
  //   })
  // }

  return values
}
