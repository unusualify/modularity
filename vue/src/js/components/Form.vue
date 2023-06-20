<template>
  <v-form
    :id="id"
    :ref="reference"
    v-model="validForm"
    @submit="submit"
    >
    <v-container>
      <v-row>
        <v-col v-if="hasStickyFrame"
          v-bind="stickyColumnAttrs"
          class="d-flex flex-column"
          style="position:sticky;"
          >
          <div class="d-flex flex-column align-items-center ml-auto mr-auto" style="position:sticky;top:100px;">
            <slot v-if="hasSubmit && stickyButton"
              name="submitButton"
              :attrs="{}"
              >
              <ue-btn :form="id" type="submit" width="60%" >
                {{ $t('submit') }}
              </ue-btn>
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
        </v-col>
        <v-col v-bind="formColumnAttrs">
          <v-row>
            <v-col cols="12" v-if="formTitle">
              <div class="text-h8 pt-2 text-primary font-weight-bold">
                <!-- {{ tableTitle }} -->
                {{ ($te(formTitle) ? $t(formTitle) : formTitle) }}
              </div>
            </v-col>
            <v-col>
              <v-custom-form-base
                id="treeview-slot"

                :row="rowAttribute"
                v-model="model"
                v-model:schema="inputSchema"

                @update="handleUpdate"
                @input="handleInput"
                @resize="handleResize"
                @blur="handleBlur"
                @click="handleClick"

                >
                <template v-slot:[`slot-inject-prepend-key-treeview-slot-permissions`]="{open}" >
                  <v-icon color="blue">
                      {{open ? 'mdi-folder-open' : 'mdi-folder'}}
                  </v-icon>
                </template>
                <template #slot-inject-label-key-treeview-slot-permissions="{item}" >
                  <span class="caption" >
                    {{item.name.toUpperCase()}}
                  </span>
                </template>
              </v-custom-form-base>
            </v-col>
          </v-row>

        </v-col>
      </v-row>
    </v-container>

    <v-container v-if="hasSubmit && !stickyButton">
      <!-- <v-spacer></v-spacer> -->
      <slot name="submitButton"
        :attrs="{
          validForm
        }"
        >
          <div class="text-right">
            <div>
              <v-btn type="submit" :disabled="!validForm">
                {{ buttonText ? ($te(buttonText) ? $t(buttonText) : buttonText) : $t('submit') }}
              </v-btn>
            </div>
          </div>
      </slot>
    </v-container>

    <v-container>
        <v-text-field
          v-if="loading"
          color="success"
          loading
          disabled
        />
    </v-container>
  </v-form>
</template>

<script>
import { mapState } from 'vuex'
import { FORM, ALERT } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'
import api from '@/store/api/form'

import logger from '@/utils/logger'

import { useInputHandlers } from '@/hooks/input-handlers.js'
import { useValidations } from '@/hooks/validations.js'

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
    schema: {
      type: Object,
      default () {
        return {}
      }
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
    async: {
      type: Boolean,
      default: true
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
    buttonText: {
      type: String
    },
    actionUrl: {
      type: String
    },
    formTitle: {
      type: String
    }
  },
  setup (props, context) {
    const inputHandlers = useInputHandlers()
    const validations = useValidations()

    // const states = {

    // }

    return {
      ...inputHandlers,
      ...validations
    }
  },
  data () {
    return {
      id: Math.ceil(Math.random() * 1000000) + '-form',

      // cascadeSelectables: this.$lodash.omitBy((this.issetSchema ? this.schema : this.$store.state.form.inputs),
      //   o => !(o.type === 'select' && o.hasOwnProperty('parent'))
      // )
      // model: {}

      validForm: false
    }
  },

  beforeCreate () {

  },

  mounted () {

  },
  created () {

  },

  watch: {
    model (newValue, oldValue) {
      // __log('model watcher', newValue.country_id, oldValue.country_id)
    }
  },

  computed: {
    issetModel () {
      return Object.keys(this.modelValue).length > 0
    },
    issetSchema () {
      return Object.keys(this.schema).length > 0
    },
    hasStickyFrame () {
      return this.stickyFrame || this.stickyButton
    },

    inputSchema: {
      get () {
        // return this.makeCascadeSelect(
        //   this.invokeRuleGenerator(this.issetSchema ? this.schema : this.$store.state.form.inputs)
        // )
        // return this.syncCascadeSelects(
        //   this.invokeRuleGenerator(this.issetSchema ? this.schema : this.$store.state.form.inputs)
        // )
        return this.invokeRuleGenerator(
          this.issetSchema ? this.schema : this.$store.state.form.inputs
        )
      },
      set (value) {
        __log('inputSchema setter', value.city_id.items)
      }
    },

    defaultItem: {
      get () {
        return this.issetModel ? this.modelValue : this.$store.state.form.editedItem
      },
      set (value) {
        __log('defaultItem setter', value)
      }
    },
    model: {
      get () {
        return this.defaultItem
      },
      set (value) {
        // __log(this.inputSchema)
      }
    },

    loading: {
      get () {
        return this.actionUrl ? false : this.$store.state.form.loading
      },
      set (value) {
        __log('loading setter', value)
      }
    },
    errors: {
      get () {
        return this.actionUrl ? this.errors : this.$store.state.form.errors
      },
      set (value) {
        __log('errors setter', value)
      }
    },

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
    handleInput (v) {
      const { on, key, value, obj } = v
      // __log(
      //   'handleInput',
      //   on,
      //   v
      // )

      if (on === 'input' && !!key && !!value) {
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
      const fields = {}
      // __log(
      //   this.defaultItem,
      //   this.model,
      //   this.editedItem
      // )
      Object.keys(this.defaultItem).forEach((key, i) => {
        fields[key] = (this.model[key] == null || this.defaultItem[key] != '')
          ? this.defaultItem[key]
          : this.model[key]
      })

      if (this.model.id) { fields.id = this.model.id }

      if (this.actionUrl) {
        this.errors = []
        this.loading = true

        const method = fields.hasOwnProperty('id') ? 'put' : 'post'
        const self = this

        api[method](this.actionUrl, fields, function (response) {
          self.loading = false

          if (response.data.hasOwnProperty('errors')) {
            self.errors = response.data.errors
          } else if (response.data.hasOwnProperty('variant') && response.data.variant.toLowerCase() === 'success') {
            self.$store.commit(ALERT.SET_ALERT, { message: response.data.message, variant: response.data.variant })
          }
        }, function (errorResponse) {
          // this.loading = false

          if (errorResponse.response.data.hasOwnProperty('exception')) {
            self.$store.commit(ALERT.SET_ALERT, { message: 'Your submission could not be processed.', variant: 'error' })
          } else {
            self.$store.dispatch(ACTIONS.HANDLE_ERRORS, errorResponse.response.data)
            self.$store.commit(ALERT.SET_ALERT, { message: 'Your submission could not be validated, please fix and retry', variant: 'error' })
          }

          if (errorCallback && typeof errorCallback === 'function') errorCallback(errorResponse.data)
        })
      } else {
        self.$store.commit(FORM.SET_EDITED_ITEM, fields)
        self.$store.dispatch(ACTIONS.SAVE_FORM, { item: null, callback, errorCallback })
      }
    },

    submit (e) {
      // __log(this.validForm)
      if (this.validForm) {
        if (this.async) {
          e.preventDefault() // don't perform submit action (i.e., `<form>.action`)
          this.saveForm()
        }
      } else {
        e.preventDefault() // don't perform submit action (i.e., `<form>.action`)
      }
      // this.$v.$touch()
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
    }
  }

}
</script>

<style>

</style>
