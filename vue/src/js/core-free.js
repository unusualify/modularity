import * as exports from '@/imports'

// import 'styles/datatable.scss'
// import `styles/themes/${process.env.VUE_APP_THEME}/pages/index.scss`

// Component Imports
import UEDatatable from '__components/others/Datatable.vue'

// Store modules
import datatable from '@/store/modules/datatable'
import form from '@/store/modules/form'
// styles

const store = exports.store

// Stores
store.registerModule('datatable', datatable)
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
app.component('ue-datatable', UEDatatable)
// app.component('ue-modal-form', UEModalForm)

const includeClientComponents = require.context('__components/customs', true, /\.vue$/i)
app.config.globalProperties.registerComponents(includeClientComponents, 'customs', 'ue-custom')

app.mount('#admin')

// document.addEventListener('DOMContentLoaded', core(app))
