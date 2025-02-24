import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import { fa } from 'vuetify/iconsets/fa'

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

function loadIcons ($font) {
  const locales = require.context('../config/icons', true, /[A-Za-z0-9-_,\s]+.json$/i)
  const messages = {}

  locales.keys().forEach(key => {
    const matched = key.match(/([A-Za-z0-9-_]+)\./i)
    if (matched && matched.length > 1) {
      const locale = matched[1]
      messages[locale] = locales(key)
    }
  })

  return messages
}

const opts = {
//   blueprint: md2,
  aliases: {
    VBtnPrimary: components.VBtn,
    VBtnSecondary: components.VBtn,
    // VBtnTertiary: components.VBtn,
    // VBtnCta: components.VBtn,
    // VBtnCtaSecondary: components.VBtn,
    VBtnSuccess: components.VBtn,
    VImgIcon: components.VImg,
    VSheetRounded: components.VSheet,
    VRowSecondary: components.VRow,
    VRowTertiary: components.VRow,


  },
  defaults: {
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
      class: 'text-uppercase',
    },
    VBtnSecondary: {
      color: 'secondary',
      density: 'comfortable',
      variant: 'elevated',
      class: 'text-uppercase',
    },
    // VBtnTertiary: {
    //   color: 'tertiary'
    //   // variant: 'plain'
    // },
    // VBtnCta: {
    //   color: 'cta'
    //   // variant: 'plain'
    // },
    // VBtnCtaSecondary: {
    //   color: 'cta-secondary'
    //   // variant: 'plain'
    // },
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

  },
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
    ...components
    // VTreeview
  }
}

export default createVuetify(opts)
