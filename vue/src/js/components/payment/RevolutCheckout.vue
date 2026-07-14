<template>
  <v-container class="d-flex flex-column justify-center align-center w-100 pa-8">
    <!-- <h2>Revolut Checkout</h2> -->

    <!-- <input type="hidden" name="token" :value="token">
    <input type="hidden" name="env" :value="env">
    <label for="cardholder-name" class="note">Cardholder name</label>
    <input name="cardholder-name" type="text" value="" placeholder="John Doe"> -->
    <div id="embed-form" class="d-flex flex-column" style="height: 200px;"></div>
    <v-btn class="mt-4" block @click="submit">Pay</v-btn>
    <!-- <div style="margin-top:12px">
      <button id="button-submit" @click="submit">Pay</button>
    </div> -->
  </v-container>

  <!-- Loading Overlay -->
  <div id="revolut-loader" style="position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(255,255,255,0.7);z-index:9999;">
    <div style="display:flex;flex-direction:column;align-items:center;font-family:system-ui, -apple-system, Segoe UI, Roboto;">
      <div style="width:32px;height:32px;border:3px solid #ccc;border-top-color:#111;border-radius:50%;animation:spin .9s linear infinite"></div>
      <div style="margin-top:10px;color:#111">Processing...</div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import RevolutCheckout from '@revolut/checkout';

import { useDynamicModal } from '@/hooks';

export default {
  props: {
    token: String,
    env: String,
    paymentId: String,
    revolutOrderId: String,
    orderId: String,
    completeUrl: String,
  },
  setup(props) {
    const DynamicModal = useDynamicModal();
    let createCardField = ref(null);
    RevolutCheckout(props.token, props.env).then(instance => {
      createCardField = instance.createCardField({
        target: document.getElementById('embed-form'),
        onSuccess() {
          DynamicModal.open('ue-recursive-stuff', {
            'props': {
              'configuration': {
                'elements': [
                  {
                    'tag': 'v-progress-circular',
                    'attributes': {
                      'size': 24,
                      'color': 'primary',
                      'indeterminate': true,
                    }
                  },
                  {
                    'tag': 'div',
                    'attributes': {
                      'class': 'd-flex flex-column justify-center align-center w-100',
                    },
                    'elements': 'Payment is processing...'
                  }
                ]
              }
            },
            'modalProps': {
              'widthType': 'md',
              'noActions': true,
            }
          })

          axios.post(props.completeUrl + '?id=' + props.paymentId + '&status=success', {
            id: props.paymentId,
            status: 'success',
          }).then((response) => {
            if(response.status === 200) {
              if(response.data.variant === 'success') {
                if(response.data.redirector) {
                  redirector(response.data);
                }
              }
            }
          }).finally(() => {
            DynamicModal.close();
          });
        },
        onError(error) {
          console.log('error', error);
        },
      });

    });

    watch(createCardField, (newVal) => {
      if (newVal) {
        console.log('createCardField', newVal);
      }
    });

    return { };
  },
  mounted() {
    const loader = document.getElementById('revolut-loader');
    const showLoader = () => { loader.style.display = 'flex'; };
    const hideLoader = () => { loader.style.display = 'none'; };

    const setButtonBusy = (btn, busy) => {
      if (!btn) return;
      if (busy) {
        btn.disabled = true;
        if (!btn.dataset.originalText) btn.dataset.originalText = btn.textContent;
        btn.textContent = 'Processing...';
        btn.setAttribute('aria-busy', 'true');
      } else {
        btn.disabled = false;
        btn.textContent = btn.dataset.originalText || 'Pay';
        btn.removeAttribute('aria-busy');
      }
    };

    try {
      // RevolutCheckout(this.token, this.env).then(instance => {
      //   console.log('instance', this.token, this.env, instance);

      //   let card = instance.createCardField({
      //     target: document.getElementById('embed-form'),
      //     onSuccess() {
      //       console.log('success');
      //     },
      //     onError(error) {
      //       console.log('error', error);
      //     },
      //   });

      //   document.getElementById('button-submit').addEventListener('click', () => {
      //     card.submit();
      //   });
      // });

      // console.log('instance', this.token, this.env, instance);
      // const cardTarget = document.getElementById('card-field');

      // if (cardTarget) {
      //   const submitBtn = document.getElementById('button-submit');

      //   const cardField = instance.createCardField({
      //     target: cardTarget,
      //     onSuccess() {
      //       hideLoader();
      //       setButtonBusy(submitBtn, false);
      //       // Handle successful payment (e.g., redirect to success page)
      //       this.$emit('payment-success');
      //     },
      //     onError(error) {
      //       hideLoader();
      //       setButtonBusy(submitBtn, false);
      //       this.$emit('payment-error', error);
      //     },
      //   });

      //   if (submitBtn) {
      //     submitBtn.addEventListener('click', () => {
      //       const nameInput = document.querySelector('input[name="cardholder-name"]');
      //       const cardholderName = (nameInput && nameInput.value) ? nameInput.value : '';
      //       const meta = { name: cardholderName, cardholderName };

      //       showLoader();
      //       setButtonBusy(submitBtn, true);
      //       cardField.submit(meta);
      //     });
      //   }
      // }
    } catch (e) {
      console.error('Failed to initialize Revolut', e);
      this.$emit('error', 'Failed to initialize payment: ' + (e?.message || e));
    }
  }
};
</script>

<style scoped>
@keyframes spin { to { transform: rotate(360deg) } }
</style>
