// hooks/useCastAttributes.js

import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { isString, isArray, isObject, isNumber, snakeCase } from 'lodash-es'

export default function useCastAttributes () {
  const { t, te } = useI18n()
  const AttributePattern = /\$([\w\d\.\*\_]+)/
  const EvalPattern = /^\{(.*)\}$/

  const castObjectAttribute = (value, ownerItem) => {
    if(!isString(value))
      return value

    let returnValue = value
    let matches

    if(AttributePattern.test(value)){
      let matches = value.match(AttributePattern)
      let notation = matches[1]
      let quoted = __preg_quote(matches[0])
      let parts = notation.split('.')

      let newParts = []
      for(const j in parts){
        let part = parts[j]
        if(part === '*'){
          let _id = ownerItem.id
          newParts.push(`*id=${_id}`)
        }else{
          newParts.push(part)
        }
      }

      notation = newParts.join('.')

      let newValue = __data_get(ownerItem, notation)

      if(newValue){
        let _value
        if(isArray(newValue) && newValue.length > 0){
          _value = newValue.join(',')
        }else if(isString(newValue)){
          _value = newValue

          let snakeCased = snakeCase(_value)

          if(te(`modules.${snakeCased}`)){
            _value = t(`modules.${snakeCased}`)
          }
        }else if(isNumber(newValue)){
          _value = newValue.toString()
        }

        if(_value){
          let remainingQuote = '\\w\\s' + __preg_quote('çşıİğüö.,;?|:_')
          let pattern = new RegExp( String.raw`^([${remainingQuote}]+)?(${quoted})([${remainingQuote}]+)?$`)

          if(value.match(pattern)){
            returnValue = value.replace(pattern, '$1' + _value + '$3')
          }else{
            __log(
              'Not matched sentence',
              pattern,
              value,
              value.match(pattern)
            )
          }
        }
      }
    }else if(EvalPattern.test(value)){
      let matches = value.match(EvalPattern)
      let evalText = matches[1]

      let evalParts = evalText.split(' ').map((v) => {
        if(AttributePattern.test(v)) {
          let evalPartMatches = v.match(AttributePattern)
          let evalPart = evalPartMatches[1]

          let evalPartCastedValue = __data_get(ownerItem, evalPart, undefined)

          if(evalPartCastedValue !== undefined) {
            return evalPartCastedValue
          }
        }

        return v
      })

      try {
        return eval(evalParts.join(' '))
      } catch (e) {
        console.error('Error in eval', e)
      }
    }

    return returnValue
  }

  const castObjectAttributes = (data, ownerItem) => {
    if(isString(data)){
      return castObjectAttribute(data, ownerItem)
    }

    if(isArray(data)){
      return data.map(item => castObjectAttributes(item, ownerItem))
    }

    if(isObject(data)){
      return Object.keys(data).reduce((acc, key) => {
        acc[key] = castObjectAttributes(data[key], ownerItem)
        return acc
      }, {})
    }

    return data
  }

  return {
    castObjectAttribute,
    castObjectAttributes
  }
}

