// utils/formEventFormatters/formatPermalinkPrefix.js
import formatHelpers from './helpers'
import filters from '@/utils/filters'

export default async function formatPermalinkPrefix(args, model, schema, input, index = null, preview = []) {
  const handlerName = input.name
  const handlerSchema = schema[handlerName]
  const handlerValue = model[handlerName]

  const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

  const inputToFormat = args.shift()
  const inputFormatter = args.shift()

  if (['select', 'combobox'].includes(handlerSchema.type) && handlerValue && isFieldFalsy) {
    const newValue = filters.slugify( handlerSchema.items.find((item) => item[handlerSchema.itemValue] === handlerValue)[handlerSchema.itemTitle])
    schema[inputToFormat ?? 'slug'].prefix = schema[inputToFormat ?? 'slug'].prefixFormat.replace(':' + inputFormatter, newValue)
  } else if (['text'].includes(handlerSchema.type) && isFieldFalsy) {
    const newValue = filters.slugify(value)
    const [firstLevelName, secondLevelName] = inputToFormat.split('.')
    schema[firstLevelName].schema[secondLevelName ?? 'slug'].prefix = schema[firstLevelName].schema[secondLevelName ?? 'slug'].prefixFormat.replace(':' + inputFormatter, newValue)
  }
}

