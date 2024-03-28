/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
*/
import * as exports from '@/imports'

// styles

// Store modules
import form from '@/store/modules/form'

const store = exports.store

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

app.mount('#admin')
