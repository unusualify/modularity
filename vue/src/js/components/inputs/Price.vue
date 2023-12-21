<template>
  <v-text-field
    v-bind="$attrs"
    v-model="input[priceInputName]"
    >
    <template v-slot:append-inner="{isActive, isFocused, controlRef, focus, blur}">
      <v-chip @click="changeCurrency($event, 1)">
        {{ displayedCurrency }}
      </v-chip>
      <!-- <template @click="changeCurrency($event, 1)">
        {{ displayedCurrency }}
      </template> -->
    </template>
  </v-text-field>
</template>

<script>
import { InputMixin } from '@/mixins' // for props
import { useInput, makeInputProps } from '@/hooks'

export default {

  name: 'v-custom-input-checklist',
  mixins: [InputMixin],
  props: {
    ...makeInputProps(),
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
      defaultModel: {
        [this.priceInputName]: '',
        [this.currencyInputName]: 1
      }
    }
  },

  methods: {
    changeCurrency (e, id) {
      const currentIndex = this.currencies.find(o => o.id === id)

      __log(
        id,
        currentIndex,
        this.input
      )

      // this.input[this.currencyInputName] = currentIndex === this.totalCurrencies - 1 ? this.currencies[currentIndex + 1].id : this.currencies[0].id
    }
  },

  computed: {
    input: {
      get () {
        let model = this.modelValue
        // __log(model, this.defaultModel)
        if (!model) {
          model = this.defaultModel
        }
        __log('input getter', model)
        return model
      },
      set (val, old) {
        __log('Price.vue input set', val, old)
        // this.updateModelValue(val)
      }
    },
    displayedCurrency () {
      const id = this.input ? this.input[this.currencyInputName] : 1
      __log(id, this.currencies.find(o => { return o.id === id }))
      return this.currencies.find(o => { return o.id === id }).name
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
// .ue-checklist
//     .v-input--horizontal .v-input__prepend
//         margin-inline-end: 0px

</style>
