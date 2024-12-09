<template>
  <template v-for="(price,i) in deepModel" :key="`price-${i}`">
    <div class="d-flex w-100 ga-2">
      <CurrencyNumber
        class="flex-grow-1"
        v-bind="{label, ...$attrs }"
        :name="`${$attrs['name']}-${i}`"
        :modelValue="deepModel[i][priceInputName]"
        @update:modelValue="updateNumberInput($event, i)"
        >
        <template v-slot:append-inner="{isActive, isFocused, controlRef, focus, blur}">
          <v-chip @click="changeCurrency($event, i)">
            {{ displayedCurrency[i] }}
          </v-chip>
        </template>
      </CurrencyNumber>
      <v-select
        v-show="vatRates.length > 0"
        v-bind="{...$lodash.omit($attrs, ['rules', 'error', 'errorMessages'])}"
        :label="$t('VAT Rate')"
        :items="vatRates"
        class="flex-grow-0"
        v-model="deepModel[i].vat_rate_id"
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
          <div class="text-h6 text-white mb-2">Total Pay:</div>
          <div class="text-h2 text-white font-weight-bold">
            {{ displayedCurrency[i] + ' ' + (deepModel[i].display_price * (1 + $lodash.find(vatRates, ['value', deepModel[i].vat_rate_id])?.rate / 100)).toFixed(2) }}
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
      type: Array,
      default () {
        return [
          {
            display_price: 1.00,
            currency_id: 1,
            vat_rate_id: 1,
            price_type_id: 1
          }
        ]
      }
    },
    priceInputName: {
      type: String,
      default: 'display_price'
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
              display_price: 1.00,
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
    totalCurrencies () {
      return this.currencies.length
    }
  },

  created () {
    // __log(this.modelValue)
    // __log(this.deepModel, this.modelValue)
    // __log(this.$attrs)
  }
}
</script>

<style lang="sass">
.v-input-price
  min-width: 150px

</style>
