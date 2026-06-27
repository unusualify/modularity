import * as helpers from '../src/js/utils/helpers.js'
import { reduce } from 'lodash-es'

HTMLCanvasElement.prototype.getContext = vi.fn()

// CSRF meta for Filepond and other components that read csrf-token
const csrfMeta = document.createElement('meta')
csrfMeta.setAttribute('name', 'csrf-token')
csrfMeta.setAttribute('content', 'test-csrf-token')
document.head.appendChild(csrfMeta)

// Assign helpers to window for components/utils that use window.__*
window.__isset = helpers.isset
window.__preg_quote = helpers.pregQuote ?? ((s) => String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&'))
window.__isObject = helpers.isObject
window.__dot = helpers.dot
window.__isString = helpers.isString
window.__isArray = helpers.isArray
window.__data_get = helpers.dataGet
window.__snakeToHeadline = helpers.snakeToHeadline
window.__log = helpers.log ?? (() => {})
window._ = { reduce }

// ResizeObserver for Vuetify VApp
class ResizeObserverMock {
  observe() {}
  unobserve() {}
  disconnect() {}
}
window.ResizeObserver = ResizeObserverMock

// visualViewport for Vuetify VOverlay/dialog positioning (jsdom does not provide it)
if (!window.visualViewport) {
  window.visualViewport = {
    width: window.innerWidth,
    height: window.innerHeight,
    offsetLeft: 0,
    offsetTop: 0,
    scale: 1,
    addEventListener: () => {},
    removeEventListener: () => {},
  }
}

// window.$ for Sidebar onMounted (querySelector fallback)
window.$ = window.$ || ((sel) => document.querySelectorAll(sel))

// Layout/sidebar tests need MODULAROUS.STORE.config (config module reads at load time)
const APP_NAME = import.meta.env?.VUE_APP_NAME || 'MODULAROUS'
if (!window[APP_NAME]) {
  window[APP_NAME] = { STORE: { config: {} } }
}
// Locale utils read window[VUE_APP_NAME].LOCALE - ensure it exists
if (!window[APP_NAME].LOCALE) {
  window[APP_NAME].LOCALE = 'en-US'
}
if (!window[APP_NAME].STORE?.config || Object.keys(window[APP_NAME].STORE.config).length === 0) {
  window[APP_NAME].STORE.config = {
    sidebarOptions: { expandHover: 'mini', rail: false, width: 264, railWidth: 56 },
    uiPreferences: {},
    profileMenu: [],
  }
}
