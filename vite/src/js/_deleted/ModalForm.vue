<template>
  <ue-modal
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"

    scrollable
    width-type="lg"
    systembar
    @screenListener="screenListener"

    transition="dialog-bottom-transition"

    >
    <template v-slot:activator="{ props }">
      <slot name="activator" :props="{...props}" />
    </template>

    <template v-slot:body="props">
      <v-card >
        <v-card-title class="text-h5 grey lighten-2">
          <slot name="title">
              <!-- <span class="text-h5" >
                {{ formTitle }}
              </span> -->
          </slot>
        </v-card-title>
        <v-card-text>
          <!-- <ue-form :ref="formReference()"/> -->
          <ue-form
            :title="formTitle"
            :ref="formRef"/>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions>
            <v-spacer></v-spacer>
            <!-- <v-btn
                color="error darken-1"
                text
                @click="cancelModal(on.closeDialog)"
                >
                {{ $t('cancel') }}
            </v-btn> -->
            <v-btn
                color="error darken-1"
                text
                @click="cancelModal(props.onClose)"
                >
                {{ textCancel }}
            </v-btn>
            <v-btn
                color="teal darken-1"
                text
                @click="confirmModal(props.onClose)"
                >
                {{ $t('save') }}
            </v-btn>
        </v-card-actions>
      </v-card>
    </template>
  </ue-modal>
</template>

<script>
import { ModalMixin } from '@/mixins'

export default {
  mixins: [ModalMixin],
  components: {
    // 'ue-form': UEForm
  },
  props: {
    routeName: {
      type: String,
      default: 'Item'
    }
  },
  data () {
    return {
      full: false
    }
  },
  computed: {
    formTitle () {
    //   __log(this.$store.state)
      return this.$t('new-item', { item: this.routeName })
      return this.$t((this.editedIndex === -1 ? 'new-item' : 'edit-item'), { item: this.routeName })
    },
    activatorText () {
      return this.$t('add-item', { item: this.routeName })
    },
    formRef () {
      return this.id + '-form'
    }
  },
  methods: {
    screenListener (e) {
      // __log(e.target);
      this.full = e.target.fullScreen
    },

    confirmCallback () {
      const self = this
      this.$refs[this.formRef].saveForm((res) => {
        if (Object.prototype.hasOwnProperty.call(res, 'variant') && res.variant.toLowerCase() === 'success') { self.closeModal() }
      })
    },

    save () {
      console.log(
        'save clicked',
        this.formObject
      )
    }
  }
}
</script>

<style>

</style>
