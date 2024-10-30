<template>
  <v-sheet>
    <v-form
      :id="id"
      :ref="reference"
      :action="actionUrl"
      method="POST"
      :class="formClass"
      v-model="validModel"
      @submit="submit"
      >
      <input v-if="!async" type="hidden" name="_token" :value="csrf"/>

      <v-sheet class="d-flex">
        <v-sheet class="w-100">
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
            :id="`ue-wrapper-${id}`"
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
            <template
              v-for="(_slot, key) in formSlots"
              :key="key"
              v-slot:[`slot-inject-${_slot.name}-key-ue-wrapper-${id}-${_slot.inputName}`]="_slotData"
              >
              <template v-if="_slot.type == 'form'">
                <v-custom-form-base
                  :id="`ue-wrapper-${id}-${_slot.name}`"
                  v-model="model"
                  v-model:schema="_slot.schema"
                  :row="rowAttribute"

                  >

                </v-custom-form-base>
              </template>
              <template v-else-if="_slot.type == 'recursive-stuff'">
                <ue-recursive-stuff
                  v-for="(context, i) in _slot.context.elements"
                  :key="i"
                  :configuration="context"
                  :bindData="_slotData">
                </ue-recursive-stuff>
              </template>
              <!-- <div>
                {{ $log(_slot, _slotData) }}
                Hello
              </div> -->
            </template>
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
                validForm: validModel || !serverValid,
                buttonDefaultText
              }"
              >
              <v-btn type="submit" :disabled="!(validModel || !serverValid)" class="ml-auto">
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
            validForm: validModel || !serverValid,
            buttonDefaultText
          }">
          <v-btn type="submit" :disabled="!(validModel || !serverValid) || loading" class="ml-auto mb-5">
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

      <v-sheet class="testclass">
        <slot name="bottom" v-bind="{}"></slot>
      </v-sheet>

    </v-form>
  </v-sheet>
</template>

<script>
import { computed, provide } from 'vue'
import { mapState } from 'vuex'
import { FORM, ALERT } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'
import api from '@/store/api/form'
import { useInputHandlers, useValidation } from '@/hooks'

import { useI18n } from 'vue-i18n'

import { getModel, getSubmitFormData, getSchema, handleInputEvents, handleEvents } from '@/utils/getFormData.js'

import { redirector } from '@/utils/response'
import { cloneDeep } from 'lodash-es'

