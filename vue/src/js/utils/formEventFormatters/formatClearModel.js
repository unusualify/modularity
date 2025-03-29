// utils/formEventFormatters/formatClearModel.js
import formatHelpers from './helpers'
import _ from 'lodash-es'

export default async function formatClearModel(args, model, schema, input, index = null, preview = []) {
  const inputNotation = formatHelpers.getInputToFormat(args, model, schema, index )

  if(!inputNotation)
    return

  let {handlerName, handlerSchema, handlerValue} = formatHelpers.handlers(input, model, index)

  let targetSchema = _.get(schema, inputNotation)
  let defaultValue = targetSchema?.default ?? []

  _.set(model, inputNotation, defaultValue)

  if(_.get(preview, inputNotation)){
    _.unset(preview, inputNotation)
  }
}

