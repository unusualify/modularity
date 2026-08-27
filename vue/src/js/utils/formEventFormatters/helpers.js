// utils/formEventFormatters/helpers.js

import { getActiveContentLocale, getFallbackContentLocale, getTranslationLanguages } from '@/utils/locale'

function isLocaleMap (value) {
  return value !== null && typeof value === 'object' && !Array.isArray(value)
}

export function resolveFormEventSourceLocale (sourceLocale = 'fallback') {
  if (!sourceLocale || sourceLocale === 'fallback') {
    return getFallbackContentLocale()
  }

  if (String(sourceLocale).startsWith('locale.')) {
    return sourceLocale.slice('locale.'.length)
  }

  return sourceLocale
}

export function isFormEventLocaleSourceToken (token) {
  if (!token || typeof token !== 'string') {
    return false
  }

  if (token === 'fallback' || token.startsWith('locale.')) {
    return true
  }

  return getTranslationLanguages().includes(token)
}

function pickScalarFromLocaleMap (value, sourceLocale = 'fallback') {
  const locale = resolveFormEventSourceLocale(sourceLocale)

  if (locale == null || !Object.prototype.hasOwnProperty.call(value, locale)) {
    return ''
  }

  const picked = value[locale]

  return picked == null ? '' : String(picked)
}

/**
 * Align set/update modelValue writes when source and target differ in translation shape.
 *
 * Translated → scalar always uses {@link resolveFormEventSourceLocale} (fallback
 * locale by default, or an explicit `en` / `locale.tr` token). Other locales
 * never fill the target.
 *
 * @param {*} newValue
 * @param {{ sourceTranslated?: boolean, targetTranslated?: boolean, currentTargetValue?: *, sourceLocale?: string }} options
 */
export function coerceFormEventValue (newValue, {
  sourceTranslated = false,
  targetTranslated = false,
  currentTargetValue = undefined,
  sourceLocale = 'fallback',
} = {}) {
  const fromMap = !!sourceTranslated || isLocaleMap(newValue)
  const toMap = !!targetTranslated

  if (fromMap && !toMap) {
    if (!isLocaleMap(newValue)) {
      return newValue ?? ''
    }

    return pickScalarFromLocaleMap(newValue, sourceLocale)
  }

  if (!fromMap && toMap) {
    const languages = getTranslationLanguages()
    const active = getActiveContentLocale() ?? languages[0]
    const base = isLocaleMap(currentTargetValue) ? { ...currentTargetValue } : {}

    for (const lang of languages) {
      if (!(lang in base)) {
        base[lang] = ''
      }
    }

    if (active) {
      base[active] = newValue ?? ''
    }

    return base
  }

  return newValue
}

export default {
  handlers: (input, model, index = null) => {
    const handlerName = input.name
    const handlerModelName = input.name
    const handlerSchemaName = input.key ?? input.name
    const handlerSchema = input // schema[index][handlerName]
    let handlerValue = __data_get(model, !isNaN(parseInt(index)) ? `${index}.${handlerModelName}` : handlerModelName)

    if(!handlerValue && __isset(handlerSchema.parentName)){
      handlerValue = __data_get(model, !isNaN(index) ? `${index}.${handlerSchema.parentName}.${handlerModelName}` : `${handlerSchema.parentName}.${handlerModelName}`)
    }

    return {
      handlerName,
      handlerModelName,
      handlerSchemaName,
      handlerSchema,
      handlerValue
    }
  },

  getInputToFormat: (args, model, schema, input, index) => {

    let inputToFormat = args.shift() // 2.packages || package
    let inputNotationParts = []

    let stages = inputToFormat.split('.')
    let targetFormIndex = parseInt(stages[0])

    if(isNaN(targetFormIndex)){
      targetFormIndex = index
    }else if(!Array.isArray(model)){
      return false
    }

    if(Array.isArray(model)){
      if(!isNaN(targetFormIndex)){
        targetFormIndex -= 1
        stages.shift()
      }
      inputNotationParts.push(`[${targetFormIndex}]`)
    }

    inputToFormat = stages.join('.')
    inputNotationParts.push(inputToFormat)

    return inputNotationParts.join('.')
  },

  hydrateModelNotation: (modelNotation, model, schema, input, index) => {
    let modelNotationParts = []
    let stages = modelNotation.split('.')
    let targetFormIndex = parseInt(stages[0])

    if(isNaN(targetFormIndex)){
      targetFormIndex = index
    } else if(!Array.isArray(model)){
      return false
    }

    if(Array.isArray(model)){
      if(!isNaN(targetFormIndex)){
        targetFormIndex -= 1
        stages.shift()
      }
      modelNotationParts.push(`[${targetFormIndex}]`)
    }

    modelNotationParts.push(stages.join('.'))

    return modelNotationParts.join('.')
  },

  getNewValue: (setPropFormat, handlerValue, handlerSchema) => {
    let newValue

    if(handlerValue){
      if((handlerSchema.accordingToEmptiness ?? false) && ['string', 'number', 'array', 'object'].includes(typeof handlerValue)){
        if( (typeof handlerValue === 'string' && handlerValue.trim() !== '' )
          || (typeof handlerValue === 'number' && handlerValue > 0 )
          || (Array.isArray(handlerValue) && handlerValue.length > 0 )
          || (typeof handlerValue === 'object' && Object.keys(handlerValue).length > 0)){
          handlerValue = 1
        }else{
          handlerValue = 0
        }
      }

      let dataSet = []
      let notation = __wildcard_change(setPropFormat, handlerValue)

      if(notation.match(/^(modelValue|model)$/g)){
        dataSet = handlerValue
      } else {
        dataSet = __data_get(handlerSchema, notation, null)
      }

      if(Array.isArray(dataSet) && (dataSet.length > 0)){
        newValue = dataSet.shift()

      }else if(dataSet !== undefined && dataSet !== null){
        newValue = dataSet
      }
    }

    return newValue
  },

  coerceFormEventValue,
  resolveFormEventSourceLocale,
  isFormEventLocaleSourceToken,
}
