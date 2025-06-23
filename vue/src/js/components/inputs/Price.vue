<template>
  <template v-for="(price,i) in deepModel" :key="`price-${i}`">
    <div class="d-flex w-100 ga-2">
      <v-input
        :ref="(el) => setInputRef(i, el)"
        v-model="deepModel[i][priceInputName]"
        class="v-input-price"
        v-bind="{...$lodash.pick($attrs, ['error', 'errorMessages'])}"
        :rules="rules"
        hide-details
        >
        <template v-slot:default="defaultSlot">
          <CurrencyNumber
            class="flex-grow-1"
            v-bind="{label, ...$attrs }"
            :name="`${$attrs['name']}-${i}`"
            :modelValue="deepModel[i][priceInputName]"
            @update:modelValue="updateNumberInput($event, i)"
            :readonly="readonly"
            :errorMessages="errorMessages[i]"
            >
            <template v-slot:append-inner="{isActive, isFocused, controlRef, focus, blur}">
              <v-chip @click="changeCurrency($event, i)">
                {{ displayedCurrency[i] }}
              </v-chip>
            </template>
          </CurrencyNumber>
        </template>

        <template v-slot:details>
          {{ errorMessages[i] }}
        </template>
      </v-input>
      <v-select
        v-show="vatRates.length > 0"
        v-bind="{...$lodash.pick($attrs, ['density', 'color', 'clearable', 'variant'])}"
        :label="$t('VAT Rate')"
        :items="vatRates"
        class="flex-grow-0"
        v-model="deepModel[i].vat_rate_id"
      />
      <v-number-input
        v-if="hasDiscount"
        v-model="deepModel[i].discount_percentage"
        v-bind="{...$lodash.pick($attrs, ['density', 'color', 'clearable', 'variant'])}"
        :label="$t('Discount %')"
        control-variant="stacked"
        :min="0"
        :max="100"
        :rules="[]"
      />
    </div>
    <div v-if="vatRates.length > 0" class="w-100 d-flex justify-center">
      <v-card
        class="w-75"
        color="success"
        rounded="lg"
        elevation="0"
      >
        <v-card-text class="text-center py-6">
          <div class="text-h6 text-white mb-2">Total Pay</div>
          <div class="text-h2 text-white font-weight-bold">
            {{ displayFormattedPrice(displayedCurrencyISO4217[i], deepModel[i] )}}
          </div>
        </v-card-text>
      </v-card>
    </div>
  </template>
</template>

<script>
import { ref, reactive } from 'vue'
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
import CurrencyNumber from '__components/others/CurrencyNumber'

export default {
  name: 'v-input-price',
  emits: [...makeInputEmits],
  components: {
    CurrencyNumber
  },
  props: {
    ...makeInputProps(),
    modelValue: {
      type: [Array, Object],
      default () {
        return [
          {
            [this.priceInputName]: 1.00,
            currency_id: 1,
            vat_rate_id: 1,
            price_type_id: 1,

            raw_amount: 1.00,
            discount_percentage: 0,
          }
        ]
      }
    },
    priceInputName: {
      type: String,
    },
    currencyInputName: {
      type: String,
      default: 'currency_id'
    },
    numberMultiplier: {
      type: Number,
      default: 100
    },
    // currencyInputName: {
    //   type: String,
    //   default: 'currency_id'
    // },
    items: {
      type: Array
    },
    vatRates: {
      type: Array,
      default: () => []
    },
    rules: {
      type: Array,
      default: () => []
    },
    error: {
      type: Boolean,
      default: false
    },
    readonly: {
      type: Boolean,
      default: false
    },
    hasDiscount: {
      type: Boolean,
      default: false
    }
  },
  setup (props, context) {
    const inputHook = useInput(props, context)

    const deepModel = ref(Array.isArray(props.modelValue)
      ? props.modelValue.map((item) => {
          return item
          return {...item, [props.priceInputName]: item[props.priceInputName] / props.numberMultiplier }
        })
      : [props.modelValue])

    const errorMessages = ref(deepModel.value.map((item) => {
      return []
    }))

    const inputRefs = reactive(new Map())

    const setInputRef = (id, el) => {
      if (el) {
        inputRefs.set(id, el)
      } else {
        inputRefs.delete(id)
      }
    }
    const getInputRef = (id) => inputRefs.get(id)

    // const { modelValue } = toRefs(props)
    return {
      ...inputHook,
      deepModel,
      inputRefs,
      setInputRef,
      getInputRef,
      errorMessages
    }
  },

  data () {
    return {
    }
  },

  methods: {
    changeCurrency (e, index) {
      if (this.readonly) {
        return
      }
      const currentIndex = this.currencies.findIndex(o => o.id === this.modelValue[index][this.currencyInputName])

      this.deepModel[index][this.currencyInputName] = currentIndex === this.totalCurrencies - 1 ? this.currencies[0].id : this.currencies[currentIndex + 1].id

      // this.updateModelValue()
    },
    updateNumberInput (e, index) {
      this.deepModel[index][this.priceInputName] = e

      this.$nextTick(() => {
        try{
          let inputRef = this.inputRefs.get(index)
          this.errorMessages[index] = inputRef.errorMessages
        }catch(err){
          console.error(err)
        }
      })

    },
    updateVatRate (e, index) {
      this.deepModel[index].vat_rate_id = e
    },
    updateModelValue() {

      this.$emit('update:modelValue', this.deepModel.map((item) => {
        return item
        return {...item, [this.priceInputName]: item[this.priceInputName] * this.numberMultiplier }
      }))
    },

    displayFormattedPrice(currency, item) {
      const vatRate = this.$lodash.find(this.vatRates, ['value', item.vat_rate_id])?.rate / 100
      const discountPercentage = (item.discount_percentage || 0) / 100
      const price = item[this.priceInputName]
      const amount = (Math.round(price * (1 - discountPercentage)) * (1 + vatRate)).toFixed(2)

      // return currency + ' ' + (price * (1 - discountPercentage) * (1 + vatRate)).toFixed(2)
      try{
        return Intl.NumberFormat('en-US', { style: 'currency', currency: currency }).format(amount)
      }catch(err){
        console.log(err)
      }
      return currency + ' ' + amount
    }
  },

  watch: {
    modelValue: {
      deep: true,
      handler (newValue, old) {
        if (newValue) {
          this.deepModel = Array.isArray(newValue)
            ? newValue.map((item) => {
                return item
                return {...item, [this.priceInputName]: item[this.priceInputName] / this.numberMultiplier }
              })
            : [newValue]
        } else {
          this.deepModel = [
            {
              [this.priceInputName]: 1.00,
              currency_id: 1
            }
          ]
        }
      }
    },
    deepModel: {
      deep: true,
      handler (newValue) {

      }
    },
  },

  computed: {
    currencies () {
      return this.items
    },
    displayedCurrency () {
      return this.deepModel.map((item, i) => {
        return this.currencies.find(o => { return o.id === item[this.currencyInputName] }).name
      })
    },
    displayedCurrencyISO4217 () {
      return this.deepModel.map((item, i) => {
        return this.currencies.find(o => { return o.id === item[this.currencyInputName] }).iso
      })
    },
    totalCurrencies () {
      return this.currencies.length
    }
  },

  created () {

  }
}
</script>

<style lang="sass">
.v-input-price
  min-width: 150px

</style>
