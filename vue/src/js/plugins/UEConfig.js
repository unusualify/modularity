import pluralize from 'pluralize'
import { upperFirst } from 'lodash-es'

import core from '@/core'
// import VFormBase from 'vuetify-form-base';
import VCustomFormBase from '__components/others/CustomFormBase.vue'

// Template Components
import UEModal from '__components/modals/Modal.vue'
import UEModalMedia from '__components/modals/ModalMedia.vue'
import DynamicModal from '__components/modals/DynamicModal.vue'
// global mixins

// mutations
import { MEDIA_LIBRARY } from '@/store/mutations'

// Add-ons
import vuetify from '@/plugins/vuetify'
import broadcasting from '@/plugins/broadcasting'
import ModalService from '@/plugins/modalService'
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
import Transition from '@/directives/transition'
import commonMethods from '@/utils/commonMethods'
import { ALERT } from '@/store/mutations'

const includeGlobalComponents = import.meta.glob('__components/*.vue', {eager: true})
const includeIteratorComponents = import.meta.glob('__components/data_iterators/*.vue', {eager: true})
const includeLayouts = import.meta.glob('__components/layouts/*.vue', {eager:true})
const includeFormInputs = import.meta.glob('__components/inputs/*.vue', {eager: true})

const includeCustomComponents = import.meta.glob('__components/customs/*.vue', {eager: true})
const includeCustomFormInputs = import.meta.glob('__components/customs/inputs/*.vue', {eager: true})

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
    app.use(broadcasting)
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
    app.config.globalProperties.$getLocale = i18n.global.locale.value
    app.config.globalProperties.$numberFormats = i18n.global.numberFormats.value
    app.config.globalProperties.$te = function (key, locale) {
      const func = i18n.global.te
      return func(key, locale) ?? false
    }

    app.config.globalProperties.$tc = function (key, locale) {
      const func = i18n.global.tc
      return func(key, locale) ?? false
    }

    app.config.globalProperties.$plural = function (str) {
      return pluralize.plural(str)
    }

    app.config.globalProperties.registerComponentsV1 = function (components, folder = '', prefix = 'ue') {
      folder = folder !== '' ? folder + '/' : ''
      components.keys().forEach((path) => {
        const fileName = path.split('/').pop().split('.')[0]
        const componentName = prefix + fileName.replace(/[A-Z]/g, m => '-' + m.toLowerCase())
        app.component(componentName, require(`__components/${folder}${fileName}.vue`).default)
      })
    }

    app.config.globalProperties.registerComponents = function (components, folder = '', prefix = 'Ue') {
      folder = folder !== '' ? folder + '/' : ''
      Object.keys(components).forEach(path => {
        const extFile = path.split('/').pop()
        const fileName = path.split('/').pop().split('.')[0]
        const module = components[path]
        // const componentName = prefix + fileName.replace(/[A-Z]/g, m => '-' + m.toLowerCase())
        const componentName = prefix + upperFirst(fileName)
        app.component(componentName, module.default)
      })
    }

    app.config.globalProperties.$call = function (functionName, ...args) {
      return this[functionName](...args)
    }

    Object.keys(commonMethods).forEach(key => {
      app.config.globalProperties[key] = commonMethods[key].bind(app.config.globalProperties)
    })

    // Global Vue mixin : Use global mixins sparsely and carefully!
    app.mixin({
      //   vuetify,
      //   i18n,
      methods: {
        notif: function (Obj) {
          this.$store.commit(ALERT.SET_ALERT, Obj)
        },
        openFreeMediaLibrary: function () {
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_CONNECTOR, null) // reset connector
          this.$store.commit(MEDIA_LIBRARY.RESET_MEDIA_TYPE) // reset to first available type
          this.$store.commit(MEDIA_LIBRARY.UPDATE_REPLACE_INDEX, -1) // we are not replacing an image here
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MAX, 0) // set max to 0
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_FILESIZE_MAX, 0) // set filesize max to 0
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_WIDTH_MIN, 0) // set width min to 0
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_HEIGHT_MIN, 0) // set height min to 0
          this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MODE, false) // set the strict to false (you can change the active type)

          this.$store.commit(MEDIA_LIBRARY.OPEN_MODAL)
        }
      }

    })
    // all components
    app.component('VCustomFormBase', VCustomFormBase)
    app.component('UeDynamicModal', DynamicModal)
    app.component('UeModal', UEModal)
    app.component('UeModalMedia', UEModalMedia)

    // crm base package components
    app.config.globalProperties.registerComponents(includeGlobalComponents)
    app.config.globalProperties.registerComponents(includeIteratorComponents)
    // app.config.globalProperties.registerComponents(includeLabComponents, 'labs')
    app.config.globalProperties.registerComponents(includeLayouts, 'layouts')
    app.config.globalProperties.registerComponents(includeFormInputs, 'inputs', 'VInput')
    app.config.globalProperties.registerComponents(includeCustomComponents, 'customs', 'UeCustom')

    // Directives
    app.directive('mask', VueMaskDirective)
    // app.directive('svg', SvgSprite)
    app.use(SvgSprite)
    app.use(Column)
    app.use(FitGrid)
    app.use(Scrollable)
    app.use(Transition)
    app.use(ModalService)
    app.provide('$app', app)
  }
}
