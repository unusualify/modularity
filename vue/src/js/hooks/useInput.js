// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { reactive, toRefs, computed } from 'vue'

// by convention, composable function names start with "use"
export default function useInput (props, context) {
  // const _props = {
  //   modelValue: ref(''),
  //   obj: ref({})
  // }

  // const { modelValue, obj } = toRefs(defineProps({
  //   modelValue: String,
  //   obj: Object
  // }))
  const { modelValue, obj } = toRefs(props)

  const states = reactive({
    id: Math.ceil(Math.random() * 1000000) + '-input',
    boundProps: _.omit(obj.value.schema ?? {}, ['offset', 'order', 'col']),

    input: computed({
      get: () => {
        return modelValue.value
      },
      set: (val, old) => {
        methods.inputOnSet(val, old)
        methods.updateModelValue(val)
        // context.emit('update:modelValue', val)
      }
    })
  })
  const methods = reactive({
    updateModelValue: function (val) {
      // __log(val)
      // __log(
      //   'useInput updateModelValue', val
      // )
      context.emit('update:modelValue', val)
      // context.emit('input', val)
    },

    inputOnSet (newValue, oldValue) {

    },

    makeReference (key) {
      return `${key}-${states.id}`
    },
    getReference (key) {
      return methods.makeReference(key)
    }

  })

  // const computed =

  // expose managed state as return value
  return {
    // ...toRefs(_props),
    ...toRefs(methods),
    ...toRefs(states)
  }
}
