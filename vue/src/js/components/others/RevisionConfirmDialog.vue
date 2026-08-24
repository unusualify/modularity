<script setup>
  import { useI18n } from 'vue-i18n'

  defineProps({
    title: {
      type: String,
      default: '',
    },
    body: {
      type: String,
      default: '',
    },
    submitColor: {
      type: String,
      default: 'primary',
    },
    zIndex: {
      type: [Number, String],
      default: 10000,
    },
  })

  const open = defineModel({ type: Boolean, required: true })

  const emit = defineEmits(['cancel', 'confirm'])

  const { t } = useI18n()

  function onCancel() {
    emit('cancel')
  }

  function onConfirm() {
    emit('confirm')
  }
</script>

<template>
  <v-dialog
    v-model="open"
    max-width="440"
    scrim="dark"
    :z-index="zIndex"
  >
    <v-card rounded="lg">
      <v-card-title class="text-title-large">{{ title }}</v-card-title>
      <v-card-text class="text-body-medium text-medium-emphasis">
        {{ body }}
      </v-card-text>
      <v-card-actions class="pa-4 pt-0">
        <v-spacer />
        <v-btn variant="text" @click="onCancel">
          {{ t('fields.cancel') }}
        </v-btn>
        <v-btn
          :color="submitColor"
          variant="flat"
          @click="onConfirm"
        >
          {{ t('fields.confirm') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
