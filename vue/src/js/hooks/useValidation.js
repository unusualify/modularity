// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import _, { cloneDeep } from 'lodash-es'
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
    valid: null
  })

  const ruleMethods = reactive({
    minRule: (l, msg) => v => (!!v && v.length >= l) || msg || `min. ${l} Characters`,
    maxRule: (l, msg) => v => (!!v && v.length <= l) || msg || `max. ${l} Characters`,
    // requiredRule: msg => v => !!v || msg || 'Required',
    emailRule: (msg) => v => (/.+@.+\..+/.test(v)) || msg || 'E-mail must be valid',
    requiredRule: (type ='classic',  minOrExact = 1, max, msg) => v => {
      /**
       * Doga:
       * if type is 'classic' then we checj existence of the value
       * if type is 'array' then we check the length of the array
       * if type is 'object' then we check the length of the object
       * I though hierachically
       *  1. We need exact number of items
       *  2. We need at least number of items
       *  3. We need at least number of items and/or at most number of items
       *  so secod parameter in requiret:TYPE:MINEOREXACT:MAX
       *  works as lower limit for the number of items
       *  or exact number of items
       *  depending of the existence of the third parameter, max, which is optional
       *  (I ould set max to Infinity however programitaclly upper boundry bening infinity is not a good idea.)
       */
      switch(type) {
        case 'classic':
          return !!v || msg || 'Required';
        case 'array':
        case 'object':
          max = (max == undefined) ? -1 : max;
          let $msg = ((minOrExact == max || max < 0) ? `Requires exactly ${minOrExact} items` : `Requires at least ${minOrExact}${((max != Infinity  && max != undefined) ? ', and maximum of:' + max : '')}) elements`);
          // let $msg = ((max != Infinity) ? ', maximum:' + max : '');
          // __log(v.length, minOrExact, max )
          __log(v)
          if(Array.isArray(v)) {
            return v.length >= minOrExact && ( max < 0 || v.length <= max) || msg || $msg;
          }
          else if(__isObject(v)) {
            return  Object.keys(v).length >= minOrExact &&  (max < 0 || Object.keys(v).length <= max) || msg || $msg;
          }
          return 'dev error: nsupported value type';
        default:
          return 'dev error: unknown rule type';
      }
    },
    // requiredArrayRule: (msg, l = 1) => v => (Array.isArray(v) && v.length >= l) || msg || ''
    // confirmedRule: (confirmInputValue, msg) => v => {
    //   // const _val = toRef('model.' + confirmationValue)
    //   __log(v, confirmInputValue)
    //   return v === confirmInputValue || msg || 'Passwords do not match'
    //   // return v === this.model[confirmationValue] || msg || 'Passwords do not match'
    // }
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
    const _inputs = cloneDeep(inputs)

    if (__isObject(_inputs)) {
      Object.keys(_inputs).forEach((name) => {
        if (Object.prototype.hasOwnProperty.call(_inputs[name], 'rules')) {
          inputs[name].rawRules = !__isset(inputs[name].rawRules)
            ? _inputs[name].rules
            : inputs[name].rawRules

          if (window.__isString(inputs[name].rawRules)) {
            _inputs[name].rules = inputs[name].rawRules.split('|')
          } else {
            _inputs[name].rules = inputs[name].rawRules
          }
          inputs[name].rules = []
          _inputs[name].rules.forEach((rule, index) => {
            if (window.__isString(rule)) {
              rule = rule.split(':')
            }
            const method = rule[0] + 'Rule'
            if (Object.prototype.hasOwnProperty.call(ruleMethods, method)) {
              // __log(name, method, rule.slice(1))
              inputs[name].rules.push(ruleMethods[method](...(rule.slice(1))))
              // try {
              //
              // } catch (error) {
              //   delete inputs[name].rules[index]
              // }
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
    return inputs
  }

  watch(() => state.valid, (newValue, oldValue) => {
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
