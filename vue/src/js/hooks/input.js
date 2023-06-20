// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { ref, reactive, toRefs, toRef } from 'vue'

// by convention, composable function names start with "use"
export function useInput (props, context) {
  const _props = {
    modelValue: ref(''),
    obj: ref({})
  }
  const states = reactive({

  })
  const methods = reactive({
    _: function (arg) {

    }
  })

  // expose managed state as return value
  return {
    ...toRef(_props),
    ...toRefs(methods),
    ...toRefs(states)
  }
}
