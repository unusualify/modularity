<template>
  <v-sheet>
    <v-form
      :id="id"
      :ref="reference"
      :action="actionUrl"
      method="POST"

      :class="formClass"

      v-model="validForm"
      @submit="submit"
      >
      <v-sheet class="d-flex">

        <v-sheet class=" w-100">
          <!-- <div class="text-h8 pt-5 pb-10 text-primary font-weight-bold" v-if="formTitle && false">
            {{ ($te(formTitle) ? $t(formTitle).toLocaleUpperCase($i18n.locale.toUpperCase()) : formTitle.toLocaleUpperCase($i18n.locale.toUpperCase())) }}
          </div> -->
          <ue-title v-if="title" :classes="['px-0']">
            <div class="d-flex">
              <div class="me-auto">
                {{ ($te(title)
                      ? $t(title).toLocaleUpperCase($i18n.locale.toUpperCase())
                      : title.toLocaleUpperCase($i18n.locale.toUpperCase()))
                }}
              </div>
              <slot name="headerRight">
                <!-- <v-btn
                    class=""
                    variant="text"
                    icon="$close"
                    density="compact"
                  ></v-btn> -->
              </slot>
            </div>
          </ue-title>
          <v-custom-form-base
            id="ue-form-wrapper"
            class="pt-5"

            v-model="model"
            v-model:schema="inputSchema"
            :row="rowAttribute"

            @update="handleUpdate"
            @input="handleInput"
            @resize="handleResize"
            @blur="handleBlur"
            @click="handleClick"
            >
            <!-- <template v-slot:[`slot-inject-prepend-key-treeview-slot-permissions`]="{open}" >
              <v-icon color="blue">
                  {{open ? 'mdi-folder-open' : 'mdi-folder'}}
              </v-icon>
            </template>
            <template #slot-inject-label-key-treeview-slot-permissions="{item}" >
              <span class="caption" >
                {{item.name.toUpperCase()}}
              </span>
            </template> -->
          </v-custom-form-base>
        </v-sheet>
        <div v-if="hasStickyFrame"
          class="d-flex flex-column mx-5"
          style="position:sticky;"
          >
          <div class="d-flex flex-column align-items-center ml-auto mr-auto" style="position:sticky;top:100px;">
            <slot v-if="hasSubmit && stickyButton" name="submit"
              v-bind="{
                validForm: validForm || !serverValid,
                buttonDefaultText
              }"
              >
              <v-btn type="submit" :disabled="!(validForm || !serverValid)" class="ml-auto">
                {{ buttonDefaultText }}
              </v-btn>
            </slot>
            <slot name="stickyBody" :attrs="{}">
              <!-- <v-card class="mt-6" height="">
                      <div>
                          <v-icon>mdi-camera</v-icon>
                          <h3>Upload</h3>
                      </div>
              </v-card> -->
            </slot>
          </div>
          <!-- <v-spacer></v-spacer> -->
        </div>
      </v-sheet>

      <v-sheet class="d-flex pt-6" v-if="hasSubmit && !stickyButton">
        <slot name="submit"
          v-bind="{
            validForm: validForm || !serverValid,
            buttonDefaultText
          }">
          <v-btn type="submit" :disabled="!(validForm || !serverValid)" class="ml-auto mb-5">
            {{ buttonDefaultText }}
          </v-btn>
        </slot>
      </v-sheet>

      <v-sheet  v-if="hasSubmit && !stickyButton">
        <v-progress-linear
          v-if="loading"
          indeterminate
          color="green"
        />
      </v-sheet>

    </v-form>
  </v-sheet>
</template>

<script>
import { computed } from 'vue'
import { mapState } from 'vuex'
import { FORM, ALERT } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'
import api from '@/store/api/form'

import { useI18n } from 'vue-i18n'

import logger from '@/utils/logger'
import { getModel, getSubmitFormData } from '@/utils/getFormData.js'

import { useInputHandlers, useValidation } from '@/hooks'
import { redirector } from '@/utils/response'
import cloneDeep from 'lodash/cloneDeep'

// Helper & Partial Functions
const minLen = l => v => (v && v.length >= l) || `min. ${l} Characters`
const maxLen = l => v => (v && v.length <= l) || `max. ${l} Characters`
const required = msg => v => !!v || msg
const requiredArray = (msg, l = 1) => v => (Array.isArray(v) && v.length > l) || msg

// Rules
const rules = {
  requiredEmail: required('E-mail is required'),
  requiredSel: required('Selection is required'),
  requiredSelMult: requiredArray('2 Selections are required'),
  max12: maxLen(12),
  min6: minLen(6),
  validEmail: v => /.+@.+\..+/.test(v) || 'E-mail must be valid'
}

