// hooks/formatter .js

import { reactive, toRefs, computed } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import { omit } from 'lodash-es'

export const makeInputProps = propsFactory({
  modelValue: null,
  // error: {
  //   type: Boolean,
  //   default: false
  // },
  // errorMessages: {
  //   type: [Array, String],
  //   default: () => []
  // },

  obj: {
    type: Object,
    default () {
      return {}
    }
  },
  label: {
    type: String,
    default: ''
  }
})

// by convention, composable function names start with "use"
export default function useInput (props, context) {
  const { modelValue, obj } = toRefs(props)

  const states = reactive({
    id: Math.ceil(Math.random() * 1000000) + '-input',
    boundProps: omit(obj.value.schema ?? {}, ['offset', 'order', 'col']),

    input: computed({
      get: () => {
        return modelValue.value ?? []
      },
      set: (val, old) => {
        methods.updateModelValue(val)
      }
    }),
  })

  const methods = reactive({
    updateModelValue: function (val) {
      context.emit('update:modelValue', val)
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
