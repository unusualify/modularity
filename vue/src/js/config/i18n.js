import axios from 'axios'
import { nextTick } from 'vue'
import { createI18n } from 'vue-i18n'

// import messages from "@intlify/unplugin-vue-i18n/messages";

function loadStaticMessages () {
  const locales = require.context('./../../../../lang', true, /[A-Za-z0-9-_,\s]+.json$/i)
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

// https://vue-i18n.intlify.dev/guide/essentials/datetime.html
const datetimeFormats = {
  en: {
    numeric: {
      year: 'numeric',
      month: 'numeric',
      day: 'numeric'
    },
    short: {
      year: '2-digit',
      month: 'short',
      day: 'numeric'
    },
    medium: {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
      // weekday: 'narrow'
      // era: 'long',
    },
    long: {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      weekday: 'long',
      hour: 'numeric',
      minute: 'numeric',
      hour12: true
    }
  },
  tr: {
    numeric: {
      year: 'numeric',
      month: 'numeric',
      day: 'numeric'
    },
    short: {
      year: '2-digit',
      month: 'short',
      day: 'numeric'
    },
    medium: {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
      // weekday: 'narrow'
      // era: 'long',
    },
    long: {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      weekday: 'long',
      hour: 'numeric',
      minute: 'numeric'
    }
  }
}

// https://vue-i18n.intlify.dev/guide/essentials/number.html
const numberFormats = {
  en: {
    currency: {
      style: 'currency',
      currency: 'USD'
      // currencyDisplay: '$'

    }
  },
  tr: {
    currency: {
      style: 'currency',
      currency: 'TRY'
      // currencyDisplay: '₺'
    }
  }
}

const setupOptions = {
//   locale: import.meta.env.UNUSUAL_DEFAULT_LOCALE || 'tr',
  locale: process.env.VUE_APP_LOCALE || 'en',
  //   fallbackLocale: import.meta.env.UNUSUAL_FALLBACK_LOCALE || 'tr',
  fallbackLocale: process.env.VUE_APP_FALLBACK_LOCALE || 'en',
  legacy: false,
  // silentFallbackWarn: true,
  missingWarn: false,
  fallbackWarn: false,
  messages: loadStaticMessages(),
  datetimeFormats,
  numberFormats

  // messages: loadLocaleMessages(),

}

export default setupI18n(setupOptions)

export function setupI18n (options = { locale: 'en' }) {
  const i18n = createI18n(options)

  setI18nLocale(i18n, options.locale)

  return i18n
}

export function setI18nLocale (i18n, locale) {
  if (i18n.mode === 'legacy') {
    i18n.global.locale = locale
  } else {
    i18n.global.locale.value = locale
  }
  /**
   * NOTE:
   * If you need to specify the language setting for headers, such as the `fetch` API, set it here.
   * The following is an example for axios.
   *
   * axios.defaults.headers.common['Accept-Language'] = locale
   */
  document.querySelector('html').setAttribute('lang', locale)
}

export async function loadLocaleMessages (i18n) {
  // const response = await axios.get('/api/languages')
  // for (const locale in response.data) {
  //   loadLocaleMessage(i18n, locale, response.data[locale])
  // }

  axios.get('/api/languages')
    .then((response) => {
      for (const locale in response.data) {
        loadLocaleMessage(i18n, locale, response.data[locale])
      }
    })
}
export function loadLocaleMessage (i18n, locale, messages) {
  // set locale and locale message
  i18n.global.setLocaleMessage(locale, messages)

  return nextTick()
}
