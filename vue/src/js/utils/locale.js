import { map } from 'lodash-es'

export const getTranslationLanguages = (input) => {
  return map(window[import.meta.env.VUE_APP_NAME].STORE.languages.all, 'value')
}


export function getCurrentLocale () {
  return window[import.meta.env.VUE_APP_NAME].LOCALE
}

export function isCurrentLocale24HrFormatted () {
  return new Intl.DateTimeFormat(getCurrentLocale(), {
    hour: 'numeric'
  }).formatToParts(
    new Date(2020, 0, 1, 13)
  ).find(part => part.type === 'hour').value.length === 2
}

export function getTimeFormatForCurrentLocale () {
  if (isCurrentLocale24HrFormatted()) {
    return 'HH:mm'
  } else {
    return 'hh:mm A'
  }
}
