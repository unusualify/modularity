// utils/formEventFormatters/formatToggleInput.js
import formatHelpers from './helpers'
import _ from 'lodash-es'

import store from '@/store'  // Adjust path to your store file
import { CACHE } from '@/store/mutations'

export default async function formatToggleInput(args, model, schema, input, index = null, preview = []) {
  const inputNotation = formatHelpers.getInputToFormat(args, model, schema, index )

  if(!inputNotation)
    return

  const inputToFormat = inputNotation // 1.
  const setPropFormat = args.shift() ?? 'items.*.toggleValue' // 2.
  let toggleLevel = args.shift() ?? '-1' // 3.

  toggleLevel = parseInt(toggleLevel) ?? -1

  let {handlerName, handlerSchema, handlerValue} = formatHelpers.handlers(input, model, index)

  if(handlerValue === undefined || handlerValue === null){
    return
  }

  let newValue = formatHelpers.getNewValue(setPropFormat, handlerValue, handlerSchema)

  if(newValue !== undefined && newValue !== null && _.isBoolean(newValue)){
    let activate = newValue

    const targetInput = __data_get(schema, inputNotation)

    let selfCacheMainKey = `formatToggleInput:${inputNotation}`

    handleToggleInput(activate, inputNotation, targetInput, schema, selfCacheMainKey, toggleLevel)
  }
}

const toggleActivateOperations = {
  class : 'd-none',
  rawRules : '#',
}
const toggleDeactivateOperations = {
  class : 'd-none',
  rawRules : '',
}

const handleToggleInput = (isActivate, toggleSetterNotation, input, schema, cacheParent, level = -1, count = 0) => {
  count += 1

  if(isActivate){
    _.each(toggleActivateOperations, (value, propName) => {
      let propSetterNotation = toggleSetterNotation + '.' + propName
      let getterCachedNotation = toggleSetterNotation + '._cached-' + propName
      let cacheKey = cacheParent ? `${cacheParent}:${propName}` : `${propName}`
      let currentValue = _.get(schema, propSetterNotation)
      let cachedDefaultValue = store.getters[CACHE.GET_LAST_CACHE](cacheKey) || _.get(schema, getterCachedNotation) || null
      let newValue = currentValue

      let valueChanged = false

      if(value === '#'){
        if(cachedDefaultValue){
          newValue = cachedDefaultValue
          valueChanged = true
        }
      }else{
        switch(propName){
          case 'class':
            let classes = currentValue ? currentValue.split(' ') : []
            classes = classes.filter(cls => cls !== value)
            newValue = classes.join(' ')
            valueChanged = true
            break
          case 'rawRules':
            newValue = value
            valueChanged = true
            break
        }
      }

      if(valueChanged){
        if(cachedDefaultValue === undefined || cachedDefaultValue === null){
          store.commit(CACHE.PUSH_CACHE, {key: cacheKey, value: currentValue})
        }

        _.set(schema, propSetterNotation, newValue)
      }
    })
  }else{
    _.each(toggleDeactivateOperations, (value, propName) => {
      let propSetterNotation = toggleSetterNotation + '.' + propName
      let getterCachedNotation = toggleSetterNotation + '._cached-' + propName
      let cacheKey = cacheParent ? `${cacheParent}:${propName}` : `${propName}`
      let currentValue = _.get(schema, propSetterNotation)
      let cachedDefaultValue = store.getters[CACHE.GET_LAST_CACHE](cacheKey) || _.get(schema, getterCachedNotation) || null
      let newValue = currentValue

      let valueChanged = false

      if(value === '#'){
        if(cachedDefaultValue){
          newValue = cachedDefaultValue
          valueChanged = true
        }
      }else{
        switch(propName){
          case 'class':
            let classes = currentValue ? currentValue.split(' ') : []
            classes.push(value)
            classes = _.uniq(classes)
            newValue = classes.join(' ')

            valueChanged = true
            break
          case 'rawRules':
            newValue = value
            valueChanged = true
            break
        }
      }

      if(valueChanged){
        if(cachedDefaultValue === undefined || cachedDefaultValue === null){
          store.commit(CACHE.PUSH_CACHE, {key: cacheKey, value: currentValue})
        }
        _.set(schema, propSetterNotation, newValue)
      }
    })
  }

  if(level > -1 && level <= count){
    return
  }

  if(input.schema && _.isObject(input.schema)){
    _.each(input.schema, (subInput, subInputName) => {
      handleToggleInput(isActivate, `${toggleSetterNotation}.schema.${subInputName}`, subInput, schema, cacheParent, level, count)
    })
  }
}

