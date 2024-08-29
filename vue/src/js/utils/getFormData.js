import { find, omitBy, reduce, cloneDeep, map, findIndex, snakeCase, orderBy, get, filter, includes, set, each, isEmpty, unset } from 'lodash-es'
import filters from '@/utils/filters'
import axios from 'axios'

const isArrayable = 'input-treeview|treeview|input-checklist|input-repeater|input-file|input-image'
// const isMediableTypes = 'input-file|input-image'
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
    if(__isset(value.type) && value.type == 'group'){
      let _name = value.name
      let _schema = cloneDeep(value.schema)
      let schema = {}
      for(const key in _schema){
        let newKey = key.split('.').filter((part) => part !== _name).join('.')
        schema[newKey] = _schema[key]
      }
      value.schema = schema
    }
    handleInputEvents(value.event, model, inputs, key)

    return value
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
    let _default = ''

    if (isArrayable.includes(input.type)) _default = []

    _default = Object.prototype.hasOwnProperty.call(input, 'default') ? input.default : _default

    if (input.type == 'group') _default = getModel(input.schema, input.default)

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
    let value = editing ? (__isset(fields[name]) ? fields[name] : (__isset(item[name]) ? item[name] : _default)) : _default
    // const value = editing ? (__isset(item[name]) ? item[name] : _default) : _default

    if(editing){
      if(input.type == 'group' && __isset(item[name])){
        if(JSON.stringify(Object.keys(__dot(_default))) !== JSON.stringify(Object.keys(__dot(item[name])))){
          value = {
            ..._default,
          }
        }
      }
    }

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

  const isArrayable = 'input-treeview|treeview|input-checklist'

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
          // __log(
          //   methodName,
          //   e
          // )
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

export const handleMultiFormEvents = ( models, schemas, input, index, preview = []) => {
  const handlerName = input.name
  const handlerSchema = schemas[index][handlerName]
  const handlerValue = models[index][handlerName]

  const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

  if (input.event) {
    input.event.split('|').forEach(event => {
      let args = event.split(':')
      let methodName = args.shift()

      if(typeof FormatFuncs[methodName] !== 'undefined')
        FormatFuncs?.[methodName](args, models, schemas, input, index, preview)

    })
  }
}

