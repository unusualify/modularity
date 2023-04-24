
import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import { fa } from 'vuetify/iconsets/fa'

import '@fortawesome/fontawesome-free/css/all.min.css' // Ensure you are using css-loader
import '@mdi/font/css/materialdesignicons.css' // Ensure you are using css-loader

import 'vuetify/styles'
// import 'vuetify/lib/styles/main.sass'
import * as components from 'vuetify/lib/components'
import * as directives from 'vuetify/lib/directives'
// import { VTreeview } from 'vuetify/lib/components/VTreeview'

import customMdiIcons from '@/config/icons/mdi'
import light from '@/config/themes/light'
import dark from '@/config/themes/dark'

// import { md2 } from 'vuetify/blueprints'

import { VBtn, VTextField } from 'vuetify/lib/components'

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
    VBtnSecondary: VBtn,
    VBtnTertiary: VBtn,
    VBtnSuccess: VBtn
  },
  defaults: {
    global: {
      ripple: false
    },
    VSheet: {
    //   elevation: 4
    },
    VTextField: {
      variant: 'outlined'
    },
    VBtn: {
      color: 'primary'
    },
    VBtnSecondary: {
      color: 'secondary',
      variant: 'tonal'
    },
    VBtnTertiary: {
      rounded: true,
      variant: 'plain'
    },
    VBtnSuccess: {
      color: 'success',
      variant: 'elevated'
    }

  },

  theme: {
    // defaultTheme: 'dark',
    themes: {
      light,
      dark
    }
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
  components: {
    ...components
    // VTreeview
  },
  directives
}

export default createVuetify(opts)
