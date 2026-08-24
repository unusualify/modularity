import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import { fa } from 'vuetify/iconsets/fa'
import { merge } from 'lodash-es'


// Stylesheets
import '@fortawesome/fontawesome-free/css/all.min.css' // Ensure you are using css-loader
import '@mdi/font/css/materialdesignicons.css' // Ensure you are using css-loader
// import '@mdi/font/scss/materialdesignicons.scss'

// import 'styles/themes/b2press/main.scss'
// import 'vuetify/styles'

// Vuetify
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
// import { VTreeview } from 'vuetify/lib/components/VTreeview'
// import { VBtn } from 'vuetify/lib/components'

import customMdiIcons from '@/config/icons/mdi'
// import { md2 } from 'vuetify/blueprints'

import * as themes from '@/config/themes'

// import 'vuetify/lib/styles/main.sass'

const APP_THEME = import.meta.env.VUE_APP_THEME || 'unusualify'

/**
 * Optional per-theme Vuetify `defaults` (elevation / rounded / border / …).
 * Missing file → empty object (no error).
 *
 * Paths (build copies app theme `defaults.js` here):
 * - built-in:  `config/themes/defaults/{theme}.js`
 * - custom:    `config/themes/customs/{theme}/defaults.js`
 */
function loadThemeDefaults (themeName) {
  const modules = import.meta.glob([
    '../config/themes/defaults/*.js',
    '../config/themes/customs/*/defaults.js',
  ], { eager: true })

  const candidates = [
    `../config/themes/customs/${themeName}/defaults.js`,
    `../config/themes/defaults/${themeName}.js`,
  ]

  for (const key of candidates) {
    const mod = modules[key]
    if (mod) {
      return mod.default ?? mod
    }
  }

  return {}
}

const themeDefaults = loadThemeDefaults(APP_THEME)

const baseDefaults = {
  global: {
    ripple: false
  },
  VSheet: {
    // class: 'rounded'
    //   elevation: 4
  },
  VSheetRounded: {
    class: 'rounded'
  },
  VBtn: {
    color: 'primary',
    density: 'comfortable',
    variant: 'elevated'
  },
  VBtnPrimary: {
    color: 'primary',
    density: 'comfortable',
    variant: 'elevated',
  },
  VBtnSecondary: {
    color: 'secondary',
    density: 'comfortable',
    variant: 'elevated',
  },
  VBtnSuccess: {
    color: 'success',
    variant: 'elevated'
  },
  VImgIcon: {
    height: '2.5rem',
  },
  VRowSecondary: {
    'class': 'v-row-secondary'
  },
  VRowTertiary: {
    'class': 'v-row-tertiary'
  }
}

const opts = {
//   blueprint: md2,
  aliases: {
    VBtnPrimary: components.VBtn,
    VBtnSecondary: components.VBtn,
    VBtnSuccess: components.VBtn,
    VImgIcon: components.VImg,
    VSheetRounded: components.VSheet,
    VRowSecondary: components.VRow,
    VRowTertiary: components.VRow,
  },
  defaults: merge({}, baseDefaults, themeDefaults),
  theme: {
    defaultTheme: APP_THEME,
    themes
  },
  icons: {
    defaultSet: 'mdi',
    // iconfont: 'mdi',
    aliases: {
      ...aliases,
      ...customMdiIcons
    },
    sets: {
      mdi,
      fa
    }
    // component: VIcon,
    // iconfont: 'mdi' || 'fa', // 'mdi' || 'mdiSvg' || 'md' || 'fa' || 'fa4' || 'faSvg'
    // values: {

    // }
  },
  directives,
  components: {
    ...components,
    // VTreeview
  }
}

export default function createModularousVuetify (options = {}) {
  const { defaults: optionDefaults, ...rest } = options

  return createVuetify({
    ...opts,
    ...rest,
    defaults: merge({}, opts.defaults, optionDefaults),
  })
}

// export default createVuetify(opts)
