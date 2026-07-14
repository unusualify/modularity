<template>
  <div class="d-flex flex-column ga-4">
    <div class="text-center">
      <ue-title
        type="h5"
        weight="bold"
        justify="center"
        :text="stepUp.title || t('messages.step_up_verification_required', 'Verification required')"
      />
      <div class="text-body-2 text-medium-emphasis mt-2">
        {{ stepUp.description || t('messages.step_up_description_default', 'Please enter the verification code sent to your email.') }}
      </div>
    </div>

    <ue-form
      :model-value="model"
      :schema="schema"
      :action-url="stepUp.verifyUrl"
      :button-text="stepUp.buttonText || t('messages.step_up_verify', 'Verify')"
      :has-submit="true"
      :no-validation="true"
      :no-title="true"
      :no-default-form-padding="true"
      :no-schema-updating-progress-bar="true"
      @submitted="handleSubmitted"
    />

    <div class="d-flex justify-center">
      <v-btn
        variant="text"
        color="primary"
        :loading="resending"
        @click="resendCode"
      >
        {{ stepUp.resendText || t('messages.step_up_resend_code', 'Resend code') }}
      </v-btn>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'

const { t } = useI18n({ useScope: 'global' })

const props = defineProps({
  stepUp: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['verified'])
const modalRef = inject('modalRef', null)
const resending = ref(false)
const model = ref({})
const otpField = computed(() => props.stepUp.otpField || 'verify-code')

const schema = computed(() => ({
  [otpField.value]: {
    type: 'otp-input',
    name: otpField.value,
    label: t('messages.step_up_otp_label', 'Verification code'),
    default: '',
    length: props.stepUp.otpLength || 6,
    rules: 'required',
    col: {
      cols: 12
    }
  }
}))

const handleSubmitted = (data) => {
  if (data?.step_up_verified) {
    emit('verified', data)
    modalRef?.close?.(data)
  }
}

const resendCode = async () => {
  if (!props.stepUp.resendUrl) return

  resending.value = true

  try {
    const response = await axios.get(props.stepUp.resendUrl, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json'
      }
    })

    window.vm.config.globalProperties.$notif({
      message: response?.data?.message ?? t('messages.step_up_resend_success', 'A new verification code has been sent.'),
      variant: response?.data?.variant ?? 'success'
    })
  } catch (error) {
    window.vm.config.globalProperties.$notif({
      message: error?.response?.data?.message ?? t('messages.step_up_resend_failed', 'The verification code could not be resent.'),
      variant: error?.response?.data?.variant ?? 'warning'
    })
  } finally {
    resending.value = false
  }
}
</script>
