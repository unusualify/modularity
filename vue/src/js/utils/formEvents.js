import _ from 'lodash-es'

import store from '@/store'  // Adjust path to your store file
import { CACHE } from '@/store/mutations'

import { getModel } from './getFormData'
import { getTranslationLanguages } from './locale'
import filters from '@/utils/filters'

import FormEventFormatters from './formEventFormatters'

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
          if (isFieldFalsy) {
            _fields[formattedInputName] = filters.slugify(_field)
          }
          break
        case 'formatPermalinkPrefix':
          if (['select', 'combobox'].includes(_schema.type) && _field && isFieldFalsy) {
            const newValue = filters.slugify(_schema.items.find((item) => item[_schema.itemValue] === _field)[_schema.itemTitle])
            moduleSchema[formattedInputName ?? 'slug'].prefix = moduleSchema[formattedInputName ?? 'slug'].prefixFormat.replace(':' + formatingInputName, newValue)
          } else if (['text'].includes(_schema.type) && isFieldFalsy) {
            const newValue = filters.slugify(_field)
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
        case 'formatFilter':
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
    let events = _.uniq(input.event.split('|'));

    events.forEach(event => {
      let args = event.split(':')
      let methodName = args.shift()
      let runnable = true

      if(methodName == 'formatSetx' && !valueChanged)
        runnable = false

      if(runnable && typeof FormEventFormatters[methodName] !== 'undefined')
        FormEventFormatters?.[methodName](args, model, schema, input)

    })
  }
}

export const handleMultiFormEvents = ( models, schemas, input, index, preview = []) => {
  const handlerName = input.name
  const handlerSchema = schemas[index][handlerName] ?? _.get(schemas, `[${index}].${handlerName}`)
  const handlerValue = models[index][handlerName] ?? _.get(models, `[${index}].${handlerName}`)

  const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

  if (input.event) {
    let events = _.uniq(input.event.split('|'));
    events.forEach(event => {
      let args = event.split(':')
      let methodName = args.shift()

      if(typeof FormEventFormatters[methodName] !== 'undefined')
        FormEventFormatters?.[methodName](args, models, schemas, input, index, preview)

    })
  }

  if(false && input.schema){
    for(const name in input.schema){

      const _input = _.cloneDeep(input.schema[name])
      _input.name = `${input.name}.${input.schema[name].name}`
      _input.key = `${input.name}.schema.${input.schema[name].name}`
    }
  }
}

export default {
  setSchemaInputField,
  onInputEventFormData,
  handleInputEvents,
  handleEvents,
  handleMultiFormEvents,
}
