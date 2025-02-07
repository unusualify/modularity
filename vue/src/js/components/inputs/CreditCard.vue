<template>
    <div class="card-item" :class="{ '-active': isCardFlipped }">
      <div class="card-item__side -front">
        <div class="card-item__focus" :class="{ '-active': focusElementStyle }" :style="focusElementStyle"
          ref="focusElement"></div>
        <div class="card-item__cover">
          <img v-if="currentCardBackground" :src="currentCardBackground" class="card-item__bg" />
        </div>
        <div class="card-item__wrapper">
          <div class="card-item__top">
            <img src="/png/cardtypes/chip.png" class="card-item__chip" />
            <div class="card-item__type">
              <Transition name="slide-fade-up">
                <img :src="cardImage" v-if="cardImage" :key="cardImage" alt class="card-item__typeImg" />
              </Transition>
            </div>
          </div>
          <label :for="fields.cardNumber" class="card-item__number" :ref="fields.cardNumber">
            <span v-for="(n, $index) in currentPlaceholder" :key="$index">
              <Transition name="slide-fade-up">
                <div class="card-item__numberItem" v-if="getIsNumberMasked($index, n)">*</div>
                <div class="card-item__numberItem" :class="{ '-active': n.trim() === '' }" :key="currentPlaceholder"
                  v-else-if="labels.cardNumber.length > $index">{{ labels.cardNumber[$index] }}</div>
                <div class="card-item__numberItem" :class="{ '-active': n.trim() === '' }" v-else
                  :key="currentPlaceholder + 1">{{ n }}</div>
              </Transition>
            </span>
          </label>
          <div class="card-item__content">
            <label :for="fields.cardName" class="card-item__info" :ref="fields.cardName">
              <div class="card-item__holder">{{ $t('Card Holder') }}</div>
              <Transition name="slide-fade-up">
                <div class="card-item__name" v-if="labels.cardName.length" key="1">
                  <TransitionGroup name="slide-fade-right">
                    <span class="card-item__nameItem" v-for="(n, $index) in labels.cardName.replace(/\s\s+/g, ' ')"
                      :key="$index + 1">{{ n }}</span>
                  </TransitionGroup>
                </div>
                <div class="card-item__name" v-else key="2">{{ $t('Full Name') }}</div>
              </Transition>
            </label>
            <div class="card-item__date" ref="cardDate">
              <label :for="fields.cardMonth" class="card-item__dateTitle">{{ $t('Expire Date') }}</label>
              <label :for="fields.cardMonth" class="card-item__dateItem">
                <Transition name="slide-fade-up">
                  <span v-if="labels.cardMonth" :key="labels.cardMonth">{{ labels.cardMonth }}</span>
                  <span v-else key="2">{{ $t('MM') }}</span>
                </Transition>
              </label>
              /
              <label :for="fields.cardYear" class="card-item__dateItem">
                <Transition name="slide-fade-up">
                  <span v-if="labels.cardYear" :key="labels.cardYear">{{ String(labels.cardYear).slice(2, 4) }}</span>
                  <span v-else key="2">{{ $t('YY') }}</span>
                </Transition>
              </label>
            </div>
          </div>
        </div>
      </div>
      <div class="card-item__side -back">
        <div class="card-item__cover">
          <img v-if="currentCardBackground" :src="currentCardBackground" class="card-item__bg" />
        </div>
        <div class="card-item__band"></div>
        <div class="card-item__cvv">
          <div class="card-item__cvvTitle">CVV</div>
          <div class="card-item__cvvBand">
            <span v-for="(n, $index) in labels.cardCvv" :key="$index">*</span>
          </div>
          <div class="card-item__type">
            <img :src="cardImage" v-if="cardImage" class="card-item__typeImg" />
          </div>
        </div>
      </div>
    </div>
</template>

<script>
import { ref, computed, watch, onMounted, nextTick } from 'vue';

