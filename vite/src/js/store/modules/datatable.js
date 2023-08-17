import api from '@/store/api/datatable'
import { DATATABLE, ALERT } from '@/store/mutations'
import ACTIONS from '@/store/actions'

import { setStorage } from '@/utils/localeStorage'

/* NESTED functions */
const getObject = (container, id, callback) => {
    container.forEach((item) => {
      if (item.id === id) callback(item)
      if (item.children) getObject(item.children, id, callback)
    })
}

const deepRemoveFromObj = (items, keys = ['id', 'children'], deep = 'children') => {
    const deepItems = JSON.parse(JSON.stringify(items))
    deepItems.forEach((obj) => {
      for (const prop in obj) {
        if (!keys.includes(prop)) {
          delete obj[prop]
        }

        if (prop === deep) {
          obj[prop] = deepRemoveFromObj(obj[prop])
        }
      }
    })
    return deepItems
}



const state = {
    baseUrl: window[process.env.JS_APP_NAME].STORE.datatable.baseUrl || '',
    name: window[process.env.JS_APP_NAME].STORE.datatable.name,
    headers: window[process.env.JS_APP_NAME].STORE.datatable.headers,
    // inputs: window[process.env.JS_APP_NAME].STORE.datatable.inputs,
    search: window[process.env.JS_APP_NAME].STORE.datatable.searchText,
    options: window[process.env.JS_APP_NAME].STORE.datatable.options,
    actions: window[process.env.JS_APP_NAME].STORE.datatable.actions,
    actionsType: window[process.env.JS_APP_NAME].STORE.datatable.actionsType,

    data: window[process.env.JS_APP_NAME].STORE.datatable.data || [],
    total: parseInt( window[process.env.JS_APP_NAME].STORE.datatable.total ),

    filter: window[process.env.JS_APP_NAME].STORE.datatable.filter || {},
    mainFilters: window[process.env.JS_APP_NAME].STORE.datatable.mainFilters || [],

    bulk: [],
    // localStorageKey: window[process.env.JS_APP_NAME].STORE.datatable.localStorageKey || window.location.pathname,
    loading: false,
    updateTracker: 0,

    // columns: window[process.env.JS_APP_NAME].STORE.datatable.columns || [],
    // dialog: false

}

// getters
const getters = {
    // defaultItem: state => {
    //   return state.inputs.reduce( (a,c) => (a[c.name] = c.default, a), {})
    // },
    totalElements: state => {
      return state.total
    },
    formatterColumns: state => {
      return state.headers.filter((h) => h.hasOwnProperty('formatter') && !!h.formatter )
    },
    editableColumns: state => {
      return state.headers.filter((h) => ( h.hasOwnProperty('isColumnEditable') && h.isColumnEditable ))
    },

    rowEditables: state => {
      return state.headers.filter((h) => ( h.hasOwnProperty('isRowEditable') && h.isRowEditable ))
    },
    rowActions: state => {
      return state.actions
    },
    rowActionsType: state => {
      return state.actionsType
    },
    mainFilters: state => {
      return state.mainFilters
    }

}

