<template>
  <div class="card-form">
    <div class="card-list">
      <CreditCard :fields="fields" :labels="formData" :isCardNumberMasked="isCardNumberMasked"
        :randomBackgrounds="randomBackgrounds" :backgroundImage="backgroundImage" />
    </div>
    <div class="card-form__inner">
      <div class="card-input">
        <label for="cardNumber" class="card-input__label">{{ $t('Card Number') }}</label>
        <v-text-field :id="fields.cardNumber" v-model="formData.cardNumber" @input="changeNumber"
          @focus="focusCardNumber" @blur="blurCardNumber" data-card-field type="tel" variant="outlined">
        </v-text-field>
      </div>
      <div class="card-input">
        <label for="cardName" class="card-input__label">{{ $t('Card Holder') }}</label>
        <v-text-field :id="fields.cardName" v-model="formData.cardName" @input="changeName" data-card-field
          variant="outlined">
        </v-text-field>
      </div>
      <div class="card-form__row">
        <div class="card-form__col">
          <div class="card-form__group">
            <label for="cardMonth" class="card-input__label">{{ $t('Expire Date') }}</label>
            <v-select :id="fields.cardMonth" :items="months" :label="$t('Month')" v-model="formData.cardMonth"
              @change="changeMonth" :disabled-item="month => month < minCardMonth" data-card-field
              variant="outlined"></v-select>
            <v-select :id="fields.cardYear" :items="years" :label="$t('Year')" v-model="formData.cardYear"
              @change="changeYear" data-card-field variant="outlined"></v-select>
          </div>
        </div>
        <div class="card-form__col -cvv">
          <div class="card-input">
            <label for="cardCvv" class="card-input__label">{{ $t('CVV') }}</label>
            <v-text-field type="tel" v-model="formData.cardCvv" v-number-only :id="fields.cardCvv" maxlength="4"
              @input="changeCvv" data-card-field autocomplete="off" variant="outlined">
            </v-text-field>
          </div>
        </div>
      </div>
      <v-btn class="card-form__button" @click="invaildCard">
        {{ $t('PAY') }}
      </v-btn>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import CreditCard from '@/components/inputs/CreditCard';