export default {
  name: 'CreditCard',
  props: {
    labels: Object,
    fields: Object,
    isCardNumberMasked: Boolean,
    randomBackgrounds: {
      type: Boolean,
      default: true
    },
    backgroundImage: [String, Object]
  },
  setup(props) {
    const focusElementStyle = ref(null);
    const currentFocus = ref(null);
    const isFocused = ref(false);
    const isCardFlipped = ref(false);
    const amexCardPlaceholder = '#### ###### #####';
    const dinersCardPlaceholder = '#### ###### ####';
    const defaultCardPlaceholder = '#### #### #### ####';
    const currentPlaceholder = ref('');

    const baseUrl = computed(() => {
      let url = new URL(import.meta.url);
      return `${url.protocol}//${url.hostname}${url.port ? ':' + url.port : ''}`;
    });

    const cardImage = computed(() => {
      let number = props.labels.cardNumber;
      let img = `.${import.meta.env.BASE_URL}/png/cardtypes/`;
      switch (true) {
        case /^4/.test(number):
          img = img + 'visa.png';
          break;
        case /^(34|37)/.test(number):
          img = img + 'amex.png';
          break;
        case /^5[1-5]/.test(number):
          img = img + 'mastercard.png';
          break;
        case /^6011/.test(number):
          img = img + 'discover.png';
          break;
        case /^62/.test(number):
          img = img + 'unionpay.png';
          break;
        case /^9792/.test(number):
          img = img + 'troy.png';
          break;
        case /^3(?:0([0-5]|9)|[689]\d?)\d{0,11}/.test(number):
          img = img + 'dinersclub.png';
          break;
        case /^35(2[89]|[3-8])/.test(number):
          img = img + 'jcb.png';
          break;
        default:
          img = img + 'visa.png';
          break;
      }
      return new URL(img, baseUrl.value).href;
    });

    const cardType = computed(() => {
      let number = props.labels.cardNumber;
      let img = `.${import.meta.env.BASE_URL}/public/png/cardtypes/`;
      switch (true) {
        case /^4/.test(number):
          return 'visa';
          break;
        case /^(34|37)/.test(number):
          return 'amex';
          break;
        case /^5[1-5]/.test(number):
          return 'mastercard';
          break;
        case /^6011/.test(number):
          return 'discover';
          break;
        case /^62/.test(number):
          return 'unionpay';
          break;
        case /^9792/.test(number):
          return 'troy';
          break;
        case /^3(?:0([0-5]|9)|[689]\d?)\d{0,11}/.test(number):
          return 'dinersclub';
          break;
        case /^35(2[89]|[3-8])/.test(number):
          return 'jcb';
          break;
        default:
          return 'visa';
          break;
      }
      return new URL(img, baseUrl.value).href;
    });
    const currentCardBackground = computed(() => {
      if (props.randomBackgrounds && !props.backgroundImage) {
        let random = Math.floor(Math.random() * 25 + 1);
        let img = `.${import.meta.env.BASE_URL}/jpg/payment/${random}.jpeg`;
        let cardBg = new URL(img, baseUrl.value).href;
        return cardBg;
      } else if (props.backgroundImage) {
        return props.backgroundImage;
      } else {
        return null;
      }
    });

    const changeFocus = () => {
      const target = document.querySelector(`label[for="${currentFocus.value}"]`);;
      focusElementStyle.value = target
        ? {
          width: `${target.offsetWidth}px`,
          height: `${target.offsetHeight}px`,
          transform: `translateX(${target.offsetLeft}px) translateY(${target.offsetTop}px)`,
        }
        : null;
    };

    const getIsNumberMasked = (index, n) => {
      return index > 4 && index < 14 && props.labels.cardNumber.length > index && n.trim() !== '' && props.isCardNumberMasked;
    };

    const changePlaceholder = () => {
      if (cardType.value === 'amex') {
        currentPlaceholder.value = amexCardPlaceholder;
      } else if (cardType.value === 'dinersclub') {
        currentPlaceholder.value = dinersCardPlaceholder;
      } else {
        currentPlaceholder.value = defaultCardPlaceholder;
      }
      nextTick(() => {
        changeFocus();
      });
    };

    watch(currentFocus, () => {
      if (currentFocus.value) {
        changeFocus();
      } else {
        focusElementStyle.value = null;
      }
    });

    watch(cardType, () => {
      changePlaceholder();
    });

    onMounted(() => {
      changePlaceholder();
      let fields = document.querySelectorAll('[data-card-field] input');
      fields.forEach((element) => {
        element.addEventListener('focus', () => {
          isFocused.value = true;
          if (element.id === props.fields.cardYear || element.id === props.fields.cardMonth) {
            currentFocus.value = 'cardDate';
          } else {
            currentFocus.value = element.id;
          }
          isCardFlipped.value = element.id === props.fields.cardCvv;
        });
        element.addEventListener('blur', () => {
          isCardFlipped.value = element.id === props.fields.cardCvv;
          setTimeout(() => {
            if (!isFocused.value) {
              currentFocus.value = null;
            }
          }, 300);
          isFocused.value = false;
        });
      });
    });

    return {
      focusElementStyle,
      currentFocus,
      isFocused,
      isCardFlipped,
      baseUrl,
      currentPlaceholder,
      cardType,
      cardImage,
      currentCardBackground,
      changeFocus,
      getIsNumberMasked,
      changePlaceholder
    };
  }
};
</script>

<style scoped>
.slide-fade-up-enter-active {
  transition: all 0.25s ease-in-out;
  transition-delay: 0.1s;
  position: relative;
}

.slide-fade-up-leave-active {
  transition: all 0.25s ease-in-out;
  position: absolute;
}

.slide-fade-up-enter {
  opacity: 0;
  transform: translateY(15px);
  pointer-events: none;
}

.slide-fade-up-leave-to {
  opacity: 0;
  transform: translateY(-15px);
  pointer-events: none;
}

.slide-fade-right-enter-active {
  transition: all 0.25s ease-in-out;
  transition-delay: 0.1s;
  position: relative;
}

.slide-fade-right-leave-active {
  transition: all 0.25s ease-in-out;
  position: absolute;
}

.slide-fade-right-enter {
  opacity: 0;
  transform: translateX(10px) rotate(45deg);
  pointer-events: none;
}

.slide-fade-right-leave-to {
  opacity: 0;
  transform: translateX(-10px) rotate(45deg);
  pointer-events: none;
}
</style>
