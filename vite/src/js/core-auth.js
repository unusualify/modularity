import * as exports from '@/imports'

// import 'styles/datatable.scss'
// import `styles/themes/${import.meta.env.VUE_APP_THEME}/pages/index.scss`

// Component Imports

// Store modules
import form from '@/store/modules/form'
// styles

const store = exports.store

// Stores
store.registerModule('form', form)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = exports.createApp({
  store,
  mixins: [exports.RootMixin]
})

app.use(exports.UEConfig)

// Component Includes

app.mount('#auth')

// document.addEventListener('DOMContentLoaded', core(app))