export default {
  name: 'CreditCardForm',
  directives: {
    'number-only': {
      beforeMount(el) {
        function checkValue(event) {
          event.target.value = event.target.value.replace(/[^0-9]/g, '');
          if (event.charCode >= 48 && event.charCode <= 57) {
            return true;
          }
          event.preventDefault();
        }
        el.addEventListener('keypress', checkValue);
      }
    },
    'letter-only': {
      beforeMount(el) {
        function checkValue(event) {
          if (event.charCode >= 48 && event.charCode <= 57) {
            event.preventDefault();
          }
          return true;
        }
        el.addEventListener('keypress', checkValue);
      }
    }
  },
  props: {
    formData: {
      type: Object,
      default: () => reactive({
        cardName: '',
        cardNumber: '',
        cardNumberNotMask: '',
        cardMonth: '',
        cardYear: '',
        cardCvv: ''
      })
    },
    backgroundImage: [String, Object],
    randomBackgrounds: {
      type: Boolean,
      default: true
    }
  },
  components: {
    CreditCard
  },
  setup(props, { emit }) {
    const fields = reactive({
      cardNumber: 'v-card-number',
      cardName: 'v-card-name',
      cardMonth: 'v-card-month',
      cardYear: 'v-card-year',
      cardCvv: 'v-card-cvv'
    });
    const minCardYear = new Date().getFullYear();
    const isCardNumberMasked = ref(true);
    const mainCardNumber = ref(props.formData.cardNumber);
    const cardNumberMaxLength = ref(19);

    const months = computed(() => {
      return [1,2,3,4,5,6,7,8,9,10,11,12];
    });

    const years = computed(() => {
      return Array.from({ length: 12 }, (v, i) => minCardYear + i);
    });

    const minCardMonth = computed(() => {
      return props.formData.cardYear === minCardYear ? new Date().getMonth() + 1 : 1;
    });

    watch(() => props.formData.cardYear, () => {
      if (props.formData.cardMonth < minCardMonth.value) {
        props.formData.cardMonth = '';
      }
    });

    onMounted(() => {
      maskCardNumber();
    });

    const generateMonthValue = (n) => {
      return n < 10 ? `0${n}` : n;
    };

    const changeName = () => {
      emit('v-card-name', props.formData.cardName);
    };

    const changeNumber = (e) => {
      props.formData.cardNumber = e.target.value;
      let value = props.formData.cardNumber.replace(/\D/g, '');
      // American Express, 15 digits
      if ((/^3[47]\d{0,13}$/).test(value)) {
        props.formData.cardNumber = value.replace(/(\d{4})/, '$1 ').replace(/(\d{4}) (\d{6})/, '$1 $2 ');
        cardNumberMaxLength.value = 17;
      } else if ((/^3(?:0[0-5]|[68]\d)\d{0,11}$/).test(value)) { // Diners Club, 14 digits
        props.formData.cardNumber = value.replace(/(\d{4})/, '$1 ').replace(/(\d{4}) (\d{6})/, '$1 $2 ');
        cardNumberMaxLength.value = 16;
      } else if ((/^\d{0,16}$/).test(value)) { // Regular CC number, 16 digits
        props.formData.cardNumber = value.replace(/(\d{4})/, '$1 ').replace(/(\d{4}) (\d{4})/, '$1 $2 ').replace(/(\d{4}) (\d{4}) (\d{4})/, '$1 $2 $3 ');
        cardNumberMaxLength.value = 19;
      }
      if (e.inputType === 'deleteContentBackward') {
        let lastChar = props.formData.cardNumber.substring(props.formData.cardNumber.length - 1);
        if (lastChar === ' ') {
          props.formData.cardNumber = props.formData.cardNumber.substring(0, props.formData.cardNumber.length - 1);
        }
      }
      emit('input-card-number', props.formData.cardNumber);
    };

    const changeMonth = () => {
      emit('input-card-month', props.formData.cardMonth);
    };

    const changeYear = () => {
      emit('input-card-year', props.formData.cardYear);
    };

    const changeCvv = () => {
      emit('input-card-cvv', props.formData.cardCvv);
    };

    const invaildCard = () => {
      let number = props.formData.cardNumberNotMask.replace(/ /g, '');
      let sum = 0;
      for (let i = 0; i < number.length; i++) {
        let intVal = parseInt(number.substr(i, 1));
        if (i % 2 === 0) {
          intVal *= 2;
          if (intVal > 9) {
            intVal = 1 + (intVal % 10);
          }
        }
        sum += intVal;
      }
      if (sum % 10 !== 0) {
        alert(props.$t('cardForm.invalidCardNumber'));
      }
    };

    const blurCardNumber = () => {
      if (isCardNumberMasked.value) {
        maskCardNumber();
      }
    };

    const maskCardNumber = () => {
      props.formData.cardNumberNotMask = props.formData.cardNumber;
      mainCardNumber.value = props.formData.cardNumber;
      let arr = props.formData.cardNumber.split('');
      arr.forEach((element, index) => {
        if (index > 4 && index < 14 && element.trim() !== '') {
          arr[index] = '*';
        }
      });
      props.formData.cardNumber = arr.join('');
    };

    const unMaskCardNumber = () => {
      props.formData.cardNumber = mainCardNumber.value;
    };

    const focusCardNumber = () => {
      unMaskCardNumber();
    };

    const toggleMask = () => {
      isCardNumberMasked.value = !isCardNumberMasked.value;
      if (isCardNumberMasked.value) {
        maskCardNumber();
      } else {
        unMaskCardNumber();
      }
    };

    return {
      fields,
      minCardYear,
      isCardNumberMasked,
      mainCardNumber,
      cardNumberMaxLength,
      months,
      years,
      minCardMonth,
      generateMonthValue,
      changeName,
      changeNumber,
      changeMonth,
      changeYear,
      changeCvv,
      invaildCard,
      blurCardNumber,
      maskCardNumber,
      unMaskCardNumber,
      focusCardNumber,
      toggleMask
    };
  }
};
</script>

<style lang="scss">
* {
  box-sizing: border-box;

  &:focus {
    outline: none;
  }
}

.wrapper {
  min-height: 100vh;
  display: flex;
  padding: 50px 15px;

  @media screen and (max-width: 700px),
  (max-height: 500px) {
    flex-wrap: wrap;
    flex-direction: column;
  }
}

