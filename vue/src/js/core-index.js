import * as exports from '@/imports'

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
  store
})

app.use(exports.UEConfig)

// Component Includes
app.component('UeDatatable', UEDatatable)
// app.component('ue-modal-form', UEModalForm)

app.mount('#admin')
