import _ from 'lodash-es'
import { useI18n } from 'vue-i18n'
import filters from '@/utils/filters'
import axios from 'axios'
import { globalError } from './errors'

import sampleModel from '@/__snapshots/getFormData/model.json';
import sampleSchema from '@/__snapshots/getFormData/schema.json';

const isArrayable = 'input-treeview|treeview|input-checklist|input-repeater|input-file|input-image'
// const isMediableTypes = 'input-file|input-image'
// const isMediableFields = 'files|medias'

export const getSchema = (inputs, model = null, isEditing = false) => {
  let _inputs = _.omitBy(inputs, (value, key) => {
    return Object.prototype.hasOwnProperty.call(value, 'slotable') || isTopEventInput(value) || isViewOnlyInput(value)
  })

  // if (_.find(_inputs, (input) => Object.prototype.hasOwnProperty.call(input, 'wrap'))) {
  //   _.reduce(_inputs, (acc, input, key) => {
  //     if(Object.prototype.hasOwnProperty.call(input, 'group')){

  //     } else{
  //       acc[key] = input
  //     }
  //     return acc
  //   }, {})
  // }

  _inputs = _.reduce(_inputs, (acc, input, key) => {
    input.col.class = input._originalClass || input.col?.class || [];
    input._originalClass = input.col.class || [];
    input.disabled = __isset(input._originalDisabled)
      ? input._originalDisabled
      : __isset(input.disabled)
        ? input.disabled
        : false
    input._originalDisabled = input.disabled

    let inputColClass = input.col?.class || [];

    if (__isString(inputColClass)) {
      inputColClass = inputColClass.split(' ');
    }

    let isCreatable = input.creatable;
    let isEditable = input.editable;

    // Check if the input has createable property and it's false or hidden
    if ((isCreatable === false || isCreatable === 'hidden') && !isEditing) {
      if (isCreatable === 'hidden') {
        inputColClass = _.union(inputColClass, ['d-none']);
        input.col.class = inputColClass.join(' ');
      } else {
        input.disabled = true;
      }
    }

    // Check if the input has editable property and it's false or hidden
    if ((isEditable === false || isEditable === 'hidden') && isEditing) {
      if (isEditable === 'hidden') {
        inputColClass = _.union(inputColClass, ['d-none']);
        input.col.class = inputColClass.join(' ');
      } else {
        input.disabled = true;
      }
    }

    if (__isset(input) && __isset(input.schema) && ['wrap', 'group', 'repeater', 'input-repeater'].includes(input.type)) {
      input.schema = getSchema(input.schema, input.type === 'wrap' ? model : model[key], isEditing);
    }

    // Always add the input to the accumulator
    acc[key] = input;

    return acc;
  }, {});

  _.map(_inputs, (value, key) => {
    if(__isset(value.type) && value.type == 'group'){
      value.schema = flattenGroupSchema(value.schema, value.name);
    }
    handleInputEvents(value.event, model, inputs, key)

    return value
  })

  return _inputs
}