.card-form {
  max-width: 570px;
  margin: auto;
  width: 100%;

  @media screen and (max-width: 576px) {
    margin: 0 auto;
  }

  &__inner {
    background: #fff;
    box-shadow: 0 30px 60px 0 rgba(90, 116, 148, 0.4);
    border-radius: 10px;
    padding: 35px;
    padding-top: 180px;

    @media screen and (max-width: 480px) {
      padding: 25px;
      padding-top: 165px;
    }

    @media screen and (max-width: 360px) {
      padding: 15px;
      padding-top: 165px;
    }
  }

  &__row {
    display: flex;
    align-items: flex-start;

    @media screen and (max-width: 480px) {
      flex-wrap: wrap;
    }
  }

  &__col {
    flex: auto;
    margin-right: 35px;

    &:last-child {
      margin-right: 0;
    }

    @media screen and (max-width: 480px) {
      margin-right: 0;
      flex: unset;
      width: 100%;
      margin-bottom: 20px;

      &:last-child {
        margin-bottom: 0;
      }
    }

    &.-cvv {
      max-width: 150px;

      @media screen and (max-width: 480px) {
        max-width: initial;
      }
    }
  }

  &__group {
    display: flex;
    align-items: flex-start;
    flex-wrap: wrap;

    .card-input__input {
      flex: 1;
      margin-right: 15px;

      &:last-child {
        margin-right: 0;
      }
    }
  }

  &__button {
    width: 100%;
    height: 55px;
    background: #2364d2;
    border: none;
    border-radius: 5px;
    font-size: 1.25rem;
    font-weight: 500;
    font-family: "Source Sans Pro", sans-serif;
    box-shadow: 3px 10px 20px 0px rgba(35, 100, 210, 0.3);
    color: #fff;
    margin-top: 20px;
    cursor: pointer;

    @media screen and (max-width: 480px) {
      margin-top: 10px;
    }
  }
}

