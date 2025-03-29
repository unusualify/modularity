// utils/formEventFormatters/formatPreview.js
import formatHelpers from './helpers'
import _ from 'lodash-es'

export default async function formatPreview(args, model, schema, input, index = null, preview = []) {
  if(Array.isArray(model)){
    let {handlerName, handlerSchema, handlerValue} = formatHelpers.handlers(input, model, index)

    /*
    *handlerValue
      {
        $key1 : {package_id: int, packageLanguages: array}
        $key2 : {package_id: int, packageLanguages: array}
      }
    *handlerSchema
      {
        ...,
        items: [ // packageRegion or packageCountry
          {
            id: int,
            name: string,
            packages: [
              {
                id: int,
                name: string,
                packageLanguages: [
                  {
                    id: int,
                    name: string
                  }
                ]
              }
            ]
          },
          {
            id: == $key1,
            name: 'France',
            packages: [
              {
                id: == handlerValue[$key1].package_id
                name: 'Premium',
                packageLanguages: [
                  {
                    id: == handlerValue[$key1].packageLanguages.*,
                    name: 'English'
                  }
                ]
              }
            ]
          },
          ...
        ]
      }
    */
    // ['United States', 'Wire (English, German, Turkish)'],
    // ['France', 'Premium (English, French)']
    //
    // handlerSchema.items // region or country parent names to get with  handlerValue Object keys wrt id
    // United States => Object.keys() $key =  handlerSchema.items.find($key)
    // Wire|Premium =>  items.find(id:$key).packages.find(id:handlerValue[$key].package_id).*.name
    // English, German, Turkish... =>  items.find(id:$key).packages.find(id:handlerValue[$key].package_id)
    //

    /*
      schema.$group.items.*.name:items
      items.*key.name,items.*key.packages.*.name,items.*key.packages.$package_id.packageLanguages.*.name:*key,*.package_id,*.packageLanguages
    */
    let inputToFormats = args.shift().split(',')
    let targetValueKeys = (args.shift() ?? '').split(',') // *key,package_id,packageLanguages

    let patternValues = {}
    let previewValue = []

    let isMultiple = inputToFormats.length > 1
    let clear = false

    for(const _index in inputToFormats){
      let inputToFormat = inputToFormats[_index]
      let targetValueKey = targetValueKeys[_index] ?? null
      let stages = inputToFormat.split('.')

      stages = _.map(stages, function(stage, i){
        let found
        let convertedStage = stage

        if( (found = stage.match(/\$(\w+)/)) ){
          if(__isset(handlerValue[found[1]])){
            convertedStage = handlerValue[found[1]]
          }
        }

        return convertedStage
      })

      inputToFormat = stages.join('.')

      let targetValue

      let matches = targetValueKey.match(/\*\.?(\w+)/)

      if(targetValueKey == '*key'){
        targetValue = Object.keys(handlerValue).map(function(item){
          return parseInt(item)
        })
      }else if(matches){
        let matches = targetValueKey.match(/\*\.?(\w+)/)

        let key = matches[1]
        targetValue = _.map( handlerValue, function(el, i){
          return el[key]
          return {id: el[key]}
        })
      }else{
        targetValue = targetValueKey ? handlerValue[targetValueKey] : handlerValue
      }
      /**
      __data_get(handlerSchema, 'items.*id=1,5.name'),
      __data_get(handlerSchema, 'items.*id=1,5.packages.*id=1,26.name'),
      __data_get(handlerSchema, 'items.*id=1,5.packageLanguages.*id=1,2,3.name'),
      __data_get(handlerSchema, 'items.*id=1,5.packageLanguages.*id=1,2.name'),
      __data_get(handlerSchema, 'items.*id=1,5.packageLanguages.*id=1.name'),
       *
       */
      if(Array.isArray(targetValue)){

        if(_index == 0){
          clear = targetValue.length < 1

          let parentPattern = inputToFormat.replace(/^([\w\.]+)(\*)([\w\.\*]+)$/, '$1*')
          patternValues[parentPattern] = _.map(targetValue, (val) => inputToFormat.replace(/^([\w\.]+)(\*)([\w\.\*]+)$/, '$1*' + `id=${val}`))

          let data = __data_get(handlerSchema, __wildcard_change(inputToFormat, targetValue))
          _.each(data, (val,i) => clear
            ? _.unset(previewValue, isMultiple ? `[${i}][${_index}]` : `[${i}]`)
            : _.set(previewValue, isMultiple ? `[${i}][${_index}]` : `[${i}]`, val)  )
          // _.set(previewValue, )
        }else{
          let parentPatterns

          Object.keys(patternValues).forEach((prev) => {
            let quotedPattern = __preg_quote(prev)
            let pattern = new RegExp( String.raw`^(${quotedPattern}).([\w\$\.\*]+)`)
            let matches = inputToFormat.match(pattern)

            if(matches){
              parentPatterns = patternValues[prev]
              inputToFormat = matches[2]
              return false
            }
          })

          _.each(targetValue, (val, i) => {
            if(val){

              if(Array.isArray(val) && _.isEmpty(val)) return

              let parentPattern = parentPatterns[i];
              let ids = Array.isArray(val) ? val.join(',') : val

              // let getter = [parentPattern, inputToFormat.replace(/^([\w\.]+)(\*)([\w\.\*]+)$/, '$1*' + `id=${ids}` + '$3')].join('.')
              let getter = [parentPattern, __wildcard_change(inputToFormat, val)].join('.')
              let data = __data_get(handlerSchema, getter).shift()

              if(!data){
                console.warn('formatPreview error', {
                  getter,
                  inputToFormat,
                  data,
                  handlerSchema,

                })
                return
              }
              let formattedData = data[0] ?? ''

              if(_index > 1 && Array.isArray(data)){
                formattedData = `(${data.join(', ')})`
              }

              _.set(previewValue, isMultiple ? `[${i}][${_index}]` : `[${_index}]`, formattedData)
            }
          })
        }
      }else{
        // value = targetValue
      }
    }

    if(!preview[index])
      preview[index] = {}

    if(clear)
      _.unset(preview[index], handlerName)
    else
      preview[index][handlerName] = previewValue

    return
  }
}

