// import { createApp } from '$vue'
import { createApp } from '~/vue/dist/vue.esm-bundler.js'

// Plugins
import UEConfig from '@/plugins/UEConfig'

import store from '@/store'

import { RootMixin } from '@/mixins'

// Global styles
// import 'styles/wireframe.scss'

export {
  createApp,
  store,
  UEConfig,
  RootMixin
}