export default {
  getSchema,
  getModel,
  getSubmitFormData,
  setSchemaInputField,
  onInputEventFormData,
  handleInputEvents,
  handleEvents,
  handleMultiFormEvents
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

  formatSet: async function(args, model, schema, input, index = null, preview = []) {
    const inputNotation = this.getInputToFormat(args, model, schema, index )

    if(!inputNotation)
      return

    const inputPropToFormat = args.shift() //
    const setterNotation = `${inputNotation}.${inputPropToFormat}`
    const setPropFormat = args.shift() // items.*.schema

    let {handlerName, handlerSchema, handlerValue} = this.handlers(input, model, index)

    if(Array.isArray(handlerValue) && handlerValue.length < 1)
      return

    if(handlerValue){
      let notation = __wildcard_change(setPropFormat, handlerValue)
      let dataSet = __data_get(handlerSchema, notation, null)

      if(dataSet){
        const newValue = dataSet.shift()
        set(schema, setterNotation, newValue)
        if(inputPropToFormat.match(/schema/)){
          set(schema, `${inputNotation}.default`, getModel(newValue))
        }
      }
    }
  },
  formatFilter: async function(args, model, schema, input, index) {
    const inputNotation = this.getInputToFormat(args, model, schema, index )

    if(!inputNotation)
      return

    let {handlerName, handlerModelName, handlerSchema, handlerValue} = this.handlers(input, model, index)

    const inputPropToFormat = args.shift() // items
    const inputReadValue = args.shift() // group

    let endpoint
    let filterValues
    let modelValue

    if(!!handlerValue[inputReadValue]){
      modelValue = handlerValue[inputReadValue]
      endpoint = input.filterEndpoint[modelValue]
      filterValues = handlerValue[inputPropToFormat]
    }else{
      modelValue = handlerModelName
      endpoint = input.filterEndpoint
      filterValues = handlerValue
    }

    let setterNotation = `${inputNotation}.${inputPropToFormat}`
    let eagers = get(schema, `${inputNotation}.eagers`) ?? [];

    if( !get(schema, setterNotation))
      set(schema, setterNotation, [])

    let newItems = cloneDeep( get(schema, setterNotation) );

    for(const i in newItems){
      if(!filterValues.includes(newItems[i].id)){
        newItems.splice(i, 1)
      }
    }

    for(const i in filterValues){
      const id = filterValues[i]

      if( !newItems.find((el) => el.id == id) ) {
        try {
          let res = await axios.get(endpoint.replace(`{${snakeCase(modelValue)}}`, id), {
            params: {
              eagers: eagers
            }
          })
          newItems.push(res.data)
        } catch (error) {
          // Handle the error here
          console.error('An error occurred:', error);
          // You can also check for specific error types or status codes
          if (error.response) {
            globalError('', {
              message: 'formatFilter error',
              value: error
            })
            // The request was made and the server responded with a status code
            // that falls out of the range of 2xx
            // console.error('Error status:', error.response.status);
            // console.error('Error data:', error.response.data);
          } else if (error.request) {
            // The request was made but no response was received
            console.error('No response received:', error.request);
          } else {
            // Something happened in setting up the request that triggered an Error
            console.error('Error message:', error.message);
          }
        }

      }
    }

    set(schema, setterNotation, orderBy(newItems, ['id'], ['asc']))
  },

  formatPreview: async function(args, model, schema, input, index, preview = []) {
    if(Array.isArray(model)){
      let {handlerName, handlerSchema, handlerValue} = this.handlers(input, model, index)

      /*
      *handlerValue
        {
          $key1 : {package_id: int, packageLanguages: array}
          $key2 : {package_id: int, packageLanguages: array}
        }
      *handlerSchema
        {
          ...,
          items: [ // packageRegion or packageCountry
            {
              id: int,
              name: string,
              packages: [
                {
                  id: int,
                  name: string,
                  packageLanguages: [
                    {
                      id: int,
                      name: string
                    }
                  ]
                }
              ]
            },
            {
              id: == $key1,
              name: 'France',
              packages: [
                {
                  id: == handlerValue[$key1].package_id
                  name: 'Premium',
                  packageLanguages: [
                    {
                      id: == handlerValue[$key1].packageLanguages.*,
                      name: 'English'
                    }
                  ]
                }
              ]
            },
            ...
          ]
        }
      */
      // ['United States', 'Wire (English, German, Turkish)'],
      // ['France', 'Premium (English, French)']
      //
      // handlerSchema.items // region or country parent names to get with  handlerValue Object keys wrt id
      // United States => Object.keys() $key =  handlerSchema.items.find($key)
      // Wire|Premium =>  items.find(id:$key).packages.find(id:handlerValue[$key].package_id).*.name
      // English, German, Turkish... =>  items.find(id:$key).packages.find(id:handlerValue[$key].package_id)
      //

      /*
        schema.$group.items.*.name:items
        items.*key.name,items.*key.packages.*.name,items.*key.packages.$package_id.packageLanguages.*.name:*key,*.package_id,*.packageLanguages
      */
      let inputToFormats = args.shift().split(',')
      let targetValueKeys = (args.shift() ?? '').split(',') // *key,package_id,packageLanguages

      let patternValues = {}
      let previewValue = []

      let isMultiple = inputToFormats.length > 1
      let clear = false

      for(const _index in inputToFormats){
        let inputToFormat = inputToFormats[_index]
        let targetValueKey = targetValueKeys[_index] ?? null
        let stages = inputToFormat.split('.')

        stages = map(stages, function(stage, i){
          let found
          let convertedStage = stage

          if( (found = stage.match(/\$(\w+)/)) ){
            if(__isset(handlerValue[found[1]])){
              convertedStage = handlerValue[found[1]]
            }
          }

          return convertedStage
        })

        inputToFormat = stages.join('.')

        let targetValue

        let matches = targetValueKey.match(/\*\.?(\w+)/)

        if(targetValueKey == '*key'){
          targetValue = Object.keys(handlerValue).map(function(item){
            return parseInt(item)
          })
        }else if(matches){
          let matches = targetValueKey.match(/\*\.?(\w+)/)

          let key = matches[1]
          targetValue = map( handlerValue, function(el, i){
            return el[key]
            return {id: el[key]}
          })
        }else{
          targetValue = targetValueKey ? handlerValue[targetValueKey] : handlerValue
        }
        /**
        __data_get(handlerSchema, 'items.*id=1,5.name'),
        __data_get(handlerSchema, 'items.*id=1,5.packages.*id=1,26.name'),
        __data_get(handlerSchema, 'items.*id=1,5.packageLanguages.*id=1,2,3.name'),
        __data_get(handlerSchema, 'items.*id=1,5.packageLanguages.*id=1,2.name'),
        __data_get(handlerSchema, 'items.*id=1,5.packageLanguages.*id=1.name'),
         *
         */
        if(Array.isArray(targetValue)){

          if(_index == 0){
            clear = targetValue.length < 1

            let parentPattern = inputToFormat.replace(/^([\w\.]+)(\*)([\w\.\*]+)$/, '$1*')
            patternValues[parentPattern] = map(targetValue, (val) => inputToFormat.replace(/^([\w\.]+)(\*)([\w\.\*]+)$/, '$1*' + `id=${val}`))

            let data = __data_get(handlerSchema, __wildcard_change(inputToFormat, targetValue))
            each(data, (val,i) => clear
              ? unset(previewValue, isMultiple ? `[${i}][${_index}]` : `[${i}]`)
              : set(previewValue, isMultiple ? `[${i}][${_index}]` : `[${i}]`, val)  )
            // set(previewValue, )
          }else{
            let parentPatterns

            Object.keys(patternValues).forEach((prev) => {
              let quotedPattern = __preg_quote(prev)
              let pattern = new RegExp( String.raw`^(${quotedPattern}).([\w\$\.\*]+)`)
              let matches = inputToFormat.match(pattern)

              if(matches){
                parentPatterns = patternValues[prev]
                inputToFormat = matches[2]
                return false
              }
            })

            each(targetValue, (val, i) => {
              if(val){

                if(Array.isArray(val) && isEmpty(val)) return

                let parentPattern = parentPatterns[i];

                let ids = Array.isArray(val) ? val.join(',') : val

                // let getter = [parentPattern, inputToFormat.replace(/^([\w\.]+)(\*)([\w\.\*]+)$/, '$1*' + `id=${ids}` + '$3')].join('.')
                let getter = [parentPattern, __wildcard_change(inputToFormat, val)].join('.')
                let data = __data_get(handlerSchema, getter).shift()
                let formattedData = data[0] ?? ''
                if(data.length > 1){
                  formattedData = `(${data.join(',')})`
                }

                set(previewValue, isMultiple ? `[${i}][${_index}]` : `[${_index}]`, formattedData)
              }
            })
          }
        }else{
          // value = targetValue
        }
      }

      if(!preview[index])
        preview[index] = {}

      if(clear)
        unset(preview[index], handlerName)
      else
        preview[index][handlerName] = previewValue

      return
    }
  },

  formatPrependSchema: async function(args, model, schema, input, index = null, preview = []) {
    const inputNotation = this.getInputToFormat(args, model, schema, index )

    if(!inputNotation)
      return

    // const inputPropToFormat = args.shift() //
    // const setterNotation = `${inputNotation}.${inputPropToFormat}`
    const prepend = args.shift() // *.packageLanguages
    const setterSchemaKey = args.shift() // schema.packageLanguages

    let {handlerName, handlerSchema, handlerValue} = this.handlers(input, model, index)

    if(__isObject(handlerValue))
      handlerValue = Object.values(handlerValue)

    if(Array.isArray(handlerValue) && handlerValue.length < 1)
      return

    if(handlerValue){
      if(prepend){
        handlerValue = __data_get(handlerValue, prepend)
        if(prepend.match(/^\*(.*)/)){
          handlerValue = reduce(handlerValue, function(acc, array){
            acc = [...(array ?? []), ...acc];

            return [...new Set(acc)]
          }, [])
        }
      }

      let inputToPrepended = get(schema, inputNotation)
      // __log(inputNotation, inputToPrepended, schema)
      if(!inputToPrepended || !inputToPrepended.schema)
          return

      let oldSchema = cloneDeep(inputToPrepended.schema);

      let prependerInput =  __data_get(handlerSchema, setterSchemaKey)
      // __log(inputNotation, prependerInput)
      if(prependerInput && __isset(prependerInput.schema)){

        if(Array.isArray(handlerValue)){
          if(!prependerInput.items)
            return
          // __log(handlerValue)
          handlerValue = reduce(handlerValue, (acc, id) => {
            acc[id] = {
              label_prefix: __data_get(prependerInput.items, `*id=${id}.${prependerInput.itemTitle}`)?.shift(),
            }
            return acc
          }, {})
        }

        let newSchema = cloneDeep({})

        for(const prependKey in prependerInput.schema){ // prependKey _content

          let quotedPattern = __preg_quote(prependKey)
          let pattern = new RegExp( String.raw`^(\d+)(${quotedPattern})`)
          for(const name in oldSchema){
            let matches = name.match(pattern)
            if(matches){
              let searchId = matches[1]
              // __log( inputNotation, setterSchemaKey, handlerValue)
              if(!__isset(handlerValue[searchId])){
                delete oldSchema[matches[0]]
                // __log('delete this', matches[0])
              }
            }
          }
          for(const val in handlerValue){
            // __log(id, handlerValue, prependKey)
            let draftSchema = cloneDeep(prependerInput.schema[prependKey])

            let pattern = /(\$[\w]+\$)/
            let _inputName = prependKey.replace(pattern, val)
            if(!oldSchema[_inputName]){
              newSchema[_inputName] = {
                ...draftSchema,
                name: _inputName,
                label: draftSchema.label ? `${handlerValue[val].label_prefix} ${draftSchema.label}` : __snakeToHeadline(handlerValue.label_prefix + draftSchema.name)
              }
              // __log(newSchema[_inputName])
            }
          }
        }

        let updatedSchema =  Object.assign(newSchema, oldSchema)
        if(JSON.stringify(inputToPrepended.schema) !== JSON.stringify(updatedSchema)){
          // __log(inputNotation, '[2].content-merge.schema.["content-merge.wrap-files"].schema')
          set(schema, inputNotation + '.schema', updatedSchema)
          // __log(inputNotation)
          // set(schema, '[2].content-merge.schema.["content-merge.wrap-files"].schema', newSchema)
          // __log(inputNotation + '.schema', get(schema, inputNotation + '.schema'))
        }

      }
    }
  },

  handlers: (input, model, index = null) => {
    const handlerName = input.name
    const handlerModelName = input.name
    const handlerSchemaName = input.key ?? input.name

    const handlerSchema = input // schema[index][handlerName]
    const handlerValue = __data_get(model, !isNaN(index) ? `${index}.${handlerModelName}` : handlerModelName)

    // const handlerSchema = get(schema, `${index}.${handlerSchemaName}`)
    // const handlerValue = get(model, `${index}.${handlerModelName}`)
    return {
      handlerName,
      handlerModelName,
      handlerSchemaName,
      handlerSchema,
      handlerValue
    }
  },
  getInputToFormat(args, model, schema, input, index){

    let inputToFormat = args.shift() // 2.packages || package
    let inputNotationParts = []

    let stages = inputToFormat.split('.')
    let targetFormIndex = parseInt(stages[0])

    if(isNaN(targetFormIndex)){
      targetFormIndex = index
    }else if(!Array.isArray(model)){
      return false
    }

    if(Array.isArray(model)){
      if(!isNaN(targetFormIndex)){
        targetFormIndex -= 1
        stages.shift()
      }
      inputNotationParts.push(`[${targetFormIndex}]`)
    }

    inputToFormat = stages.join('.')
    inputNotationParts.push(inputToFormat)

    return inputNotationParts.join('.')
  }

}



