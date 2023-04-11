// import { createApp } from '$vue'
import { createApp } from '~/vue/dist/vue.esm-bundler.js'

// Plugins
import UEConfig from '@/plugins/UEConfig'

import store from '@/store'

import { RootMixin } from '@/mixins'

// import { loadFonts } from '@/plugins/webfontloader'

// loadFonts()

export {
  createApp,
  store,
  //   core,
  UEConfig,
  RootMixin
}
