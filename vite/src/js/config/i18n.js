import { createI18n } from 'vue-i18n'
// import messages from "@intlify/unplugin-vue-i18n/messages";

function loadLocaleMessages(){
    const locales = require.context('./lang', true, /[A-Za-z0-9-_,\s]+.json$/i);
    const messages = {};
    console.log(locales)

    locales.keys().forEach(key => {
        const matched = key.match(/([A-Za-z0-9-_]+)\./i);
        if(matched && matched.length > 1){
            const locale = matched[1];
            messages[locale] = locales(key);
        }
    })

    return messages;
}

const dateTimeFormats = {
    'en': {
      short: {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
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
    'tr': {
      short: {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      },
      long: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
        hour: 'numeric',
        minute: 'numeric',
      }
    }
}

const numberFormats = {
  'en': {
    currency: {
      style: 'currency',
      currency: 'USD',
      // currencyDisplay: '$'

    }
  },
  'tr': {
    currency: {
      style: 'currency',
      currency: 'TRY',
      // currencyDisplay: '₺'
    }
  }
}


export default createI18n({
    locale: import.meta.env.UNUSUAL_DEFAULT_LOCALE || 'tr',
    fallbackLocale: import.meta.env.UNUSUAL_FALLBACK_LOCALE || 'tr',
    legacy: false,
    silentFallbackWarn: true,
    // messages: loadLocaleMessages(),
    messages: loadLocaleMessages(),
    dateTimeFormats,
    numberFormats
})

