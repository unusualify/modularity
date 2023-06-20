import { createI18n } from 'vue-i18n'
// import messages from "@intlify/unplugin-vue-i18n/messages";

function loadLocaleMessages () {
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
export default createI18n({
//   locale: import.meta.env.UNUSUAL_DEFAULT_LOCALE || 'tr',
  locale: process.env.VUE_APP_LOCALE || 'en',
  //   fallbackLocale: import.meta.env.UNUSUAL_FALLBACK_LOCALE || 'tr',
  fallbackLocale: process.env.VUE_APP_FALLBACK_LOCALE || 'en',
  legacy: false,
  silentFallbackWarn: true,
  // messages: loadLocaleMessages(),
  messages: loadLocaleMessages(),
  datetimeFormats,
  numberFormats
})
