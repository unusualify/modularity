// hooks/useCurrencyNumber.js

import { reactive, toRefs, computed, watch, toRef, ref } from 'vue'
import { useCurrencyInput } from 'vue-currency-input'
import { useStore } from 'vuex'

// by convention, composable function names start with "use"
export default function useCurrencyNumber(props, context) {
  // const { modelValue } = toRefs(props)
  const store = useStore()
  // const inputRef = ref(null)
  const { inputRef, formattedValue, numberValue, setValue } = useCurrencyInput({
    currency: 'EUR',
    locale: store.state.language.active.value,
    autoDecimalDigits: true,
    precision: 2,
    currencyDisplay: 'hidden',

    hideCurrencySymbolOnFocus: false,
    hideGroupingSeparatorOnFocus: false,
    valueRange: { min: 0 },
  })

  const states = reactive({
    inputRef: inputRef,
    formattedValue: formattedValue,
    numberValue: numberValue
  })

  // const methods = reactive({

  // })

  watch(
    () => props.modelValue,
    (value) => {
      setValue(value);
    }
  );

  // expose managed state as return value
  return {
    // ...toRefs(methods),
    ...toRefs(states),
    // ...toRef(inputRef),
    // ...toRef(formattedValue),
    // ...toRef(numberValue)
  }
}
