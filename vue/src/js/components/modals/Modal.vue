<template>
  <v-dialog
    v-model="dialog"
    v-bind="$bindAttributes()"
    :transition="transition"
    :fullscreen="full"
    :width="modalWidth"
    >
    <template v-for="(_, name) in $slots" v-slot:[name]="slotProps">
      <slot :name="name" v-bind="slotProps || {}"></slot>
    </template>

    <template v-slot:default="defaultScope">
      <slot v-bind="{
          ...defaultScope,
          isFullActive: this.full,
          toggleFullscreen: () => this.toggleFullscreen(),
          close: () => this.close(),
          confirm: () => this.confirm(),
          open: () => this.open(),
        }"
      >
        <slot name="systembar">
          <v-layout v-if="hasSystembar" style="height: 40px">
            <v-col>
              <v-system-bar dark>
                <v-icon @click="toggleFullscreen()" :x-small="full">
                  mdi-checkbox-blank-outline
                </v-icon>
                <v-icon @click="close()">mdi-close</v-icon>
              </v-system-bar>
            </v-col>
          </v-layout>
        </slot>
        <slot name="body"
            v-bind="{
                textCancel: this.textCancel,
                textConfirm: this.textConfirm,
                textDescription: this.description,
                isFullActive: this.full,

                open: () => this.open(),
                close: () => this.close(),
                confirm: () => this.confirm(),
                toggleFullscreen: () => this.toggleFullscreen(),
            }"
            :closeDialog="close"
          >
          <v-card >
            <v-card-title v-if="title" class="text-h5 text-center" style="word-break: break-word;">
              <ue-title justify="space-between" padding="y-2">
                {{ title }}
                <template #right>
                  <div class="d-flex align-center">
                    <v-btn v-if="hasFullscreenButton" :icon="full ? 'mdi-fullscreen-exit' : 'mdi-fullscreen'" variant="plain" color="grey-darken-5" size="compact" @click="toggleFullscreen"/>
                    <v-btn v-if="hasCloseButton" icon="$close" variant="plain" size="compact" color="grey-darken-5" rounded @click="close()" />
                  </div>
                </template>
              </ue-title>
              <v-divider v-if="hasTitleDivider"/>
            </v-card-title>
            <v-card-text v-if="description || $slots['body.description']" class="text-center" style="word-break: break-word;" :class="{'pa-0': noDefaultBodyPadding}">
              <slot name="body.description" v-bind="{description}">
                <div v-html="description" />
              </slot>
            </v-card-text>
            <template v-if="!noActions">
              <v-divider/>
              <v-card-actions>
                <slot name="body.options" v-bind="{description}">
                  <v-spacer/>
                  <!-- CANCEL BUTTON -->
                  <v-btn v-if="!noCancelButton"
                    ref="modalCancel"
                    v-bind="rejectButtonAttributes"
                    class="modal-cancel"
                    @click="cancel()"
                    :loading="rejectLoading" :disabled="rejectLoading"
                  >
                    {{ textCancel }}
                  </v-btn>
                  <!-- CONFIRM BUTTON -->
                  <v-btn v-if="!noConfirmButton"
                    ref="modalConfirm"
                    v-bind="confirmButtonAttributes"
                    class="modal-confirm"
                    @click="confirm()"
                    :loading="confirmLoading"
                    :disabled="confirmLoading"
                  >
                    {{ textConfirm }}
                  </v-btn>
                </slot>
                <v-spacer/>
              </v-card-actions>
            </template>
          </v-card>
        </slot>
      </slot>
    </template>
  </v-dialog>
</template>

<script>
import { makeModalProps, useModal } from '@/hooks'

export default {
  emits: [
    'update:modelValue',
    'opened',
    'confirm',
    'cancel'
  ],
  props: {
    ...makeModalProps()
  },
  setup(props, context) {
    return {
      ...useModal(props, context)
    }
  },
  computed: {
    textCancel() {
      return this.cancelText !== '' ? this.cancelText : this.$t('fields.cancel')
    },
    textConfirm() {
      return this.confirmText !== '' ? this.confirmText : this.$t('fields.confirm')
    },
  },
  watch: {
    dialog(value) {
      if (value) {
        this.emitOpened()
      }
    }
  },
  methods: {
    // Imperative API methods
    toggle() {
      return this.toggleModal()
    },
    close(callback = null) {
      if (callback) {
        callback()
      }
      return this.closeModal()
    },
    open(callback = null, nextCallback = null) {
      if (callback) {
        callback()
      }

      this.openModal()

      if (nextCallback) {
        const _this = this
        this.$nextTick().then(() => {
          if (_this.$refs.modalConfirm) {
            _this.$refs.modalConfirm.$el.addEventListener('click', (e) => {
              nextCallback()
              _this.close()
            })
          }

          if (_this.$refs.modalCancel) {
            _this.$refs.modalCancel.$el.addEventListener('click', (e) => {
              _this.close()
            })
          }
        })
      }

      return true
    },

    async confirm(callback = null) {
      let shouldClose = true

      if (callback) {
        const res = await callback()
        shouldClose = !!res

        if (shouldClose) {
          this.closeModal()
          this.$emit('confirm')
        }
      } else if (this.confirmCallback && typeof this.confirmCallback === 'function') {
        let res = await this.confirmCallback()
        shouldClose = typeof res === 'boolean' ? res : true

        if (shouldClose && this.confirmClosing) {
          this.closeModal()
        }

        this.$emit('confirm')
      } else {
        this.closeModal()
        this.$emit('confirm')
      }

      return shouldClose
    },
    async cancel(callback = null) {
      if (callback) {
        callback()
      }

      if (this.rejectCallback && typeof this.rejectCallback === 'function') {
        let res = await this.rejectCallback()
        shouldClose = typeof res === 'boolean' ? res : true

        if (shouldClose && this.rejectClosing) {
          this.closeModal()
        }
      } else {
        this.closeModal()
      }
    },
    toggleFullscreen() {
      this.full = !this.full
    },
    screenListener(e) {
      this.full = e.target.fullScreen
    }
  }
}
</script>

<style>
/* You can add any additional styles here */
</style>
