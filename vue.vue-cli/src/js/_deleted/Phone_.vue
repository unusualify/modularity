<script >
import VTextField from 'vuetify/components/VTextField'
import VSelect from 'vuetify/components/VSelect'
import countries from '@/utils/countries.js'
import { parsePhoneNumber } from 'libphonenumber-js'

export default {
  name: 'v-custom-input-phone',
  extends: VTextField,

  props: {
    modelValue: {
      type: String,
      default: () => ''
    },
    type: {
      type: String,
      default: () => 'tel'
    },
    placeholder: {
      type: String,
      default: () => '#'
    },
    countries: {
      type: Array,
      default: () => []
    },
    countryCode: {
      type: [Number, String],
      default: () => 'TR'
    },
    prependCountryCode: {
      type: Boolean,
      default: () => true
    },
    returnWithCountryCode: {
      type: Boolean,
      default: () => true
    }
  },
  data: () => {
    return {
      internalCountryCode: ''
    }
  },
  watch: {
    modelValue: {
      handler (value) {
        // __log(
        //   value
        // )
        if (value) {
          this.setPhoneNumber(
            value.replace(new RegExp('^00'), '+')
          )
        }
      }
      // immediate: true
    },
    countryCode: {
      handler (countryCode) {
        __log(
          countryCode,
          this.internalValue
        )
        this.setPhoneNumber('+' + countryCode + this.internalValue)
      }
      // immediate: true
    }
  },
  methods: {
    genPrependSlot () {
      if (!this.prependCountryCode) return false

      return this.genSelector()
    },
    genSelector () {
      const selections = this.countries.length ? this.countries : countries.sort((a, b) => Number(a.code) - Number(b.code))

      if (selections.length === 0) return

      return this.$createElement(VSelect, {
        props: {
          items: selections.map((el) => {
            return {
              text: `${el.name} (+${el.code})`,
              value: el.code
            }
          }),
          placeholder: this.placeholder,
          value: this.internalCountryCode || selections[0].code,
          disabled: selections.length == 1,
          onInput: this.selectCountry
        },
        on: {
          input: this.selectCountry
        },
        class: {
          'pt-0': true,
          'mt-0': true,
          'mr-1': true
        },
        ref: 'countrySelector'
      })
    },
    onInput (e) {
      VTextField.options.methods.onInput.call(this, e)

      this.setPhoneNumber(e.target.value)
    },
    onBlur (e) {
      VTextField.options.methods.onBlur.call(this, e)

      this.setPhoneNumber(e.target.value)

      this.$emit('phone', this.preparePhoneNumber(this.internalValue))
    },
    selectCountry (code) {
      this.$emit('country', this.internalCountryCode = code)

      if (this.returnWithCountryCode) this.$emit('phone', this.preparePhoneNumber(this.internalValue))
    },
    preparePhoneNumber (phone) {
      let final = phone

      if (!this.prependCountryCode) return this.internalCountryCode + phone

      if (!final.startsWith('+')) {
        final = '+' + this.internalCountryCode + phone
      }

      return final.split(' ').join('')
    },
    setPhoneNumber (phone) {
      const phoneNumber = parsePhoneNumber(this.preparePhoneNumber(phone), this.internalCountryCode.toString())

      if (!phoneNumber) return

      if (!phoneNumber.isValid()) return

      this.internalValue = phoneNumber.formatNational()
      this.internalCountryCode = Number(phoneNumber.countryCallingCode)
    }
  }
}
</script>
