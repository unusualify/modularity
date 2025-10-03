// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import _, { cloneDeep } from 'lodash-es'
import { reactive, toRefs, toRef, watch } from 'vue'

// by convention, composable function names start with "use"
export default function useValidation (props) {
  // state encapsulated and managed by the composable
  // const { d } = useI18n({ useScope: 'global' })

  // const formatterColumns = ref(headers.filter((h) =>
  //   h.hasOwnProperty('formatter') && h.formatter.length > 0
  // ))

  const { valid } = toRefs(props)

  const state = reactive({
    validModel: valid?.value ?? null,
  })

  const ruleMethods = reactive({
    minRule: (l, msg) => v => (!!v && (typeof v === 'string' ? v.trim().length : v.length) >= l) || msg || `min. ${l} ${Array.isArray(v) ? 'Selections' : 'Characters'}`,
    maxRule: (l, msg) => v => (!v || v.length <= l) || msg || `max. ${l} ${Array.isArray(v) ? 'Selections' : 'Characters'}`,
    nameRule: (msg) => v => {
      if (!v) return true;
      const trimmed = v.trim().replace(/\s+/g, ' ');
      return /^[\p{L}'\-\-]+(?: [\p{L}'\-\-]+)*$/u.test(trimmed) || msg || 'Only letters, apostrophes, and hyphens allowed. Spaces only between names.';
    },
    // requiredRule: msg => v => !!v || msg || 'Required',
    emailRule: (options = {}, msg) => v => {
        if (!v) return true;

        const {
            allowedDomains = [], // Specific allowed domains
            blockedDomains = [], // Specific blocked domains
            allowSubdomains = true, // Allow email addresses with subdomains
            minLength = 3, // Minimum length before @
            maxLength = 254, // Maximum total length (RFC 5321)
            strict = false // Stricter validation
        } = options;

        // Basic format check
        const basicEmailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!basicEmailRegex.test(v)) {
            return msg || 'Invalid email format';
        }

        // Strict format check
        if (strict) {
            const strictEmailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            if (!strictEmailRegex.test(v)) {
                return msg || 'Email contains invalid characters';
            }
        }

        // Length checks
        const [localPart, domain] = v.split('@');
        if (localPart.length < minLength) {
            return msg || `Email username must be at least ${minLength} characters`;
        }
        if (v.length > maxLength) {
            return msg || `Email cannot exceed ${maxLength} characters`;
        }

        // Domain checks
        if (allowedDomains.length > 0) {
            const emailDomain = domain.toLowerCase();
            const isAllowed = allowedDomains.some(allowed => {
                if (allowSubdomains) {
                    return emailDomain.endsWith(allowed.toLowerCase());
                }
                return emailDomain === allowed.toLowerCase();
            });
            if (!isAllowed) {
                return msg || `Email domain must be one of: ${allowedDomains.join(', ')}`;
            }
        }

        // Blocked domains check
        if (blockedDomains.length > 0) {
            const emailDomain = domain.toLowerCase();
            const isBlocked = blockedDomains.some(blocked => {
                if (allowSubdomains) {
                    return emailDomain.endsWith(blocked.toLowerCase());
                }
                return emailDomain === blocked.toLowerCase();
            });
            if (isBlocked) {
                return msg || `Email domain not allowed`;
            }
        }

        // Check for common typos in popular domains
        const commonTypos = {
            'gmial': 'gmail',
            'gmal': 'gmail',
            'gamil': 'gmail',
            'yaho': 'yahoo',
            'ymail': 'yahoo',
            'hotmal': 'hotmail',
            'hotnail': 'hotmail',
            'outloo': 'outlook'
        };

        const domainWithoutTLD = domain.split('.')[0].toLowerCase();
        if (commonTypos[domainWithoutTLD]) {
            return msg || `Did you mean ${commonTypos[domainWithoutTLD]}?`;
        }

        return true;
    },
    requiredRule: (type ='classic',  minOrExact = 1, max, msg) => v => {
      switch(type) {
        case 'classic':
          return !!v || msg || 'Required';
        case 'array':
        case 'object':
          max = _.toNumber(max)
          max = _.isNaN(max) ? -1 : max;
          let $msg = ((minOrExact == max || max < 0) ? `Requires exactly ${minOrExact} items` : `Requires at least ${minOrExact}${((max != Infinity  && max != undefined) ? ', and maximum of:' + max : '')}) elements`);
          // let $msg = ((max != Infinity) ? ', maximum:' + max : '');
          if(Array.isArray(v)) {
            return v.length >= minOrExact && ( max < 0 || v.length <= max) || msg || $msg;
          }
          else if(__isObject(v)) {
            return  Object.keys(v).length >= minOrExact &&  (max < 0 || Object.keys(v).length <= max) || msg || $msg;
          }

          if(v == null) {
            return msg || 'Must select at least one item';
          }

          return 'dev error: nsupported value type';
        default:
          return 'dev error: unknown rule type';
      }
    },
    arrayRule: (msg) => v => Array.isArray(v) || msg || `Value must be array`,
    // requiredArrayRule: (msg, l = 1) => v => (Array.isArray(v) && v.length >= l) || msg || ''
    // confirmedRule: (confirmInputValue, msg) => v => {
    //   // const _val = toRef('model.' + confirmationValue)
    //   return v === confirmInputValue || msg || 'Passwords do not match'
    //   // return v === this.model[confirmationValue] || msg || 'Passwords do not match'
    // }

    // Numeric validation rules
    numberRule: (msg) => v => !isNaN(parseFloat(v)) && isFinite(v) || msg || 'Must be a valid number',
    integerRule: (msg) => v => Number.isInteger(Number(v)) || msg || 'Must be an integer',
    minValueRule: (min, msg) => v => v === undefined || v === null || Number(v) >= min  || msg || `Must be at least ${min}`,
    maxValueRule: (max, msg) => v => !v || Number(v) <= max || msg || `Must not exceed ${max}`,

    // String format validation rules
    phoneRule: (msg) => v => !v || /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/.test(v) || msg || 'Invalid phone number',
    urlRule: (msg) => v => !v || /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test(v) || msg || 'Invalid URL',
    alphaRule: (msg) => v => !v || /^[A-Za-z]+$/.test(v) || msg || 'Only letters allowed',
    alphaNumRule: (msg) => v => !v || /^[A-Za-z0-9]+$/.test(v) || msg || 'Only letters and numbers allowed',

    // Password validation rules
    passwordRule: (minLength = 8, msg) => v => !v || /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{${minLength},}$/.test(v) ||
        msg || `Password must be at least ${minLength} characters, contain at least one letter and one number`,
    strongPasswordRule: (minLength = 8, msg) => v => !v || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{${minLength},}$/.test(v) ||
        msg || `Password must be at least ${minLength} characters, contain uppercase, lowercase, number and special character`,

    // Date validation rules
    dateRule: (msg) => v => !v || !isNaN(Date.parse(v)) || msg || 'Invalid date format',
    futureDateRule: (interval = 0, unit = 'days', msg) => v => {
        if (!v) return true;
        let today = new Date();
        today.setHours(0, 0, 0, 0);

        const futureDate = new Date(today);

        if(_.isString(interval)) {
          interval = parseInt(interval)
        }

        switch (unit.toLowerCase()) {
            case 'minutes':
                futureDate.setMinutes(today.getMinutes() + interval);
                break;
            case 'hours':
                futureDate.setHours(today.getHours() + interval);
                break;
            case 'days':
                futureDate.setDate(today.getDate() + interval);
                break;
            case 'months':
                futureDate.setMonth(today.getMonth() + interval);
                break;
            case 'years':
                futureDate.setFullYear(today.getFullYear() + interval);
                break;
            default:
                futureDate.setDate(today.getDate() + interval);
        }

        return new Date(v) >= futureDate ||
            msg || `Date must be at least ${interval} ${unit} in the future`;
    },
    pastDateRule: (interval = 0, unit = 'days', msg) => v => {
        if (!v) return true;
        const today = new Date();
        const pastDate = new Date(today);

        switch (unit.toLowerCase()) {
            case 'minutes':
                pastDate.setMinutes(today.getMinutes() - interval);
                break;
            case 'hours':
                pastDate.setHours(today.getHours() - interval);
                break;
            case 'days':
                pastDate.setDate(today.getDate() - interval);
                break;
            case 'months':
                pastDate.setMonth(today.getMonth() - interval);
                break;
            case 'years':
                pastDate.setFullYear(today.getFullYear() - interval);
                break;
            default:
                pastDate.setDate(today.getDate() - interval);
        }

        return new Date(v) <= pastDate ||
            msg || `Date must be at least ${interval} ${unit} in the past`;
    },

    // Custom format validation rules
    zipCodeRule: (msg) => v => !v || /^\d{5}(-\d{4})?$/.test(v) || msg || 'Invalid ZIP code',
    ipAddressRule: (msg) => v => !v || /^(\d{1,3}\.){3}\d{1,3}$/.test(v) || msg || 'Invalid IP address',

    // File validation rules
    fileTypeRule: (types, msg) => v => {
        if (!v || !v.type) return true;
        const allowedTypes = Array.isArray(types) ? types : [types];
        return allowedTypes.includes(v.type) || msg || `File type must be: ${allowedTypes.join(', ')}`;
    },
    fileSizeRule: (maxSize, msg) => v => {
        if (!v || !v.size) return true;
        return v.size <= maxSize || msg || `File size must not exceed ${maxSize/1024/1024}MB`;
    },
    // Comparison validation rules
    equalsRule: (target, msg) => v => v === target || msg || `Must equal ${target}`,
    notEqualsRule: (target, msg) => v => v !== target || msg || `Must not equal ${target}`,
    matchesRule: (field, msg) => (v, formData) => v === formData[field] || msg || `Must match ${field}`,

    // Range validation rules
    betweenRule: (min, max, msg) => v => {
        if (!v) return true;
        const num = Number(v);
        return (num >= min && num <= max) || msg || `Must be between ${min} and ${max}`;
    },
    notBetweenRule: (min, max, msg) => v => {
        if (!v) return true;
        const num = Number(v);
        return (num < min || num > max) || msg || `Must not be between ${min} and ${max}`;
    },

    // String content validation rules
    containsRule: (substring, msg) => v => !v || v.includes(substring) || msg || `Must contain "${substring}"`,
    notContainsRule: (substring, msg) => v => !v || !v.includes(substring) || msg || `Must not contain "${substring}"`,
    startsWithRule: (prefix, msg) => v => !v || v.startsWith(prefix) || msg || `Must start with "${prefix}"`,
    endsWithRule: (suffix, msg) => v => !v || v.endsWith(suffix) || msg || `Must end with "${suffix}"`,

    // Pattern validation rules
    patternRule: (pattern, msg) => v => !v || pattern.test(v) || msg || 'Invalid format',
    customFormatRule: (formats, msg) => v => {
        if (!v) return true;
        const patterns = {
            creditCard: /^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|3[47][0-9]{13})$/,
            ssn: /^(?!000|666)[0-8][0-9]{2}-(?!00)[0-9]{2}-(?!0000)[0-9]{4}$/,
            hex: /^#?([a-f0-9]{6}|[a-f0-9]{3})$/i,
            rgb: /^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/,
            isbn: /^(?:ISBN(?:-1[03])?:? )?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[0-9X]$/,
        };
        const formatArray = Array.isArray(formats) ? formats : [formats];
        return formatArray.every(format => patterns[format].test(v)) || msg || `Invalid ${formatArray.join(' and ')} format`;
    },

    // Array/Collection validation rules
    uniqueRule: (msg) => v => {
        if (!Array.isArray(v)) return true;
        return v.length === new Set(v).size || msg || 'Must contain unique values';
    },
    includesAnyRule: (values, msg) => v => {
        if (!Array.isArray(v)) return true;
        return values.some(val => v.includes(val)) || msg || `Must include at least one of: ${values.join(', ')}`;
    },
    includesAllRule: (values, msg) => v => {
        if (!Array.isArray(v)) return true;
        return values.every(val => v.includes(val)) || msg || `Must include all of: ${values.join(', ')}`;
    },

    // Conditional validation rules
    whenRule: (condition, thenRules, elseRules = []) => (v, formData) => {
        const rules = (typeof condition === 'function' ? condition(formData) : condition) ? thenRules : elseRules;
        return rules.every(rule => rule(v, formData) === true) || 'Conditional validation failed';
    },
    dependentRule: (field, rules, msg) => (v, formData) => {
        if (!formData[field]) return true;
        return rules.every(rule => rule(v, formData) === true) || msg || `Invalid based on ${field}`;
    },




    generateInputRules,
    validateInput,
  })

  // function invokeRuleValidator () {
  //   const camelSlotName = _.camelCase(slotName)

  //   if (obj.schema.hasOwnProperty('slotHandlers') &&
  //     obj.schema.slotHandlers.hasOwnProperty(camelSlotName)) {
  //     const name = _.camelCase(obj.schema.slotHandlers[camelSlotName])
  //     const func = `${name}Handler`
  //     return methods[func](obj, camelSlotName)
  //   }
  // }

  function generateInputRules(input){
    let availableRules = []

    if (Object.prototype.hasOwnProperty.call(input, 'rules')) {
      let rules = input.rawRules ?? input.rules

      let arrayRules
      if (window.__isString(rules)) {
        arrayRules = rules.split('|')
      } else {
        arrayRules = rules
      }

      arrayRules.forEach((rule, index) => {
        if (window.__isString(rule)) {
          rule = rule.split(':')
        }
        const method = rule[0] + 'Rule'
        if (Object.prototype.hasOwnProperty.call(ruleMethods, method)) {
          availableRules.push(ruleMethods[method](...(rule.slice(1))))
        }
      })
    }

    return availableRules
  }

  function validateInput(input, v){
    let ruleFuncs = generateInputRules(input)

    let isValid = true

    for(const i in ruleFuncs){
      let result = ruleFuncs[i](v)
      if(result !== true){
        isValid = result
        break
      }
    }

    return isValid
  }

  function invokeRule(input){
    let Input = cloneDeep(input)
    let _input = cloneDeep(input)

    if (Object.prototype.hasOwnProperty.call(_input, 'rules')) {
      Input.rawRules = !__isset(Input.rawRules)
        ? _input.rules
        : Input.rawRules

      if (window.__isString(Input.rawRules)) {
        _input.rules = Input.rawRules.split('|')
      } else {
        _input.rules = Input.rawRules
      }
      Input.rules = []
      _input.rules.forEach((rule, index) => {
        if (window.__isString(rule)) {
          rule = rule.split(':')
        }
        const method = rule[0] + 'Rule'
        if (Object.prototype.hasOwnProperty.call(ruleMethods, method)) {
          Input.rules.push(ruleMethods[method](...(rule.slice(1))))
        }
      })
    }

    if(__isset(Input.schema)){
      Input.schema = invokeRuleGenerator(Input.schema)
    }

    if(!__isset(input.rawRules)){
      Input.rules = []
      Input.rawRules = ''
    }

    return Input
  }

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
              inputs[name].rules.push(ruleMethods[method](...(rule.slice(1))))
              // try {
              //
              // } catch (error) {
              //   delete inputs[name].rules[index]
              // }
            }
          })
        }
        if(__isset(inputs[name].schema)){
          inputs[name].schema = invokeRuleGenerator(inputs[name].schema)
        }
      })
    }

    return inputs
  }

  watch(() => state.valid, (newValue, oldValue) => {

  })
  // expose managed state as return value
  return {
    // invokeRuleValidator,
    invokeRule,
    invokeRuleGenerator,
    ...toRefs(ruleMethods),
    ...toRefs(state)
  }
}
