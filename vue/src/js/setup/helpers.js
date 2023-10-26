'use strict'

/*
 |--------------------------------------------------------------------------
 | Global Helper Definitions
 |--------------------------------------------------------------------------
 |
 | Global js functions are defined here.
 | Functions will be defined with '__' prefix as a unusual enterprise's standard
 |
 */

global.__log = console.log

global.__isString = (obj) => {
  return (Object.prototype.toString.call(obj) === '[object String]')
}

global.__isObject = (obj) => {
  return Object.prototype.toString.call(obj) === '[object Object]'
}

global.__isset = (...args) => {
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

global.__getMethods = (obj) => Object.getOwnPropertyNames(obj).filter(item => typeof obj[item] === 'function')

global.__globalizeMethods = (input) => {
  if (Array.isArray(input)) {
    input.forEach(function (obj) {
      __getMethods(obj).forEach(function (v) {
        global[v] = obj[v]
      })
    })
  } else if (__isObject(input)) {
    __getMethods(obj).forEach(function (v) {
      global[v] = obj[v]
    })
  }
}

global.__responseHandler = (response) => {
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
global.__errorHandler = (errors) => {
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

global.__functionDefinition = (func) => {
  return Function.prototype.toString.call(func)
}

global.__convertArrayOrObject = (el, key = null) => {
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

global.__printDefinition = (variable) => {
  // return  __convertArrayOrObject(variable);
  return JSON.stringify(__convertArrayOrObject(variable))
}

global.__shorten = (string, maxLength = 40) => {
  // return  __convertArrayOrObject(variable);
  return string.length > maxLength ? string.substring(0, maxLength) + '...' : string
}

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
