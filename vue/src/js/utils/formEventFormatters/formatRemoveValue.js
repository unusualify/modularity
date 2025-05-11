// utils/formEventFormatters/formatRemoveValue.js
import formatHelpers from './helpers'
import _ from 'lodash-es'


export default async function formatRemoveValue(args, model, schema, input, index = null, preview = []) {
  const inputNotation = formatHelpers.getInputToFormat(args, model, schema, index )

  if(!inputNotation)
    return

  let {handlerName, handlerSchema, handlerValue} = formatHelpers.handlers(input, model, index)

  if(__isObject(handlerValue))
    handlerValue = Object.values(handlerValue)

  let currentTargetValue = _.get(model, inputNotation);

  if(Array.isArray(currentTargetValue) && currentTargetValue.length > 0){
    currentTargetValue = currentTargetValue.filter(item => item.id !== handlerValue)

    _.set(model, inputNotation, currentTargetValue)
  } else if (window.__isObject(currentTargetValue) && Object.keys(currentTargetValue).length > 0){
    let currentKeys = Object.keys(currentTargetValue)

    currentKeys = currentKeys.map(key => window.__isString(key) ? (parseInt(key) > 0 ? parseInt(key) : key) : key)

    let absentKeys = currentKeys.filter(key => !handlerValue.includes(key))
    absentKeys.forEach(key => {
      delete currentTargetValue[key]
    })

    if(absentKeys.length > 0){
      _.set(model, inputNotation, currentTargetValue)
    }
  }
}

