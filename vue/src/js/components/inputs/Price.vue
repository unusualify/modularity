<template>
  <div>
    <template v-for="(price,i) in deepModel" :key="`price-${i}`">
      <v-text-field
        v-bind="{label, ...$attrs}"
        :name="`${$attrs['name']}-${i}`"
        v-model="deepModel[i][priceInputName]"
        >
        <template v-slot:append-inner="{isActive, isFocused, controlRef, focus, blur}">
          <v-chip @click="changeCurrency($event, i)">
            {{ displayedCurrency[i] }}
          </v-chip>
        </template>
      </v-text-field>
    </template>
  </div>
</template>

<script>
import { InputMixin } from '@/mixins' // for props
import { useInput, makeInputProps } from '@/hooks'

export default {

  name: 'v-custom-input-checklist',
  mixins: [InputMixin],
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
      deepModel: this.modelValue
    }
  },

  methods: {
    changeCurrency (e, index) {
      const currentIndex = this.currencies.findIndex(o => o.id === this.deepModel[index][this.currencyInputName])

      this.deepModel[index][this.currencyInputName] = currentIndex === this.totalCurrencies - 1 ? this.currencies[0].id : this.currencies[currentIndex + 1].id
    }
  },

  watch: {
    modelValue: {
      deep: true,
      handler (newValue) {
        // console.log('watchedValue?', newValue)
        if (newValue) {
          this.deepModel = newValue
        } else {
          this.deepModel = [
            {
              display_price: '',
              currency_id: 1
            }
          ]
        }
      }
    },
    deepModel: {
      deep: true,
      handler (newValue) {
        // console.log('watchedDeepModel?', newValue)
        this.$emit('update:modelValue', newValue)
      }
    }
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
    // __log(this.deepModel, this.modelValue)
    // __log(this.$attrs)
  }
}
</script>

<style lang="sass">
// .ue-checklist
//     .v-input--horizontal .v-input__prepend
//         margin-inline-end: 0px

</style>
