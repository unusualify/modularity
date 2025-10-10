<template>
  <slot name="button" v-bind="{ pay }">
    <v-btn id="pay-button" @click="pay">Pay</v-btn>
  </slot>
</template>

<script>
import RevolutCheckout from '@revolut/checkout';
import { useDynamicModal } from '@/hooks';
import { redirector } from '@/utils/response';

export default {
  props: {
    token: String,
    env: String,
    paymentId: String,
    orderId: String,
    revolutOrderId: String,
    completeUrl: String,
  },

  setup(props) {
    const DynamicModal = useDynamicModal()

    let payWithPopup = null;

    RevolutCheckout(props.token, props.env).then(instance => {
      payWithPopup = instance.payWithPopup;
    });

    const pay = () => {
      payWithPopup({
        email: 'test@test.com',
        onSuccess(...args) {
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
        onError(...args) {
          axios.post(props.completeUrl + '?id=' + props.paymentId + '&status=error', {
            id: props.paymentId,
            status: 'error',
          }).then(() => {
            DynamicModal.close();
          });
          console.log('error', args);
        },
        onClose() {
          console.log('close');
        },
        onCancel() {
          console.log('cancel');
        },
      });
    }

    return { pay };
  },
  mounted() {
    // const loader = document.getElementById('revolut-loader');
    // const showLoader = () => { loader.style.display = 'flex'; };
    // const hideLoader = () => { loader.style.display = 'none'; };

    // const setButtonBusy = (btn, busy) => {
    //   if (!btn) return;
    //   if (busy) {
    //     btn.disabled = true;
    //     if (!btn.dataset.originalText) btn.dataset.originalText = btn.textContent;
    //     btn.textContent = 'Processing...';
    //     btn.setAttribute('aria-busy', 'true');
    //   } else {
    //     btn.disabled = false;
    //     btn.textContent = btn.dataset.originalText || 'Pay';
    //     btn.removeAttribute('aria-busy');
    //   }
    // };

    // try {
    //   RevolutCheckout(this.token, this.env).then(instance => {
    //     console.log('instance', this.token, this.env, instance);

    //     let card = instance.createCardField({
    //       target: document.getElementById('card-field'),
    //       onSuccess() {
    //         console.log('success');
    //       },
    //       onError(error) {
    //         console.log('error', error);
    //       },
    //     });

    //     document.getElementById('button-submit').addEventListener('click', () => {
    //       card.submit();
    //     });
    //   });

    //   // console.log('instance', this.token, this.env, instance);
    //   // const cardTarget = document.getElementById('card-field');

    //   // if (cardTarget) {
    //   //   const submitBtn = document.getElementById('button-submit');

    //   //   const cardField = instance.createCardField({
    //   //     target: cardTarget,
    //   //     onSuccess() {
    //   //       hideLoader();
    //   //       setButtonBusy(submitBtn, false);
    //   //       // Handle successful payment (e.g., redirect to success page)
    //   //       this.$emit('payment-success');
    //   //     },
    //   //     onError(error) {
    //   //       hideLoader();
    //   //       setButtonBusy(submitBtn, false);
    //   //       this.$emit('payment-error', error);
    //   //     },
    //   //   });

    //   //   if (submitBtn) {
    //   //     submitBtn.addEventListener('click', () => {
    //   //       const nameInput = document.querySelector('input[name="cardholder-name"]');
    //   //       const cardholderName = (nameInput && nameInput.value) ? nameInput.value : '';
    //   //       const meta = { name: cardholderName, cardholderName };

    //   //       showLoader();
    //   //       setButtonBusy(submitBtn, true);
    //   //       cardField.submit(meta);
    //   //     });
    //   //   }
    //   // }
    // } catch (e) {
    //   console.error('Failed to initialize Revolut', e);
    //   this.$emit('error', 'Failed to initialize payment: ' + (e?.message || e));
    // }
  }
};
</script>

<style scoped>
@keyframes spin { to { transform: rotate(360deg) } }
</style>
