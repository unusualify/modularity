import _ from 'lodash-es'
import { useI18n } from 'vue-i18n'
import filters from '@/utils/filters'
import axios from 'axios'

import {
  isViewOnlyInput,
  isFormEventInput,
  getTranslationInputsCount,
  getTranslationInputs,
  flattenGroupSchema,
  processInputs
} from './schema'

import {
  setSchemaInputField,
  onInputEventFormData,
  handleInputEvents,
  handleEvents,
  handleMultiFormEvents,
} from './formEvents'

import { globalError } from './errors'
import { checkItemConditions } from './itemConditions'
import { getTranslationLanguages } from './locale'

import sampleModel from '@/__snapshots/getFormData/model.json';
import sampleSchema from '@/__snapshots/getFormData/schema.json';


const isArrayable = 'input-treeview|treeview|input-checklist|input-repeater|input-file|input-image'
const numberable = 'number-input'
// const isMediableTypes = 'input-file|input-image'
// const isMediableFields = 'files|medias'

export const getSchema = (inputs, model = null, isEditing = false) => {
  let _inputs = _.omitBy(inputs, (value, key) => {
    return !checkItemConditions(value.conditions, model)
      || Object.prototype.hasOwnProperty.call(value, 'slotable')
      || isFormEventInput(value, model)
      || isViewOnlyInput(value)
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
    let parsedKey = parseInt(key)
    if(!_.isNaN(parsedKey) && input.name){
      key = input.name
    }

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

    let isCreatable = input.creatable ?? true;
    let isEditable = input.editable ?? true;

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

    input.creatable = isCreatable
    input.editable = isEditable
    input.isEditing = isEditing

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

      if(Object.prototype.hasOwnProperty.call(input, 'connectedRelationship') && _.isString(input.connectedRelationship) && item && __isset(item[input.connectedRelationship])){
        value = item[input.connectedRelationship]
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
        }else{ // translations create
          fields[name] = getTranslationLanguages().reduce(function (map, lang) {
            map[lang] = __isset(value[lang]) ? value[lang] : ''
            return map
          }, {})
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

    if(numberable.includes(input.type) && __isset(value) && __isString(value)){
      console.log(name, value)
      fields[name] = _.toNumber(value)
    }

    if(input.type == 'preview' && __isset(input.previewKey) && item && __isset(item[input.previewKey])){
      fields[name] = item[input.previewKey]
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

export const getFormEventSchema = (inputs, model = null, isEditing = false) => {
  return _.filter(inputs, (input) => {
    if(isEditing && __isset(input.editable) && (input.editable === false || input.editable === 'hidden'))
      return false

    if(!isEditing && __isset(input.creatable) && (input.creatable === false || input.creatable === 'hidden'))
      return false

    return isFormEventInput(input, model) && (input.conditions ? checkItemConditions(input.conditions, model) : true)
  })
}

export const getSubmitFormData = (inputs, item = null, rootState = null) => {
  inputs = processInputs(inputs)

  const isArrayable = 'input-treeview|treeview|input-checklist'

  const values = Object.keys(inputs).reduce((fields, k) => {

    if(window.__isset(inputs[k].noSubmit) && inputs[k].noSubmit)
      return fields

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

