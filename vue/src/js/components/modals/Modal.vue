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

        }">
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
                textDescription: this.textDescription,
                isFullActive: this.full,

                open: () => this.open(),
                close: () => this.close(),
                confirm: () => this.confirm(),
                toggleFullscreen: () => this.toggleFullscreen(),
            }"
            :closeDialog="close"
            >
            <v-card >
              <v-card-title class="text-h5 text-center" style="word-break: break-word;">
                <!-- {{ textDescription }} -->
              </v-card-title>
              <v-card-text class="text-center" style="word-break: break-word;" >
                <slot name="body.description" v-bind="{textDescription}">
                  {{ textDescription }}
                </slot>
              </v-card-text>
              <v-divider/>
              <v-card-actions>
                <v-spacer/>
                <slot name="body.options" v-bind="{textDescription}">
                  <v-btn ref="modalCancel" class="modal-cancel" color="red" text @click="cancel()"> {{ textCancel }}</v-btn>
                  <v-btn ref="modalConfirm" class="modal-confirm" color="green" text @click="confirm()"> {{ textConfirm }}</v-btn>
                </slot>
                <v-spacer/>
              </v-card-actions>
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
    'opened'
  ],
  props: {
    ...makeModalProps()
  },
  setup (props, context) {
    return {
      ...useModal(props, context)
    }
  },
  computed: {
    textCancel () {
      return this.cancelText !== '' ? this.cancelText : this.$t('fields.cancel')
    },
    textConfirm () {
      return this.confirmText !== '' ? this.confirmText : this.$t('fields.confirm')
    },
    textDescription () {
      return this.descriptionText
    }
  },

  watch: {
    dialog (value) {
      !value || this.emitOpened()
    }
  },

  methods: {
    toggle () {
      this.dialog = !this.dialog
    },
    close (callback = null) {
      if (callback) {
        callback()
      }
      this.dialog = false
    },
    open (callback = null, nextCallback = null) {
      if (callback) {
        callback()
      }

      this.dialog = true
      if (nextCallback) {
        const _this = this
        this.$nextTick().then(() => {
          _this.$refs.modalConfirm.$el.addEventListener('click', (e) => {
            nextCallback()
            _this.close()
          })

          _this.$refs.modalCancel.$el.addEventListener('click', (e) => {
            _this.close()
          })
        })
      }
    },
    confirm (callback = null) {
      if (callback) {
        callback()
      }
      this.dialog = false
      this.$emit('confirm')
    },
    cancel (callback = null) {
      if (callback) {
        callback()
      }
      this.dialog = false
      this.$emit('cancel')
    },
    // attrs (attrs) {
    //   return attrs
    // },
    toggleFullscreen () {
      this.full = !this.full
    },
    screenListener (e) {
      this.full = e.target.fullScreen
    }
  },
  beforeUnmount: function () {

  },
  created () {
    // setInterval((self) => {
    //   __log('dialog', self.dialog)
    // }, 5000, this)
  }
}
</script>

<style>

</style>
