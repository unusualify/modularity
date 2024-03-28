import { isEmpty, find, filter, omitBy, forOwn, reduce, cloneDeep } from 'lodash-es'
import filters from '@/utils/filters'

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

const formatPermalink = (newValue) => {
  let text = ''
  if (newValue.value && typeof newValue.value === 'string') {
    text = newValue.value
  } else if (typeof newValue === 'string') {
    text = newValue
  }

  return filters.slugify(text)
}

export const handleInputEvents = (events = null, fields, moduleSchema, name = null) => {
  const _fields = fields
  const _field = fields[name]
  const _schema = moduleSchema[name]

  const isFieldFalsy = (Array.isArray(_field) && _field.length > 0) || (!Array.isArray(_field) && !!_field)

  if (events) {
    const formatEvents = events.split('|')
    formatEvents.forEach(e => {
      const [methodName, formattedInputName, formatingInputName] = e.split(':')
      switch (methodName) {
        case 'formatPermalink':
          if (isFieldFalsy) {
            _fields[formattedInputName] = formatPermalink(_field)
          }
          break
        case 'formatPermalinkPrefix':
          if (['select', 'combobox'].includes(_schema.type) && _field && isFieldFalsy) {
            const newValue = formatPermalink(_schema.items.find((item) => item[_schema.itemValue] === _field)[_schema.itemTitle])
            moduleSchema[formattedInputName ?? 'slug'].prefix = moduleSchema[formattedInputName ?? 'slug'].prefixFormat.replace(':' + formatingInputName, newValue)
          } else if (['text'].includes(_schema.type) && isFieldFalsy) {
            const newValue = formatPermalink(_field)
            const [firstLevelName, secondLevelName] = formattedInputName.split('.')
            moduleSchema[firstLevelName].schema[secondLevelName ?? 'slug'].prefix = moduleSchema[firstLevelName].schema[secondLevelName ?? 'slug'].prefixFormat.replace(':' + formatingInputName, newValue)
          }
          break
        case 'formatLock':
          if (['select', 'combobox'].includes(_schema.type) && _field) {
            const lockInput = _schema.items.find((item) => item[_schema.itemValue] === _field)?.[formatingInputName]
            moduleSchema[formattedInputName].disabled = !!lockInput
            moduleSchema[formattedInputName].focused = !!lockInput
            moduleSchema[formattedInputName].placeHolder = lockInput
          } else if (['text'].includes(_schema.type) && _field) {
            const lockInput = _field
            const [firstLevelName, secondLevelName] = formattedInputName.split('.')
            moduleSchema[firstLevelName].schema[secondLevelName].disabled = !!lockInput
            moduleSchema[firstLevelName].schema[secondLevelName].focused = !!lockInput
            moduleSchema[firstLevelName].schema[secondLevelName].placeHolder = lockInput
          }
          break
        default:
          break
      }
    })
  }
  return {
    _fields,
    moduleSchema
  }
}

export const getSchema = (inputs, model = null) => {
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
  map(_inputs, (value, key) => {
    handleInputEvents(value.event, model, inputs, key)
  })
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
    const value = editing ? (__isset(fields[name]) ? fields[name] : (__isset(item[name]) ? item[name] : _default)) : _default
    // const value = editing ? (__isset(item[name]) ? item[name] : _default) : _default

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
    const newFields = handleInputEvents(input.event, fields, inputs, name)._fields // return fields;
    return newFields
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