const mutations = {
    [DATATABLE.UPDATE_DATATABLE_OPTIONS] (state, options) {
        state.options = options
    },
    [DATATABLE.UPDATE_DATATABLE_SEARCH] (state, search) {
        state.search = search
    },
    [DATATABLE.UPDATE_DATATABLE_TOTAL] (state, total) {
        state.total = total
    },
    [DATATABLE.UPDATE_DATATABLE_DATA] (state, data) {
        // Each time the data is changing, we reset the bulk ids
        state.bulk = []

        state.data = data;
    },
    [DATATABLE.SET_DATATABLE_DIALOG] (state, val) {
        state.dialog = val;
    },


    [DATATABLE.UPDATE_DATATABLE_BULK] (state, id) {
      if (state.bulk.indexOf(id) > -1) {
        state.bulk = state.bulk.filter(function (item) {
          return item !== id
        })
      } else {
        state.bulk.push(id)
      }
    },
    [DATATABLE.REPLACE_DATATABLE_BULK] (state, ids) {
      state.bulk = ids
    },
    [DATATABLE.ADD_DATATABLE_COLUMN] (state, column) {
      state.columns.splice(column.index, 0, column.data)
    },
    [DATATABLE.REMOVE_DATATABLE_COLUMN] (state, columnName) {
      state.columns.forEach(function (column, index) {
        if (column.name === columnName) state.columns.splice(index, 1)
      })
    },
    [DATATABLE.UPDATE_DATATABLE_FILTER] (state, filter) {
      state.filter = Object.assign({}, state.filter, filter)
    },
    [DATATABLE.CLEAR_DATATABLE_FILTER] (state) {
      state.filter = Object.assign({}, {
        search: '',
        status: state.filter.status
      })
    },
    [DATATABLE.UPDATE_DATATABLE_FILTER_STATUS] (state, slug) {
      state.filter.status = slug
    },
    [DATATABLE.UPDATE_DATATABLE_OFFSET] (state, offsetNumber) {
      state.offset = offsetNumber
      setStorage(state.localStorageKey + '_page-offset', state.offset)
    },
    [DATATABLE.UPDATE_DATATABLE_PAGE] (state, pageNumber) {
    //   state.page = pageNumber
      // state.options.page = pageNumber
    },
    [DATATABLE.UPDATE_DATATABLE_MAXPAGE] (state, maxPage) {
      if (state.page > maxPage) state.page = maxPage
      state.maxPage = maxPage
    },
    [DATATABLE.UPDATE_DATATABLE_VISIBLITY] (state, columnNames) {
      setStorage(state.localStorageKey + '_columns-visible', JSON.stringify(columnNames))
      state.columns.forEach(function (column) {
        for (let i = 0; i < columnNames.length; i++) {
          if (columnNames[i] === column.name) {
            column.visible = true

            break
          }

          column.visible = false
        }
      })
    },
    [DATATABLE.UPDATE_DATATABLE_SORT] (state, column) {
      const defaultSortDirection = 'asc'

      if (state.sortKey === column.name) {
        state.sortDir = state.sortDir === defaultSortDirection ? 'desc' : defaultSortDirection
      } else {
        state.sortDir = defaultSortDirection
      }

      state.sortKey = column.name
    },
    [DATATABLE.UPDATE_DATATABLE_NAV] (state, newFilters) {
      newFilters.forEach(function (newItem) {
        state.mainFilters.forEach(function (filterItem) {
          if (filterItem.name === newItem.name) filterItem.number = newItem.number
        })
      })
    },
    [DATATABLE.PUBLISH_DATATABLE] (state, data) {
      const id = data.id
      const value = data.value

      function updateState (index) {
        if (index >= 0) {
          if (value === 'toggle') state.data[index].published = !state.data[index].published
          else state.data[index].published = value
        }
      }

      function getIndex (id) {
        return state.data.findIndex(function (item, index) { return (item.id === id) })
      }

      // bulk
      if (Array.isArray(id)) {
        id.forEach(function (itemId) {
          const index = getIndex(itemId)
          updateState(index)
        })

        state.bulk = []
      } else {
        const index = getIndex(id)
        updateState(index)
      }
    },
    [DATATABLE.FEATURE_DATATABLE] (state, data) {
      const id = data.id
      const value = data.value

      function updateState (index) {
        if (index >= 0) {
          if (value === 'toggle') state.data[index].featured = !state.data[index].featured
          else state.data[index].featured = value
        }
      }

      function getIndex (id) {
        return state.data.findIndex(function (item, index) { return (item.id === id) })
      }

      // bulk
      if (Array.isArray(id)) {
        id.forEach(function (itemId) {
          const index = getIndex(itemId)
          updateState(index)
        })

        state.bulk = []
      } else {
        const index = getIndex(id)
        updateState(index)
      }
    },
    [DATATABLE.UPDATE_DATATABLE_LOADING] (state, loading) {
      state.loading = !state.loading
    },
    [DATATABLE.UPDATE_DATATABLE_NESTED] (state, data) {
      getObject(state.data, data.parentId, (item) => {
        item.children = data.val
      })
    },
    [DATATABLE.UPDATE_DATATABLE_TRACKER] (state, newTracker) {
      state.updateTracker = newTracker ? state.updateTracker + 1 : 0
    }
}

const activeOption = (option, key) => {
  let exist = true;
  let value;

  if(key.match(/sortBy|sortDesc/)){
    if(option.length > 0)
      value = option[0];
    else
      exist = false;
  }else if(key.match(/page|itemsPerPage/)){
    value = option;
  }else{
    exist = false;
  }

  return {
    active: exist,
    value: value
  }
}

