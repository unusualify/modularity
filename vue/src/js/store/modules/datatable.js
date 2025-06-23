import api from '@/store/api/datatable'
import { DATATABLE, ALERT } from '@/store/mutations'
import ACTIONS from '@/store/actions'

import { setStorage } from '@/utils/localeStorage'
import { redirector } from '@/utils/response'
import { isArray, isEmpty } from 'lodash-es'

const state = {
  advancedFilters: window[import.meta.env.VUE_APP_NAME].STORE.datatable.advancedFilters || [],
  customModal: _.isEmpty(window[import.meta.env.VUE_APP_NAME].STORE.datatable.customModal )?  false : window[import.meta.env.VUE_APP_NAME].STORE.datatable.customModal,
}

// getters
const getters = {

}

const mutations = {

}

const activeOption = (option, key) => {
  let exist = true
  let value

  if (key.match(/sortBy|sortDesc/)) {
    if (option.length > 0) { value = option } else { exist = false }
  } else if (key.match(/page|itemsPerPage|replaceUrl/)) {
    value = option
  } else {
    exist = false
  }

  return {
    active: exist,
    value
  }
}

const actions = {
  [ACTIONS.GET_DATATABLE] ({ commit, state, getters }, { payload = {}, callback = null, errorCallback = null, endpoint = null } = {}) {
    // if (!state.loading) {
    const keys = Object.keys(payload)
    let _changed = keys.length === 0

    keys.every((key) => {
      if (__isset(state[key])) {
        if (__isObject(state[key]) && __isObject(payload[key])) { _changed = !(Object.equals(payload[key], state[key])) } else if (Array.isArray(payload[key]) && Array.isArray(state[key])) { _changed = !(Array.equals(payload[key], state[key])) } else { _changed = (state[key] !== payload[key]) }
      }
      return !_changed
    })

    if (keys.includes('options')) _changed = true

    if (_changed) {
      commit(DATATABLE.UPDATE_DATATABLE_LOADING, true)
      const parameters = {
        ...(Object.keys(state.options).reduce(function (filtered, key) {
          const { active, value } = activeOption(
            __isset(payload.options?.[key]) ? payload?.options[key] : state?.options[key],
            key
          )
          if (active) { filtered[key] = value }

          return filtered
        }, {})),
        ...(state.search !== '' ? { search: state.search } : {}),
        ...( { filter : Object.fromEntries(
          Object.entries(state.filter).reduce((result ,[key, value]) => {

            if(key === 'status' && value === 'all') return result
            if(isEmpty(value)) return result

            result.push([key, value])
            return result
          }, [])
        ) }),
        // ...(state.filter.status !== 'all' ? { filter: state.filter } : {})

      }

      const url = endpoint ?? window[import.meta.env.VUE_APP_NAME].ENDPOINTS.index

      api.get(url,parameters, function (resp) {
        const tableData = payload.infiniteScroll ? state.data.concat(resp.resource.data) : resp.resource.data

        commit(DATATABLE.UPDATE_DATATABLE_DATA, tableData)
        commit(DATATABLE.UPDATE_DATATABLE_TOTAL, resp.resource.total)
        commit(DATATABLE.UPDATE_DATATABLE_NAV, resp.mainFilters)
        commit(DATATABLE.UPDATE_DATATABLE_LOADING, false)

        if (__isset(payload.options)) { commit(DATATABLE.UPDATE_DATATABLE_OPTIONS, payload.options) }
        if (__isset(payload.search)) { commit(DATATABLE.UPDATE_DATATABLE_SEARCH, payload.search) }

        if (callback && typeof callback === 'function') callback(resp)
      }, function (error) {
        commit(DATATABLE.UPDATE_DATATABLE_LOADING, false)
        if (errorCallback && typeof errorCallback === 'function') errorCallback(error)
      })
    }
    // }
  },
}

export default {
  state,
  getters,
  actions,
  mutations
}
