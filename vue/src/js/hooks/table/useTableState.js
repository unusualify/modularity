// hooks/table/useTableState.js
import { ref, computed, reactive, toRefs } from 'vue'
import { removeQueryKeys } from '@/utils/pushState'
import { pick } from 'lodash'

export default function useTableState(props, context) {

  // Get the current route path as the unique identifier
  const path = window.location.pathname
  const filterStorageKey = `table_filters_${path}`

  const removeKeys = ['id', 'page', 'itemsPerPage', 'sortBy', 'groupBy', 'filter', 'replaceUrl']
  const cachedKeys = ['page', 'itemsPerPage', 'sortBy', 'groupBy', 'filter']

  const getQueryParameters = () => {
    const url = new URL(window.location.href)
    const params = Object.fromEntries(url.searchParams.entries())

      // attempt JSON.parse on each value
    Object.keys(params).forEach((key) => {
      const raw = params[key]
      try {
        params[key] = JSON.parse(raw)
      } catch {
        // not JSON, leave it as a string
        params[key] = raw
      }
    })

    return params
  }

  const getLastParameters = () => {
    const lastParameters = localStorage.getItem(filterStorageKey)
    const queryParameters = getQueryParameters()

    const cachedParameters = lastParameters ? JSON.parse(lastParameters) : {}

    return {
      ...cachedParameters,
      ...queryParameters
    }
  }

  const setLastParameters = (parameters) => {
    const url    = new URL(window.location.href)
    const params = Object.fromEntries(url.searchParams.entries())

    let keysToRemove = []
    Object.keys(parameters).forEach((key) => {
      if(Object.prototype.hasOwnProperty.call(params, key) && removeKeys.includes(key)){
        keysToRemove.push(key)
      }
    })

    if(keysToRemove.length > 0){
      removeQueryKeys(keysToRemove)
    }

    localStorage.setItem(filterStorageKey, JSON.stringify(pick(parameters, cachedKeys)))
  }

  const states = reactive({
    lastParameters: getLastParameters(),
    queryParameters: getQueryParameters()
  })

  return {
    getQueryParameters,
    getLastParameters,
    setLastParameters,
    ...states
  }
}
