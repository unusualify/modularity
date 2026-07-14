<template>
  <div class="locale">
    <template v-if="languages && languages.length && languages.length > 0">
      <template v-for="language in languages" :key="language.value">
        <component
          v-if="shouldRenderLocaleInput(language)"
          v-bind:is="`${type}`"
          v-bind="$lodash.omit(attributesPerLang[`${language.value}`], ['class'])"
          :obj="obj"
          :localeKey="language.value"
          :class="[
            attributesPerLang[`${language.value}`].class ?? '',
            shouldHideInactiveLocale(language) ? 'd-none' : ''
          ]"
          @update:modelValue="modelUpdated($event, language.value)"
          >
          <template v-slot:appendx>
            <v-chip v-if="languages.length > 1" @click="updateLocale(currentLocale)">
              {{ displayedLocale }}
              <v-tooltip
                activator="parent"
                location="top"
                >
                {{ $t('fields.switch-language') }}
              </v-tooltip>
            </v-chip>
          </template>
          <template v-slot:label="labelScope">
            {{ labelScope.label }}
            <v-chip v-if="labelScope.label && (labelScope.isActive) &&  (labelScope.isActive.value || labelScope.isFocused.value)" style="font-size: var(--v-field-label-scale); height: calc(var(--v-field-label-scale) * 1.5);"
              size="x-small"
              density="compact"
              color="primary"
              class="ml-1"
            >
              {{ displayedLocale }}
            </v-chip>
            <v-chip v-else-if="labelScope.label && !$isset(labelScope.isActive)" style=""
              size="x-small"
              density="compact"
              color="primary"
              class="ml-1"
            >
              {{ displayedLocale }}
            </v-chip>
            <v-chip v-else-if="!labelScope.label && ['v-select', 'v-combobox', 'v-autocomplete', 'v-input-tag', 'v-input-editor', 'VInputEditor'].includes(type)" style=""
              size="x-small"
              density="compact"
              color="primary"
              class="ml-1"
            >
              {{ displayedLocale }}
            </v-chip>
          </template>
          <slot></slot>
        </component>
      </template>
    </template>
    <template v-else>
      <component
        v-bind:is="`${type}`"
        :name="attributes.name"
        :obj="obj"
        v-bind="attributesNoLang()"
        @change="updateValue(false, ...arguments)"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
        >
        <slot></slot>
      </component>
    </template>
  </div>
</template>

<script>
import { LANGUAGE } from '@/store/mutations'
import { useInput, makeInputProps, makeInputEmits, useCastAttributes, useLocale } from '@/hooks'

import { cloneDeep, omit, isObject } from 'lodash-es'

