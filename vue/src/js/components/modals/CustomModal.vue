<template>
  <v-dialog v-model="dialog" v-bind="$bindAttributes()" :transition="transition" :fullscreen="full" :width="modalWidth">
    <template v-for="(_, name) in $slots" v-slot:[name]="slotProps">
      <slot :name="name" v-bind="slotProps || {}"></slot>
    </template>

    <slot v-if="systembar" name="systembar">
      <v-layout style="height: 40px">
        <v-col>
          <v-system-bar dark>
            <v-icon @click="toggleFullScreen()" :x-small="full">
              mdi-checkbox-blank-outline
            </v-icon>
            <v-icon @click="close()">mdi-close</v-icon>
          </v-system-bar>
        </v-col>
      </v-layout>
    </slot>
    <slotname="body" v-bind="{
    textCancel: this.textCancel,
    textConfirm: this.textConfirm,
    textDescription: this.textDescription,
    onOpen: this.open,
    onClose: this.close,
    onConfirm: this.confirm,
    closeDialog: close
  }">
      <v-card>
        <v-card-title v-if="modalType !== 'form'" class="text-h5 text-center" style="word-break: break-word;">
          {{ modalTitle }}
        </v-card-title>
        <v-card-text class="text-center" style="word-break: break-word;">
          <slot name="body.description" v-bind="{ textDescription }">
            {{ textDescription }}
          </slot>
        </v-card-text>
        <v-divider v-if="modalType !== 'form'" />
        <v-card-actions v-if="modalType !== 'form'">
          <v-spacer />
          <slot name="body.options" v-bind="{ textCancel, textConfirm }">
            <v-btn ref="modalCancel" class="modal-cancel" color="error" text @click="cancel()"> {{ textCancel }}</v-btn>
            <v-btn ref="modalConfirm" class="modal-confirm" color="success" text @click="confirm()"> {{ textConfirm
              }}</v-btn>
          </slot>
          <v-spacer />
        </v-card-actions>
      </v-card>
    </slot>
  </v-dialog>
</template>

<script>
import { makeModalProps, useModal } from '@/hooks'
import { computed } from 'vue'

export default {
  name: 'CustomModal',
  emits: [
    'update:modelValue',
    'opened',
    'confirm',
    'cancel'
  ],
  props: {
    ...makeModalProps(),
    modalType: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'form', 'action', 'delete', 'custom'].includes(value)
    },
    modalTitle: {
      type: String,
      default: ''
    }
  },
  setup(props, context) {
    const modalSetup = useModal(props, context)

    const textCancel = computed(() => props.cancelText || context.root.$t('cancel'))
    const textConfirm = computed(() => props.confirmText || context.root.$t('confirm'))
    const textDescription = computed(() => props.descriptionText || '')

    return {
      ...modalSetup,
      textCancel,
      textConfirm,
      textDescription
    }
  },
  methods: {
    cancel() {
      this.close()
      this.$emit('cancel')
    },
    confirm() {
      this.close()
      this.$emit('confirm')
    }
  }
}
</script>
