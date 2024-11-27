// hooks/useLocale.js
import { reactive, computed, onMounted, toRefs, getCurrentInstance } from 'vue'
import { useStore } from 'vuex'
import { LANGUAGE } from '@/store/mutations'
export default function useLocale () {
  const store = useStore()

  const state = reactive({
    currentLocale: computed(() => store.state.language.active),
    languages: computed(() => store.state.language.all),
    locales: computed(() => store.state.language.all),
    displayedLocale: computed(() => store.state.language.active.shortlabel),

    // hasLocale
    // isLocaleRTL: computed(() => {
    //   const rtlLocales = ['ar', 'arc', 'dv', 'fa', 'ha', 'he', 'khw', 'ks', 'ku', 'ps', 'ur', 'yi']
    //   if (this.hasLocale) return rtlLocales.includes(this.locale.shortlabel.toLowerCase())
    //   else return false
    // }),
    // dirLocale: computed(() => store.state.language.active.rtl ? 'rtl' : 'auto')
  })

  const methods = reactive({
    updateLocale: function (value) {
      store.commit(LANGUAGE.UPDATE_LANG, value)
    }
  })

  return {
    ...toRefs(state),
    ...toRefs(methods)
  }
}
