// hooks/formatter .js

import { reactive, toRefs, computed, ref, inject } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { omit, isObject } from 'lodash-es'

export const makeInputProps = propsFactory({
  modelValue: {
    type: [Array, Object, String, Number, Boolean],
    default: null
  },
  obj: {
    type: Object,
    default () {
      return {}
    }
  },
  label: {
    type: String,
    default: ''
  },
  hideIfEmpty: {
    type: Boolean,
    default: false
  },
  default: {
    type: [Array, Object, String, Number, Boolean],
    default: null
  },
  protectInitialValue: {
    type: Boolean,
    default: false
  },
  isEditing: {
    type: Boolean,
    default: false
  },
  editable: {
    type: [Boolean, String],
    default: true
  },
  creatable: {
    type: [Boolean, String],
    default: true
  }
})
export const makeInputEmits = [
  'update:modelValue',
  'change',
  'update:preview',
  'focus',
  'blur',
  'click:append',
  'click:appendInner',
  'click:prepend',
  'click:prependInner',
  'click:clear',
  'click:hour',
  'click:minute',
  'click:second',

  'update:input',

]
export const makeInputInjects = ['manualValidation']

// by convention, composable function names start with "use"
export default function useInput (props, context) {
  const { modelValue, obj } = toRefs(props)
  const VInput = ref(null)

  const initialValue = ref(modelValue.value ?? props?.default ?? props?.obj?.schema?.default ?? (Object.prototype.hasOwnProperty.call(props, 'multiple') && props.multiple ? [] : null))

  const states = reactive({
    VInput,
    id: Math.ceil(Math.random() * 1000000) + '-input',
    boundProps: omit(obj.value.schema ?? {}, ['offset', 'order', 'col']),

    initialValue,
    input: computed({
      get: () => {
        let _val = modelValue.value ?? props?.default ?? props?.obj?.schema?.default ?? []

        if(props.convertObject && Object.prototype.hasOwnProperty.call(props, 'multiple') && props.multiple){
          _val = _val.map(item => isObject(item) ? item[props.itemValue] : item)
        }

        if(Array.isArray(_val) && Object.prototype.hasOwnProperty.call(props, 'multiple') && !props.multiple && _val.length === 0){
          _val = !Array.isArray(modelValue.value) ? modelValue.value : null
        }

        if(!Array.isArray(_val) && Object.prototype.hasOwnProperty.call(props, 'multiple') && props.multiple){
          _val = []
        }

        return context.initializeInput ? context.initializeInput(_val) : _val
      },
      set: (val, old) => {
        context.updateModelValue
          ? context.updateModelValue(val, old)
          : methods.emitModelValue(val, old)
      }
    }),
  })

  const methods = reactive({
    async validate() {
      if(this.$refs.VInput){
        const result = await this.$refs.VInput.validate()
      }
    },
    updateModelValue: function (val, old) {
      context.emit('update:modelValue', val, old)
    },
    emitModelValue: function (val, old) {
      context.emit('update:modelValue', val, old)
    },
    makeReference (key) {
      return `${key}-${states.id}`
    },
    getReference (key) {
      return methods.makeReference(key)
    }
  })

  // expose managed state as return value
  return {
    // ...toRefs(_props),
    ...toRefs(methods),
    ...toRefs(states)
  }
}
