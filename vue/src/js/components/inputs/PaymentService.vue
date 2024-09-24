<template>
  <v-input v-model="input">
    <v-row>
      <v-col class="d-flex flex-column justify-center">
        <v-card class="pa-4 payment-container" elevation="2">
          <v-card-title class="headline">
            <p class="total">Total Pay:</p>
            <p class="amount">{{ price }}</p>
          </v-card-title>
          <v-card-text>
            <p class="select-title">How would you like to pay?</p>
          </v-card-text>
          <v-radio-group v-model="localPaymentMethod" column>
            <div class="service-container">
              <v-radio :label="$t('Credit Card')" :value="-1" class="service-label">
              </v-radio>
              <v-row align="center">
                <v-col class="d-flex justify-content-end">
                  <div class="service-icon-container">
                    <v-img-icon src="/storage/uploads/38fd5286-5643-48fc-8a5c-b6f1452f1276/group-25287.png" contain class="v-img__img--relative"></v-img-icon>
                  </div>
                </v-col>
              </v-row>
            </div>
            <div v-for="(service, key) in serviceItems" :key="`service-${key}`" class="service-container">
              <v-radio :label="service.title" :value="service[itemValue]" class="service-label">
              </v-radio>
              <v-row align="center">
                <v-col class="d-flex justify-content-end">
                  <div class="service-icon-container">
                    <v-img-icon :src="service[iconKey]" contain class="v-img__img--relative"></v-img-icon>
                  </div>
                </v-col>
              </v-row>
            </div>
          </v-radio-group>
        </v-card>
      </v-col>
      <v-col>
        <CreditCardForm
          v-if="showCreditCardForm"
          v-model:cardName="localCreditCard.card_name"
          v-model:cardNumber="localCreditCard.card_number"
          v-model:cardMonth="localCreditCard.card_month"
          v-model:cardYear="localCreditCard.card_year"
          v-model:cardCvv="localCreditCard.card_cvv"
        />
        <v-btn
          v-if="!showCreditCardForm"
          color="primary"
          @click="submitForm"
        >
          Pay with {{ selectedService?.title || 'External Service' }}
        </v-btn>
      </v-col>
    </v-row>
  </v-input>
</template>

<script>
import { computed, ref, reactive, watch, inject } from 'vue';
import { makeInputProps, makeInputEmits } from '@/hooks';
import CreditCardForm from '@/components/inputs/CreditCardForm';
import useValidation from '@/hooks/useValidation';

export default {
  name: 'PaymentService',
  emits: [
    ...makeInputEmits,
    'update:modelValue',
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
    itemTitle: {
      type: String,
      default: 'name'
    },
    price: {
      type: String,
    },
    currency:{
      type: Number,
    },
    items: {
      type: Array,
      default: () => []
    },
    serviceItems: {
      type: Array,
      default: () => []
    },
    item: {
      type: [Object, Proxy, Array],
      default () {
        return {}
      }
    },
    iconKey: {
      default: '_icon'
    },
    showCreditCardForm : {
      type: Boolean,
      default: true
    }
  },
  components: {
    CreditCardForm
  },
  setup(props, { emit }) {

    const submitForm = inject('submitForm')


    const localPaymentMethod = ref('');
    const localCreditCard = reactive({
      card_name: '',
      card_number: '',
      card_month: '',
      card_year: '',
      card_cvv: ''
    });

    const input = computed({
      get: () => props.modelValue,
      set: (newValue) => {
        emit('update:modelValue', newValue);
      }
    });
    watch(() => props.modelValue, (newValue) => {
      if (newValue && typeof newValue === 'object') {
        localPaymentMethod.value = newValue.payment_method || '';
        Object.assign(localCreditCard, newValue.credit_card || {});
      }
    }, { immediate: true, deep: true });

    watch([localPaymentMethod, localCreditCard], () => {
      input.value = {
        payment_method: localPaymentMethod.value,
        credit_card: { ...localCreditCard }
      };
    }, { deep: true });

    const filterItemsByCurrencyId = (items, currencyId) => {
      return items.filter(item => {
        // Check if payment_currencies exists and is an array
        if (Array.isArray(item.payment_currencies)) {
          // Check if any currency in payment_currencies has the specified id
          return item.payment_currencies.some(currency => currency.id === currencyId);
        }
        // If payment_currencies doesn't exist or isn't an array, keep the item
        // (adjust this logic if you want to remove such items instead)
        return true;
      });
    };

    const serviceItems = computed(() => {
      return filterItemsByCurrencyId(props.items, props.currency);
    });

    props.serviceItems = serviceItems;

    const selectedService = computed(() => {
      return serviceItems.value.find(service => service[props.itemValue] === localPaymentMethod.value);
    });

    const showCreditCardForm = computed(() => {
      return !selectedService.value || !selectedService.value.is_external;
    });


    const validatePaymentService = () => {
      const isValid = useValidation(
        { rules: 'required' },
        localPaymentMethod.value
      );
      return isValid === true ? true : 'Please select a payment service';
    };

    return {
      input,
      localPaymentMethod,
      localCreditCard,
      serviceItems,
      validatePaymentService,
      showCreditCardForm,
      submitForm
    };
  }
};
</script>

<!-- Styles remain unchanged -->
<style lang="scss" scoped>
.payment-container {
  padding: 0 !important;
}

.payment-card {
  padding: 2rem;
}

.headline{
  background-color: #7CB749;
  color: white;
  padding: 2rem;
  border-radius: 8px 8px 0 0;

  *{
    text-align: center;
    font-weight: 700;
    line-height: 1;
  }
  .total{
    font-size: 1.5rem;
    color: #47791D;
  }
  .amount{
    font-size: 4rem;
    color: #fff;
  }
}

.service-label{
 width: 100%;
}

.select-title {
  font-size: 1.3rem;
  color: #323C47;
  font-weight: 700;
  text-align: center;
  margin: 3rem 0;
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

  .service-icon-container {
    width: fit-content;
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
    .service-icon-container {
      .v-img__img--relative {
        img {
          position: relative !important;
        }
      }
    }
</style>
