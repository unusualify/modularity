// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import _ from 'lodash'
import { reactive, toRefs } from 'vue'

// by convention, composable function names start with "use"
export function useValidations () {
  // state encapsulated and managed by the composable
  // const { d } = useI18n({ useScope: 'global' })

  // const formatterColumns = ref(headers.filter((h) =>
  //   h.hasOwnProperty('formatter') && h.formatter.length > 0
  // ))

  const methods = reactive({
    min: (l, msg) => v => (v && v.length >= l) || msg || `min. ${l} Characters`,
    max: (l, msg) => v => (v && v.length <= l) || msg || `max. ${l} Characters`,
    required: msg => v => !!v || msg,
    requiredArray: (msg, l = 1) => v => (Array.isArray(v) && v.length > l) || msg,
    email: (msg) => v => /.+@.+\..+/.test(v) || msg || 'E-mail must be valid',
    passwordConfirmation: (confirmationValue, msg) => v => v === confirmationValue || msg || 'Passwords do not match'
  })

  function invokeRuleValidator () {
    // __log(methods, obj.schema, methods.hasOwnProperty('passwordHandler'))
    const camelSlotName = _.camelCase(slotName)

    if (obj.schema.hasOwnProperty('slotHandlers') &&
      obj.schema.slotHandlers.hasOwnProperty(camelSlotName)) {
      const name = _.camelCase(obj.schema.slotHandlers[camelSlotName])
      const func = `${name}Handler`
      return methods[func](obj, camelSlotName)
    }
  }
  function invokeRuleGenerator (inputs) {
    const _inputs = inputs

    Object.keys(_inputs).forEach((name) => {
      if (_inputs[name].hasOwnProperty('rules')) {
        _inputs[name].rules.forEach((rule, index) => {
          const method = rule[0]

          if (methods.hasOwnProperty(method)) {
            // __log(method)
            inputs[name].rules[index] = methods[method](...(rule.slice(1)))
          } else {
            delete inputs[name].rules[index]
          }
        })
      }
    })
    // __log(methods, obj.schema, methods.hasOwnProperty('passwordHandler'))
    // const camelSlotName = _.camelCase(slotName)

    // if (obj.schema.hasOwnProperty('slotHandlers') &&
    //   obj.schema.slotHandlers.hasOwnProperty(camelSlotName)) {
    //   const name = _.camelCase(obj.schema.slotHandlers[camelSlotName])
    //   const func = `${name}Handler`
    //   return methods[func](obj, camelSlotName)
    // }
    return inputs
  }

  // expose managed state as return value
  return {
    invokeRuleValidator,
    invokeRuleGenerator,
    ...toRefs(methods)
  }
}
