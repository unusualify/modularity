import { map } from 'lodash-es'

import store from '@/store'

/**
 * Active CMS content locale (user’s selected language tab).
 * Reads `store.state.language.active.value` (see `store/modules/language.js`).
 *
 * @param {import('vuex').Store} [storeInstance]
 * @returns {string|undefined}
 */
export function getActiveContentLocale (storeInstance = store) {
  return storeInstance?.state?.language?.active?.value
}

/**
 * Default/fallback content locale (not the language tab).
 * Used when copying a translated field onto a scalar (e.g. title → name).
 */
export function getFallbackContentLocale (storeInstance = store) {
  const bag = storeInstance?.state?.language
    ?? window[import.meta.env.VUE_APP_NAME]?.STORE?.languages
    ?? {}

  if (typeof bag.fallback === 'string' && bag.fallback !== '') {
    return bag.fallback
  }

  if (bag.fallback?.value) {
    return bag.fallback.value
  }

  const all = bag.all ?? []
  const marked = all.find((language) => language?.fallback || language?.default)

  if (marked?.value) {
    return marked.value
  }

  return all[0]?.value
}

/**
 * Language codes for translated form fields.
 * Reads Vuex directly — safe outside setup() (e.g. getModel from a watch).
 * Do not call useLocale()/useStore() here; inject() only works in setup.
 *
 * @returns {string[]}
 */
export const getTranslationLanguages = () => {
  const languages = store?.state?.language?.all
    ?? window[import.meta.env.VUE_APP_NAME]?.STORE?.languages?.all
    ?? []

  return map(languages, 'value')
}

/**
 * Full language locale objects for translated form fields.
 * Same store-direct rule as {@link getTranslationLanguages}.
 *
 * @returns {object[]}
 */
export const getTranslationLocales = () => {
  return store?.state?.language?.all
    ?? window[import.meta.env.VUE_APP_NAME]?.STORE?.languages?.all
    ?? []
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
