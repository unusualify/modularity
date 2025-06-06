<template>
  <v-input v-model="input">
    <v-row>
      <v-col class="d-flex flex-column justify-center px-0">
        <v-card class="pa-4 payment-container" elevation="0">
          <v-card-text class="pa-0">
            <p class="select-title">{{ $t('How would you like to pay?') }}</p>
          </v-card-text>

          <!-- Currency Selector -->
          <v-select
            v-model="selectedCurrency"
            :items="formattedCurrencies"
            label="Currency"
            variant="outlined"
            density="comfortable"
            item-title="display"
            item-value="id"
            @update:model-value="handleCurrencyChange"
            class="mx-6"
          />

          <!-- Payment Methods -->
          <v-radio-group v-model="localPaymentMethod" column>
            <!-- Credit Card Option -->
            <div
              class="service-container px-3 py-cs-1 credit-card-service"
              :class="{ 'selected-service--focus': localPaymentMethod === localDefaultPaymentMethod }"
            >
              <v-radio
                :label="$t('Credit Card')"
                :value="localDefaultPaymentMethod"
                class="service-label"
              />
              <v-row align="center">
                <v-col class="d-flex justify-content-end">
                  <v-img-icon
                    v-for="(currencyCardType, key) in currencyCardTypes[selectedCurrencyIso]"
                    :key="`type-${key}`"
                    :src="currencyCardType.logo"
                    style="max-width: 64px;"
                  />
                </v-col>
              </v-row>
            </div>

            <!-- Other Payment Services -->
            <div
              v-for="(service, key) in filteredServiceItems"
              :key="`service-${key}`"
              class="service-container px-3 py-1"
              :class="{ 'selected-service--focus': localPaymentMethod === service[itemValue] }"
            >
              <v-radio
                :label="service.name"
                :value="service[itemValue]"
                class="service-label"
              />
              <v-row align="center">
                <v-col class="d-flex justify-content-end">
                  <div class="service-icon-container">
                    <v-img-icon
                      :Xsrc="'/storage/uploads/' + service?.medias.find(item => item?.pivot?.role === 'logo')?.uuid"
                      :src="service.button_logo_url"
                      class="v-img__img--relative"
                    />
                  </div>
                </v-col>
              </v-row>
            </div>
          </v-radio-group>
        </v-card>

        <!-- Total Amount Display -->
        <v-card-title class="headline">
          <p class="total mb-2">{{ $t('Total Amount') }}</p>
          <p class="amount mb-2">{{ displayPrice }}</p>
          <p class="" v-if="isExchanged">{{ $t('Exchange Rate') }}: ~{{ exchangeRate }}</p>
        </v-card-title>
      </v-col>

      <!-- Payment Form Section -->
      <v-col class="px-0 d-flex align-center justify-center">
        <CreditCardForm
          v-if="showCreditCardForm"
          v-model:cardName="localCreditCard.card_name"
          v-model:cardNumber="localCreditCard.card_number"
          v-model:cardMonth="localCreditCard.card_month"
          v-model:cardYear="localCreditCard.card_year"
          v-model:cardCvv="localCreditCard.card_cvv"
        />
        <v-btn
          v-else
          :style="selectedService?.button_style"
          @click="submitForm"
          density="comfortable"
          max-width="300px"
          min-width="80%"
          class="d-flex align-center justify-center"
        >
          <v-img
            width="100px"
            contain
            max-height="36px"
            :src="selectedService?.button_logo_url"
          />
          <span class="ml-2 font-weight-bold text-body-2 text-align-center" >
            {{ displayPrice }}
          </span>
          <!-- {{ selectedService?.name }} -->
        </v-btn>
      </v-col>
    </v-row>
  </v-input>
</template>

<script>
import { computed, ref, reactive, watch, inject } from 'vue';
import { makeInputProps, makeInputEmits, useCurrency } from '@/hooks';
import CreditCardForm from '@/components/inputs/CreditCardForm';

