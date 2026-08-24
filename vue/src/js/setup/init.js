// import 'bootstrap';

import axios from 'axios'
import jquery from 'jquery'
import lodash, { snakeCase } from 'lodash-es'
import pluralize from 'pluralize'
import moment from 'moment/dist/moment'
import 'moment/dist/locale/tr'
import 'moment/dist/locale/de'
import 'moment/dist/locale/fr'
import 'moment/dist/locale/nl'

import { useI18n } from 'vue-i18n'

import store from '@/store' // Adjust the import based on your store structure
import { CONFIG } from '@/store/mutations'
import { addParametersToUrl, replaceState } from '@/utils/pushState'

import { handleSuccessResponse, handleErrorResponse } from '@/utils/response'
import { redirectFromUnauthorizedPayload, redirectToLoginFullPage } from '@/utils/authRedirect'

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

/*
 |--------------------------------------------------------------------------
 | Global Helper Definitions (backward compatibility)
 |--------------------------------------------------------------------------
 | Helpers are exported from @/utils/helpers. Prefer: import { isObject } from '@/utils/helpers'
 | window.__* is deprecated but kept for backward compatibility during migration.
 */
import * as helpers from '@/utils/helpers'

function assignBOMHElpers () {
  window.__log = helpers.log
  window.__isString = helpers.isString
  window.__isBoolean = helpers.isBoolean
  window.__isNumber = helpers.isNumber
  window.__isObject = helpers.isObject
  window.__isArray = helpers.isArray
  window.__isset = helpers.isset
  window.__issetReturn = helpers.issetReturn
  window.__getMethods = helpers.getMethods
  window.__globalizeMethods = helpers.globalizeMethods
  window.__functionDefinition = helpers.functionDefinition
  window.__convertArrayOrObject = helpers.convertArrayOrObject
  window.__printDefinition = helpers.printDefinition
  window.__shorten = helpers.shorten
  window.__preg_quote = helpers.pregQuote
  window.__extract = helpers.extract
  window.__dot = helpers.dot
  window.__reverseDot = helpers.reverseDot
  window.__wildcard_change = helpers.wildcardChange
  window.__data_get = helpers.dataGet
  window.__snakeToHeadline = helpers.snakeToHeadline
  window.__headline = helpers.headline
  window.__extractForeignKey = helpers.extractForeignKey
  window.__snakeNameFromForeignKey = helpers.snakeNameFromForeignKey
  window.__nameFromForeignKey = helpers.nameFromForeignKey
  window.__moduleName = helpers.moduleName
  window.__removeQueryParams = helpers.removeQueryParams
  window.__pluralize = helpers.pluralizeStr
  window.__pushQueryParams = helpers.pushQueryParams
  window.__formatCurrencyPrice = helpers.formatCurrencyPrice
  window.__addParametersToUrl = helpers.addParametersToUrl
  window.__removeParametersFromUrl = helpers.removeParametersFromUrl

  window.__moduleTranslationName = (str) => {
    const { t, te } = useI18n({ useScope: 'global' })
    return helpers.moduleTranslationName(str, { t, te })
  }

  window.__responseHandler = (response) => {
    if (helpers.isset(response?.data?.errors)) {
      return { status: false, text: '' }
    }
    return { status: true, data: response?.data?.data }
  }

  window.__errorHandler = (errors) => {
    let rows = ''
    Object.keys(errors || {}).forEach((key) => {
      rows += `<tr><td><strong>${helpers.snakeToHeadline(key)}</strong></td><td>${(errors[key] || []).join('<br>')}</td></tr>`
    })
    return `<table><tbody>${rows}</tbody></table>`
  }

  window.__helpers = helpers
}

function assignObjectHelpers(){
  if (!Object.equals) {
    Object.equals = function (object1, object2) {
      // For the first loop, we only check for types
      for (const k1 in object1) {
        // Check for inherited methods and properties - like .equals itself
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/hasOwnProperty
        // Return false if the return value is different
        if (object1.hasOwnProperty(k1) != object2.hasOwnProperty(k1)) {
          return false
        }
        // Check instance type
        else if (typeof object1[k1] !== typeof object2[k1]) {
          // Different types => not equal
          return false
        }
      }
      // Now a deeper check using other objects property names
      for (const k2 in object2) {
        // We must check instances anyway, there may be a property that only exists in object2
        // I wonder, if remembering the checked values from the first loop would be faster or not
        if (object1.hasOwnProperty(k2) != object2.hasOwnProperty(k2)) {
          return false
        } else if (typeof object1[k2] !== typeof object2[k2]) {
          return false
        }
        // If the property is inherited, do not check any more (it must be equa if both objects inherit it)
        if (!object1.hasOwnProperty(k2)) { continue }

        // Now the detail check and recursion

        // This returns the script back to the array comparing
        /** REQUIRES Array.equals**/
        if (object1[k2] instanceof Array && object2[k2] instanceof Array) {
          // recurse into the nested arrays
          if (!Array.equals(object1[k2], object2[k2])) { return false }
        } else if (object1[k2] instanceof Object && object2[k2] instanceof Object) {
          // recurse into another objects
          if (!Object.equals(object1[k2], object2[k2])) { return false }
        }
        // Normal value comparison for strings and numbers
        else if (object1[k2] != object2[k2]) {
          return false
        }
      }
      // If everything passed, let's say YES
      return true
    }
  }
}