export default {
  name: 'ue-form',
  props: {
    modelValue: {
      type: Object,
      default () {
        return {}
      }
    },
    formClass: {
      type: [Array, String],
      default: 'px-theme pb-theme'
    },
    actionUrl: {
      type: String
    },
    title: {
      type: String
    },
    schema: {
      type: Object,
      default () {
        return {}
      }
    },
    async: {
      type: Boolean,
      default: true
    },
    buttonText: {
      type: String
    },
    hasSubmit: {
      type: Boolean,
      default: false
    },
    stickyFrame: {
      type: Boolean,
      default: false
    },
    stickyButton: {
      type: Boolean,
      default: false
    },
    rowAttribute: {
      type: Object,
      default () {
        return {
          noGutters: false
          // justify:'center',
          // align:'center'
        }
      }
    },
    slots: {
      type: Object,
      default () {
        return {}
      }
    }
  },
  setup (props, context) {
    const inputHandlers = useInputHandlers()
    const validations = useValidation()

    const { t, te } = useI18n({ useScope: 'global' })

    const buttonDefaultText = computed(() => props.buttonText ? (te(props.buttonText) ? t(props.buttonText) : props.buttonText) : t('submit'))
    return {
      ...inputHandlers,
      ...validations,
      buttonDefaultText
    }
  },
  data () {
    return {
      id: Math.ceil(Math.random() * 1000000) + '-form',

      formLoading: false,
      formErrors: {},
      inputs: this.invokeRuleGenerator(this.schema),

      // validForm: false

      issetModel: Object.keys(this.modelValue).length > 0,
      issetSchema: Object.keys(this.schema).length > 0,
      hasStickyFrame: this.stickyFrame || this.stickyButton,

      model: this.issetModel ? this.modelValue : this.editedItem,
      inputSchema: null,
      defaultItem: null
    }
  },

  created () {
    this.inputSchema = this.issetSchema
      ? this.inputs
      : this.invokeRuleGenerator(
        this.$store.state.form.inputs
      )

    this.defaultItem = this.issetSchema ? getModel(this.inputSchema) : this.$store.getters.defaultItem

    this.model = getModel(
      this.inputSchema,
      this.issetModel ? this.modelValue : this.editedItem,
      this.$store.state
    )
  },

  watch: {
    // model (newValue, oldValue) {
    //   __log('model watcher', newValue, oldValue)
    //   // this.resetValidation()
    // },
    model: {
      handler (value, oldValue) {
        // __log('model watcher', value, oldValue)
      },
      deep: true
    },
    editedItem (newValue, oldValue) {
      // __log('editedItem', newValue)
      if (!this.issetModel) {
        // __log('editedItem watcher', getModel(this.inputSchema, newValue, this.$store.state))
        this.model = getModel(this.inputSchema, newValue, this.$store.state)
      }
      // this.resetValidation()
    },
    errors (newValue, oldValue) {
      this.setSchemaErrors(newValue)
    }
  },

  computed: {
    inputSchema_: {
      get () {
        __log('inputSchema getter')
        return this.issetSchema
          ? this.inputs
          : this.invokeRuleGenerator(
            this.$store.state.form.inputs
          )
      },
      set (value) {
        // this._schema = value
        __log('inputSchema setter', value, this.inputSchema)
      }
    },
    inputSchema__ () {
      return this.issetSchema
        ? this.inputs
        : this.invokeRuleGenerator(
          this.$store.state.form.inputs
        )
    },

    defaultItem_ () { return this.issetSchema ? getModel(this.inputSchema) : this.$store.getters.defaultItem },
    // defaultItem: {
    //   get () {
    //     __log(this.issetModel)
    //     return this.issetModel ? this.modelValue : this.$store.state.form.editedItem
    //   },
    //   set (value) {

    //   }
    // },
    model_: {
      get () {
        return getModel(
          this.inputSchema,
          this.issetModel ? this.modelValue : this.$store.state.form.editedItem,
          this.$store._state.data
        )
      },
      set (value) {
        __log('model setter', value)
        // this.resetValidation()
      }
    },
    loading () {
      return this.actionUrl ? this.formLoading : this.$store.state.form.loading
      // get () {
      //   return this.actionUrl ? this._loading : this.$store.state.form.loading
      // },
      // set (value) {
      //   __log('loading setter', value, this.loading)
      // }
    },
    errors () {
      return this.actionUrl ? this.formErrors : this.$store.state.form.errors
    },
    // errors_: {
    //   get () {
    //     return this.actionUrl ? this.formErrors : this.$store.state.form.errors
    //   },
    //   set (value) {
    //     for (const name in value) {
    //       __log('errors setter', value[name][0], this.inputSchema)
    //       this.inputSchema[name].errorMessages = value[name][0]
    //     }
    //     __log('errors setter', value, this.errors)
    //   }
    // },

    reference () {
      return 'ref-' + this.id
    },
    formColumnAttrs () {
      return this.hasStickyFrame
        ? {
            cols: '12',
            sm: '12',
            md: '12',
            lg: '8',
            xl: '6',
            'order-lg': '0',
            'order-xl': '0'
          }
        : {
            cols: '12'
          }
    },
    stickyColumnAttrs () {
      return {
        cols: '12',
        sm: '12',
        md: '12',
        lg: '4',
        xl: '6',
        'order-lg': '1',
        'order-xl': '1'
      }
    },
    ...mapState({
      editedItem: state => state.form.editedItem,
      serverValid: state => state.form.serverValid
      // loading: state => state.form.loading,
      // errors: state => state.form.errors
    })
  },

  methods: {
    validate () {
      return this.$refs[this.reference].validate()
    },
    resetValidation () {
      this.$refs[this.reference].resetValidation()
    },
    handleInput (v, s) {
      const { on, key, value, obj } = v

      if (on === 'input' && !!key && !!value && !this.serverValid) {
        __log(
          'handleInput',
          on,
          key
        )
        this.$store.commit(FORM.SET_SERVER_VALID, true)
        this.resetSchemaError(key)
        // __log(obj.schema, key)
      }
    },
    handleUpdate (v) {
      // __log('handleUpdate', v)
    },
    handleResize (v) {
      // __log('handleResize', v)
    },
    handleBlur (v) {
      // __log('handleBlur', v)
    },
    saveForm (callback = null, errorCallback = null) {
      if (this.actionUrl) {
        this.formErrors = {}
        this.formLoading = true

        const formData = getSubmitFormData(this.inputSchema, this.model, this.$store._state.data)
        const method = Object.prototype.hasOwnProperty.call(formData, 'id') ? 'put' : 'post'
        const self = this

        api[method](this.actionUrl, formData, function (response) {
          self.formLoading = false
          if (Object.prototype.hasOwnProperty.call(response.data, 'errors')) {
            self.$store.commit(FORM.SET_SERVER_VALID, false)

            self.formErrors = response.data.errors
          } else if (Object.prototype.hasOwnProperty.call(response.data, 'variant')) {
            self.$store.commit(FORM.SET_SERVER_VALID, false)
            self.$store.commit(ALERT.SET_ALERT, { message: response.data.message, variant: response.data.variant })
          }

          redirector(response.data)
          if (Object.prototype.hasOwnProperty.call(response.data, 'redirector')) {
            // self.$store.commit(ALERT.SET_ALERT, { message: response.data.message, variant: response.data.variant })
            setTimeout(function (url) {
              window.location.href = url
            }, 2000, response.data.redirector)
          }

          if (callback && typeof callback === 'function') callback(response.data)
        }, function (response) {
          self.formLoading = false
          if (Object.prototype.hasOwnProperty.call(response.data, 'exception')) {
            self.$store.commit(ALERT.SET_ALERT, { message: 'Your submission could not be processed.', variant: 'error' })
          } else {
            self.$store.dispatch(ACTIONS.HANDLE_ERRORS, response.response.data)
            self.$store.commit(ALERT.SET_ALERT, { message: 'Your submission could not be validated, please fix and retry', variant: 'error' })
          }

          if (errorCallback && typeof errorCallback === 'function') errorCallback(response.data)
        })
      } else {
        // this.$store.commit(FORM.SET_EDITEM_ITEM, getModel(this.inputSchema, cloneDeep(this.model)))
        // __log(getModel(this.inputSchema, cloneDeep(this.model)))
        const self = this
        this.$nextTick(function () {
          self.$store.dispatch(ACTIONS.SAVE_FORM, { item: null, callback, errorCallback })
        })
      }
    },
    submit (e, callback = null, errorCallback = null) {
      if (this.validForm) {
        if (this.async) {
          e && e.preventDefault() // don't perform submit action (i.e., `<form>.action`)
          if (!this.actionUrl) {
            this.$store.commit(FORM.SET_EDITED_ITEM, this.model)
            this.$nextTick(() => {
              this.saveForm(callback, errorCallback)
            })
          } else {
            this.saveForm(callback, errorCallback)
          }
        }
      } else {
        e && e.preventDefault() // don't perform submit action (i.e., `<form>.action`)
      }
    },
    handleClick (val) {
      // logger(val)

      const { on, key, obj, params } = val
      // check 'click' is from prependInner Icon (Print) at key 'subgroups.content'
      // if (on === 'click' && key === 'subgroups.content' && (params && params.tag) === 'prepend-inner') {
      //   window.print()
      // }
      // check 'click' is from from appendIcon at key password

      // for click slot handlers
      // __log(params, val)
      if (on === 'click' && params && params.tag) {
        // toggle visibility of password control
        this.invokeInputClickHandler(obj, params.tag)
        // obj.schema.type === 'password' ? obj.schema.appendInnerIcon = '$non-visibility' : obj.schema.appendInnerIcon = '$visibility'
        // obj.schema.type = obj.schema.type === 'password' ? 'text' : 'password'
      }
    },
    setSchemaErrors (errors) {
      // __log(errors, this.inputSchema)
      const _errors = {}
      for (const name in errors) {
        const pattern = /(\w+)\.(\w+)/
        const matches = name.match(pattern)
        if (matches) {
          const _name = matches[1]
          const _locale = matches[2]
          if (!__isset(_errors[_name])) {
            _errors[_name] = []
          }
          _errors[_name][_locale] = errors[name]
        } else {
          _errors[name] = errors[name]
        }
      }
      for (const name in _errors) {
        this.inputSchema[name].errorMessages = _errors[name]
      }
    },
    resetSchemaError (key) {
      this.inputSchema[key].errorMessages = []
    }
  }

}
</script>

<style>

</style>
