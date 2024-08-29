// import 'bootstrap';

import axios from 'axios'
import jquery from 'jquery'
import lodash, { snakeCase } from 'lodash-es'
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */


/*
 |--------------------------------------------------------------------------
 | Global Helper Definitions
 |--------------------------------------------------------------------------
 |
 | Global js functions are defined here.
 | Functions will be defined with '__' prefix as a unusual enterprise's standard
 |
 */
 function assignBOMHElpers(){
  window.__log = console.log

  window.__isString = (obj) => {
    return (Object.prototype.toString.call(obj) === '[object String]')
  }

  window.__isNumber = (obj) => {
    return !isNaN(obj)
  }

  window.__isObject = (obj) => {
    return Object.prototype.toString.call(obj) === '[object Object]'
  }

  window.__isset = (...args) => {
    // !No description available for isset. @php.js developers: Please update the function summary text file.
    //
    // version: 1103.1210
    // discuss at: http://phpjs.org/functions/isset
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: FremyCompany
    // +   improved by: Onno Marsman
    // +   improved by: Rafał Kukawski
    // *     example 1: isset( undefined, true);
    // *     returns 1: false
    // *     example 2: isset( 'Kevin van Zonneveld' );
    // *     returns 2: true
    const a = args
    const l = a.length
    let i = 0
    let undef

    if (l === 0) {
      throw new Error('Empty isset')
    }

    while (i !== l) {
      if (a[i] === undef || a[i] === null) {
        return false
      }
      i++
    }
    return true
  }

  window.__getMethods = (obj) => Object.getOwnPropertyNames(obj).filter(item => typeof obj[item] === 'function')

  window.__globalizeMethods = (input) => {
    if (Array.isArray(input)) {
      input.forEach(function (obj) {
        __getMethods(obj).forEach(function (v) {
          window[v] = obj[v]
        })
      })
    } else if (__isObject(input)) {
      __getMethods(obj).forEach(function (v) {
        window[v] = obj[v]
      })
    }
  }

  window.__responseHandler = (response) => {
    if (__isset(response.data.errors)) {
      return {
        status: false,
        text: errorHandler(response.data.errors)
      }
    } else {
      return {
        status: true,
        data: response.data.data
      }
    }
  }

  /**
   * @param  {} errors
   * !danger, does not work
   * TODO make it work
   */
  window.__errorHandler = (errors) => {
    let rows = ''
    Object.keys(errors).forEach((key, i) => {
      rows += `
              <tr>
                  <td> <strong> ${capitalCase(key)} </strong> </td>
                  <td>
                      ${errors[key].join('</br>')}
                  </td>
              </tr>
          `
    })

    const html = `
      <table> \
         <tbody> \
          ${rows} \
         </tbody> \
      </table>`

    return html
  }

  window.__functionDefinition = (func) => {
    return Function.prototype.toString.call(func)
  }

  window.__convertArrayOrObject = (el, key = null) => {
    if (__isObject(el)) {
      const object = {}
      Object.keys(el).forEach((key) => {
        object[key] = __convertArrayOrObject(el[key], key)
      })
      return object
    } else if (Array.isArray(el)) {
      const array = []
      el.forEach((item) => {
        array.push(__convertArrayOrObject(item))
      })
      return array
    } else if (typeof el === 'function') {
      let string = __functionDefinition(el)

      if (key) {
        string = string.replace(key + '(', 'function (')
      }
      return string
    } else if (el instanceof RegExp) {
      return el.toString()
    } else {
      return el
    }
  }

  window.__printDefinition = (variable) => {
    // return  __convertArrayOrObject(variable);
    return JSON.stringify(__convertArrayOrObject(variable))
  }

  window.__shorten = (string, maxLength = 40) => {
    // return  __convertArrayOrObject(variable);
    return string.length > maxLength ? string.substring(0, maxLength) + '...' : string
  }

  window.__preg_quote = (str, delimeter = '') => {
    return (str + '').replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + '-]', 'g'), '\\$&');
  }

  window.__dot = (obj, prefix = '') => {
    return Object.keys(obj).reduce((acc, key) => {
      const newKey = prefix ? `${prefix}.${key}` : key;

      if (typeof obj[key] === 'object' && obj[key] !== null && !Array.isArray(obj[key])) {
        Object.assign(acc, __dot(obj[key], newKey));
      } else {
        acc[newKey] = obj[key];
      }

      return acc;
    }, {});
  }
  window.__wildcard_change = (string, val, search_key = 'id') => {
    let values = Array.isArray(val) ? val.join(',') : val
    // __log('wildcard_change', string, val)
    return string.replace(/^([\w\.]+)(\*)([\w\.\*]+)$/, '$1*' + `${search_key}=${values}` + '$3')
  }

  window.__data_get = (data, path, defaultValue) => {
    if (!data || typeof path !== 'string') {
      return defaultValue;
    }

    const parts = tokenizePath(path);
    let current = data;

    for (const index in parts) {
      let part = parts[index]
      if (current === undefined || current === null) {
        return defaultValue;
      }

      if (typeof current === 'object') {
        let _tmp

        let matches = part ? part.match(/^\*(.*)/) : false

        if (matches) {
          // if part is *id=1,2,3, current will be filtered wrt values splitted ',' comma
          let filterMatches
          if(matches[1] && (filterMatches = matches[1].match(/^(\w+)\=([\w\d\,]+)/))){
            let filterKey = filterMatches[1]
            let filterValues = filterMatches[2].split(',')
            current = lodash.filter(current, (el) => filterValues.includes( lodash.isNumber(el[filterKey]) ? el[filterKey].toString() : el[filterKey] ))
            // __log(filterValues, current)
          }

          // Handle wildcard (modified for array case):
          if (Array.isArray(current)) {
            let _index = parseInt(index)
            // return current.map(item => window.__data_get(item, parts.slice(1).join('.'), defaultValue)); // Recursively get desired property from each object
            return current.map(item => __data_get(item, parts.slice(_index+1).join('.'), defaultValue)); // Recursively get desired property from each object
          } else {
            return Object.assign({}, current); // Shallow copy for single object
          }
        } else if (part.indexOf('[') !== -1) {
          // Handle brackets for array access
          const key = extractKeyFromBracket(part);
          if (key !== undefined) {
            current = current[key];
          } else {
            return defaultValue; // Invalid bracket syntax
          }
        } else {
          _tmp = current[part];
        }

        if(!_tmp){
          current = lodash.get(current, parts.slice(index).join('.'))
          if(current)
            break;
        }else{
          current = _tmp
        }

      } else {
        return defaultValue; // Path points to a non-object
      }
    }

    return current;
  }

  // TODO: inputs/TabGroup.vue
  window.__cast_value_match = (value, ownerItem ) => {
    let matches

    let returnValue = value

    if(__isString(value) && (matches = value.match(/\$([\w\.\*]+)/))){
      let notation = matches[1]
      let quoted = __preg_quote(matches[0])
      let parts = notation.split('.')
      // __log(parts)

      let newParts = []
      for(const j in parts){
        let part = parts[j]
        if(part === '*'){
          // let searchedValue =
          let _id = ownerItem.id
          // parts[j] = `*id=${_id}`
        }else{
          newParts.push(part)
        }
      }

      notation = newParts.join('.')

      let newValue = __data_get(ownerItem, notation)

      if(newValue){
        let _value
        if(Array.isArray(newValue) && newValue.length > 0){
          _value = newValue.join(',')
        }else if(__isString(newValue)){
          _value = newValue
        }

        if(_value){
          let remainingQuote = '\\w\\s' + __preg_quote('çşıİğüö.,;?')
          let pattern = new RegExp( String.raw`^([${remainingQuote}]+)?(${quoted})([${remainingQuote}]+)?$`)

          if(value.match(pattern)){
            returnValue = value.replace(pattern, '$1' + _value + '$3')
          }else{
            __log(
              'Not matched sentence',
              remainingQuote,
              pattern,
              value,
              value.match(pattern)
            )
          }
        }
      }
    }

    return returnValue
  },

  window.__snakeToHeadline = (str) => {
    return str
      .split('_')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ');
  }
  window.__removeQueryParams = (paramsToRemove) => {

    // Get the current URL
    const currentUrl = new URL(window.location.href);
    // Get the search params
    const searchParams = currentUrl.searchParams;
    // Remove specified parameters
    paramsToRemove.forEach(param => {
      searchParams.delete(param);
    });
    // Construct the new URL
    const newUrl = currentUrl.origin + currentUrl.pathname + searchParams.toString();
    console.log(newUrl);
    // Update the URL without refreshing the page
    window.history.pushState({}, '', newUrl);

    // window.history.replaceState({}, '', newUrl);
  }
}

