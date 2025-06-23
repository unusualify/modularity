// utils/formEventFormatters/formatFilter.js
import formatHelpers from './helpers'
import _ from 'lodash-es'

import store from '@/store'  // Adjust path to your store file
import { CACHE } from '@/store/mutations'


export default async function formatFilter(args, model, schema, input, index = null, preview = []) {
  const inputNotation = formatHelpers.getInputToFormat(args, model, schema, index )

  if(!inputNotation)
    return

  let {handlerName, handlerModelName, handlerSchema, handlerValue} = formatHelpers.handlers(input, model, index)

  const inputPropToFormat = args.shift() // items
  const inputReadValue = args.shift() // group

  let endpoint
  let filterValues
  let modelValue

  if(!!handlerValue[inputReadValue]){
    modelValue = handlerValue[inputReadValue]
    endpoint = input.filterEndpoint[modelValue]
    filterValues = handlerValue[inputPropToFormat]
  }else{
    modelValue = handlerModelName
    endpoint = input.filterEndpoint
    filterValues = handlerValue
  }

  let setterNotation = `${inputNotation}.${inputPropToFormat}`
  let eagers = _.get(schema, `${inputNotation}.eagers`) ?? [];
  let lazy = _.get(schema, `${inputNotation}.lazy`) ?? [];

  if( !_.get(schema, setterNotation))
    _.set(schema, setterNotation, [])

  let newItems = _.cloneDeep( _.get(schema, setterNotation) );

  for(const i in newItems){
    if(!filterValues.includes(newItems[i].id)){
      newItems.splice(i, 1)
    }
  }

  for(const i in filterValues){
    const id = filterValues[i]

    if( !newItems.find((el) => el.id == id) ) {
      try {
        let params = {}
        if(lazy.length > 0){
          params.lazy = lazy
        }
        if(eagers.length > 0){
          params.eagers = eagers
        }
        let res = await axios.get(endpoint.replace(`{${_.snakeCase(modelValue)}}`, id), {
          params
        })

        newItems.push(res.data)

      } catch (error) {
        // Handle the error here
        console.error('An error occurred:', error);
        // You can also check for specific error types or status codes
        if (error.response) {
          globalError('', {
            message: 'formatFilter error',
            value: error
          })
          // The request was made and the server responded with a status code
          // that falls out of the range of 2xx
          // console.error('Error status:', error.response.status);
          // console.error('Error data:', error.response.data);
        } else if (error.request) {
          // The request was made but no response was received
          console.error('No response received:', error.request);
        } else {
          // Something happened in setting up the request that triggered an Error
          console.error('Error message:', error.message);
        }
      }
    }
  }

  _.set(schema, setterNotation, _.orderBy(newItems, ['id'], ['asc']))
}