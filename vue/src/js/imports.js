// import { createApp } from '$vue'
import { createApp } from '~/vue/dist/vue.esm-bundler.js'

// Plugins
import UEConfig from '@/plugins/UEConfig'

import store from '@/store'

import { RootMixin } from '@/mixins'

// Global styles
import 'styles/wireframe.scss'
// import 'styles/themes/default/_main.scss'
// import(`styles/themes/${process.env.VUE_APP_THEME}/pages/index.scss`)
// import { loadFonts } from '@/plugins/webfontloader'

// loadFonts()

export {
  createApp,
  store,
  //   core,
  UEConfig,
  RootMixin
}
