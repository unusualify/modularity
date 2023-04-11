<template>
    <v-dialog
        v-model="dialog"

        v-bind="{
            ...bindProps()
        }"
        :fullscreen="full"
        :width="modalWidth"
        transition="dialog-bottom-transition"

    >

        <template v-slot:activator="{ props }">
            <slot
                name="activator"
                :props="{
                    ...props
                }"
                >

            </slot>
        </template>

        <v-card>
            <slot v-if="systembar" name="systembar">
                <v-layout style="height: 40px">
                    <v-system-bar dark>
                        <v-icon @click="toggleFullScreen()" :x-small="full">
                            mdi-checkbox-blank-outline
                        </v-icon>
                        <!-- <v-icon @click="cancelModal(on.closeDialog)" >mdi-close</v-icon> -->
                        <v-icon @click="close()">mdi-close</v-icon>
                    </v-system-bar>
                </v-layout>
            </slot>
            <slot
                name="body"
                :props="{
                    onOpen: this.open,
                    onClose: this.close,
                    onConfirm: this.confirm
                }"

                :closeDialog="close"
                >

            </slot>
        </v-card>
    </v-dialog>
</template>

<script>
import htmlClasses from '@/utils/htmlClasses'

export default {
  emits: ['update:modelValue'],
  props: {
    modelValue: {
      type: Boolean
    },
    name: {
      type: String,
      default: 'Item'
    },
    transition: {
      type: String,
      default: 'bottom'
    },

    widthType: {
      type: String
    },
    systembar: {
      type: Boolean,
      default: false
    },
    fullscreen: {
      type: Boolean,
      default: false
    }

  },
  data () {
    return {
      // dialog: this.value,
      widths: {
        sm: '300px',
        md: '500px',
        lg: '750px'
      },
      width: this.widthType,

      modalClass: htmlClasses.modal,
      firstFocusableEl: null,
      lastFocusableEl: null,

      full: this.fullscreen
    }
  },

  computed: {
    dialog: {
      get () {
        return this.modelValue
      },
      set (value) {
        this.$emit('update:modelValue', value)
      }
    },
    // full: {
    //     get () {
    //         return this.fullscreen
    //         return this.fullScreen
    //     },
    //     set (value) {
    //         // this.$emit('screenListener', this.full)
    //     }
    // },
    togglePersistent () {
      return this.persistent
    },

    toggleScrollable () {
      return this.scrollable
    },
    modalWidth () {
      return this.width ? this.widths[this.width] : null
    }
  },

  watch: {
    dialog (newVal, oldVal) {
    //   __log('modal vue watcher dialog', newVal, oldVal)
    }
  },

  methods: {
    toggle () {
      this.dialog = !this.dialog
    },
    close () {
      this.dialog = false
    },
    open () {
      this.dialog = true
    },
    confirm () {
      this.dialog = false
    },
    attrs (attrs) {
      return attrs
    },
    toggleFullScreen () {
      return this.full = !this.full
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
