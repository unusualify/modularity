import { find, omitBy, reduce, cloneDeep, map, findIndex, snakeCase, orderBy } from 'lodash-es'
import filters from '@/utils/filters'
import axios from 'axios'

const isArrayable = 'custom-input-treeview|treeview|custom-input-checklist|custom-input-repeater|custom-input-file|custom-input-image'
// const isMediableTypes = 'custom-input-file|custom-input-image'
// const isMediableFields = 'files|medias'

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

    // const newFields = handleInputEvents(input.event, fields, inputs, name)._fields // return fields;
    // return newFields
    handleEvents(fields, inputs, input)

    return fields;

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

export const setSchemaInputField = (schema, value) => {

  for (const key in schema) {
    const sch = schema[key]
    const cascadeKey = sch.cascadeKey ?? 'items' //schema
    if (sch.type === 'select' && Object.prototype.hasOwnProperty.call(sch, 'cascade')) {
      if(key.includes('repeater'))
        break;
      // eslint-disable-next-line vue/no-side-effects-in-computed-properties
      schema[sch.cascade][cascadeKey] = find(sch[cascadeKey], [sch.itemValue, value[sch.name]])?.[cascadeKey] ?? []
      // this.formSchema[key].items = find(this.formSchema[sch.parent].items, [this.formSchema[sch.parent].itemValue, this.valueIntern[sch.parent]]).items
    }
  }
}

