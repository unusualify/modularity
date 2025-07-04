// utils/formEventFormatters/formatPrependSchema.js
import formatHelpers from './helpers'
import _ from 'lodash-es'
import { replaceVariablesFromHaystack } from '@/utils/notation'

export default async function formatPrependSchema(args, model, schema, input, index = null, preview = []) {
  const inputNotation = formatHelpers.getInputToFormat(args, model, schema, index )

  if(!inputNotation)
    return

  // const inputPropToFormat = args.shift() //
  // const setterNotation = `${inputNotation}.${inputPropToFormat}`
  const prepend = args.shift() // *.packageLanguages
  const setterSchemaKey = args.shift() // schema.packageLanguages
  const hasOrder = args.shift() == 'true'

  let {handlerName, handlerSchema, handlerValue} = formatHelpers.handlers(input, model, index)

  if(__isObject(handlerValue))
    handlerValue = Object.values(handlerValue)

  if(Array.isArray(handlerValue) && handlerValue.length < 1)
    return

  if(handlerValue){
    if(prepend){
      handlerValue = __data_get(handlerValue, prepend)
      if(prepend.match(/^\*(.*)/)){
        handlerValue = _.reduce(handlerValue, function(acc, array){
          acc = [...(array ?? []), ...acc];

          return [...new Set(acc)]
        }, [])
      }
    }

    let inputToPrepended = _.get(schema, inputNotation)

    if(!inputToPrepended || !inputToPrepended.schema)
        return

    let oldSchema = _.cloneDeep(inputToPrepended.schema);

    let prependerInput =  __data_get(handlerSchema, setterSchemaKey)

    if(prependerInput && __isset(prependerInput.schema)){

      let prependerItems = []
      let prependerItem
      if(Array.isArray(handlerValue)){
        if(!prependerInput.items)
          return

        prependerItems = _.reduce(handlerValue, (acc, id) => {
          acc[id] = _.find(prependerInput.items, (item) => item.id == id)
          return acc
        }, {})
        handlerValue = _.reduce(handlerValue, (acc, id) => {
          acc[id] = {
            label_prefix: __data_get(prependerInput.items, `*id=${id}.${prependerInput.itemTitle}`)?.shift(),
          }
          return acc
        }, {})
      }
      let newSchema = _.cloneDeep({})
      let lastPrependedKeys = _.get(schema, inputNotation + '._prependedKeys', [])

      let relatedKeys = []
      for(const prependKey in prependerInput.schema){ // prependKey _content

        let quotedPattern = __preg_quote(prependKey)
        let pattern = new RegExp( String.raw`^(\d+)(${quotedPattern})`)
        for(const val in handlerValue){
          let draftSchema = _.cloneDeep(prependerInput.schema[prependKey])
          prependerItem = prependerItems[val]

          let pattern = /(\${[\w]+}\$)/
          let _inputName = prependKey.replace(pattern, val)
          relatedKeys.push(_inputName)
          if(!oldSchema[_inputName]){
            newSchema[_inputName] = {
              ...draftSchema,
              name: _inputName,
              // label: draftSchema.label
              //   ? `${handlerValue[val].label_prefix} ${draftSchema.label}`
              //   : __snakeToHeadline(handlerValue.label_prefix + draftSchema.name)
            }

            // newSchema[_inputName] = replacePatternInObject(newSchema[_inputName], /\$(id)\$/g, val)
            newSchema[_inputName] = replaceVariablesFromHaystack(newSchema[_inputName], prependerItem)
          }
        }
      }

      let deletedKeys = lastPrependedKeys.filter(key => !relatedKeys.includes(key))

      // delete oldSchema last prepended keys that are not in newSchema
      deletedKeys.forEach(key => {
        delete oldSchema[key]
      })
      let prependedKeys = relatedKeys.filter(key => !deletedKeys.includes(key))

      if(hasOrder){
        // reorder newSchema by prependedKeys
        prependedKeys = prependedKeys.sort((a, b) => {
          const aId = parseInt(a.match(/^(\d+)/)[1]);
          const bId = parseInt(b.match(/^(\d+)/)[1]);
          return aId - bId;
        })
        newSchema = prependedKeys.reduce((acc, key) => {
          acc[key] = newSchema[key]
          return acc;
        }, {})
      }

      let updatedSchema =  Object.assign(newSchema, oldSchema)

      if(JSON.stringify(inputToPrepended.schema) !== JSON.stringify(updatedSchema)){
        _.set(schema, inputNotation + '.schema', updatedSchema)
        _.set(schema, inputNotation + '._prependedKeys', prependedKeys)
      }

    }
  }
}

