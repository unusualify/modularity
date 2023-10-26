// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import _ from 'lodash'
import { reactive, toRefs, toRef, watch } from 'vue'

// by convention, composable function names start with "use"
export default function useValidation () {
  // state encapsulated and managed by the composable
  // const { d } = useI18n({ useScope: 'global' })

  // const formatterColumns = ref(headers.filter((h) =>
  //   h.hasOwnProperty('formatter') && h.formatter.length > 0
  // ))

  // const model = toRefs()

  const state = reactive({
    validForm: true
  })

  const ruleMethods = reactive({
    minRule: (l, msg) => v => {
      return (v && v.length >= l) || msg || `min. ${l} Characters`
    },
    maxRule: (l, msg) => v => (v && v.length <= l) || msg || `max. ${l} Characters`,
    requiredRule: msg => v => {
      return !!v || msg || 'Required'
    },
    requiredArrayRule: (msg, l = 1) => v => (Array.isArray(v) && v.length > l) || msg || '',
    emailRule: (msg) => v => /.+@.+\..+/.test(v) || msg || 'E-mail must be valid',
    confirmationRule: (confirmationValue, msg) => v => {
      // const _val = toRef('model.' + confirmationValue)
      // __log(v, val)
      return v === confirmationValue || msg || 'Passwords do not match'
      // return v === this.model[confirmationValue] || msg || 'Passwords do not match'
    }
  })

  // function invokeRuleValidator () {
  //   // __log(methods, obj.schema, methods.hasOwnProperty('passwordHandler'))
  //   const camelSlotName = _.camelCase(slotName)

  //   if (obj.schema.hasOwnProperty('slotHandlers') &&
  //     obj.schema.slotHandlers.hasOwnProperty(camelSlotName)) {
  //     const name = _.camelCase(obj.schema.slotHandlers[camelSlotName])
  //     const func = `${name}Handler`
  //     return methods[func](obj, camelSlotName)
  //   }
  // }

  function invokeRuleGenerator (inputs) {
    const _inputs = inputs

    if (__isObject(_inputs)) {
      Object.keys(_inputs).forEach((name) => {
        if (Object.prototype.hasOwnProperty.call(_inputs[name], 'rules')) {
          _inputs[name].rules.forEach((rule, index) => {
            const method = rule[0] + 'Rule'
            if (Object.prototype.hasOwnProperty.call(ruleMethods, method)) {
              inputs[name].rules[index] = ruleMethods[method](...(rule.slice(1)))
            } else {
              delete inputs[name].rules[index]
            }
          })
        }
      })
    }
    // __log(methods, obj.schema, methods.hasOwnProperty('passwordHandler'))
    // const camelSlotName = _.camelCase(slotName)

    // if (obj.schema.hasOwnProperty('slotHandlers') &&
    //   obj.schema.slotHandlers.hasOwnProperty(camelSlotName)) {
    //   const name = _.camelCase(obj.schema.slotHandlers[camelSlotName])
    //   const func = `${name}Handler`
    //   return methods[func](obj, camelSlotName)
    // }
    // __log(inputs)
    return inputs
  }

  watch(() => state.validForm, (newValue, oldValue) => {
    // __log('validForm watch', newValue, oldValue)
  })
  // expose managed state as return value
  return {
    // invokeRuleValidator,
    invokeRuleGenerator,
    ...toRefs(ruleMethods),
    ...toRefs(state)
  }
}
