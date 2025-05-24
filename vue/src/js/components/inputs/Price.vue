<template>
  <template v-for="(price,i) in deepModel" :key="`price-${i}`">
    <div class="d-flex w-100 ga-2">
      <CurrencyNumber
        class="flex-grow-1"
        v-bind="{label, ...$attrs }"
        :name="`${$attrs['name']}-${i}`"
        :modelValue="deepModel[i][priceInputName]"
        @update:modelValue="updateNumberInput($event, i)"
        :readonly="readonly"
        >
        <template v-slot:append-inner="{isActive, isFocused, controlRef, focus, blur}">
          <v-chip @click="changeCurrency($event, i)">
            {{ displayedCurrency[i] }}
          </v-chip>
        </template>
      </CurrencyNumber>
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


    // const { modelValue } = toRefs(props)
    return {
      ...inputHook
    }
  },

  data () {
    return {
      deepModel: Array.isArray(this.modelValue)
        ? this.modelValue.map((item) => {
            return item
            return {...item, [this.priceInputName]: item[this.priceInputName] / this.numberMultiplier }
          })
        : [this.modelValue]
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
