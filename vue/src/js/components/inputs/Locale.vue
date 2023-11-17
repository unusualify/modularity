<template>
  <div class="locale">
    <template v-if="languages && languages.length && languages.length > 0">
      <template v-for="language in languages" :key="language.value">
        <component
            v-bind:is="`${type}`"
            :class="[language.value === currentLocale.value || isCustomForm ? '' : 'd-none']"
            v-bind="attributesPerLang(language.value)"

            @update:modelValue="modelUpdated($event, language.value)"
          >
          <template v-slot:append>
            <!-- <v-tooltip
              location="top"
              >
              <template v-slot:activator>
                <v-chip v-if="languages.length > 1" @click="updateLocale(currentLocale)">
                  {{ displayedLocale }}
                </v-chip>
              </template>
              <div>
                {{ $t('fields.generic.switch-language') }}
              </div>
            </v-tooltip> -->

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
          <slot></slot>
        </component>
      </template>
    </template>
    <template v-else>
      <component v-bind:is="`${type}`"
                 :name="attributes.name"
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
import { mapState } from 'vuex'
import { LANGUAGE } from '@/store/mutations'
import { InputMixin, LocaleMixin } from '@/mixins'
import { useInput, makeInputProps } from '@/hooks'

import cloneDeep from 'lodash/cloneDeep'

export default {
  mixins: [InputMixin, LocaleMixin],
  name: 'UELocale',
  setup (props, context) {
    return {
      ...useInput(props, context)
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
    }
  },
  watch: {
    modelValue (value) {
      if (__isObject(value)) {
        // this.input = value
        // __log('locale modelValue', value)
        for (const locale in value) {
          this.input[locale] = value[locale]
        }
      }
    }
  },
  computed: {
    input: {
      get () {
        // __log('Locale.vue inputget', this.modelValue)
        return this.modelValue
      },
      set (val, old) {
        // __log('Locale.vue input set', val, old)
        this.inputOnSet(val, old)
        this.updateModelValue(val)
        // context.emit('update:modelValue', val)
      }
    },
    ...mapState({
      currentLocale: state => state.language.active,
      languages: state => state.language.all
    })
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
    // __log(this.currentLocale, this.languages)
  },
  methods: {
    attributesPerLang: function (lang) {
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
    updateLocale: function (oldValue) {
      this.$store.commit(LANGUAGE.SWITCH_LANG, { oldValue })
      // auto focus new field
      this.$nextTick(function () {
        // const currentLanguageItem = this.$el.querySelector('[data-lang="' + this.currentLocale.value + '"]')

        // if (currentLanguageItem) {
        //   const field = currentLanguageItem.querySelector('input:not([disabled]), textarea:not([disabled]), select:not([disabled])')
        //   if (field) field.focus()
        // }
      })

      // this.$emit('localize', this.currentLocale)
    },
    updateValue: function (locale, newValue) {
      __log('updateValue', locale, newValue)
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
          this.input[lang] = value
          this.updateModelValue(this.input)
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
