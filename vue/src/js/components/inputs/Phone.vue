<template>
    <v-row no-gutters  :class="['vue-tel-input-vuetify flex-nowrap', $lodash.pick(boundProps, ['wrapperClasses']) ?? getDefault('wrapperClasses')]">
      <v-autocomplete
        class="flex-grow-0 flex-shrink-1"
        :ref="makeReference('countryInput')"
        v-model="countryCode"
        :items="sortedCountries"
        item-title="name"
        item-value="iso2"
        :menu-props="{ maxHeight: 200 }"
        v-bind="$lodash.pick(boundProps, ['variant', 'menuProps', 'selectClasses', 'selectLabel', 'dense', 'density'])"
        autocomplete="off"
        return-object
        @update:model-value="choose"
      >
        <template #selection>
          <div v-if="activeCountry && activeCountry.iso2" :class="activeCountry.iso2.toLowerCase()" class="vti__flag" />
        </template>
        <template #item="{ item, props }">
          <v-list-item v-bind="props">
            <template #prepend>
              <div :class="item.raw.iso2.toLowerCase()" class="vti__flag" />
            </template>
            <v-list-item-title>
              +{{ item.raw.dialCode }}
            </v-list-item-title>
          </v-list-item>
        </template>
      </v-autocomplete>
      <v-text-field
          :ref="makeReference('phoneInput')"
          type="tel"
          class="flex-grow-1 flex-shrink-0"
          v-model="phone"
          v-bind="boundProps"

          @input="onInput"
          @blur="onBlur"
          @focus="onFocus"
          @click="onClick"
          @change="onChange"
          @mousedown="onMouseDown"
          @mouseup="onMouseUp"
          @keydown="onKeyDown"
          @keyup.enter="onEnter"
          @keyup.space="onSpace"
        >

      </v-text-field>
    </v-row>

</template>

<script>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

import PhoneNumber, { getExample } from 'awesome-phonenumber'
import Phone, { getCountry, setCaretPosition } from '@/utils/phone'

function getDefault (key) {
  return Phone.options[key]
}

// Polyfill for Event.path in IE 11: https://stackoverflow.com/a/46093727
function getParents (node, memo) {
  const parsedMemo = memo || []
  const { parentNode } = node
  if (!parentNode) {
    return parsedMemo
  }
  return getParents(parentNode, parsedMemo.concat(parentNode))
}