.card-item {
  max-width: 430px;
  height: 270px;
  margin-left: auto;
  margin-right: auto;
  position: relative;
  z-index: 2;
  width: 100%;

  @media screen and (max-width: 480px) {
    max-width: 310px;
    height: 220px;
    width: 90%;
  }

  @media screen and (max-width: 360px) {
    height: 180px;
  }

  &.-active {
    .card-item__side {
      &.-front {
        transform: perspective(1000px) rotateY(180deg) rotateX(0deg) rotateZ(0deg);
      }

      &.-back {
        transform: perspective(1000px) rotateY(0) rotateX(0deg) rotateZ(0deg);
      }
    }
  }

  &__focus {
    position: absolute;
    z-index: 3;
    border-radius: 5px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    transition: all 0.35s cubic-bezier(0.71, 0.03, 0.56, 0.85);
    opacity: 0;
    pointer-events: none;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.65);

    &:after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      background: rgb(8, 20, 47);
      height: 100%;
      border-radius: 5px;
      filter: blur(25px);
      opacity: 0.5;
    }

    &.-active {
      opacity: 1;
    }
  }

  &__side {
    border-radius: 15px;
    overflow: hidden;
    // box-shadow: 3px 13px 30px 0px rgba(11, 19, 41, 0.5);
    box-shadow: 0 20px 60px 0 rgba(14, 42, 90, 0.55);
    transform: perspective(2000px) rotateY(0deg) rotateX(0deg) rotate(0deg);
    transform-style: preserve-3d;
    transition: all 0.8s cubic-bezier(0.71, 0.03, 0.56, 0.85);
    backface-visibility: hidden;
    height: 100%;

    &.-back {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      transform: perspective(2000px) rotateY(-180deg) rotateX(0deg) rotate(0deg);
      z-index: 2;
      padding: 0;
      height: 100%;

      .card-item__cover {
        transform: rotateY(-180deg)
      }
    }
  }

  &__bg {
    max-width: 100%;
    display: block;
    max-height: 100%;
    height: 100%;
    width: 100%;
    object-fit: cover;
  }

  &__cover {
    height: 100%;
    position: absolute;
    height: 100%;
    background-color: #1c1d27;
    background-image: linear-gradient(147deg, #354fce 0%, #0c296b 74%);
    left: 0;
    top: 0;
    width: 100%;
    border-radius: 15px;
    overflow: hidden;

    &:after {
      content: "";
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(6, 2, 29, 0.45);
    }
  }

  &__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 40px;
    padding: 0 10px;

    @media screen and (max-width: 480px) {
      margin-bottom: 25px;
    }

    @media screen and (max-width: 360px) {
      margin-bottom: 15px;
    }
  }

  &__chip {
    width: 60px;

    @media screen and (max-width: 480px) {
      width: 50px;
    }

    @media screen and (max-width: 360px) {
      width: 40px;
    }
  }

  &__type {
    height: 45px;
    position: relative;
    display: flex;
    justify-content: flex-end;
    max-width: 100px;
    margin-left: auto;
    width: 100%;

    @media screen and (max-width: 480px) {
      height: 40px;
      max-width: 90px;
    }

    @media screen and (max-width: 360px) {
      height: 30px;
    }
  }

  &__typeImg {
    max-width: 100%;
    object-fit: contain;
    max-height: 100%;
    object-position: top right;
  }

  &__info {
    color: #fff;
    width: 100%;
    max-width: calc(100% - 85px);
    padding: 10px 15px;
    font-weight: 500;
    display: block;

    cursor: pointer;

    @media screen and (max-width: 480px) {
      padding: 10px;
    }
  }

  &__holder {
    opacity: 0.7;
    font-size: .8rem;
    margin-bottom: 6px;

    @media screen and (max-width: 480px) {
      font-size: .75rem;
      margin-bottom: 5px;
    }
  }

  &__wrapper {
    font-family: "Source Code Pro", monospace;
    padding: 25px 15px;
    position: relative;
    z-index: 4;
    height: 100%;
    text-shadow: 7px 6px 10px rgba(14, 42, 90, 0.8);
    user-select: none;

    @media screen and (max-width: 480px) {
      padding: 20px 10px;
    }
  }

  &__name {
    font-size: 1rem;
    line-height: 1;
    white-space: nowrap;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;

    @media screen and (max-width: 480px) {
      font-size: 1rem;
    }
  }

  &__nameItem {
    display: inline-block;
    min-width: 8px;
    position: relative;
  }

  &__number {
    font-weight: 500;
    line-height: 1;
    color: #fff;
    font-size: 27px;
    margin-bottom: 25px;
    display: inline-block;
    padding: 10px 15px;
    cursor: pointer;

    @media screen and (max-width: 480px) {
      font-size: 1.25rem;
      margin-bottom: 15px;
      padding: 10px 10px;
    }

    @media screen and (max-width: 360px) {
      font-size: 1rem;
      margin-bottom: 10px;
      padding: 10px 10px;
    }
  }

  &__numberItem {
    width: 16px;
    display: inline-block;

    &.-active {
      width: 30px;
    }

    @media screen and (max-width: 480px) {
      width: .75rem;

      &.-active {
        width: 1rem;
      }
    }

    @media screen and (max-width: 360px) {
      width: .75rem;

      &.-active {
        width: .5rem;
      }
    }
  }

  &__content {
    color: #fff;
    display: flex;
    align-items: flex-start;
  }

  &__date {
    flex-wrap: wrap;
    font-size: 1rem;
    margin-left: auto;
    padding: 10px;
    display: inline-flex;
    width: 80px;
    white-space: nowrap;
    flex-shrink: 0;
    cursor: pointer;

    @media screen and (max-width: 480px) {
      font-size: 1rem;
    }
  }

  &__dateItem {
    position: relative;

    span {
      width: 1.5rem;
      display: inline-block;
    }
  }

  &__dateTitle {
    opacity: 0.7;
    font-size: .75rem;
    padding-bottom: 6px;
    width: 100%;

    @media screen and (max-width: 480px) {
      font-size: .75rem;
      padding-bottom: 5px;
    }
  }

  &__band {
    background: rgba(0, 0, 19, 0.8);
    width: 100%;
    height: 50px;
    margin-top: 30px;
    position: relative;
    z-index: 2;

    @media screen and (max-width: 480px) {
      margin-top: 1.3rem;
    }

    @media screen and (max-width: 360px) {
      height: 40px;
      margin-top: 10px;
    }
  }

  &__cvv {
    text-align: right;
    position: relative;
    z-index: 2;
    padding: 15px;

    .card-item__type {
      opacity: 0.7;
    }

    @media screen and (max-width: 360px) {
      padding: 10px 15px;
    }
  }

  &__cvvTitle {
    padding-right: 10px;
    font-size: 1rem;
    font-weight: 500;
    color: #fff;
    margin-bottom: 5px;
  }

  &__cvvBand {
    height: 45px;
    background: #fff;
    margin-bottom: 30px;
    text-align: right;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 10px;
    color: #1a3b5d;
    font-size: 1.2rem;
    border-radius: 4px;
    box-shadow: 0px 10px 20px -7px rgba(32, 56, 117, 0.35);

    @media screen and (max-width: 480px) {
      height: 40px;
      margin-bottom: 20px;
    }

    @media screen and (max-width: 360px) {
      margin-bottom: 15px;
    }
  }
}

.card-list {
  margin-bottom: -130px;

  @media screen and (max-width: 480px) {
    margin-bottom: -120px;
  }
}

.card-input {
  margin-bottom: 20px;
  position: relative;

  &__label {
    font-size: .8rem;
    margin-bottom: 5px;
    font-weight: 500;
    color: #1a3b5d;
    width: 100%;
    display: block;
    user-select: none;
  }
}
</style>