export const getModel = (inputs, item = null, rootState = null) => {
  const languages = getTranslationLanguages()
  const editing = __isset(item)

  inputs = processInputs(inputs)
  const values = Object.keys(inputs).reduce((fields, k) => {
    const input = inputs[k]
    const name = input.name
    const isTranslated = Object.prototype.hasOwnProperty.call(input, 'translated') && input.translated

    // default model value
    let _default = ''

    if (isArrayable.includes(input.type)) _default = []

    _default = Object.prototype.hasOwnProperty.call(input, 'default') ? input.default : _default

    if (input.type == 'group') _default = getModel(input.schema, item ?? input.default)

    if (isTranslated) {
      _default = _.reduce(languages, function (acc, language, k) {
        acc[language] = _default
        return acc
      }, {})
    }

    let accessName = name.replace(/->/g, '.')
    let value = editing ? (__isset(fields[name]) ? fields[name] : (__data_get(item, accessName, _default) ?? _default)) : _default

    if(editing){
      if(input.type == 'group' && __isset(item[name])){
        let defaultGroupKeys = Object.keys(_.omit(__dot(_default), ['id']));
        if(JSON.stringify(defaultGroupKeys) !== JSON.stringify(Object.keys(_.omit(__dot(item[name]), ['id'])))){
          value = {
            ..._default,
            ..._.pick(item[name], defaultGroupKeys)
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
              map[lang] = _.find(item.translations, { locale: lang })
                ? _.find(item.translations, { locale: lang })[name]
                : item.translations[name][lang]
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

export const getTopSchema = (inputs, isEditing = false) => {
  return _.filter(inputs, (input) => {
    if(isEditing && __isset(input.editable) && (input.editable === false || input.editable === 'hidden'))
      return false

    if(!isEditing && __isset(input.creatable) && (input.creatable === false || input.creatable === 'hidden'))
      return false

    return isTopEventInput(input)
  })
}

export const getTranslationInputsCount = (inputs) => {
  return getTranslationInputs(inputs).length
}

export const getTranslationInputs = (inputs, acc = []) => {
  return _.reduce(inputs, (acc, input) => {
    if(__isset(input) && __isset(input.schema) && ['wrap', 'group', 'repeater', 'input-repeater'].includes(input.type)){
      acc = getTranslationInputs(input.schema, acc)
    } else if(Object.prototype.hasOwnProperty.call(input, 'translated') && input.translated)
      acc.push(input)

    return acc
    // return Object.prototype.hasOwnProperty.call(input, 'translated') && input.translated
  }, acc)
}

export const getSubmitFormData = (inputs, item = null, rootState = null) => {
  inputs = processInputs(inputs)

  const isArrayable = 'input-treeview|treeview|input-checklist'

  const values = Object.keys(inputs).reduce((fields, k) => {
    const input = inputs[k]
    // if (isMediableTypes.includes(input.type)) {
    //   return fields
    // }

    const name = input.name

    const value = __data_get(item, name, undefined)

    // default model value
    if (!__isset(value)) {
      let value = input.default ?? ''
      if (isArrayable.includes(input.type)) {
        value = []
      }

      fields[name] = value

      return fields
    }
    // const value = item[name]

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
      if(__isset(schema[sch.cascade]))
        schema[sch.cascade][cascadeKey] = _.find(sch[cascadeKey], [sch.itemValue, value[sch.name]])?.[cascadeKey] ?? []
      // this.formSchema[key].items = _.find(this.formSchema[sch.parent].items, [this.formSchema[sch.parent].itemValue, this.valueIntern[sch.parent]]).items
    }
  }
}

export const onInputEventFormData = (obj, schema, stateData, sortedStateData, value) => {

  if (obj.schema.type === 'select' && obj.schema.hasOwnProperty('cascade')) {
    const cascadedSelectName = obj.schema.name.includes('repeater') ? obj.schema.name.match(/repeater\d+-input\[\d+\]/) + `[${obj.schema.cascade}]` : obj.schema.cascade
    const cascadeKey = obj.schema.cascadeKey ?? 'items'
    const selectItemValue = obj.schema.itemValue ?? 'id'

    // ACTIONS
    schema[cascadedSelectName][cascadeKey] = _.find(obj.schema[cascadeKey], [selectItemValue, value[obj.key]])?.schema ?? []
    const sortIndex = _.findIndex(sortedStateData, ['key', cascadedSelectName])

    stateData[cascadedSelectName] = schema[cascadedSelectName][cascadeKey].length > 0 ? schema[cascadedSelectName][cascadeKey][0].value : []
    sortedStateData[sortIndex].value = value[cascadedSelectName]

    onInputEventFormData(sortedStateData[sortIndex], schema, stateData, sortedStateData, value)

  } else if ((obj.schema.type === 'select') && obj.schema.hasOwnProperty('autofill')) {
    __log('autofill')
    // obj.schema.autofill.forEach(element => {
    //   if (schema[element].autofillable) {
    //     stateData[element] = _.find(obj.schema.items, ['id', value[obj.key]])?.[element] ?? ''
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

export const handleEvents = ( model, schema, input, valueChanged = false) => {

  const handlerName = input.name
  const handlerSchema = schema[handlerName]
  const handlerValue = model[handlerName]

  const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

  if (input.event) {
    input.event.split('|').forEach(event => {
      let args = event.split(':')
      let methodName = args.shift()
      let runnable = true

      if(methodName == 'formatSet' && !valueChanged)
        runnable = false

      if(runnable && typeof FormatFuncs[methodName] !== 'undefined')
        FormatFuncs?.[methodName](args, model, schema, input)

    })
  }
}

export const handleMultiFormEvents = ( models, schemas, input, index, preview = []) => {
  const handlerName = input.name
  const handlerSchema = schemas[index][handlerName] ?? _.get(schemas, `[${index}].${handlerName}`)
  const handlerValue = models[index][handlerName] ?? _.get(models, `[${index}].${handlerName}`)

  const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

  if (input.event) {
    input.event.split('|').forEach(event => {
      let args = event.split(':')
      let methodName = args.shift()

      if(typeof FormatFuncs[methodName] !== 'undefined')
        FormatFuncs?.[methodName](args, models, schemas, input, index, preview)

    })
  }
  if(false && input.schema){
    for(const name in input.schema){

      const _input = _.cloneDeep(input.schema[name])
      _input.name = `${input.name}.${input.schema[name].name}`
      _input.key = `${input.name}.schema.${input.schema[name].name}`
      // __log(
      //   // _.get(schemas, `[${index}].${_input.key}`),
      //   // _.get(models, `[${index}].${_input.name}`),
      // )
    }
  }
}

export const testMethods = {
  // getDisplayData: () => {
  //   return getDisplayData(sampleSchema, sampleModel)
  // },
  getSubmitFormData: () => {
    return getSubmitFormData(sampleSchema, sampleModel)
  },
  getSchema: () => {
    return getSchema(sampleSchema, sampleModel)
  },
  getModel: () => {
    return getModel(sampleSchema, sampleModel)
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
  handleMultiFormEvents,
  testMethods
}

const processInputs = (inputObj) => {
  return _.reduce(inputObj, (acc, value, key) => {
    if (value.type === 'wrap' && value.schema) {
      Object.assign(acc, processInputs(value.schema));
    } else if (value.type === 'groupz' && value.schema) {
      // acc[key] = {
      //   ...value,
      //   schema: processInputs(value.schema)
      // };
    } else if (!value.slotable) {
      acc[key] = value;
    } else {
      acc[key] = value;
      // __log(inputObj, value, key)
    }
    return acc;
  }, {});
};

const flattenGroupSchema = (schema, groupName) => {
  return _.reduce(schema, (acc, value, key) => {
    const newKey = key.split('.').filter(part => part !== groupName).join('.');
    acc[newKey] = value;
    return acc;
  }, {});
};

const slugify = (newValue) => {
  let text = ''
  if (newValue.value && typeof newValue.value === 'string') {
    text = newValue.value
  } else if (typeof newValue === 'string') {
    text = newValue
  }

  return filters.slugify(text)
}

export const isTopEventInput = (input) => {
  return Object.prototype.hasOwnProperty.call(input, 'topEvent')
    && input.topEvent
    && ( ['select', 'autocomplete', 'combobox', 'switch'].includes(input.type) || __isset(input.viewOnlyComponent) )
}

const isViewOnlyInput = (input) => {
  return Object.prototype.hasOwnProperty.call(input, 'viewOnlyComponent')
}

const getTranslationLanguages = (input) => {
  return _.map(window[import.meta.env.VUE_APP_NAME].STORE.languages.all, 'value')
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
    const languages = getTranslationLanguages()

    if(!inputNotation)
      return

    const inputToFormat = inputNotation
    const inputPropToFormat = args.shift() //
    const setterNotation = `${inputNotation}.${inputPropToFormat}`
    const setPropFormat = args.shift() // items.*.schema

    let {handlerName, handlerSchema, handlerValue} = this.handlers(input, model, index)

    if(Array.isArray(handlerValue) && handlerValue.length < 1)
      return

    if(handlerValue){
      let dataSet = []
      let notation = __wildcard_change(setPropFormat, handlerValue)
      dataSet = __data_get(handlerSchema, notation, null)

      if(dataSet && dataSet.length > 0){
        let newValue = dataSet.shift()


        let matches = inputPropToFormat.match(/^(modelValue|model)$/g)
        // __log(inputPropToFormat, matches)
        if(matches){ // setting modelValue
          let targetInput = _.get(schema, inputToFormat)
          let targetInputName = targetInput.name
          let targetForeignKey = __extractForeignKey(targetInputName)
          let targetInputSchema = targetInput.schema ?? null
          let isRepeater = targetInput.type == 'input-repeater'
          let isArrayValue = Array.isArray(newValue)

          if(__isset(targetInput['translated']) && targetInput['translated']){
            let translationParts = notation.split('.')
            let field = translationParts.pop()
            let translationNotation = translationParts.join('.') + '.translations'
            notation.split('.').pop()
            let rawTranslation = __data_get(handlerSchema, translationNotation).shift()

            if(rawTranslation){
              // TODO: translations do not comes from package_type
              __log(rawTranslation)
              newValue = _.reduce(languages, (acc, language) => {
                let translation = _.find(rawTranslation, (el) => el.locale == language) ?? rawTranslation[0]
                let value = translation[field] ?? null
                acc[language] = translation[field] ?? null
                return acc
              }, {})
            }
          }

          if(isArrayValue && newValue.length > 0){
            let values = newValue.map((item) => {
              if(targetInputSchema){
                return _.reduce(targetInputSchema, (acc, value, key) => {
                  // __log(key, value)
                  if(isRepeater && key == targetForeignKey){
                    acc[targetForeignKey] = item['id'] ?? null
                  }else{
                    acc[key] = item[key] ?? null
                  }

                  return acc
                }, {})
              }
            })

            _.set(model, inputToFormat, values)
            // let currentValue = _.get(model, inputToFormat)
            // __log( inputToFormat, __data_get(model, inputToFormat), model )
            // if( !(Array.isArray(currentValue) && currentValue.length > 0)){
            //   // __log('setting')
            // }
          }else if(!isArrayValue){
            try{
              _.set(model, targetInputName, newValue)
            }catch(e){
              console.error(e)
            }
          }
        }else{
          _.set(schema, setterNotation, newValue)
          if(inputPropToFormat.match(/schema/)){
            _.set(schema, `${inputNotation}.default`, getModel(newValue))
          }

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
    let eagers = _.get(schema, `${inputNotation}.eagers`) ?? [];

    if( !_.get(schema, setterNotation))
      _.set(schema, setterNotation, [])

    let newItems = _.cloneDeep( _.get(schema, setterNotation) );

    for(const i in newItems){
      if(!filterValues.includes(newItems[i].id)){
        newItems.splice(i, 1)
      }
    }

    for(const i in filterValues){
      const id = filterValues[i]

      if( !newItems.find((el) => el.id == id) ) {
        try {
          let res = await axios.get(endpoint.replace(`{${_.snakeCase(modelValue)}}`, id), {
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

    _.set(schema, setterNotation, _.orderBy(newItems, ['id'], ['asc']))
  },

  formatClearModel: async function(args, model, schema, input, index = null, preview = []) {
    const inputNotation = this.getInputToFormat(args, model, schema, index )

    if(!inputNotation)
      return

    let {handlerName, handlerSchema, handlerValue} = this.handlers(input, model, index)

    let targetSchema = _.get(schema, inputNotation)
    let defaultValue = targetSchema?.default ?? []

    _.set(model, inputNotation, defaultValue)

    if(_.get(preview, inputNotation)){
      _.unset(preview, inputNotation)
      __log('formatClearModel', preview)
    }

  },

  formatResetItems: async function(args, model, schema, input, index = null, preview = []) {
    const inputNotation = this.getInputToFormat(args, model, schema, index )

    if(!inputNotation)
      return

    let {handlerName, handlerSchema, handlerValue} = this.handlers(input, model, index)

    let targetSchema = _.get(schema, inputNotation)
    let defaultValue = []

    _.set(schema, inputNotation + '.items', defaultValue)
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

        stages = _.map(stages, function(stage, i){
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
          targetValue = _.map( handlerValue, function(el, i){
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
            patternValues[parentPattern] = _.map(targetValue, (val) => inputToFormat.replace(/^([\w\.]+)(\*)([\w\.\*]+)$/, '$1*' + `id=${val}`))

            let data = __data_get(handlerSchema, __wildcard_change(inputToFormat, targetValue))
            _.each(data, (val,i) => clear
              ? _.unset(previewValue, isMultiple ? `[${i}][${_index}]` : `[${i}]`)
              : _.set(previewValue, isMultiple ? `[${i}][${_index}]` : `[${i}]`, val)  )
            // _.set(previewValue, )
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

            _.each(targetValue, (val, i) => {
              if(val){

                if(Array.isArray(val) && _.isEmpty(val)) return

                let parentPattern = parentPatterns[i];
                let ids = Array.isArray(val) ? val.join(',') : val

                // let getter = [parentPattern, inputToFormat.replace(/^([\w\.]+)(\*)([\w\.\*]+)$/, '$1*' + `id=${ids}` + '$3')].join('.')
                let getter = [parentPattern, __wildcard_change(inputToFormat, val)].join('.')
                let data = __data_get(handlerSchema, getter).shift()

                if(!data){
                  console.warn('formatPreview error', {
                    getter,
                    inputToFormat,
                    data,
                    handlerSchema,

                  })
                  return
                }
                let formattedData = data[0] ?? ''

                if(_index > 1 && Array.isArray(data)){
                  formattedData = `(${data.join(', ')})`
                }

                _.set(previewValue, isMultiple ? `[${i}][${_index}]` : `[${_index}]`, formattedData)
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
        _.unset(preview[index], handlerName)
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
          handlerValue = _.reduce(handlerValue, function(acc, array){
            acc = [...(array ?? []), ...acc];

            return [...new Set(acc)]
          }, [])
        }
      }

      let inputToPrepended = _.get(schema, inputNotation)

      if(!inputToPrepended || !inputToPrepended.schema)
          return

      let oldSchema = _.cloneDeep(inputToPrepended.schema);

      let prependerInput =  __data_get(handlerSchema, setterSchemaKey)

      if(prependerInput && __isset(prependerInput.schema)){

        if(Array.isArray(handlerValue)){
          if(!prependerInput.items)
            return
          // __log(handlerValue)
          handlerValue = _.reduce(handlerValue, (acc, id) => {
            acc[id] = {
              label_prefix: __data_get(prependerInput.items, `*id=${id}.${prependerInput.itemTitle}`)?.shift(),
            }
            return acc
          }, {})
        }
        let newSchema = _.cloneDeep({})
        let lastPrependedKeys = _.get(schema, inputNotation + '._prependedKeys', [])

        let relatedKeys = []
        for(const prependKey in prependerInput.schema){ // prependKey _content

          let quotedPattern = __preg_quote(prependKey)
          let pattern = new RegExp( String.raw`^(\d+)(${quotedPattern})`)
          for(const val in handlerValue){
            let draftSchema = _.cloneDeep(prependerInput.schema[prependKey])

            let pattern = /(\$[\w]+\$)/
            let _inputName = prependKey.replace(pattern, val)
            relatedKeys.push(_inputName)
            if(!oldSchema[_inputName]){
              newSchema[_inputName] = {
                ...draftSchema,
                name: _inputName,
                label: draftSchema.label
                  ? `${handlerValue[val].label_prefix} ${draftSchema.label}`
                  : __snakeToHeadline(handlerValue.label_prefix + draftSchema.name)
              }
            }
          }
        }

        let deletedKeys = lastPrependedKeys.filter(key => !relatedKeys.includes(key))

        // delete oldSchema last prepended keys that are not in newSchema
        deletedKeys.forEach(key => {
          delete oldSchema[key]
        })
        let prependedKeys = relatedKeys.filter(key => !deletedKeys.includes(key))

        let updatedSchema =  Object.assign(newSchema, oldSchema)

        if(JSON.stringify(inputToPrepended.schema) !== JSON.stringify(updatedSchema)){
          _.set(schema, inputNotation + '.schema', updatedSchema)
          _.set(schema, inputNotation + '._prependedKeys', prependedKeys)
        }

      }
    }
  },

  handlers: (input, model, index = null) => {
    const handlerName = input.name
    const handlerModelName = input.name
    const handlerSchemaName = input.key ?? input.name
    const handlerSchema = input // schema[index][handlerName]
    let handlerValue = __data_get(model, !isNaN(parseInt(index)) ? `${index}.${handlerModelName}` : handlerModelName)
    // __log(handlerName, handlerValue)
    if(!handlerValue && __isset(handlerSchema.parentName)){
      handlerValue = __data_get(model, !isNaN(index) ? `${index}.${handlerSchema.parentName}.${handlerModelName}` : `${handlerSchema.parentName}.${handlerModelName}`)
    }
    // const handlerSchema = _.get(schema, `${index}.${handlerSchemaName}`)
    // const handlerValue = _.get(model, `${index}.${handlerModelName}`)
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



