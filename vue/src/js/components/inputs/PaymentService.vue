<template>
  <v-input v-model="input">
    <v-row
      no-gutters
      :inert="paymentProcessing"
      :aria-busy="paymentProcessing"
    >
      <v-col class="d-flex flex-column justify-center px-0">
        <v-card class="pa-4 payment-container" elevation="0">
          <v-card-text class="pa-0">
            <p class="text-h5 text-center ma-6">{{ $t('How would you like to pay?') }}</p>
          </v-card-text>

          <!-- Currency Selector -->
          <v-select
            v-model="currencyModel"
            :items="formattedCurrencies"
            :label="$t('Currency')"
            variant="outlined"
            density="comfortable"
            item-title="display"
            item-value="id"
            @update:model-value="handleCurrencyChange"
            class="mx-6"
            :disabled="builtInFormLoading"
          />

          <!-- Payment Methods -->
          <v-radio-group v-model="localPaymentMethod" column >
            <!-- Credit Card Option -->
            <div v-if="creditCardService"
              class="service-container px-3 py-cs-1 credit-card-service"
              :class="{ 'selected-service--focus': localPaymentMethod === localDefaultPaymentMethod }"
            >
              <v-radio
                :label="$t('Credit Card') + ' ' + (creditCardService.has_transaction_fee && includeTransactionFee ? '(+' + creditCardService.transaction_fee_percentage + '%)*' : '')"
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
                 :label="`${service.name} ${service.has_transaction_fee && includeTransactionFee ? '(+' + service.transaction_fee_percentage + '%)*' : ''}`"
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
            <div v-if="currencyHasTransactionFee" class="service-container" style="border: none;">
              <div class="text-body-1">
                {{ '* ' + $t(transactionFeeDescription) }}
              </div>
            </div>
          </v-radio-group>
        </v-card>

        <!-- Total Amount Display -->
        <v-card-title class="headline">
          <p class="total mb-2">{{ $t('Total Amount') }}</p>
          <p class="amount mb-2">{{ displayPriceFormatted }}</p>
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
              @cancel="runBuiltInForm"
              @success="handlePaymentSuccess"
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
              :modelValue="transferFormModel"
              :action-url="paymentUrl"

              noDefaultSurface
              noDefaultFormPadding
              hasSubmit
              buttonText="I Have Completed The Transfer"
              @submitted="handleTransferSubmit"
            >
              <template #submit="{ validForm, loading, saveForm }">
                <v-btn color="success" block :disabled="!validForm || transferFormCompleted" :loading="loading"  @click="startTransferSubmission(saveForm)">
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

  <Teleport to="body">
    <div v-if="paymentProcessing" class="payment-service-overlay">
      <v-progress-circular indeterminate color="primary" size="64" :width="5" />
      <p class="mt-4 text-body-1 font-weight-medium">{{ $t('Please wait...') }}</p>
    </div>
  </Teleport>
</template>

