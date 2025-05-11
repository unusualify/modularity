// utils/formEventFormatters/formatPermalink.js
import formatHelpers from './helpers'
import filters from '@/utils/filters'

export default async function formatPermalink(args, model, schema, input, index = null, preview = []) {
  const handlerName = input.name
  const handlerSchema = schema[handlerName]
  const handlerValue = model[handlerName]

  const isFieldFalsy = (Array.isArray(handlerValue) && handlerValue.length > 0) || (!Array.isArray(handlerValue) && !!handlerValue)

  const inputToFormat = args.shift()

  if (isFieldFalsy) {
    model[inputToFormat] = filters.slugify(handlerValue)
  }
}

