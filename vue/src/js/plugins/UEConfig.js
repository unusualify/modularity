import pluralize from 'pluralize'

import core from '@/core'
// import VFormBase from 'vuetify-form-base';
import VCustomFormBase from '__components/others/CustomFormBase.vue'

// Template Components
import UEModal from '__components/modals/Modal.vue'
import UEModalMedia from '__components/modals/ModalMedia.vue'

// global mixins

// mutations
import { MEDIA_LIBRARY } from '@/store/mutations'

// Add-ons
import vuetify from '@/plugins/vuetify'

// Store
import store from '@/store'

// Config
import i18n, { loadLocaleMessages, setI18nLocale } from '@/config/i18n'

// Directives
import { VueMaskDirective } from 'v-mask'

// Directives
import SvgSprite from '@/directives/svg'
import Column from '@/directives/column'
import FitGrid from '@/directives/fit-grid'
import Scrollable from '@/directives/scrollable'
import commonMethods from '@/utils/commonMethods'
import { ALERT } from '../store/mutations'

// const includeGlobalComponents = require.context('__components', false, /[A-Za-z]*(?<!_)\.vue$/i)


// const includeLabComponents = require.context('__components/labs', false, /[A-Za-z]*(?<!_)\.vue$/i)
// const includeLayouts = require.context('__components/layouts/', false, /[A-Za-z]*(?<!_)\.vue$/i)
// const includeFormInputs = require.context('__components/inputs', true, /\.vue$/i)
// const includeCustomFormInputs = require.context('__components/inputs', true, /\.vue$/i)

const includeGlobalComponents = import.meta.glob('__components/*.vue', {eager: true})
const includeIteratorComponents = import.meta.glob('__components/data_iterators/*.vue', {eager: true})
const includeLayouts = import.meta.glob('__components/layouts/*.vue', {eager:true})
const includeCustomFormInputs = import.meta.glob('__components/inputs/*.vue', {eager: true})
const includeCustomComponents = import.meta.glob('__components/customs/*.vue', {eager: true})

core()

