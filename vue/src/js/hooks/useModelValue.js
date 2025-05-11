// hooks/useTable.js
import { watch, computed, nextTick, reactive, toRefs } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

export const makeModelValueProps = propsFactory({
  modelValue: [String, Number, Object, Boolean]
})

// by convention, composable function names start with "use"
export default function useModelValue (props, context, name = 'activeItem') {
  const state = reactive({
    [name]: computed({
      get () {
        return props.modelValue
      },
      set (value) {
        return context.emit('update:modelValue', value)
      }
    })
  })

  return toRefs(state)
}
