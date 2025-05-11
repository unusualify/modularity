// hooks/formatter .js

import _ from 'lodash-es'
import { reactive, toRefs } from 'vue'

// by convention, composable function names start with "use"
export default function useInputHandlers () {
  // state encapsulated and managed by the composable
  // const { d } = useI18n({ useScope: 'global' })

  // const formatterColumns = ref(headers.filter((h) =>
  //   h.hasOwnProperty('formatter') && h.formatter.length > 0
  // ))

  const methods = reactive({
    passwordHandler: function (obj, slotName) {
      obj.schema[`${slotName}Icon`] = obj.schema.type === 'password' ? '$visibility' : '$non-visibility'
      obj.schema.type = obj.schema.type === 'password' ? 'text' : 'password'
    }
  })

  function invokeInputClickHandler (obj, slotName) {
    const camelSlotName = _.camelCase(slotName)

    if (Object.prototype.hasOwnProperty.call(obj.schema, 'slotHandlers') &&
      Object.prototype.hasOwnProperty.call(obj.schema.slotHandlers, camelSlotName)) {
      const name = _.camelCase(obj.schema.slotHandlers[camelSlotName])
      const func = `${name}Handler`

      return methods[func](obj, camelSlotName)
    }
  }

  // expose managed state as return value
  return {
    invokeInputClickHandler,
    ...toRefs(methods)
  }
}
