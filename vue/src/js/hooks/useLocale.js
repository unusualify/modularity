/**
 * useLocale - Composable replacement for LocaleMixin.
 * Provides locale state and helpers from Vuex language store.
 */
import { computed, ref, } from 'vue'
import { useStore } from 'vuex'
import defaultStore from '@/store'
import { LANGUAGE } from '@/store/mutations'

const RTL_LOCALES = ['ar', 'arc', 'dv', 'fa', 'ha', 'he', 'khw', 'ks', 'ku', 'ps', 'ur', 'yi']

export default function useLocale (props = {}) {
  const store = useStore() ?? defaultStore
  const _locale = ref(props.locale ?? null)

  const currentLocale = computed(() => store.state.language?.active ?? null)
  const languages = computed(() => store.state.language?.all ?? [])

  const hasLocale = computed(() => _locale.value != null)
  const hasCurrentLocale = computed(() => currentLocale.value != null)
  const isLocaleRTL = computed(() => {
    if (!hasLocale.value) return false
    const shortlabel = props.locale?.shortlabel ?? _locale.value?.shortlabel ?? ''
    return RTL_LOCALES.includes(shortlabel.toLowerCase())
  })
  const dirLocale = computed(() => (isLocaleRTL.value ? 'rtl' : 'auto'))
  const displayedLocale = computed(() => currentLocale.value?.shortlabel ?? '')

  const updateLocale = (value) => {
    store.commit(LANGUAGE.UPDATE_LANG, value)
  }

  // `_locale` is intentionally NOT returned. Vue 3 reserves keys starting
  // with `_` or `$` in setup() return objects (and composables that get
  // spread into setup() returns). Exposing `_locale` triggered:
  //   "setup() return property "_locale" should not start with "$" or "_"
  //    which are reserved prefixes for Vue internals."
  // The ref is used internally by `hasLocale` and `isLocaleRTL` above; no
  // external consumer reads it (verified via grep). If a consumer ever needs
  // direct access, expose it under a non-prefixed alias such as
  // `localeRef` or `currentLocaleProp` — never with a leading underscore.
  return {
    currentLocale,
    languages,
    hasLocale,
    hasCurrentLocale,
    isLocaleRTL,
    dirLocale,
    displayedLocale,
    updateLocale
  }
}
