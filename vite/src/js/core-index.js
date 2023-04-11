// setup
import core from '@/core'

// styles
import 'styles/datatable.scss'

// Plugins
import UEConfig from '@/plugins/UEConfig'

// Store
import store from '@/store'

// Component Imports
import UEDatatable from '__components/Datatable.vue';
import UEModalForm from '__components/modals/ModalForm.vue';
import UEModalDialog from '__components/modals/ModalDialog.vue';
import UEModalTest from '__components/modals/ModalTest.vue';

// Store modules
import datatable from '@/store/modules/datatable'
import form from '@/store/modules/form'

import { RootMixin } from './mixins'

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Stores
store.registerModule('datatable', datatable)
store.registerModule('form', form)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = createApp({
   store,
   mixins: [RootMixin],
});

// Component Includes
app.component('ue-datatable', UEDatatable);
app.component('ue-modal-form', UEModalForm);
app.component('ue-modal-dialog', UEModalDialog);

UEConfig(app)

app.mount('#admin')

window[process.env.JS_APP_NAME].vm = window.vm = app;

// core(app)

// document.addEventListener('DOMContentLoaded', core(app))
