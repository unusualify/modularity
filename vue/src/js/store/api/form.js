import axios from 'axios'
import {
  globalError
} from '@/utils/errors'

const component = 'FORM'

export default {
  get (endpoint, callback, errorCallback) {
    axios.get(endpoint).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }, function (resp) {
      const error = {
        message: 'Get request error.',
        value: resp
      }
      globalError(component, error)
      if (errorCallback && typeof errorCallback === 'function') errorCallback(resp)
    })
  },
  post (endpoint, data, callback, errorCallback) {
    axios.post(endpoint, data, {
      validateStatus: status => (status >= 200 && status < 300) || status === 422
    }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }).catch(function (err) {
      const error = {
        message: 'Post request error.',
        value: err
      }

      globalError(component, error)

      if (errorCallback && typeof errorCallback === 'function') errorCallback(err.response)
    })
  },
  put (endpoint, data, callback, errorCallback) {
    const url = endpoint.replace(':id', data.id)
    axios.put(endpoint, data, {
      validateStatus: status => (status >= 200 && status < 300) || status === 422
    }).then(function (resp) {
      if (callback && typeof callback === 'function') callback(resp)
    }).catch(function (err) {
      const error = {
        message: 'Put request error.',
        value: err
      }
      globalError(component, error)

      if (errorCallback && typeof errorCallback === 'function') errorCallback(err.response)
    })
  }
}
