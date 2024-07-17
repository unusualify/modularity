<template>
  <v-input
    v-model="deepModel"
    hide-details
    :variant="boundProps.variant"
    class="v-input-price"
    >
    <template v-for="(price,i) in deepModel" :key="`price-${i}`">
      <CurrencyNumber
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
    </template>
  </v-input>
</template>

<script>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
import CurrencyNumber from '__components/others/CurrencyNumber'

export default {
  name: 'v-custom-input-price',
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
            display_price: '',
            currency_id: 1
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
    currencies: {
      type: Array
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
      deepModel: this.modelValue.map((item) => {
        return item
        return {...item, [this.priceInputName]: item[this.priceInputName] / this.numberMultiplier }
      }),
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

      // this.updateModelValue()

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
          this.deepModel = newValue.map((item) => {
            return item
            return {...item, [this.priceInputName]: item[this.priceInputName] / this.numberMultiplier }
          })
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
