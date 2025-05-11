const inBrowser = typeof window !== 'undefined'

export const supportsPushState = inBrowser && (function () {
  const ua = window.navigator.userAgent

  if (
    (ua.indexOf('Android 2.') !== -1 || ua.indexOf('Android 4.0') !== -1) &&
    ua.indexOf('Mobile Safari') !== -1 &&
    ua.indexOf('Chrome') === -1 &&
    ua.indexOf('Windows Phone') === -1
  ) {
    return false
  }

  return window.history && 'pushState' in window.history
})()

// use User Timing api (if present) for more accurate key precision
const Time = inBrowser && window.performance && window.performance.now
  ? window.performance
  : Date

let _key = genKey()

function genKey () {
  return Time.now().toFixed(3)
}

export function getStateKey () {
  return _key
}

export function setStateKey (key) {
  _key = key
}

export function pushState (url, replace) {
  // try...catch the pushState call to get around Safari
  // DOM Exception 18 where it limits to 100 pushState calls
  const history = window.history
  try {
    if (replace) {
      history.replaceState({ key: _key }, '', url)
    } else {
      _key = genKey()
      history.pushState({ key: _key }, '', url)
    }
  } catch (e) {
    window.location[replace ? 'replace' : 'assign'](url)
  }
}

export function replaceState (url) {
  pushState(url, true)
}

export function getURLWithoutQuery (url = null) {
  if(!url) return location.protocol + '//' + location.host + location.pathname

  return getOrigin(url) + getPath(url)
}

export function getParameters (url = window.location) {

	// Create a params object
	let params = {};

	new URL(url).searchParams.forEach(function (val, key) {
		if (params[key] !== undefined) {
			if (!Array.isArray(params[key])) {
				params[key] = [params[key]];
			}
			params[key].push(val);
		} else {
			params[key] = val;
		}
	});

	return params;
}

export function serializeParameters(params, prefix) {
  const query = Object.keys(params).map((key) => {
    const value  = params[key];

    if (params.constructor === Array)
      key = `${prefix}[]`;
    else if (params.constructor === Object)
      key = (prefix ? `${prefix}[${key}]` : key);

    if (typeof value === 'object')
      return serializeParameters(value, key);
    else
      return `${key}=${encodeURIComponent(value)}`;
  });

  return [].concat.apply([], query).join('&');
}

export function addParametersToUrl(url, params, prefix) {
  let string = ''
  if(__isString(params)){
    string = params
  }else{
    string = serializeParameters(params, prefix)
  }

  if(string.length > 0)
      string = '?' + string

  return url + string
}

/**
 * Remove the given keys from window.location.search
 * and update the URL via history.replaceState (no reload).
 *
 * @param {string[]} keysToRemove
 */
export function removeQueryKeys(keysToRemove = []) {
  const url = new URL(window.location.href)

  // delete each key
  keysToRemove.forEach(key => url.searchParams.delete(key))

  // build the new URL (pathname + updated search)
  const newUrl = url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : '')

  // replace browser state (no page reload)
  // window.history.replaceState({}, '', newUrl)
  window.history.pushState({}, '', newUrl)
}

export function getOrigin(url) {
  return new URL(url).origin
}

export function getPath(url) {
  return new URL(url).pathname
}

export function removeParameterFromUrl(url, parameter) {
  const urlObj = new URL(url)
  urlObj.searchParams.delete(parameter)
  return urlObj.toString()
}

export function removeParameterFromHistory(parameter) {
  // const urlObj = new URL(window.location.href)

  // urlObj.searchParams.delete(parameter)
  window.history.replaceState({}, '', removeParameterFromUrl(window.location.href, parameter))
}