const actions = {
    [ACTIONS.DELETE_ITEM] ({ commit, state, dispatch }, {id=null, callback=null, errorCallback=null} = {}) {
      api.delete(id, function (resp) {
        commit(ALERT.SET_ALERT, { message: resp.data.message, variant: resp.data.variant })
        if(resp.data.variant == 'success'){
          dispatch(ACTIONS.GET_DATATABLE)
          callback(resp.data)
        }
        else
          errorCallback(resp.data)

      })
    },
    [ACTIONS.GET_DATATABLE] ({ commit, state, getters }, { payload = {}, callback = null, errorCallback = null } = {}) {
      // if (!state.loading) {

        let keys = Object.keys(payload);
        let _changed = keys.length == 0 ? true : false  ;

        keys.every((key) => {
          if( __isset(state[key]) ){
            if( __isObject(state[key]) && __isObject(payload[key]) )
              _changed = ! (Object.equals(payload[key], state[key]) );
            else if( Array.isArray(payload[key]) && Array.isArray(state[key]) )
              _changed = !( Array.equals(payload[key], state[key]) );
            else
              _changed = ( state[key] !== payload[key]);
          }
          return !_changed;
        })

        if( _changed ){
          commit(DATATABLE.UPDATE_DATATABLE_LOADING, true)
          const parameters = {
              ...( Object.keys(state.options).reduce(function(filtered, key) {
                let {active, value} = activeOption(
                  __isset( payload.options ) ? payload.options[key] : state.options[key],
                  key
                )

                if(active)
                  filtered[key] = value;

                return filtered;
              }, {}) ),
              ...( state.search != '' ? {search: state.search} : {}),
          };

          // __log(parameters)
          const params = {
            // columns: getters.visibleColumnsNames,
            // filter: state.filter
            page: __isset(payload.options) ? payload.options.page : state.options.page,
            itemsPerPage: __isset(payload.options) ? payload.options.itemsPerPage : state.options.itemsPerPage,
            ...( state.search != '' ? {search: state.search} : {}),
          }
          api.get(parameters, function (resp) {
              // __log('ACTIONS.GET_DATATABLE')
              commit(DATATABLE.UPDATE_DATATABLE_DATA, resp.data)
              commit(DATATABLE.UPDATE_DATATABLE_TOTAL, resp.total)
              commit(DATATABLE.UPDATE_DATATABLE_LOADING, false)

              if( __isset(payload.options) )
                commit(DATATABLE.UPDATE_DATATABLE_OPTIONS, payload.options);
              if( __isset(payload.search) )
                commit(DATATABLE.UPDATE_DATATABLE_SEARCH, payload.search);

          })
        }
      // }
    },


    [ACTIONS.SET_DATATABLE_NESTED] ({ commit, state, dispatch }) {
      // Get all ids and children ids if any
      const ids = deepRemoveFromObj(state.data)
      api.reorder(ids, function (resp) {
        commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
      })
    },
    [ACTIONS.SET_DATATABLE] ({ commit, state, dispatch }) {
      const ids = state.data.map((row) => row.id)

      api.reorder(ids, function (resp) {
        commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
      })
    },
    [ACTIONS.TOGGLE_PUBLISH] ({ commit, state, dispatch }, row) {
      api.togglePublished(row, function (resp) {
        commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
        dispatch(ACTIONS.GET_DATATABLE)
      }, function (errorResp) {
        commit(NOTIFICATION.SET_NOTIF, { message: errorResp.data.error.message, variant: 'error' })
      })
    },
    // [ACTIONS.DELETE_ROW] ({ commit, state, dispatch }, row) {
    //   api.delete(row, function (resp) {
    //     commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
    //     dispatch(ACTIONS.GET_DATATABLE)
    //   })
    // },
    [ACTIONS.DUPLICATE_ROW] ({ commit, state, dispatch }, row) {
      api.duplicate(row, function (resp) {
        commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
        if (resp.data.hasOwnProperty('redirect')) {
          window.location.replace(resp.data.redirect)
        }
      })
    },
    [ACTIONS.RESTORE_ROW] ({ commit, state, dispatch }, row) {
      api.restore(row, function (resp) {
        commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
        dispatch(ACTIONS.GET_DATATABLE)
      })
    },
    [ACTIONS.DESTROY_ROW] ({ commit, state, dispatch }, row) {
      api.destroy(row, function (resp) {
        commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
        dispatch(ACTIONS.GET_DATATABLE)
      })
    },
    [ACTIONS.BULK_PUBLISH] ({ commit, state, dispatch }, payload) {
      api.bulkPublish(
        {
          ids: state.bulk.join(),
          toPublish: payload.toPublish
        },
        function (resp) {
          commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
          dispatch(ACTIONS.GET_DATATABLE)
        }
      )
    },
    [ACTIONS.TOGGLE_FEATURE] ({ commit, state }, row) {
      api.toggleFeatured(row, resp => {
        commit(DATATABLE.FEATURE_DATATABLE, {
          id: row.id,
          value: 'toggle'
        })
        commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
      })
    },
    [ACTIONS.BULK_FEATURE] ({ commit, state }, payload) {
      api.bulkFeature(
        {
          ids: state.bulk.join(),
          toFeature: payload.toFeature
        },
        function (resp) {
          commit(DATATABLE.FEATURE_DATATABLE, {
            id: state.bulk,
            value: true
          })
          commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
        }
      )
    },
    [ACTIONS.BULK_DELETE] ({ commit, state, dispatch }) {
      api.bulkDelete(state.bulk.join(), function (resp) {
        commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
        dispatch(ACTIONS.GET_DATATABLE)
      })
    },
    [ACTIONS.BULK_RESTORE] ({ commit, state, dispatch }) {
      api.bulkRestore(state.bulk.join(), function (resp) {
        commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
        dispatch(ACTIONS.GET_DATATABLE)
      })
    },
    [ACTIONS.BULK_DESTROY] ({ commit, state, dispatch }) {
      api.bulkDestroy(state.bulk.join(), function (resp) {
        commit(NOTIFICATION.SET_NOTIF, { message: resp.data.message, variant: resp.data.variant })
        dispatch(ACTIONS.GET_DATATABLE)
      })
    }
}

export default {
    state,
    getters,
    actions,
    mutations
}
