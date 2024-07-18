<template>
  <div :class="['vue-tel-input-vuetify', $lodash.pick(boundProps, ['wrapperClasses']) ?? getDefault('wrapperClasses')]">
    <div class="d-flex w-100">
      <div style="width=52px;">
        <v-combobox
          :ref="makeReference('countryInput')"
          v-model="countryCode"
          :items="sortedCountries"
          item-title="name"
          item-value="iso2"
          width="52px"
          v-bind="$lodash.pick(boundProps, ['variant', 'menuProps', 'selectClasses', 'selectLabel', 'dense', 'density'])"
          autocomplete="off"
          return-object
          >
          <template #selection="object">
            <!-- {{ $log(object) }} -->
            <div :class="activeCountry.iso2.toLowerCase()" class="vti__flag" />
            <!-- <span class="v-select__selection-text">
              {{ activeCountry.iso2 }}
            </span> -->
          </template>
          <template v-if="false" #item="itemSlot">
            <!-- {{  $log(itemSlot) }} -->
            {{ itemSlot.item.raw.name }}
            <!-- <v-list-item :title="`${itemSlot.item.raw.name} +${itemSlot.item.raw.dialCode}`" @click="itemSlot.props.onClick">
              <template #prepend>
                <span :class="itemSlot.raw.iso2.toLowerCase()" class="vti__flag" />
              </template>
            </v-list-item> -->
            <!-- <span :class="item.iso2.toLowerCase()" class="vti__flag" />
            <span>{{ item.name }} {{ `+${item.dialCode}` }}</span> -->
          </template>
        </v-combobox>
      </div>
      <v-text-field
          :ref="makeReference('phoneInput')"
          type="tel"

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
    </div>
    <!-- <div class="country-code">
    </div> -->
  </div>
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
      }
      this.$emit('validate', this.phoneObject)
      this.$emit('onValidate', this.phoneObject) // Deprecated
    },
    modelValue (val, oldValue) {
      __log('phone.vue modelValue watch', val, this.modelValue, this.phone)
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
    phone (newValue, oldValue) {
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
    this.reset()
  },
  created () {
    if (this.modelValue) {
      this.phone = this.modelValue.trim()
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
    choose (country, toEmitInputEvent = false) {
      // __log('choose()', country, toEmitInputEvent)
      this.activeCountry = country || this.activeCountry || {}
      if (
        this.phone &&
        this.phone[0] === '+' &&
        this.activeCountry.iso2 &&
        this.phoneObject.number.significant
      ) {
        // Attach the current phone number with the newly selected country
        this.phone = PhoneNumber(
          this.phoneObject.number.significant,
          this.activeCountry.iso2
        ).getNumber('international')
      } else if (
        this.inputOptions &&
        this.inputOptions.showDialCode &&
        country
      ) {
        // Reset phone if the showDialCode is set
        this.phone = `+${country.dialCode}`
      }
      if (toEmitInputEvent) {
        this.$emit('input', this.phoneText, this.phoneObject)
        this.$emit('onInput', this.phoneObject) // Deprecated
      }
    },

    reset () {
      // __log('reset()', this.activeCountry)
      if (__isObject(this.activeCountry) && this.activeCountry.iso2) { this.countryCode = this.activeCountry }
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
          this.countryCode = this.activeCountry
          this.$emit('validate', this.phoneObject)
          this.$emit('onValidate', this.phoneObject) // Deprecated
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
      __log(this.phoneText, this.phoneObject)
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
  }
</style>