<script>
import { computed, ref, reactive, watch, inject } from 'vue';
import { useI18n } from 'vue-i18n';
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

    includeTransactionFee: {
      type: Boolean,
      default: false
    },
    transactionFeeDescription: {
      type: String,
      default: 'Transaction fee for the payment service'
    },
    useCountryBasedVatRates: {
      type: Boolean,
      default: false
    },
  },

  setup(props, { emit }) {
    const submitForm = inject('submitForm');
    const { formatPrice } = useCurrency();
    const { t } = useI18n();

    // Refs
    const localPaymentMethod = ref('');
    const localDefaultPaymentMethod = ref(-1);
    const currencyModel = ref(props.price_object.currency_id || props.supportedCurrencies[0]?.id);
    const transferFormCompleted = ref(false);

    const selectedCurrency = computed(() =>
      props.supportedCurrencies.find(curr => curr.id === currencyModel.value)
    );

    const currencyHasCreditCardService = computed(() => {
      return !!selectedCurrency.value.payment_service
    });

    const creditCardService = computed(() => {
      return selectedCurrency.value.payment_service;
    });

    const currencyHasTransactionFee = computed(() => {
      return props.includeTransactionFee && (selectedCurrency.value.payment_service?.has_transaction_fee || selectedCurrency.value.payment_services.some(service => service.has_transaction_fee));
    });

    const selectedPaymentService = computed(() => {
      return localPaymentMethod.value == -1
        ? selectedCurrency.value.payment_service
        : selectedCurrency.value.payment_services.find(service => service.id === localPaymentMethod.value);
    });

    const calculatePrice = (amount) => {
      let transactionFeePercentage = props.includeTransactionFee ? selectedPaymentService.value?.transaction_fee_percentage ?? 0 : 0;
      let transactionFeeMultiplier = transactionFeePercentage / 100;
      let transactionFee = transactionFeeMultiplier * amount;

      return (amount + Math.round(transactionFee)) / 100;
    }

    const priceAmount = ref(props.useCountryBasedVatRates && selectedCurrency.value.companyVatRate
      ? props.price_object.discounted_raw_amount * (1 + selectedCurrency.value.companyVatRate.vat_multiplier)
      : props.price_object.total_amount
    );

    const calculatedPriceAmount = computed(() => {
      return calculatePrice(priceAmount.value);
    });

    const displayPriceFormatted = computed(() => {
      return formatPrice(calculatedPriceAmount.value, selectedCurrency.value?.symbol || '');
    });

    const builtInFormLoading = ref(true);
    const builtInFormAttributes = ref({});
    const paymentProcessing = ref(false);

    // Lock the entire payment form once a built-in gateway (e.g. Revolut) reports
    // a successful charge. The server-side completion call will redirect the user
    // shortly after, but this prevents a second payment attempt during that window.
    const handlePaymentSuccess = () => {
      paymentProcessing.value = true;
    };

    // Reactive state
    const localCreditCard = reactive({
      card_name: '',
      card_number: '',
      card_month: '',
      card_year: '',
      card_cvv: ''
    });

    const formattedCurrencies = computed(() =>
      props.supportedCurrencies.map(supportedCurrency => ({
        id: supportedCurrency.id,
        display: `${supportedCurrency.symbol} - ${supportedCurrency.name}` + (props.useCountryBasedVatRates && supportedCurrency.companyVatRate? ` (+${supportedCurrency.companyVatRate.vat_percentage}% ` + t('VAT') + ')'  : ''),
      }))
    );

    const filteredServiceItems = computed(() => {
      if (!currencyModel.value) return [];

      return props.items.filter(service => {
        return service.payment_currencies?.some(currency => currency.id === currencyModel.value && (service.is_external || service.transferrable))
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

      return items.find(service => service[props.itemValue] === methodValue) || selectedCurrency.value.payment_service || null;
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
      if (!service) return false;

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

    if(!currencyHasCreditCardService.value){
      localPaymentMethod.value = filteredServiceItems.value[0].id;

      input.value = {
        payment_method: localPaymentMethod.value,
        credit_card: { ...localCreditCard },
        currency: selectedCurrency.value,
      };
    } else {
      localPaymentMethod.value = localDefaultPaymentMethod.value;

      input.value = {
        payment_method: localPaymentMethod.value,
        credit_card: { ...localCreditCard },
        currency: selectedCurrency.value,
      };
    }

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

    // Optimistically lock the form the moment the user clicks "I Have Completed
    // The Transfer" so the "Please wait…" overlay shows immediately, not after
    // the ~1s network round-trip. If the server replies with a non-success
    // status the overlay is released again in handleTransferSubmit.
    const startTransferSubmission = (saveForm) => {
      handlePaymentSuccess();
      saveForm();
    }

    const handleTransferSubmit = (data) => {
      if(data.status === 'success'){
        transferFormCompleted.value = true;
        handlePaymentSuccess();
      } else {
        // Server rejected the submission — release the overlay so the user can
        // see/fix the errors instead of being stuck on "Please wait…".
        paymentProcessing.value = false;
      }
    }

    const setBuiltInFormAttributes = (attributes) => {
      builtInFormAttributes.value = attributes;
    }

    const runBuiltInForm = () => {
      builtInFormLoading.value = true;
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

      // const selectedCurrencyObject = props.supportedCurrencies.find(curr => curr.id === newCurrencyId);
      const selectedCurrencyObject = _.cloneDeep(selectedCurrency.value);
      if (!selectedCurrency.value || !props.currencyConversionEndpoint) return;

      try {
        const response = await axios.post(props.currencyConversionEndpoint, {
          currency: selectedCurrency.value.iso_4217,
          amount: props.price_object.discounted_raw_amount
        });

        exchangeRate.value = response.data.exchange_rate;

        let vatMultiplier = props.price_object.vat_multiplier;
        if(props.useCountryBasedVatRates && selectedCurrency.value.companyVatRate){
          vatMultiplier = selectedCurrency.value.companyVatRate.vat_multiplier;
        }

        // const calculatedAmount = response.data.converted_amount * ( 1 + vatMultiplier);
        const calculatedAmount = parseInt(response.data.converted_amount * ( 1 + vatMultiplier));
        priceAmount.value = calculatedAmount;

        emit('update:price', displayPriceFormatted.value);
        emit('currency-converted', displayPriceFormatted.value);
      } catch (error) {
        console.error('Currency conversion error:', error);
      }
    };

    // Watchers
    watch(currencyModel, (newValue) => {
      localPaymentMethod.value = currencyHasCreditCardService.value ? localDefaultPaymentMethod.value : filteredServiceItems.value[0]?.id ?? -1;
    });

    watch(() => props.modelValue, (newValue) => {
      if (newValue && typeof newValue === 'object') {
        // console.log('watch props.modelValue', newValue.payment_method || localDefaultPaymentMethod.value, newValue);
        // localPaymentMethod.value = newValue.payment_method || localPaymentMethod.value || localDefaultPaymentMethod.value;
        // Object.assign(localCreditCard, newValue.credit_card || {});
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

    watch([isTransferrableForm, localPaymentMethod, selectedCurrency], () => {
      if(isTransferrableForm.value){
        transferFormModel.value.payment_service_id = localPaymentMethod.value;
        transferFormModel.value.currency_id = selectedCurrency.value?.id ?? null;
      }
    });

    if(selectedCurrency.value.has_built_in_form){
      runBuiltInForm();
    }

    return {
      input,
      localPaymentMethod,
      localDefaultPaymentMethod,
      localCreditCard,
      creditCardService,

      selectedPaymentService,
      currencyHasTransactionFee,

      isCreditCardForm,
      isTransferrableForm,

      submitForm,

      currencyModel,
      selectedCurrency,
      formattedCurrencies,
      filteredServiceItems,
      currencyHasExternalService,

      transferFormCompleted,
      transferSchema,
      transferFormModel,
      createTransferModel,
      updateTransferModel,
      handleTransferSubmit,

      handleCurrencyChange,
      displayPriceFormatted,
      selectedService,
      exchangeRate,
      isExchanged,

      builtInFormLoading,
      builtInFormAttributes,
      setBuiltInFormAttributes,
      runBuiltInForm,
      paymentProcessing,
      handlePaymentSuccess,
      startTransferSubmission,
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

      .v-img__img--relative {
        background: rgba(211, 216, 221, 0.30);
        padding: 8px 28px;
        border-radius: 4px;
      }
    }
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

  .payment-service-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(2px);
    color: #20363B;
    pointer-events: auto;
    // It's a status indicator, not content — don't let the user highlight it.
    user-select: none;
    -webkit-user-select: none;
  }
</style>