export const onInputEventFormData = (obj, schema, stateData, sortedStateData, value) => {

  if (obj.schema.type === 'select' && obj.schema.hasOwnProperty('cascade')) {
    const cascadedSelectName = obj.schema.name.includes('repeater') ? obj.schema.name.match(/repeater\d+-input\[\d+\]/) + `[${obj.schema.cascade}]` : obj.schema.cascade
    const cascadeKey = obj.schema.cascadeKey ?? 'items'
    const selectItemValue = obj.schema.itemValue ?? 'id'

    // ACTIONS
    schema[cascadedSelectName][cascadeKey] = find(obj.schema[cascadeKey], [selectItemValue, value[obj.key]])?.schema ?? []
    const sortIndex = findIndex(sortedStateData, ['key', cascadedSelectName])

    stateData[cascadedSelectName] = schema[cascadedSelectName][cascadeKey].length > 0 ? schema[cascadedSelectName][cascadeKey][0].value : []
    sortedStateData[sortIndex].value = value[cascadedSelectName]

    onInputEventFormData(sortedStateData[sortIndex], schema, stateData, sortedStateData, value)

  } else if ((obj.schema.type === 'select') && obj.schema.hasOwnProperty('autofill')) {
    __log('autofill')
    // obj.schema.autofill.forEach(element => {
    //   if (schema[element].autofillable) {
    //     stateData[element] = find(obj.schema.items, ['id', value[obj.key]])?.[element] ?? ''
    //     // ## TODO type conditional default value
    //   }
    // })
  }
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
          // __log(methodName, e)
          if (isFieldFalsy) {
            // __log('formatPermalink', _field, slugify(_field))
            _fields[formattedInputName] = slugify(_field)
          }
          break
        case 'formatPermalinkPrefix':
          // __log(methodName, e)
          if (['select', 'combobox'].includes(_schema.type) && _field && isFieldFalsy) {
            const newValue = slugify(_schema.items.find((item) => item[_schema.itemValue] === _field)[_schema.itemTitle])
            moduleSchema[formattedInputName ?? 'slug'].prefix = moduleSchema[formattedInputName ?? 'slug'].prefixFormat.replace(':' + formatingInputName, newValue)
          } else if (['text'].includes(_schema.type) && isFieldFalsy) {
            const newValue = slugify(_field)
            const [firstLevelName, secondLevelName] = formattedInputName.split('.')
            moduleSchema[firstLevelName].schema[secondLevelName ?? 'slug'].prefix = moduleSchema[firstLevelName].schema[secondLevelName ?? 'slug'].prefixFormat.replace(':' + formatingInputName, newValue)
          }
          break
        case 'formatLock':
          // __log(methodName, e)
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
        case 'formatFilter':
          __log(
            methodName,
            e
          )
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

export const handleEvents = ( model, schema, input) => {

  const handlerName = input.name
  const handlerSchema = schema[handlerName]
  const handlerValue = model[handlerName]

  const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

  if (input.event) {
    input.event.split('|').forEach(event => {
      let args = event.split(':')
      let methodName = args.shift()

      if(typeof FormatFuncs[methodName] !== 'undefined')
        FormatFuncs?.[methodName](args, model, schema, input)

    })
  }
}

export const handleMultiFormEvents = ( models, schemas, input, index) => {
  const handlerName = input.name
  const handlerSchema = schemas[index][handlerName]
  const handlerValue = models[index][handlerName]

  const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

  if (input.event) {
    input.event.split('|').forEach(event => {
      let args = event.split(':')
      let methodName = args.shift()

      if(typeof FormatFuncs[methodName] !== 'undefined')
        FormatFuncs?.[methodName](args, models, schemas, input, index)

    })
  }
}

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

const slugify = (newValue) => {
  let text = ''
  if (newValue.value && typeof newValue.value === 'string') {
    text = newValue.value
  } else if (typeof newValue === 'string') {
    text = newValue
  }

  return filters.slugify(text)
}

const FormatFuncs = {
  formatPermalink: function(args, model, schema, input) {
    const handlerName = input.name
    const handlerSchema = schema[handlerName]
    const handlerValue = model[handlerName]

    const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

    const inputToFormat = args.shift()
    if (isFieldFalsy) {
      model[inputToFormat] = slugify(handlerValue)
    }
  },
  formatPermalinkPrefix: function(args, model, schema, input) {
    const handlerName = input.name
    const handlerSchema = schema[handlerName]
    const handlerValue = model[handlerName]

    const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

    const inputToFormat = args.shift()
    const inputFormatter = args.shift()

    if (['select', 'combobox'].includes(handlerSchema.type) && handlerValue && isFieldFalsy) {
      const newValue = slugify( handlerSchema.items.find((item) => item[handlerSchema.itemValue] === handlerValue)[handlerSchema.itemTitle])
      schema[inputToFormat ?? 'slug'].prefix = schema[inputToFormat ?? 'slug'].prefixFormat.replace(':' + inputFormatter, newValue)
    } else if (['text'].includes(handlerSchema.type) && isFieldFalsy) {
      const newValue = slugify(value)
      const [firstLevelName, secondLevelName] = inputToFormat.split('.')
      schema[firstLevelName].schema[secondLevelName ?? 'slug'].prefix = schema[firstLevelName].schema[secondLevelName ?? 'slug'].prefixFormat.replace(':' + inputFormatter, newValue)
    }
  },
  formatLock: function(args, model, schema, input) {
    const handlerName = input.name
    const handlerSchema = schema[handlerName]
    const handlerValue = model[handlerName]

    const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

    const inputToFormat = args.shift()
    const inputFormatter = args.shift()
    if (['select', 'combobox'].includes(handlerSchema.type) && handlerValue) {
      const lockInput = handlerSchema.items.find((item) => item[handlerSchema.itemValue] === handlerValue)?.[inputFormatter]
      schema[inputToFormat].disabled = !!lockInput
      schema[inputToFormat].focused = !!lockInput
      schema[inputToFormat].placeHolder = lockInput
    } else if (['text'].includes(handlerSchema.type) && _field) {
      const lockInput = _field
      const [firstLevelName, secondLevelName] = inputToFormat.split('.')
      schema[firstLevelName].schema[secondLevelName].disabled = !!lockInput
      schema[firstLevelName].schema[secondLevelName].focused = !!lockInput
      schema[firstLevelName].schema[secondLevelName].placeHolder = lockInput
    }
  },
  formatFilter: async function(args, model, schema, input, index) {

    if(Array.isArray(model)){
      const handlerName = input.name
      const handlerSchema = schema[index][handlerName]
      const handlerValue = model[index][handlerName]

      let inputToFormat = args.shift() // 2.packages
      let targetFormIndex;

      if(Array.isArray(model)){
        let stages = inputToFormat.split('.')

        inputToFormat = stages.pop()
        targetFormIndex = (stages.pop() ?? 0) - 1
      }

      const inputPropToFormat = args.shift() // items
      const inputReadValue = args.shift() // group

      // __log(
      //   'formatFilter',
      //   inputToFormat,
      //   targetFormIndex

      // )
      const modelName = handlerValue[inputReadValue]
      const endpoint = input.filterEndpoints[modelName]
      const filterValues = handlerValue['items']
      let eagers = schema[targetFormIndex][inputToFormat]?.eagers ?? [];

      if( !schema[targetFormIndex][inputToFormat][inputPropToFormat])
        schema[targetFormIndex][inputToFormat][inputPropToFormat] = []

      let newItems = cloneDeep( schema[targetFormIndex][inputToFormat][inputPropToFormat] );

      for(const i in newItems){
        if(!filterValues.includes(newItems[i].id)){
          newItems.splice(i, 1)
        }
      }
      for(const i in filterValues){
        const id = filterValues[i]

        if( !newItems.find((el) => el.id == id) ) {
          let res = await axios.get(endpoint.replace(`{${snakeCase(modelName)}}`, id), {
            params: {
              eagers: eagers
            }
          })
          newItems.push(res.data)
        }
      }

      schema[targetFormIndex][inputToFormat][inputPropToFormat] = orderBy(newItems, ['id'], ['asc'])

      // axios.get(endpoint)
      //   .then((res) => {
      //     schema[targetFormIndex][inputToFormat][inputPropToFormat] = res.data.resource.data

      //   })
    }
  },
}



