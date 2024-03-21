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
  get (params, callback) {
    const url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.index

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

          callback(resp.data)
        }
      }, function (resp) {
        const error = {
          message: 'Get request error.',
          value: resp
        }
        //   globalError(component, error)
      })
  },

  // delete (item, callback) {
  delete (id, callback) {
    const url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.destroy.replace(':id', id)
    // var url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.index.replace(':id', item.id);

    axios.delete(url).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Delete request error.',
        value: resp
      }
      globalError(component, error)
    })
  },
  forceDelete (id, callback) {
    const url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.forceDelete

    axios.put(url, { id }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Force Delete request error.',
        value: resp
      }
      globalError(component, error)
    })
  },
  restore (id, callback) {
    const url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.restore

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
  duplicate (id, callback) {
    const url = window[import.meta.env.VUE_APP_NAME].ENDPOINTS.duplicate.replace(':id', id)

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

  reorder (ids, callback) {
    axios.post(window[import.meta.env.VUE_APP_NAME].CMS_URLS.reorder, { ids }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Reorder request error.',
        value: resp
      }
      globalError(component, error)
    })
  },

  bulkPublish (params, callback) {
    axios.post(window[import.meta.env.VUE_APP_NAME].CMS_URLS.bulkPublish, { ids: params.ids, publish: params.toPublish }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Bulk publish request error.',
        value: resp
      }
      globalError(component, error)
    })
  },

  bulkFeature (params, callback) {
    axios.post(window[import.meta.env.VUE_APP_NAME].CMS_URLS.bulkFeature, { ids: params.ids, feature: params.toFeature }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Bulk feature request error.',
        value: resp
      }
      globalError(component, error)
    })
  },

  bulkDelete (ids, callback) {
    axios.post(window[import.meta.env.VUE_APP_NAME].CMS_URLS.bulkDelete, { ids }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Bulk delete request error.',
        value: resp
      }
      globalError(component, error)
    })
  },

  bulkRestore (ids, callback) {
    axios.post(window[import.meta.env.VUE_APP_NAME].CMS_URLS.bulkRestore, { ids }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Bulk restore request error.',
        value: resp
      }
      globalError(component, error)
    })
  },

  bulkDestroy (ids, callback) {
    axios.post(window[import.meta.env.VUE_APP_NAME].CMS_URLS.bulkForceDelete, { ids }).then(function (resp) {
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
