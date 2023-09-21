<template>
  <v-dialog
    v-model="dialog"
    v-bind="$bindAttributes()"
    transition="dialog-bottom-transition"
    :fullscreen="full"
    :width="modalWidth"
    >
    <template v-for="(_, name) in $slots" v-slot:[name]="slotProps">
      <slot :name="name" v-bind="slotProps || {}"></slot>
    </template>

    <!-- <template v-slot:activator="{ props }">
      <slot name="activator" :props="{...props}"></slot>
    </template> -->

    <!-- <v-card>
      <slot v-if="systembar" name="systembar">
        <v-layout style="height: 40px">
          <v-system-bar dark>
            <v-icon @click="toggleFullScreen()" :x-small="full">
              mdi-checkbox-blank-outline
            </v-icon>
            <v-icon @click="close()">mdi-close</v-icon>
          </v-system-bar>
        </v-layout>
      </slot>
      <slot name="body"
        v-bind="{
            onOpen: this.open,
            onClose: this.close,
            onConfirm: this.confirm
        }"
        :closeDialog="close"
        >
      </slot>
    </v-card> -->

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
    <slot name="body"
        v-bind="{
            textCancel: this.textCancel,
            textConfirm: this.textConfirm,

            onOpen: this.open,
            onClose: this.close,
            onConfirm: this.confirm,
        }"
        :closeDialog="close"
        >
    </slot>
  </v-dialog>
</template>

<script>
import htmlClasses from '@/utils/htmlClasses'
import { makeModalProps, useModal } from '@/hooks'

export default {
  emits: ['update:modelValue'],
  props: {
    ...makeModalProps()
  },
  setup (props, context) {
    return {
      ...useModal(props, context)
    }
  },
  // data () {
  //   return {
  //     // dialog: this.value,
  //     widths: {
  //       sm: '300px',
  //       md: '500px',
  //       lg: '750px'
  //     },
  //     width: this.widthType,

  //     modalClass: htmlClasses.modal,

  //     full: this.fullscreen
  //   }
  // },

  computed: {
    // dialog: {
    //   get () {
    //     return this.modelValue
    //   },
    //   set (value) {
    //     this.$emit('update:modelValue', value)
    //   }
    // },
    // full: {
    //     get () {
    //         return this.fullscreen
    //         return this.fullScreen
    //     },
    //     set (value) {
    //         // this.$emit('screenListener', this.full)
    //     }
    // },

    // togglePersistent () {
    //   return this.persistent
    // },

    // toggleScrollable () {
    //   return this.scrollable
    // },
    // modalWidth () {
    //   return this.width ? this.widths[this.width] : null
    // },

    textCancel () {
      return this.cancelText !== '' ? this.cancelText : this.$t('cancel')
    },
    textConfirm () {
      return this.confirmText !== '' ? this.confirmText : this.$t('confirm')
    }
  },

  watch: {
    // dialog (value, oldVal) {
    //   __log('modal vue watcher dialog', value, oldVal)
    //   this.$emit('update:modelValue', value)
    // }
  },

  methods: {
    toggle () {
      this.dialog = !this.dialog
    },
    close (callback = null) {
      __log('model.vue close()', callback)
      if (callback) {
        callback()
      }
      this.dialog = false
    },
    open (callback = null) {
      if (callback) {
        callback()
      }
      this.dialog = true
    },
    confirm (callback = null) {
      if (callback) {
        callback()
      }
      this.dialog = false
    },
    // attrs (attrs) {
    //   return attrs
    // },
    toggleFullScreen () {
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
