import * as exports from '@/imports'

const store = exports.store

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = exports.createApp({
  store
})

app.use(exports.UEConfig)

// const includeClientComponents = require.context('__components/customs', true, /\.vue$/i)
const includeClientComponents = import.meta.glob('__components/customs/*.vue', { eager: true })
app.config.globalProperties.registerComponents(includeClientComponents, 'customs', 'ue-custom')

app.mount('#admin')
