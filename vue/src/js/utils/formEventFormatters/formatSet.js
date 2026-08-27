// utils/formEventFormatters/formatSet.js
import formatHelpers, { coerceFormEventValue } from './helpers'
import _ from 'lodash-es'

import store from '@/store'  // Adjust path to your store file
import { CACHE } from '@/store/mutations'

import { getModel } from '@/utils/getFormData'

export default async function formatSet(args, model, schema, input, index = null, preview = []) {
  const targetInputNotation = formatHelpers.getInputToFormat(args, model, schema, index)

  if (!targetInputNotation)
    return

  const targetPropName = args.shift()
  const setterNotation = `${targetInputNotation}.${targetPropName}`
  const setPropFormat = args.shift() // items.*.schema

  let { handlerSchema, handlerValue } = formatHelpers.handlers(input, model, index)

  if (!(handlerSchema.accordingToEmptiness ?? false) && Array.isArray(handlerValue) && handlerValue.length < 1)
    return

  if (handlerValue) {
    let newValue = formatHelpers.getNewValue(setPropFormat, handlerValue, handlerSchema)

    if (newValue !== undefined && newValue !== null) {
      let matches = targetPropName.match(/^(modelValue|model)$/g)

      if (matches) { // setting modelValue
        let targetInput = _.get(schema, targetInputNotation)

        if (targetInput == undefined || targetInput == null) {
          return
        }

        let targetInputName = targetInput.name
        let targetForeignKey = __extractForeignKey(targetInputName)
        let targetInputSchema = targetInput.schema ?? null
        let isRepeater = targetInput.type == 'input-repeater'
        let isArrayValue = Array.isArray(newValue)

        let explicitModelNotation = null
        let sourceLocale = 'fallback'

        while (args.length) {
          const token = args.shift()
          if (formatHelpers.isFormEventLocaleSourceToken(token)) {
            sourceLocale = token
          } else if (!explicitModelNotation) {
            explicitModelNotation = token
          }
        }

        const resolvedNotation = formatHelpers.hydrateModelNotation(
          explicitModelNotation ?? targetInputName,
          model,
          schema,
          input,
          index,
        )
        const modelNotation = (resolvedNotation === false || resolvedNotation == null)
          ? targetInputName
          : resolvedNotation

        if (!isArrayValue) {
          newValue = coerceFormEventValue(newValue, {
            sourceTranslated: !!input.translated,
            targetTranslated: !!targetInput.translated,
            currentTargetValue: _.get(model, modelNotation),
            sourceLocale,
          })

          if (_.get(model, modelNotation) === newValue) {
            return
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

          _.set(model, modelNotation, values)
        } else if (!isArrayValue) {
          try {
            _.set(model, modelNotation, newValue)
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
        if (targetPropName.match(/schema/)) {
          _.set(schema, `${targetInputNotation}.default`, getModel(newValue))
        }
      }
    }
  }
}
