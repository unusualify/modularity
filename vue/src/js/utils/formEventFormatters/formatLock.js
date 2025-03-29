// utils/formEventFormatters/formatLock.js
import formatHelpers from './helpers'

export default async function formatLock(args, model, schema, input, index = null, preview = []) {
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
}