export default {
  name: 'v-input-locale',
  emits: [...makeInputEmits],
  setup (props, context) {
    const { castObjectAttributes } = useCastAttributes()
    const locale = useLocale(props)

    return {
      ...useInput(props, context),
      castObjectAttributes,
      ...locale
    }
  },
  props: {
    ...makeInputProps(),
    type: {
      type: String,
      default: 'text'
    },
    attributes: {
      type: Object,
      default: function () {
        return {}
      }
    },
    initialValues: {
      type: Object,
      default: function () {
        return {}
      }
    },
    translatedProps: {
      type: Array,
      default: function () {
        return []
      }
    }
  },
  watch: {
    modelValue (value) {
      if (isObject(value)) {
        for (const locale in value) {
          this.input[locale] = value[locale]
        }
      }
    },
    attributes: {
      handler (value, oldValue) {

      },
      deep: true
    }
  },
  computed: {
    isHeavyInput () {
      return ['VInputEditor', 'v-input-editor', 'input-editor'].includes(this.type)
    },
    input: {
      get () {
        return this.modelValue
      },
      set (val, old) {
        this.inputOnSet(val, old)
        this.updateModelValue(val)
        // context.emit('update:modelValue', val)
      }
    },
    attributesPerLang: function () {
      // const language = this.languages.find(l => l.value === lang)

      const localeAttributes = {}
      // const attributes = cloneDeep(this.attributes)
      const errorMessages = this.attributes.errorMessages ?? {}

      this.languages.forEach((language) => {
        let attributes = cloneDeep(omit(this.attributes, ['errorMessages']))

        attributes = {
          ...this.castObjectAttributes(attributes, {localeParameter: language.value}),
          ...{
            rules: attributes.rules ?? []
          }
        }

        // for textfields set initial values using the initialValues prop
        // if (this.initialValues && typeof this.initialValues === 'object' && this.initialValues[lang]) {
        //   attributes.initialValue = this.initialValues[lang]
        // } else if (!attributes.initialValue) {
        //   attributes.initialValue = ''
        // }
        attributes.required = !!language.published && this.isRequired
        attributes.name = `${attributes.name}[${language.value}]`

        // if items is an object, and has a property for the current language, set the items to the property value
        if(!!attributes.items && isObject(attributes.items)) {
          if(attributes.items[language.value]) {
            attributes.items = attributes.items[language.value];
          }else {
            attributes.items = [];
          }
        }

        if (__isset(errorMessages[language.value])) {
          attributes.errorMessages = errorMessages[language.value]
          attributes.error = false
        }

        if (this.input) {
          attributes.modelValue = this.input[language.value]
        } else if (attributes.default) {
          // attributes.modelValue = attributes.default
        }

        attributes['originalProps'] = {}
        if (this.translatedProps.length > 0) {
          this.translatedProps.forEach(prop => {
            attributes['originalProps'][prop] = attributes[prop] ?? null
            attributes[prop] = attributes[prop]?.[language.value] ?? null
          })
        }

        localeAttributes[language.value] = attributes
      })

      // this.attributes.errorMessages = new Proxy({})
      return localeAttributes
    }
  },
  data () {
    return {
      isCustomForm: false,
      isRequired: this.attributes.required,

      inputObject: this.modelValue

    }
  },
  mounted () {
    this.isCustomForm = this.$root.$refs.customForm !== undefined
    this.isRequired = this.attributes.required ?? false
  },
  created () {

  },
  methods: {
    shouldRenderLocaleInput (language) {
      if (!this.isHeavyInput) {
        return true
      }

      return language.value === this.currentLocale.value || this.isCustomForm
    },
    shouldHideInactiveLocale (language) {
      if (this.isHeavyInput) {
        return false
      }

      return !(language.value === this.currentLocale.value || this.isCustomForm)
    },
    attributesPerLang_: function (lang) {
      const language = this.languages.find(l => l.value === lang)

      const attributes = cloneDeep(this.attributes)
      // for textfields set initial values using the initialValues prop
      // if (this.initialValues && typeof this.initialValues === 'object' && this.initialValues[lang]) {
      //   attributes.initialValue = this.initialValues[lang]
      // } else if (!attributes.initialValue) {
      //   attributes.initialValue = ''
      // }
      attributes.required = !!language.published && this.isRequired
      attributes.name = `${attributes.name}[${lang}]`

      if (this.input) {
        attributes.modelValue = this.input[lang]
      }

      return attributes
    },
    attributesNoLang: function () {
      const attributes = cloneDeep(this.attributes)
      // for textfields set initial values using the initialValue prop
      if (this.initialValue) attributes.initialValue = this.initialValue
      return attributes
    },
    updateValue: function (locale, newValue) {
      // if (locale) {
      //   this.$emit('change', {
      //     locale,
      //     value: newValue
      //   })
      // } else {
      //   this.$emit('change', {
      //     value: newValue
      //   })
      // }
    },
    modelUpdated (value, lang) {
      try {
        if (this.input && __isset(this.input[lang])) {
          const input = cloneDeep(this.input);
          input[lang] = value
          this.input = input
          // this.updateModelValue(this.input)
        } else if (this.input && !__isset(this.input[lang]) && value) {
          this.input = {}
          this.input[lang] = value
          this.updateModelValue(this.input)
        } else if (!this.input && value) {
          this.input = {}
          // this.input[lang] = value
          // this.updateModelValue(this.input)
        }
      } catch (error) {
        __log('catch', this.input, lang, value)
      }

      // this.updateValue(lang, value)
    }
  }

}
</script>

<style lang="scss" scoped>

  .input {
    margin-top:35px;
    position: relative;
  }

  .input:empty {
    display:none;
  }

  .input__add {
    position:absolute;
    top:0;
    right:0;
    text-decoration:none;
    // color:$color__link;
  }

  .input__label {
    display:block;
    // color:$color__text;
    margin-bottom:10px;
    word-wrap:break-word;
    position:relative;
  }

  .input__lang {
    border-radius:2px;
    display:inline-block;
    height:15px;
    line-height:15px;
    font-size:10px;
    // color:$color__background;
    text-transform:uppercase;
    // background:$color__icons;
    padding:0 5px;
    position:relative;
    top:-2px;
    margin-left:5px;
    cursor:pointer;
    user-select: none;
    letter-spacing:0;

    &:hover {
      // background:$color__f--text;
    }
  }

  /* Input inline */
  .input__inliner {
    > .input {
      display:inline-block;
      margin-top:0;
      margin-right: 20px;

      .singleCheckbox {
        padding:7px 0 8px 0;
      }
    }
  }

  /* small variant */

  .input--small {
    margin-top:16px;

    .input__label {
      margin-bottom:9px;
      // @include font-small;
    }
  }

</style>
