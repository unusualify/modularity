import axios from 'axios'
import { replaceState } from '@/utils/pushState.js'
import { globalError } from '@/utils/errors'

const component = 'DATATABLE'

export default {
  /*
    *
    * Main listing request with multiple params
    *
    * sortKey : column used for sorting content
    * sortDir : desc or asc
    * page : current page number
    * offset : number of items per page
    * columns: the set of visible columns
    * filter: the current navigation ("all", "mine", "published", "draft", "trash")
    *
    */
  get (url, params, callback, errorCallback = null) {

    axios.get(url, { params })
      .then(function (resp) {
        if (resp.data.replaceUrl) {
          const url = resp.request.responseURL
          replaceState(url)
        }
        if (callback && typeof callback === 'function') {
          // const data = {
          //     // data: resp.data.data ? resp.data.data : [],
          //     // data: resp.data.tableData ? resp.data.tableData : [],
          //     // nav: resp.data.tableMainFilters ? resp.data.tableMainFilters : [],
          //     // maxPage: (resp.data.maxPage ? resp.data.maxPage : 1)
          // }

          callback(resp.data, resp)
        }
      }, function (resp) {
        const error = {
          message: 'Get request error.',
          value: resp
        }
        if (errorCallback && typeof errorCallback === 'function') {
          errorCallback(error)
        } else {
          // globalError(component, error)
        }
      })
  },

  // delete (item, callback) {
  delete (url, id, callback, errorCallback) {
    // const url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.destroy.replace(':id', id)
    // var url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.index.replace(':id', item.id);
    url = url.replace(':id', id)
    axios.delete(url).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Delete request error.',
        value: resp
      }
      globalError(component, error)

      if (errorCallback && typeof errorCallback === 'function') errorCallback(error)
    })
  },
  forceDelete (url, id, callback, errorCallback) {
    // const url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.forceDelete
    url = url.replace(':id', id)
    axios.put(url, { id }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Force Delete request error.',
        value: resp
      }
      globalError(component, error)

      if (errorCallback && typeof errorCallback === 'function') errorCallback(error)
    })
  },
  restore (url, id, callback) {
    // const url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.restore
    url = url.replace(':id', id)
    axios.put(url, { id }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Restore request error.',
        value: resp
      }
      globalError(component, error)
    })
  },
  duplicate (url, id, callback) {
    // const url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.duplicate.replace(':id', id)
    url = url.replace(':id', id)
    axios.put(url).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Duplicate request error.',
        value: resp
      }
      globalError(component, error)
    })
  },
  reorder (url = null ,ids, callback) {
    const requestUrl = url ?? window[import.meta.env.VUE_APP_NAME].ENDPOINTS.reorder;
    axios.post(requestUrl, {
      ids: ids
    }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Reorder request error.',
        value: resp
      }
      globalError(component, error)
    })
  },

  bulkPublish (url, params, callback) {
    // const url = window[import.meta.env.VUE_APP_NAME].CMS_URLS.bulkPublish
    axios.post(url, { ids: params.ids, publish: params.toPublish }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Bulk publish request error.',
        value: resp
      }
      globalError(component, error)
    })
  },
  bulkFeature (url, params, callback) {
    // const url = window[import.meta.env.VUE_APP_NAME].CMS_URLS.bulkFeature
    axios.post(url, { ids: params.ids, feature: params.toFeature }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Bulk feature request error.',
        value: resp
      }
      globalError(component, error)
    })
  },
  bulkDelete (url, ids, callback) {
    // axios.post(window[import.meta.env.VUE_APP_NAME].ENDPOINTS.bulkDelete, { ids }).then(function (resp) {
    axios.post(url, { ids }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Bulk delete request error.',
        value: resp
      }
      globalError(component, error)
    })
  },
  bulkRestore (url, ids, callback) {
    // axios.post(window[import.meta.env.VUE_APP_NAME].ENDPOINTS.bulkRestore, { ids }).then(function (resp) {
    axios.post(url, { ids }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Bulk restore request error.',
        value: resp
      }
      globalError(component, error)
    })
  },
  bulkDestroy (url, ids, callback) {
    // axios.post(window[import.meta.env.VUE_APP_NAME].ENDPOINTS.bulkForceDelete, { ids }).then(function (resp) {
    axios.post(url, { ids }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Bulk destroy request error.',
        value: resp
      }
      globalError(component, error)
    })
  }
}
