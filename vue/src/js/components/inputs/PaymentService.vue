<template>
  <v-input v-model="input">
    <v-row no-gutters>
      <v-col class="d-flex flex-column justify-center px-0">
        <v-card class="pa-4 payment-container" elevation="0">
          <v-card-text class="pa-0">
            <p class="text-h5 text-center ma-6">{{ $t('How would you like to pay?') }}</p>
          </v-card-text>

          <!-- Currency Selector -->
          <v-select
            v-model="currencyModel"
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
          <v-radio-group v-model="localPaymentMethod" column >
            <!-- Credit Card Option -->
            <div
              class="service-container px-3 py-cs-1 credit-card-service"
              :class="{ 'selected-service--focus': localPaymentMethod === localDefaultPaymentMethod }"
            >
              <v-radio
                :label="$t('Credit Card') + ' *' "
                :value="localDefaultPaymentMethod"
                class="service-label flex-md-1-1-0 flex-1-1-100"
              />
              <div class="flex-md-1-1-0 flex-1-1-100">
                <div class="d-flex justify-md-end ga-1">
                  <v-img-icon v-for="(currencyCardType, key) in currencyCardTypes[selectedCurrency.iso_4217]"
                    :key="`type-${key}`"
                    :src="currencyCardType.logo"
                    style="max-width: 64px;"
                    class="v-img__img--relative"
                  />
                </div>
              </div>
            </div>


            <!-- Other Payment Services -->
            <div
              v-for="(service, key) in filteredServiceItems"
              :key="`service-${key}`"
              class="service-container px-3 py-1"
              :class="{ 'selected-service--focus': localPaymentMethod === service[itemValue] }"
            >
              <v-radio
                 :labelX="!service.name.includes('Bank Transfer') ? service.name + '*' : service.name"
                 :label="`${service.name} ${service.is_external ? '*' : ''}`"
                 :value="service[itemValue]"
                 class="service-label flex-md-1-1-0 flex-1-1-100"
              />
              <div class="flex-md-0-1 flex-1-1-100">
                <div class="service-icon-container">
                  <v-img-icon
                    :Xsrc="'/storage/uploads/' + service?.medias.find(item => item?.pivot?.role === 'logo')?.uuid"
                    :src="service.button_logo_url"
                    class="v-img__img--relative"
                  />
                </div>

              </div>
              <!-- <v-row align="center">
                <v-col class="d-flex justify-content-end">
                </v-col>
              </v-row> -->
            </div>
            <div v-if="currencyHasExternalService" class="service-container" style="border: none;">
              <div class="text-body-1">
                {{ '* ' + '+' + transactionFeePercentage + '% ' + transactionFeeDescription }}
              </div>
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
      <v-col class="pa-4 pa-sm-0 d-flex align-center justify-center">
        <template v-if="isCreditCardForm">
          <template v-if="selectedCurrency.has_built_in_form">
            <v-progress-circular v-if="builtInFormLoading" indeterminate />
            <!-- <ue-revolut-checkout v-else
              v-bind="builtInFormAttributes"
            /> -->
            <ue-revolut-checkout-modal v-else
              v-bind="builtInFormAttributes"
              :complete-url="completeUrl"
            >
              <template #button="{ pay }">
                <div class="d-flex flex-column align-center" style="width: 100%;">
                  <p class="text-h5 text-center ma-4">{{ $t('Click below to pay securely') }}</p>
                  <v-btn
                    :style="selectedCurrency?.payment_service?.button_style"
                    density="comfortable"
                    max-height=""
                    max-width="300px"
                    min-width="80%"
                    class="d-flex align-center justify-center py-6"
                    @click="pay"
                  >
                    <v-img v-if="selectedCurrency?.payment_service?.button_logo_url"
                      contain
                      :width="100"
                      style="max-height: 36px; max-width: 100px;"
                      aspect-ratio="16/9"
                      :src="selectedCurrency?.payment_service?.button_logo_url"
                    />
                  </v-btn>
                </div>
              </template>
            </ue-revolut-checkout-modal>
          </template>
          <CreditCardForm v-else
            v-model:cardName="localCreditCard.card_name"
            v-model:cardNumber="localCreditCard.card_number"
            v-model:cardMonth="localCreditCard.card_month"
            v-model:cardYear="localCreditCard.card_year"
            v-model:cardCvv="localCreditCard.card_cvv"
          />
        </template>
        <template v-else-if="isTransferrableForm">
          <div class="w-100 h-100 d-flex border-thin d-flex flex-column pa-4 ga-4">
            <div v-for="(value, key) in selectedService?.bank_details ?? {}" :key="key" class="">
              <h6 class="text-body-1 font-weight-bold " >{{ $headline(key) }}</h6>
              <p class="text-body-1" >{{ value }}</p>
            </div>
            <ue-form
              ref="transferForm"
              :schema="transferSchema"
              :modelValue="createTransferModel()"
              :action-url="paymentUrl"

              noDefaultSurface
              noDefaultFormPadding
              hasSubmit
              buttonText="I Have Completed The Transfer"
              @submitted="handleTransferSubmit"
            >
              <template #submit="{ validForm, loading, saveForm }">
                <v-btn color="success" block :disabled="!validForm" :loading="loading" @click="saveForm">
                  {{ $t('I Have Completed The Transfer') }}
                </v-btn>
              </template>
            </ue-form>
          </div>
        </template>
        <div v-else class="d-flex flex-column align-center" style="width: 100%;">
          <p class="text-h5 text-center ma-4">{{ $t('Click below to pay securely') }}</p>
          <v-btn
            :style="selectedService?.button_style"
            @click="submitForm"
            density="comfortable"
            max-height=""
            max-width="300px"
            min-width="80%"
            class="d-flex align-center justify-center py-6"
          >
            <v-img
              contain
              width="100px"
              height="36px"
              style="max-width: 100px; max-height: 36px;"
              :src="selectedService?.button_logo_url"
            />
          </v-btn>
        </div>
          <!-- {{ selectedService?.name }} -->
      </v-col>
    </v-row>
  </v-input>