function assignArrayHelpers(){
  if (!Array.equals) {
    Array.equals = function (first, array) {
      // if the other array is a falsy value, return
      if (!array) { return false }

      // compare lengths - can save a lot of time
      if (first.length != array.length) { return false }

      for (let i = 0, l = first.length; i < l; i++) {
        // Check if we have nested arrays
        if (first[i] instanceof Array && array[i] instanceof Array) {
          // recurse into the nested arrays
          if (!first[i].equals(array[i])) { return false }
        } else if (first[i] != array[i]) {
          // Warning - two different object instances will never be equal: {x:20} != {x:20}
          return false
        }
      }
      return true
    }
  }
}


export default function init(){

  assignBOMHElpers()
  assignObjectHelpers()
  assignArrayHelpers()

  window.axios = axios
  window.$ = jquery
  window._ = lodash
  window.$moment = moment

  const commonHeaders = {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
    'Cache-Control': 'no-cache, no-store, must-revalidate',
    Pragma: 'no-cache',
    Expires: '0',
  }

  window.axios.defaults.headers.common = commonHeaders

  axios.defaults.headers.common = commonHeaders


  window.axios.defaults.headers.post = {
    'Content-Type': 'application/json'
  }

  // Set validateStatus on both axios instances
  const validateStatus = (status) => {
    return (status >= 200 && status < 300) || status === 422 || status === 401 || status === 419 || status === 403;
  }

  // Add validateStatus config to accept 422 and 401 as valid
  axios.defaults.validateStatus = validateStatus
  window.axios.defaults.validateStatus = validateStatus

  axios.interceptors.request.use(function (config) {
    store.commit(CONFIG.INCREASE_AXIOS_REQUEST)

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
      if (config.headers && typeof config.headers.set === 'function') {
        config.headers.set('X-CSRF-TOKEN', csrfToken)
      } else {
        config.headers = config.headers || {}
        config.headers['X-CSRF-TOKEN'] = csrfToken
      }
    }

    // Axios 1 applies defaults.headers.post['Content-Type'] = application/json.
    // That prevents PHP from populating $_FILES for FilePond FormData uploads.
    if (typeof FormData !== 'undefined' && config.data instanceof FormData) {
      const headers = config.headers
      if (headers && typeof headers.setContentType === 'function') {
        headers.setContentType(false)
      } else if (headers && typeof headers.delete === 'function') {
        headers.delete('Content-Type')
      } else if (headers) {
        delete headers['Content-Type']
        delete headers['content-type']
      }
    }

    return config;
  }, function (error) {
    store.commit(CONFIG.DECREASE_AXIOS_REQUEST)
    // Do something with request error
    return Promise.reject(error);
  });

  axios.interceptors.response.use(function (response) {
    // Any status code that lie within the range of 2xx cause this function to trigger
    // Do something with response data
    store.commit(CONFIG.DECREASE_AXIOS_REQUEST)

    // 401: server sends JSON { message, login_url?, redirect? }; client redirects when asked.
    if (response.status === 401) {
      if (!redirectFromUnauthorizedPayload(response.data)) {
        redirectToLoginFullPage()
      }
    }

    if (response.status === 419 || response.data.message === 'CSRF token mismatch.') {
      // Optionally: reload or redirect to login
    }

    handleSuccessResponse(response)

    return response;
  }, function (error) {
    handleErrorResponse(error)

    store.commit(CONFIG.DECREASE_AXIOS_REQUEST)
    // Any status codes that falls outside the range of 2xx cause this function to trigger

    if (error.response?.status === 401) {
      if (!redirectFromUnauthorizedPayload(error.response?.data)) {
        redirectToLoginFullPage()
      }
    }
    // Do something with response error
    return Promise.reject(error);
  });
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.MIX_PUSHER_APP_KEY,
//     cluster: import.meta.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
