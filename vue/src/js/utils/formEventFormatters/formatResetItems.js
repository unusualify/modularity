// utils/formEventFormatters/formatResetItems.js
import formatHelpers from './helpers'
import _ from 'lodash-es'

export default async function formatResetItems(args, model, schema, input, index = null, preview = []) {
  const inputNotation = formatHelpers.getInputToFormat(args, model, schema, index )

  if(!inputNotation)
    return

  let {handlerName, handlerSchema, handlerValue} = formatHelpers.handlers(input, model, index)

  let targetSchema = _.get(schema, inputNotation)
  let defaultValue = []

  _.set(schema, inputNotation + '.items', defaultValue)
}