</template>

<script>
import { computed, ref, reactive, watch, inject } from 'vue';
import _ from 'lodash-es';
import { getModel, getSchema } from '@/utils/getFormData.js'
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
    supportedCurrencies: {
      type: Array,
      default: () => []
    },

    price_object: {
      type: [Object, Array, Proxy],
      default: () => ({})
    },
    currencyConversionEndpoint: {
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
    },
    transferFormSchema: {
      type: [Array, Object],
      default: () => []
    },
    paymentUrl: {
      type: String,
      default: ''
    },
    checkoutUrl: {
      type: String,
      default: null
    },
    completeUrl: {
      type: String,
      default: null
    },

    transactionFeePercentage: {
      type: Number,
      default: 3
    },
    transactionFeeDescription: {
      type: String,
      default: 'transaction fee'
    },
  },

  setup(props, { emit }) {
    const submitForm = inject('submitForm');
    const { formatPrice } = useCurrency();

    // Refs
    const localPaymentMethod = ref('');
    const localDefaultPaymentMethod = ref("-1");
    const currencyModel = ref(props.price_object.currency_id || props.supportedCurrencies[0]?.id);
    const displayPrice = ref(formatPrice.value(props.price_object.total_amount / 100, props.supportedCurrencies[0]?.symbol || ''));

    const builtInFormLoading = ref(true);
    const builtInFormAttributes = ref({});

    // Reactive state
    const localCreditCard = reactive({
      card_name: '',
      card_number: '',
      card_month: '',
      card_year: '',
      card_cvv: ''
    });

    // Computed
    const selectedCurrency = computed(() =>
      props.supportedCurrencies.find(curr => curr.id === currencyModel.value)
    );

    const formattedCurrencies = computed(() =>
      props.supportedCurrencies.map(currency => ({
        id: currency.id,
        display: `${currency.symbol} - ${currency.name}`,
      }))
    );

    const filteredServiceItems = computed(() => {
      if (!currencyModel.value) return [];

      return props.items.filter(service => {
        return service.payment_currencies?.some(currency => currency.id === currencyModel.value)
      });
    });

    const currencyHasExternalService = computed(() => {
      return filteredServiceItems.value.some(service => service.is_external);
    });

    const selectedService = computed(() => {
      // ensure dependency tracking and avoid type mismatch issues
      const items = filteredServiceItems.value;
      const methodValue = localPaymentMethod.value;

      if (methodValue == null || methodValue === '') return null;

      return items.find(service => service[props.itemValue] === methodValue) || null;
    });

    const transferFormModel = ref({
      ...getModel(props.transferFormSchema),
      price_id: props.price_object.id,
      payment_service_id: localPaymentMethod.value,
      currency_id: selectedCurrency.value?.id ?? null,
    })

    const transferSchema = computed(() => {
      return getSchema(props.transferFormSchema, transferFormModel.value)
    })

    const serviceIsTransferrable = (service) => {
      if (!service) return false;

      return service.transferrable
    };

    const serviceHasCreditCard = (service) => {
      if (!service) return true;

      return service.is_internal && !serviceIsTransferrable(service)
    };

    const isCreditCardForm = computed(() => {
      return serviceHasCreditCard(selectedService.value)
    });

    const isTransferrableForm = computed(() => {
      return serviceIsTransferrable(selectedService.value)
    });

    const input = computed({
      get: () => props.modelValue,
      set: (newValue) => emit('update:modelValue', newValue)
    });

    const isExchanged = computed(() =>
      selectedCurrency.value?.iso_4217 !== props.baseCurrency
    );

    const exchangeRate = ref(0);

    const updateTransferModel = (event) => {
      // console.log('updateTransferModel', event)
      transferFormModel.value = event;
    }

    const createTransferModel = () => {
      return {
        ...getModel(props.transferFormSchema),
        price_id: props.price_object.id,
        payment_service_id: localPaymentMethod.value,
        currency_id: selectedCurrency.value?.id ?? null,
      }
    }

    const handleTransferSubmit = (data) => {
      console.log('transfer submitted', data);
    }

    const setBuiltInFormAttributes = (attributes) => {
      builtInFormAttributes.value = attributes;
    }

    const runBuiltInForm = () => {
      axios.post(props.checkoutUrl, {
        price_id: props.price_object.id,
        payment_service: {
          payment_method: selectedCurrency.value.payment_service_id,
          credit_card: {
            card_name: localCreditCard.card_name,
            card_number: localCreditCard.card_number,
            card_month: localCreditCard.card_month,
            card_year: localCreditCard.card_year,
            card_cvv: localCreditCard.card_cvv,
          },
          currency: selectedCurrency.value,
        }
      }).then(response => {
        setBuiltInFormAttributes({...response.data, orderId: response.data.order_id});
        builtInFormLoading.value = false;
      });
    }

    // Methods
    const handleCurrencyChange = async (newCurrencyId) => {
      currencyModel.value = newCurrencyId;
      localPaymentMethod.value = localDefaultPaymentMethod.value;

      const selectedCurrencyObject = props.supportedCurrencies.find(curr => curr.id === newCurrencyId);
      if (!selectedCurrencyObject || !props.currencyConversionEndpoint) return;

      try {
        const response = await axios.post(props.currencyConversionEndpoint, {
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

    watch([localPaymentMethod, localCreditCard, selectedCurrency], () => {
      if(selectedCurrency.value.has_built_in_form){
        runBuiltInForm();
      }

      input.value = {
        payment_method: localPaymentMethod.value,
        credit_card: { ...localCreditCard },
        currency: selectedCurrency.value,
      };
    }, { deep: true });

    watch(isTransferrableForm, (newValue) => {
      if(newValue){
        let paymentServiceID = localPaymentMethod.value;
        transferFormModel.value.payment_service_id = paymentServiceID;
        transferFormModel.value.currency_id = selectedCurrency.value.id;
      }
    }, { deep: true });

    if(selectedCurrency.value.has_built_in_form){
      runBuiltInForm();
    }

    return {
      input,
      localPaymentMethod,
      localDefaultPaymentMethod,
      localCreditCard,

      isCreditCardForm,
      isTransferrableForm,

      submitForm,

      currencyModel,
      selectedCurrency,
      formattedCurrencies,
      filteredServiceItems,
      currencyHasExternalService,

      transferSchema,
      transferFormModel,
      createTransferModel,
      updateTransferModel,
      handleTransferSubmit,

      handleCurrencyChange,
      displayPrice,
      selectedService,
      exchangeRate,
      isExchanged,

      builtInFormLoading,
      builtInFormAttributes,
      setBuiltInFormAttributes,
      runBuiltInForm,
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
    // flex-flow: row;
    flex-wrap: wrap;
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
      }
      .v-img__img--relative{
        background: rgba(211, 216, 221, 0.30);
        width:min-content;
        padding: 8px 28px;
        border-radius: 4px;
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
