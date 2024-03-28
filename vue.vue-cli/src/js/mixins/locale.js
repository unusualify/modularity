import { mapState } from 'vuex'

export default {
  props: {
    locale: {
      default: null
    }
  },
  data () {
    return {
      _locale: this.locale
    }
  },
  computed: {
    hasLocale: function () {
      return this._locale != null
    },
    hasCurrentLocale: function () {
      return this.currentLocale != null
    },
    // isCurrentLocale: function () {
    //   if (this.hasLocale && this.hasCurrentLocale) {
    //     return this._locale.value === this.currentLocale.value
    //   } else {
    //     return true
    //   }
    // },
    isLocaleRTL: function () {
      /* List of RTL locales */
      /*
        ar : Arabic
        arc : Aramaic
        dv : Divehi
        fa : Persian
        ha : Hausa
        he : Hebrew
        khw : Khowar
        ks : Kashmiri
        ku : Kurdish
        ps : Pashto
        ur : Urdu
        yi : Yiddish
      */
      const rtlLocales = ['ar', 'arc', 'dv', 'fa', 'ha', 'he', 'khw', 'ks', 'ku', 'ps', 'ur', 'yi']
      if (this.hasLocale) return rtlLocales.includes(this.locale.shortlabel.toLowerCase())
      else return false
    },
    dirLocale: function () {
      return (this.isLocaleRTL ? 'rtl' : 'auto')
    },
    displayedLocale: function () {
      return this.currentLocale.shortlabel
      // if (this.hasLocale) return this._locale.shortlabel
      // else return false
    },
    ...mapState({
      currentLocale: state => state.language.active,
      languages: state => state.language.all
    })
  },
  methods: {
    onClickLocale: function () {
      this.$emit('localize', this.locale)
    },
    updateLocale: function (oldValue) {
      this.$emit('localize', oldValue)
    }
  },
  mounted () {
    // if (this.currentLocale) {
    //   this._locale = this.currentLocale
    // }
  }
}
