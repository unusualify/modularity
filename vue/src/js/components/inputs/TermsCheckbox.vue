<template>
  <v-checkbox
    ref="VInput"
    v-model="input"
    hideDetails="auto"
    :variant="boundProps.variant"
    :rules="checkboxRules"
    :label="label"
    :class="[
      'v-input-terms-checkbox',
      noCheckbox ? 'v-input-terms-checkbox-no-checkbox' : ''
    ]"
    @click.prevent="handleCheckboxClick"

    :true-value="trueValue"
    :false-value="falseValue"
  >
    <template v-slot:label="labelScope">
      <slot name="label" v-bind="labelScope">
        <span class="text-grey-lighten-2">
          {{ $t('I agree with') }}
          <v-tooltip location="bottom">
            <template v-slot:activator="{ props }">
              <a
                class="text-decoration-underline"
                v-bind="props"
                @click="handleReadTerms('terms')"
              >
                {{ $t('Terms') }}
              </a>
            </template>
            {{ $t('Show Terms') }}
          </v-tooltip>
          {{ ' and '}}
          <v-tooltip location="bottom">
            <template v-slot:activator="{ props }">
              <a
                class="text-decoration-underline"
                v-bind="props"
                @click="handleReadTerms('conditions')"
              >
                {{ $t('Conditions') }}
              </a>
            </template>
            {{ $t('Show Conditions') }}
          </v-tooltip>
        </span>
      </slot>
    </template>
    <template v-slot:append>
      <ue-modal
        v-model="showDialog"
        Xtitle="Terms and Conditions"
        max-width="600px"
        no-cancel-button

        :confirm-callback="agreeAndClose"
        confirm-text="I agree"
      >
        <template v-slot:activator="{ props }">
          <v-btn
            v-if="isRead && false"
            variant="text"
            density="compact"
            color="primary"
            v-bind="props"
            @click.stop="showDialog = true"
          >
            {{ $t('Read Terms') }}
          </v-btn>
        </template>
        <template v-slot:body.description>
          <div class="d-flex flex-column align-center justify-center">
            <ue-title justify="center" :text="$t(`${readType}`)" class="mb-4"></ue-title>
            <v-sheet class="pa-4 overflow-y-auto" max-height="400px">
              <!-- Replace this with your actual terms and conditions content -->
              <div v-html="modalContent"></div>
            </v-sheet>
          </div>
        </template>
      </ue-modal>
    </template>
  </v-checkbox>
</template>

<script>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

export default {
  name: 'v-input-terms-checkbox',
  emits: [...makeInputEmits],
  components: {

  },
  props: {
    ...makeInputProps(),
    label: {
      type: String,
      default: 'I agree to the terms and conditions'
    },
    terms: {
      type: String,
      default() {
        const { t } = useI18n()
        return t('authentication.terms-policy')
      }
    },
    conditions: {
      type: String,
      default() {
        const { t } = useI18n()
        return t('authentication.conditions-policy')
      }
    },
    trueValue: {
      type: [Boolean, String, Number],
      default: 1
    },
    falseValue: {
      type: [Boolean, String, Number],
      default: 0
    },
    noCheckbox: {
      type: Boolean,
      default: false
    },
    noHandleClick: {
      type: Boolean,
      default: false
    }
  },
  setup (props, context) {
    const showDialog = ref(false)

    return {
      ...useInput(props, { ...context, initializeInput: (initialValue) => {
        return initialValue === props.trueValue ? props.trueValue : props.falseValue
      }}),
      showDialog
    }
  },
  data: function () {
    return {
      showDialog: false,
      isRead: false,
      readType: null,
      count: 0,
    }
  },
  computed: {
    checkboxRules() {
      return [
        v => this.count < 2 || !!v || 'You must agree to continue!'
      ];
    },
    modalContent() {
      return this.readType === 'terms' ? this.terms : this.conditions
    }
  },
  methods: {
    agreeAndClose() {
      this.isRead = true
      this.showDialog = false
      this.input = this.trueValue
    },
    handleReadTerms(type) {
      this.isRead = true
      this.showDialog = true
      this.readType = type
    },
    handleCheckboxClick() {
      if (this.noHandleClick) {
        return
      }
      if (!this.isRead) {
        this.readType ??= 'terms'
        this.showDialog = true
        this.isRead = true
      } else {
        if(!this.showDialog) {
          this.count++
          this.input = this.input === this.trueValue ? this.falseValue : this.trueValue
        }
      }
    }
  }
}
</script>

<style lang="sass">
  .v-input-terms-checkbox
    &.v-input-terms-checkbox-no-checkbox
      .v-selection-control__wrapper
        display: none !important


</style>

<style lang="scss">

</style>
