import axios from 'axios'
import { replaceState } from '@/utils/pushState.js'
// import { globalError } from '@/utils/errors'

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
    const url = window[process.env.VUE_APP_NAME].ENDPOINTS.index

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

          callback(resp.data.resource)
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
    const url = window[process.env.VUE_APP_NAME].ENDPOINTS.delete.replace(':id', id)
    // var url = window[process.env.VUE_APP_NAME].ENDPOINTS.index.replace(':id', item.id);

    axios.delete(url).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Delete request error.',
        value: resp
      }
      //   globalError(component, error)
    })
  }

}