export default {
  name: 'ue-form',
  emits: [
    'update:valid',
    'update:modelValue',
    'input'
  ],
  props: {
    modelValue: {
      type: Object,
      default () {
        return {}
      }
    },
    formClass: {
      type: [Array, String],
      default: 'px-12 pb-12'
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
    },
    valid: null
  },
  setup (props, context) {
    const inputHandlers = useInputHandlers()
    const validations = useValidation(props)

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
      // inputs: this.invokeRuleGenerator(this.schema),

      issetModel: Object.keys(this.modelValue).length > 0,
      issetSchema: Object.keys(this.schema).length > 0,
      hasStickyFrame: this.stickyFrame || this.stickyButton,

      model: this.issetModel ? this.modelValue : this.editedItem,

      rawSchema: null,
      inputSchema: null,
      defaultItem: null,
      manualValidation: false
    }
  },

  provide() {
    // use function syntax so that we can access `this`
    return {
      manualValidation: computed(() => this.manualValidation),
      submitForm: computed(() => this.submit)
    }
  },

  created () {
    this.rawSchema = this.issetSchema ? this.schema : this.$store.state.form.inputs
    this.defaultItem = this.issetSchema ? getModel(this.rawSchema) : this.$store.getters.defaultItem

    this.model = getModel(
      this.rawSchema,
      this.issetModel ? this.modelValue : this.editedItem,
      this.$store.state
    )

    this.inputSchema = this.invokeRuleGenerator(getSchema(this.rawSchema, this.model))

    this.resetSchemaErrors()
  },

  watch: {
    // model (newValue, oldValue) {
    //   __log('model watcher', newValue, oldValue)
    //   // this.resetValidation()
    // },

    model: {
      handler (value, oldValue) {
        this.$emit('update:modelValue', value)
      },
      deep: true
    },
    editedItem (newValue, oldValue) {
      if (!this.issetModel) {
        this.regenerateInputSchema(newValue)
        this.model = getModel(this.rawSchema, newValue, this.$store.state)
      }
      // this.resetValidation()
    },
    errors (newValue, oldValue) {
      this.setSchemaErrors(newValue)
    },
    validModel(newValue, oldValue) {
      this.$emit('update:valid', newValue)
    },
    schema: {
      handler (value, oldValue) {
        // __log('schema watcher', value, JSON.stringify(value) !== JSON.stringify(oldValue))
      },
      deep: true
    },
    inputSchema: {
      handler (value, oldValue) {
        // __log('inputSchema watch', value)
      },
      deep: true
    },
    rawSchema: {
      handler (value, oldValue, ...other) {
        let oldModel = cloneDeep(this.model)
        let model = getModel(value, this.model, this.$store.state)
        if(JSON.stringify(Object.keys(__dot(model)) )!== JSON.stringify(Object.keys(__dot(oldModel)))){
          this.model = model
          this.inputSchema = this.invokeRuleGenerator(getSchema(value, this.model))
        }
      },
      deep: true
    },
  },

  computed: {
    formSlots () {
      const slots = []

      Object.values(this.issetSchema ? this.schema : this.$store.state.form.inputs).forEach((schema, index) => {
        if (Object.prototype.hasOwnProperty.call(schema, 'slots') && Object.keys(schema.slots).length > 0) {
          Object.keys(schema.slots).forEach((slotName) => {
            slots.push({
              name: slotName,
              inputName: schema.name,
              type: 'recursive-stuff',
              context: schema.slots[slotName]
            })
          })
        } else if (Object.prototype.hasOwnProperty.call(schema, 'slotable')) {
          slots.push({
            name: schema.slotable.name,
            inputName: schema.slotable.slotTo,
            selfName: schema.name,
            type: 'form',
            schema: cloneDeep(this.invokeRuleGenerator({
              [schema.name]: this.$lodash.omit(schema, ['slotable'])
            }))
          })
        }
      })
      return slots
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
    async validate () {
      const result = await this.$refs[this.reference].validate()

      return result
    },
    resetValidation () {
      this.$refs[this.reference].resetValidation()
      // this.valid = null
    },
    reset () {
      this.$refs[this.reference].reset()
    },
    handleInput (v, s) {
      const { on, key, obj } = v
      if (on === 'input' && !!key) {
        if (!this.serverValid) {
          this.resetSchemaError(key)
        }
        this.handleEvent(obj)
      }

      this.$emit('input', v)
    },
    handleEvent (obj) {
      handleEvents(this.model, this.inputSchema, obj.schema)
      // const { _fields: newModel, moduleSchema: newSchema } = handleInputEvents(obj.schema.event, this.model, this.inputSchema, obj.key)
      // this.model = newModel
      // this.inputSchema = newSchema
    },
    // handleInputSlot (v, s) {
    //   const { on, key, value, obj } = v
    //   __log('handleInputSlot', v, on, key, value, obj)
    // },
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

        const formData = getSubmitFormData(this.rawSchema, this.model, this.$store._state.data)
        // console.log(formData)
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
        const self = this
        this.$nextTick(function () {
          self.$store.dispatch(ACTIONS.SAVE_FORM, { item: null, callback, errorCallback })
        })
      }
    },
    sendSync (e){
      e && e.preventDefault()
      // console.log(this.modelValue);
      // console.log(this.convertToNestedFormData(this.modelValue).values)

      const form = document.createElement('form');
      form.method = 'POST';
      form.action = this.actionUrl;
      form.enctype = 'multipart/form-data';

      let formData = this.convertToNestedFormData(this.modelValue);

      for (const [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
      }

      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = '_token';
      input.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      form.appendChild(input);

      document.body.appendChild(form);
      form.submit();
    },
    convertToNestedFormData(obj, parentKey = '') {
      const formData = new FormData();
      for (const [key, value] of Object.entries(obj)) {
        const formKey = parentKey ? `${parentKey}[${key}]` : key;

        if (value === null || value === undefined) {
          continue;
        } else if (typeof value === 'object') {
          if (Array.isArray(value)) {
            value.forEach((item, index) => {
              if (typeof item === 'object' && item !== null) {
                const nestedFormData = this.convertToNestedFormData(item, `${formKey}[${index}]`);
                for (const [nestedKey, nestedValue] of nestedFormData.entries()) {
                  formData.append(nestedKey, nestedValue);
                }
              } else {
                formData.append(`${formKey}[${index}]`, item);
              }
            });
          } else {
            const nestedFormData = this.convertToNestedFormData(value, formKey);
            for (const [nestedKey, nestedValue] of nestedFormData.entries()) {
              formData.append(nestedKey, nestedValue);
            }
          }
        } else {
          formData.append(formKey, value);
        }
      }
      return formData;
    },
    submit (e, callback = null, errorCallback = null) {
      if (this.validModel) {
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
        }else{
          this.sendSync(e);

        }
      } else {
        e && e.preventDefault() // don't perform submit action (i.e., `<form>.action`)
      }
    },
    handleClick (val) {
      // logger(val)

      const { on, obj, params } = val
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
        if( this.inputSchema[name]) this.inputSchema[name].errorMessages = _errors[name]
      }
    },
    resetSchemaError (key) {
      this.inputSchema[key].errorMessages = []
    },
    resetSchemaErrors () {
      for (const key in this.inputSchema) {
        this.resetSchemaError(key)
      }
    },

    updatedSlotModel (value, inputName) {
      __log(this.model, value, inputName)
    },

    regenerateInputSchema (newItem) {
      // #TODO regenerate inputschema for prefix regex pattern
      // for (const key in this.rawSchema) {
      //   if (__isset(this.rawSchema[key].event)) {

      //   }
      // }
    }
  }

}
</script>

<style>

</style>