function tokenizePath(path) {
  const tokens = [];
  let currentToken = '';
  let inBracket = false;
  let escaped = false;

  for (let i = 0; i < path.length; i++) {
    const char = path[i];

    if (escaped) {
      currentToken += char;
      escaped = false;
    } else if (char === '\\') {
      escaped = true;
    } else if (char === '[') {
      if (currentToken) {
        tokens.push(currentToken);
        currentToken = '';
      }
      inBracket = true;
      currentToken += char;
    } else if (char === ']') {
      if (!inBracket) {
        throw new Error('Mismatched brackets in path');
      }
      currentToken += char;
      tokens.push(extractBracketContent(currentToken));
      currentToken = '';
      inBracket = false;
    } else if (char === '.' && !inBracket) {
      if (currentToken) {
        tokens.push(currentToken);
        currentToken = '';
      }
    } else {
      currentToken += char;
    }
  }

  if (currentToken) {
    tokens.push(currentToken);
  }

  return tokens;
}

function extractBracketContent(bracketToken) {
  // Remove brackets and trim whitespace
  const content = bracketToken.slice(1, -1).trim();

  // If it's a number, return as a number
  if (/^\d+$/.test(content)) {
    return content;
  }

  // Otherwise, return as a string
  return content;
}

function extractKeyFromBracket(part) {
  const match = part.match(/\[([^\]]*)\]/);
  return match ? match[1] : undefined;
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
          // console.log("Recursing to compare ", this[propName],"with",object2[propName], " both named \""+propName+"\"");
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

  window.axios.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json'
  }

  window.axios.defaults.headers.post = {
    'Content-Type': 'application/json'
  }
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