export default {
  install: (app, opts) => {
    window.vm = app
    // document.addEventListener('DOMContentLoaded', core(app))
    // treat all tags starting with 'ue-' as custom elements
    // app.config.compilerOptions.isCustomElement = (tag) => {
    //   return tag.startsWith('ue-')
    // }

    app.use(vuetify)
    app.use(store)
    app.use(i18n)

    app.config.globalProperties.$jquery = window.$
    app.config.globalProperties.$axios = window.axios
    app.config.globalProperties.$lodash = window._
    app.config.globalProperties.window = window

    app.config.globalProperties.$app = app

    // app.config.errorHandler = (err) => {}
    // set locale wrt user profile preference
    setI18nLocale(i18n, store.state.user.locale)
    loadLocaleMessages(i18n, window[import.meta.env.VUE_APP_NAME]?.ENDPOINTS.languages ?? '')
    // i18n.global.setDateTimeFormat('tr', 'Europe/Istanbul')

    // add Global methods to all components
    app.config.globalProperties = {
      ...app.config.globalProperties,
      ...commonMethods,
      $getLocale: i18n.global.locale.value,
      $numberFormats: i18n.global.numberFormats.value,
      $te: function (key, locale) {
        const func = i18n.global.te
        return func(key, locale) ?? false
      },
      $tc: function (key, locale) {
        const func = i18n.global.tc
        return func(key, locale) ?? false
      },
      $plural: function (str) {
        return pluralize.plural(str)
      },
      registerComponentsV1: function (components, folder = '', prefix = 'ue') {
        folder = folder !== '' ? folder + '/' : ''
        components.keys().forEach((path) => {
          const fileName = path.split('/').pop().split('.')[0]
          const componentName = prefix + fileName.replace(/[A-Z]/g, m => '-' + m.toLowerCase())
          // __log(componentName, fileName, folder)
          app.component(componentName, require(`__components/${folder}${fileName}.vue`).default)
        })
      },
      registerComponents: function (components, folder = '', prefix = 'ue') {
        folder = folder !== '' ? folder + '/' : ''
        Object.keys(components).forEach(path => {
          const extFile = path.split('/').pop()
          const fileName = path.split('/').pop().split('.')[0]
          const module = components[path]
          const componentName = prefix + fileName.replace(/[A-Z]/g, m => '-' + m.toLowerCase())
          app.component(componentName, module.default)
        })
        // components.keys().forEach((path) => {
        //   const fileName = path.split('/').pop().split('.')[0]
        //   const componentName = prefix + fileName.replace(/[A-Z]/g, m => '-' + m.toLowerCase())
        //   // __log(componentName, fileName, folder)
        //   app.component(componentName, require(`__components/${folder}${fileName}.vue`).default)
        // })
      }
    }

    // Global Vue mixin : Use global mixins sparsely and carefully!
    app.mixin({
      //   vuetify,
      //   i18n,
      methods: {
        notif: function (Obj) {
          this.$store.commit(ALERT.SET_ALERT, Obj)
        },
        openFreeMediaLibrary: function () {
          // __log('openFreeMedialibrary triggered', this.$root.$refs.main.$refs)
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_CONNECTOR, null) // reset connector
          this.$store.commit(MEDIA_LIBRARY.RESET_MEDIA_TYPE) // reset to first available type
          this.$store.commit(MEDIA_LIBRARY.UPDATE_REPLACE_INDEX, -1) // we are not replacing an image here
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MAX, 0) // set max to 0
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_FILESIZE_MAX, 0) // set filesize max to 0
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_WIDTH_MIN, 0) // set width min to 0
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_HEIGHT_MIN, 0) // set height min to 0
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MODE, false) // set the strict to false (you can change the active type)

          if (this.$root.$refs.main && this.$root.$refs.main.$refs.mediaLibrary) {
            this.$root.$refs.main.$refs.mediaLibrary.openModal()
          }
          // if (this.$root.$refs.mediaLibrary) this.$root.$refs.mediaLibrary.open()
        }
      }

    })
    // all components

    // app.component('v-form-base', VFormBase);
    app.component('v-custom-form-base', VCustomFormBase)
    app.component('ue-modal', UEModal)
    // app.component('ue-modal-dialog', UEModalDialog)
    app.component('ue-modal-media', UEModalMedia)
    // Vue.component('ue-medialibrary', UEMediaLibrary)

    // crm base package components
    app.config.globalProperties.registerComponents(includeGlobalComponents)
    app.config.globalProperties.registerComponents(includeIteratorComponents)
    // app.config.globalProperties.registerComponents(includeLabComponents, 'labs')
    app.config.globalProperties.registerComponents(includeLayouts, 'layouts')
    app.config.globalProperties.registerComponents(includeCustomFormInputs, 'inputs', 'v-input')
    app.config.globalProperties.registerComponents(includeCustomComponents, 'customs', 'ue-custom')
    // // Configurations
    // Vue.config.productionTip = isProd
    // Vue.config.devtools = true
    // app.config.globalProperties.$http = axios

    // axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

    // axios.interceptors.response.use((response) => response, (error) => {
    //     globalError('CONTENT', error)

    //     return Promise.reject(error)
    // })

    // // Plugins
    // Vue.use(VueTimeago, {
    //     name: 'timeago', // component name
    //     locale: window[import.meta.env.VUE_APP_NAME].twillLocalization.locale,
    //     locales: mapValues(locales, 'date-fns')
    // })

    // Directives
    app.directive('mask', VueMaskDirective)
    // app.directive('svg', SvgSprite)
    app.use(SvgSprite)
    app.use(Column)
    app.use(FitGrid)
    app.use(Scrollable)

    app.provide('$app', app)
  }
}
