<script setup>
  import { ref, computed, onMounted, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import {
    useInput,
    makeInputProps,
    makeInputEmits,
    useValidation,
    useDynamicModal,
    useAuthorization,
    useAlert
  } from '@/hooks'
  import axios from 'axios'

  const props = defineProps({
    ...makeInputProps(),
    name: {
      type: String,
      default: 'date',
    },
    variant: {
      type: String,
      default: 'outlined',
    },
    density: {
      type: String,
      default: 'default',
    },
    useTimezone: {
      type: Boolean,
      default: false,
    },
  })

  const emit = defineEmits([...makeInputEmits])

  const findActiveTimezone = () => {
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone
    return timezone
  }

  const substractTimezone = (date) => {
    const timezone = findActiveTimezone()
    const newDate = new Date(date)
    const timezoneOffset = newDate.getTimezoneOffset()
    const substractedDate = new Date(newDate.getTime() - timezoneOffset * 60000)
    return substractedDate
  }

  const addTimezone = (date) => {
    const timezone = findActiveTimezone()
    const newDate = new Date(date)
    const timezoneOffset = newDate.getTimezoneOffset()
    const addedDate = new Date(newDate.getTime() + timezoneOffset * 60000)
    return addedDate
  }

  const { input, id , boundProps } = useInput(props, {
    emit,
    initializeInput: (val) => {
      if (!props.useTimezone) {
        return addTimezone(val)
      }
      return val
    },
    updateModelValue: (val, old) => {
      if (!props.useTimezone) {
        emit('update:modelValue', substractTimezone(val))
      } else {
        emit('update:modelValue', val)
      }
    }
  })

  const {requiredRule, minRule, futureDateRule, dateRule, invokeRule} = useValidation(props)
  const { t, d } = useI18n()
  const DynamicModal = useDynamicModal()
  const Authorization = useAuthorization()
  const Alert = useAlert()
</script>

<template>
  <v-input
    v-model="input"
    hide-details
    class="v-input-date"
    >
    <template v-slot:default="defaultSlot">
      <v-date-input
        v-model="input"
        :density="density"
        :label="label"
        :variant="variant"
        v-bind="$attrs"
      />
    </template>
  </v-input>
</template>

<style lang="scss">

</style>