export default {
  name: 'PaymentService',

  components: {
    CreditCardForm
  },

  emits: [
    ...makeInputEmits,
    'update:price',
    'currency-converted'
  ],

  props: {
    ...makeInputProps(),
    modelValue: {
      type: Object,
      default: () => ({})
    },
    itemValue: {
      type: String,
      default: 'id'
    },
    items: {
      type: Array,
      default: () => []
    },
    currencies: {
      type: Array,
      default: () => []
    },

    price_object: {
      type: [Object, Array, Proxy],
      default: () => ({})
    },
    api: {
      type: String,
      default: ''
    },
    currencyCardTypes: {
      type: [Object, Proxy, Array],
      default: () => ({})
    },
    baseCurrency: {
      type: String,
      default: 'EUR'
    }
  },

  setup(props, { emit }) {
    const submitForm = inject('submitForm');
    const { formatPrice } = useCurrency();

    // Refs
    const localPaymentMethod = ref('');
    const localDefaultPaymentMethod = ref("-1");
    const selectedCurrency = ref(props.price_object.currency_id || props.currencies[0]?.id);
    const displayPrice = ref(formatPrice.value(props.price_object.total_amount / 100, props.currencies[0]?.symbol || ''));

    // Reactive state
    const localCreditCard = reactive({
      card_name: '',
      card_number: '',
      card_month: '',
      card_year: '',
      card_cvv: ''
    });

    // Computed
    const selectedCurrencyObj = computed(() =>
      props.currencies.find(curr => curr.id === selectedCurrency.value)
    );

    const selectedCurrencyIso = computed(() =>
      selectedCurrencyObj.value?.iso_4217
    );

    const formattedCurrencies = computed(() =>
      props.currencies.map(currency => ({
        id: currency.id,
        display: `${currency.symbol} - ${currency.name}`,
      }))
    );

    const filteredServiceItems = computed(() => {
      if (!selectedCurrency.value) return [];

      return props.items.filter(service => {
        return service.payment_currencies?.some(currency => currency.id === selectedCurrency.value)
      });
    });

    const selectedService = computed(() =>
      filteredServiceItems.value.find(service => service[props.itemValue] === localPaymentMethod.value)
    );

    const showCreditCardForm = computed(() =>
      !selectedService.value?.is_external
    );

    const input = computed({
      get: () => props.modelValue,
      set: (newValue) => emit('update:modelValue', newValue)
    });

    const isExchanged = computed(() =>
      selectedCurrencyObj.value?.iso_4217 !== props.baseCurrency
    );

    const exchangeRate = ref(0);

    // Methods
    const handleCurrencyChange = async (newCurrencyId) => {
      selectedCurrency.value = newCurrencyId;
      localPaymentMethod.value = localDefaultPaymentMethod.value;

      const selectedCurrencyObject = props.currencies.find(curr => curr.id === newCurrencyId);
      if (!selectedCurrencyObject || !props.api) return;

      try {
        const response = await axios.post(props.api, {
          currency: selectedCurrencyObject.iso_4217,
          amount: props.price_object.discounted_raw_amount / 100
        });

        exchangeRate.value = response.data.exchange_rate;

        const calculatedAmount = response.data.converted_amount * ( 1 + props.price_object.vat_multiplier);

        displayPrice.value = formatPrice.value(
          calculatedAmount,
          selectedCurrencyObject.symbol
        );

        emit('update:price', displayPrice.value);
        emit('currency-converted', displayPrice.value);
      } catch (error) {
        console.error('Currency conversion error:', error);
      }
    };

    // Watchers
    watch(() => props.modelValue, (newValue) => {
      if (newValue && typeof newValue === 'object') {
        localPaymentMethod.value = newValue.payment_method || localDefaultPaymentMethod.value;
        Object.assign(localCreditCard, newValue.credit_card || {});
      }
    }, { immediate: true, deep: true });

    watch([localPaymentMethod, localCreditCard, selectedCurrencyObj], () => {
      input.value = {
        payment_method: localPaymentMethod.value,
        credit_card: { ...localCreditCard },
        currency: selectedCurrencyObj.value,
      };
    }, { deep: true });

    return {
      input,
      localPaymentMethod,
      localDefaultPaymentMethod,
      localCreditCard,
      showCreditCardForm,
      submitForm,
      selectedCurrency,
      selectedCurrencyIso,
      formattedCurrencies,
      filteredServiceItems,
      handleCurrencyChange,
      displayPrice,
      selectedService,
      exchangeRate,
      isExchanged,
    };
  }
};
</script>
<!-- Styles remain unchanged -->
<style lang="scss" scoped>
  .py-cs-1{
    padding-top:5px !important;
    padding-bottom: 5px !important;
  }
  .payment-container {
    padding: 0 !important;
    border: 1px solid #CACBCB;
    border-radius: 4px 4px 0 0px;
  }

  .payment-card {
    padding: 2rem;
  }

  .headline{
    background-color: #54AF4C;
    color: white;
    padding: 1rem 2rem;
    border-radius: 0px 0px 4px 4px;

    *{
      text-align: center;
      font-weight: 400;
      line-height: 1;
    }
    .total{
      font-size: 1.5rem;
      color: white;
    }
    .amount{
      font-size: 3.5rem;
      color: white;
    }
  }

  .select-title {
    font-size: 1.5rem;
    color: #32454A;
    font-weight: 400;
    text-align: center;
    margin: 1.5rem 0;
  }
  .v-btn {
    background-color: #f5f5f5;

    &:hover {
      background-color: #e0e0e0;
    }
  }
  .service-container{
    display:flex;
    flex-flow: row;
    border: 1px solid #CACBCB;
    border-radius: 4px;
    margin: 0.5rem 1.5rem;
    background-color: transparent;
    transition: 0.3s linear all;
    position: relative;

    &.credit-card-service{
      .custom-service-col{
        position: relative;
        .service-icon-container{
          display: flex;
          padding: 0px !important;
          width: 100%;
          top: 50%;
          transform: translateY(-50%);
        }
        .v-img__img--relative{
          width:min-content;
          padding: 4px;
        }
      }

    }

    &.selected-service--focus{
      border: 1px solid #54AF4C;
      background-color: #E4F4D8;
    }

    .service-label{
      width: 100%;
    }

    .service-icon-container {
      width: fit-content;
      position:absolute;
      top: 5px;

      .v-img__img--relative {
        background: rgba(211, 216, 221, 0.30);
        padding: 8px 28px;
        border-radius: 4px;
      }
    }
  }

  .d-flex{
    display: flex;
  }
  .justify-content-end{
    justify-content:flex-end;
  }
</style>

<style lang="scss">
  .service-container{
    .v-label {
      color: #20363B !important;
      font-weight: 600 !important;
    }
  }
  .service-icon-container {
    .v-img__img--relative {
      img {
        position: relative !important;
      }
    }
  }

</style>
