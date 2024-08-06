<template>
  <v-row>
    <v-col>
      <v-card class="pa-4 payment-container" elevation="2">
        <v-card-title class="headline">
          <p class="total">
            Total Pay:
          </p>
          <p class="amount">
            ${{ totalPay }}
          </p>

        </v-card-title>
        <v-card-text>
          <p class="select-title">
            How would you like to pay?
          </p>
        </v-card-text>

        <v-radio-group v-model="input" column>
          <div v-for="(service, key) in this.items" :key="`service-${key}`" class="service-container">
            <v-radio :label="service.title" :value="service.name" class="service-label">
            </v-radio>
            <v-row align="center">
              <v-col class="d-flex justify-content-end">
                {{ service.label }}
                <div class="service-icon-container">
                  <v-img-icon :src="service._icon" contain class="v-img__img--relative"></v-img-icon>

                </div>

              </v-col>
            </v-row>
          </div>
        </v-radio-group>
        <!-- <v-card-actions>
          <v-btn @click="goBack">Back</v-btn>
        </v-card-actions> -->
      </v-card>
    </v-col>
    <v-col>
      <CreditCardForm />
    </v-col>
  </v-row>
</template>


<script>
import {computed} from 'vue';
import { useInput, makeInputProps, makeInputEmits } from '@/hooks';
import CreditCardForm from '@/components/inputs/CreditCardForm';

export default {
  name: 'PaymentService',
  emits: [...makeInputEmits],
  props: {
    ...makeInputProps(),
    label: {
      type: String,
      default: ''
    },
    itemValue: {
      type: String,
      default: 'id'
    },
    itemTitle: {
      type: String,
      default: 'name'
    },
    totalPay: {
      type: String,
      default: '1500'
    },
    items: {
      type: Array,
      default: () => []
    }
  },
  components: {
    CreditCardForm
  },
  data() {

  },
  setup(props, context) {
    const serviceItems = computed(() => {
      const services = [];
      for (const i in props.items) {
        services[i] = props.items[i];
      }
      return services;
    });

    console.log(serviceItems.value);

    return {
      ...useInput(props, context),
      serviceItems
    }
  },
  methods:{

  }
};

</script>

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