export default {
  // name: 'VueTelInputVuetify',
  emits: [
    ...makeInputEmits,
    'country-changed',
    'validate',
    'onValidate',
    'keydown',
    'mousedown',
    'mouseup',
    'input',
    'change',
    'focus',
    'blur',
    'click',
    'space',
    'onInput',
    'onSpace',
    'onBlur',
    'onMouseup',
    'onMousedown'
  ],
  directives: {
    // Click-outside by BosNaufal: https://github.com/BosNaufal/vue-click-outside
    'click-outside': {
      bind (el, binding, vNode) {
        // Provided expression must evaluate to a function.
        if (typeof binding.value !== 'function') {
          const compName = vNode.context.name
          let warn = `[Vue-click-outside:] provided expression ${binding.expression} is not a function, but has to be`
          if (compName) {
            warn += `Found in component ${compName}`
          }
          console.warn(warn)
        }
        // Define Handler and cache it on the element
        const { bubble } = binding.modifiers
        const handler = (e) => {
          // Fall back to composedPath if e.path is undefined
          const path = e.path ||
            (e.composedPath ? e.composedPath() : false) ||
            getParents(e.target)
          if (
            bubble ||
            (path.length && !el.contains(path[0]) && el !== path[0])
          ) {
            binding.value(e)
          }
        }
        el.__vueClickOutside__ = handler
        // add Event Listeners
        document.addEventListener('click', handler)
      },
      unbind (el) {
        // Remove Event Listeners
        document.removeEventListener('click', el.__vueClickOutside__)
        el.__vueClickOutside__ = null
      }
    }
  },
  props: {
    ...makeInputProps(),
    invalidMsg: {
      type: String,
      default: () => getDefault('invalidMsg')
    },
    mode: {
      type: String,
      default: () => getDefault('mode')
    },
    disabledFetchingCountry: {
      type: Boolean,
      default: () => getDefault('disabledFetchingCountry')
    },
    allCountries: {
      type: Array,
      default: () => getDefault('allCountries')
    },
    defaultCountry: {
      // Default country code, ie: 'AU'
      // Will override the current country of user
      type: String,
      default: () => getDefault('defaultCountry')
    },
    preferredCountries: {
      type: Array,
      default: () => getDefault('preferredCountries')
    },
    onlyCountries: {
      type: Array,
      default: () => getDefault('onlyCountries')
    },
    ignoredCountries: {
      type: Array,
      default: () => getDefault('ignoredCountries')
    }

  },
  setup (props, context) {
    return {
      ...useInput(props, context)
    }
  },
  data () {
    return {
      phone: '',
      activeCountry: { iso2: '' },
      open: false,
      finishMounted: false,
      selectedIndex: null,
      typeToFindInput: '',
      typeToFindTimer: null,
      cursorPosition: 0,
      countryCode: {
        handler(newValue) {
        if (newValue && newValue !== this.activeCountry) {
            this.choose(newValue, true)
          }
        },
        deep: true
      },
      lastValidCountry: null,

      data() {
  return {
    phone: '',
    activeCountry: { iso2: '' },
    open: false,
    finishMounted: false,
    selectedIndex: null,
    typeToFindInput: '',
    typeToFindTimer: null,
    cursorPosition: 0,
    countryCode: null,

    phoneRule: v => {
      return v
        ? (PhoneNumber(
            this.phone || '',
            this.activeCountry.iso2
          ).toJSON().valid || 'Geçersiz telefon numarası')
        : 'Telefon Numarası gerekli'
    }
  }
},
    }
  },
  computed: {
    parsedMode () {
      if (this.mode) {
        if (!['international', 'national'].includes(this.mode)) {
          console.error('Invalid value of prop "mode"')
        } else {
          return this.mode
        }
      }
      if (!this.phone || this.phone[0] !== '+') {
        return 'national'
      }
      return 'international'
    },
    filteredCountries () {
      // List countries after filtered
      if (this.onlyCountries.length) {
        return this.getCountries(this.onlyCountries)
      }
      if (this.ignoredCountries.length) {
        return this.allCountries.filter(
          ({ iso2 }) => !this.ignoredCountries.includes(iso2.toUpperCase()) &&
            !this.ignoredCountries.includes(iso2.toLowerCase())
        )
      }
      return this.allCountries
    },
    sortedCountries () {
      // Sort the list countries: from preferred countries to all countries
      const preferredCountries = this.getCountries(this.preferredCountries).map(
        country => ({
          ...country,
          preferred: true
        })
      )
      return [...preferredCountries, ...this.filteredCountries]
    },
    phoneObject () {
      // __log('phoneObject', this.input, this.activeCountry.iso2)
      const result = PhoneNumber(
        this.phone || '',
        this.activeCountry.iso2
      ).toJSON()

      Object.assign(result, {
        isValid: result.valid,
        country: this.activeCountry
      })

      // __log('phoneObject computed', result, getExample(this.activeCountry.iso2).toJSON())
      // if (result.valid) { __log('phoneObject computed isValid', result) }

      if (!this.phone) {
        return {
          ...result,
          number: {
            _phone: ''
          }
        }
      }
      return result
    },
    phoneText () {
      let key = '_phone'
      // __log('phoneText computed', this.phoneObject)
      if (this.phoneObject.isValid) {
        key = this.parsedMode
      } else {
        this.boundProps.validate = false
      }
      return this.phoneObject.number[key] || ''
    }
  },
  watch: {
    // eslint-disable-next-line func-names
    'phoneObject.valid': function (value) {
      if (value) {
        this.phone = this.phoneText
        // Store the last valid country when phone is valid
        this.lastValidCountry = { ...this.activeCountry }
      }
      this.$emit('validate', this.phoneObject)
      this.$emit('onValidate', this.phoneObject)
    },
    modelValue (val, oldValue) {
      // __log('phone.vue modelValue watch', val, this.modelValue, this.phone)
      if (__isString(val)) { this.phone = this.modelValue }
    },
    open (isDropdownOpened) {
      // Emit open and close events
      if (isDropdownOpened) {
        this.$emit('open')
      } else {
        this.$emit('close')
      }
    },
    phone: {
      handler(newValue, oldValue) {
        if (newValue) {
          if (newValue[0] === '+') {
            // Handle numbers starting with '+'
            const code = PhoneNumber(newValue).getRegionCode()
            if (code) {
              const newCountry = this.findCountry(code)
              if (newCountry) {
                this.activeCountry = newCountry
                this.countryCode = newCountry
              }
            }
          } else {
            // Try to find matching country for numbers without '+'
            for (const country of this.sortedCountries) {
              const phoneObj = PhoneNumber(newValue, country.iso2)

              if (phoneObj.valid) {

                this.activeCountry = country
                this.countryCode = country
                break
              }
            }
          }
        }

        // Preserve cursor position
        if (oldValue && this.cursorPosition < oldValue.length) {
          this.$nextTick(() => {
            setCaretPosition(this.$refs[this.getReference('phoneInput')], this.cursorPosition)
          })
        }

        // Update model value
        this.updateModelValue(this.phoneText)
      },
      immediate: true
    },
    activeCountry (value) {
      // __log('activeCountry watch', value)
      if (value && value.iso2) {
        this.boundProps.placeholder = getExample(value.iso2).toJSON().number.national
        this.boundProps.persistentPlaceholder = true

        this.$emit('country-changed', value)
      }
    },
    countryCode (newValue, oldValue) {
      this.choose(newValue, true)
    }
  },
  mounted () {
    this.$watch(`$refs.${this.getReference('countryInput')}.isResetting`, v => v && this.reset())

    // Only call reset if we don't have an activeCountry yet
    if (!this.activeCountry || !this.activeCountry.iso2) {
      this.reset()
    }
  },
  created() {
    if (this.modelValue) {
      this.phone = this.modelValue.trim()
      // Initialize country from model value if it starts with '+'
      if (this.phone[0] === '+') {
        const code = PhoneNumber(this.phone).getRegionCode()
        if (code) {
          const initialCountry = this.findCountry(code)
          if (initialCountry) {
            this.activeCountry = initialCountry
            this.countryCode = initialCountry
          }
        }
      }
    }

    if (!__isset(this.boundProps.rules)) {
      this.boundProps.rules = []
    }
    this.boundProps.rules.push(this.phoneRule)
  },
  methods: {

    inputOnSet (newValue, oldValue) {
      // __log('phone watch', newValue, oldValue)
      if (newValue || __isString(newValue)) {
        if (newValue[0] === '+') {
          const code = PhoneNumber(newValue).getRegionCode()
          if (code) {
            this.activeCountry = this.findCountry(code) || this.activeCountry
          }
        }
      }
      // Reset the cursor to current position if it's not the last character.
      if (oldValue && this.cursorPosition < oldValue.length) {
        this.$nextTick(() => {
          setCaretPosition(this.$refs[this.getReference('phoneInput')], this.cursorPosition)
        })
      }

      // this.$emit('input', this.phoneText, this.phoneObject)
      this.updateModelValue(this.phoneText)
      // this.updateModelValue(val)
      // context.emit('update:modelValue', val)
    },
    initializeCountry () {
      return new Promise((resolve) => {
        /**
         * 1. If the phone included prefix (+12), try to get the country and set it
         */
        if (this.phone && this.phone[0] === '+') {
          const activeCountry = PhoneNumber(this.input).getRegionCode()
          if (activeCountry) {
            this.choose(activeCountry)
            resolve()
            return
          }
        }
        /**
         * 2. Use default country if passed from parent
         */
        if (this.defaultCountry) {
          const defaultCountry = this.findCountry(this.defaultCountry)
          if (defaultCountry) {
            this.choose(defaultCountry)
            resolve()
            return
          }
        }
        const fallbackCountry = this.findCountry(this.preferredCountries[0]) ||
          this.filteredCountries[0]
        /**
         * 3. Check if fetching country based on user's IP is allowed, set it as the default country
         */
        if (!this.disabledFetchingCountry) {
          getCountry()
            .then((res) => {
              if (this.phone === '') {
                this.activeCountry = this.findCountry(res) || this.activeCountry
              }
            })
            .catch((error) => {
              console.warn(error)
              /**
               * 4. Use the first country from preferred list (if available) or all countries list
               */
              this.choose(fallbackCountry)
            })
            .finally(() => {
              resolve()
            })
        } else {
          /**
           * 4. Use the first country from preferred list (if available) or all countries list
           */
          this.choose(fallbackCountry)
          resolve()
        }
      })
    },
    /**
     * Get the list of countries from the list of iso2 code
     */
    getCountries (list = []) {
      return list
        .map(countryCode => this.findCountry(countryCode))
        .filter(Boolean)
    },
    findCountry (iso = '') {
      return this.allCountries.find(
        country => country.iso2 === iso.toUpperCase()
      )
    },
    getItemClass (index, iso2) {
      const highlighted = this.selectedIndex === index
      const lastPreferred = index === this.preferredCountries.length - 1
      const preferred = this.preferredCountries.some(
        c => c.toUpperCase() === iso2
      )
      return {
        highlighted,
        'last-preferred': lastPreferred,
        preferred
      }
    },
    choose(country, toEmitInputEvent = false) {
      // Set the active country
      this.activeCountry = country || this.activeCountry || {}

      // Format phone number if we have a country and phone number
      if (
        this.phone &&
        this.phone[0] === '+' &&
        this.activeCountry.iso2 &&
        this.phoneObject.number.significant
      ) {
        const phoneNumber = PhoneNumber(
          this.phoneObject.number.significant,
          this.activeCountry.iso2
        )
        if (phoneNumber.valid) {
          this.phone = phoneNumber.getNumber('international')
        }
      } else if (
        this.inputOptions &&
        this.inputOptions.showDialCode &&
        country &&
        country.dialCode
      ) {
        this.phone = `+${country.dialCode}`
      }

      // Always sync countryCode with activeCountry
      this.countryCode = this.activeCountry

      if (toEmitInputEvent) {
        this.$emit('input', this.phoneText, this.phoneObject)
        this.$emit('onInput', this.phoneObject)
      }
    },

    reset() {
      if (this.activeCountry && this.activeCountry.iso2) {
        this.countryCode = this.activeCountry
      }

      this.initializeCountry()
        .then(() => {
          if (
            !this.phone &&
            this.inputOptions &&
            this.inputOptions.showDialCode &&
            this.activeCountry.dialCode
          ) {
            this.phone = `+${this.activeCountry.dialCode}`
          }
          // Ensure countryCode is synced with activeCountry
          this.countryCode = this.activeCountry
          this.$emit('validate', this.phoneObject)
          this.$emit('onValidate', this.phoneObject)
        })
        .catch(console.error)
        .finally(() => {
          this.finishMounted = true
        })

      this.open = false
    },

    onInput (e) {
      // this.$refs.input.setCustomValidity(
      //   this.phoneObject.valid ? "" : this.invalidMsg
      // );
      // Returns response.number to assign it to v-model (if being used)
      // Returns full response for cases @input is used
      // and parent wants to return the whole response.
      // __log(this.phoneText, this.phoneObject, e, e.target)
      // this.$emit('input', this.phoneText, this.phoneObject)
      // this.$emit('onInput', this.phoneObject) // Deprecated
      // Keep the current cursor position just in case the input reformatted
      // and it gets moved to the last character.
      if (e && e.target) {
        this.cursorPosition = e.target.selectionStart
      }
    },
    onBlur () {
      this.$emit('blur')
      this.$emit('onBlur') // Deprecated
    },
    onFocus (event) {
      this.$emit('focus', event)
    },
    onClick (event) {
      this.$emit('click', event)
    },
    onChange (value) {
      this.$emit('change', value)
    },
    onMouseUp (event) {
      this.$emit('mouseup', event)
    },
    onMouseDown (event) {
      this.$emit('mousedown', event)
    },
    onKeyDown (event) {
      this.$emit('keydown', event)
    },
    onEnter () {
      this.$emit('enter')
      this.$emit('onEnter') // Deprecated
    },
    onSpace () {
      this.$emit('space')
      this.$emit('onSpace') // Deprecated
    },
    focus () {
      this.$refs[this.getReference('phoneInput')].focus()
    },
    toggleDropdown () {
      if (this.disabled) {
        return
      }
      this.open = !this.open
    },
    clickedOutside () {
      this.open = false
    },
    keyboardNav (e) {
      if (e.keyCode === 40) {
        // down arrow
        e.preventDefault()
        this.open = true
        if (this.selectedIndex === null) {
          this.selectedIndex = 0
        } else {
          this.selectedIndex = Math.min(
            this.sortedCountries.length - 1,
            this.selectedIndex + 1
          )
        }
        const selEle = this.$refs.list.children[this.selectedIndex]
        if (
          selEle.offsetTop + selEle.clientHeight >
          this.$refs.list.scrollTop + this.$refs.list.clientHeight
        ) {
          this.$refs.list.scrollTop = selEle.offsetTop -
            this.$refs.list.clientHeight +
            selEle.clientHeight
        }
      } else if (e.keyCode === 38) {
        // up arrow
        e.preventDefault()
        this.open = true
        if (this.selectedIndex === null) {
          this.selectedIndex = this.sortedCountries.length - 1
        } else {
          this.selectedIndex = Math.max(0, this.selectedIndex - 1)
        }
        const selEle = this.$refs.list.children[this.selectedIndex]
        if (selEle.offsetTop < this.$refs.list.scrollTop) {
          this.$refs.list.scrollTop = selEle.offsetTop
        }
      } else if (e.keyCode === 13) {
        // enter key
        if (this.selectedIndex !== null) {
          this.choose(this.sortedCountries[this.selectedIndex])
        }
        this.open = !this.open
      } else {
        // typing a country's name
        this.typeToFindInput += e.key
        clearTimeout(this.typeToFindTimer)
        this.typeToFindTimer = setTimeout(() => {
          this.typeToFindInput = ''
        }, 700)
        // don't include preferred countries so we jump to the right place in the alphabet
        const typedCountryI = this.sortedCountries
          .slice(this.preferredCountries.length)
          .findIndex(c => c.name.toLowerCase().startsWith(this.typeToFindInput))
        if (typedCountryI >= 0) {
          this.selectedIndex = this.preferredCountries.length + typedCountryI
          const selEle = this.$refs.list.children[this.selectedIndex]
          const needToScrollTop = selEle.offsetTop < this.$refs.list.scrollTop
          const needToScrollBottom = selEle.offsetTop + selEle.clientHeight >
            this.$refs.list.scrollTop + this.$refs.list.clientHeight
          if (needToScrollTop || needToScrollBottom) {
            this.$refs.list.scrollTop = selEle.offsetTop - this.$refs.list.clientHeight / 2
          }
        }
      }
    }
  }
}
</script>

<!-- <style src="css/sprite.css"></style> -->
<style lang="scss">
  @import 'css/sprite.css';
  .v-autocomplete__selection{
    height: 1.25rem !important;
  }
  .vti__flag {
    margin-right: 8px;
  }

  .vue-tel-input-vuetify {
    display: flex;
    align-items: center;

    .country-code {
      width: 35%;
    }

    li.last-preferred {
      border-bottom: 1px solid #cacaca;
    }

    .v-text-field {
      .v-select__selections {
        position: relative;
        .vti__flag {
          position: absolute;
          margin-left: 18px;
        }
      }
      &--outlined {
        .v-select__selections {
          .vti__flag {
            margin-left: auto;
          }
        }
      }
    }
    .v-field__input{
      input{
        min-width:0 !important
      }
    }
  }
</style>
