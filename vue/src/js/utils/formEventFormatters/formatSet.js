// utils/formEventFormatters/formatSet.js
import formatHelpers from './helpers'
import _ from 'lodash-es'

import store from '@/store'  // Adjust path to your store file
import { CACHE } from '@/store/mutations'

import { getModel } from '@/utils/getFormData'
import { getTranslationLanguages } from '@/utils/locale'

export default async function formatSet(args, model, schema, input, index = null, preview = []) {
  const inputNotation = formatHelpers.getInputToFormat(args, model, schema, index)
  const languages = getTranslationLanguages()

  if (!inputNotation)
    return

  const inputToFormat = inputNotation
  const inputPropToFormat = args.shift()
  const setterNotation = `${inputNotation}.${inputPropToFormat}`
  const setPropFormat = args.shift() // items.*.schema

  let { handlerName, handlerSchema, handlerValue } = formatHelpers.handlers(input, model, index)

  if (Array.isArray(handlerValue) && handlerValue.length < 1)
    return

  if (handlerValue) {
    let dataSet = []
    let newValue = formatHelpers.getNewValue(setPropFormat, handlerValue, handlerSchema)

    if (newValue !== undefined && newValue !== null) {
      let matches = inputPropToFormat.match(/^(modelValue|model)$/g)

      if (matches) { // setting modelValue
        let targetInput = _.get(schema, inputToFormat)
        let targetInputName = targetInput.name
        let targetForeignKey = __extractForeignKey(targetInputName)
        let targetInputSchema = targetInput.schema ?? null
        let isRepeater = targetInput.type == 'input-repeater'
        let isArrayValue = Array.isArray(newValue)

        if (__isset(targetInput['translated']) && targetInput['translated']) {
          let translationParts = notation.split('.')
          let field = translationParts.pop()
          let translationNotation = translationParts.join('.') + '.translations'
          notation.split('.').pop()
          let rawTranslation = __data_get(handlerSchema, translationNotation).shift()

          if (rawTranslation) {
            // TODO: translations do not comes from package_type
            newValue = _.reduce(languages, (acc, language) => {
              let translation = _.find(rawTranslation, (el) => el.locale == language) ?? rawTranslation[0]
              let value = translation[field] ?? null
              acc[language] = translation[field] ?? null
              return acc
            }, {})
          }
        }

        if (isArrayValue && newValue.length > 0) {
          let values = newValue.map((item) => {
            if (targetInputSchema) {
              return _.reduce(targetInputSchema, (acc, value, key) => {
                if (isRepeater && key == targetForeignKey) {
                  acc[targetForeignKey] = item['id'] ?? null
                } else {
                  acc[key] = item[key] ?? null
                }

                return acc
              }, {})
            }
          })

          _.set(model, inputToFormat, values)
        } else if (!isArrayValue) {
          try {
            _.set(model, targetInputName, newValue)
          } catch (e) {
            console.error(e)
          }
        }
      } else {
        let currentValue = _.get(schema, setterNotation)
        let lastValue = store.getters[CACHE.GET_LAST_CACHE](setterNotation) ?? currentValue

        if (_.isString(newValue) && newValue == '#') { // previous value is true
          newValue = lastValue
        } else if (newValue !== currentValue) {
          store.commit(CACHE.PUSH_CACHE, { key: setterNotation, value: currentValue })
        }

        _.set(schema, setterNotation, newValue)
        if (inputPropToFormat.match(/schema/)) {
          _.set(schema, `${inputNotation}.default`, getModel(newValue))
        }
      }
    }
  }
}
