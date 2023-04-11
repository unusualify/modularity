<template>
    <v-dialog
      v-model="show"
      max-width="500px"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-btn
          color="primary"
          dark
          class="mb-2"
          v-bind="attrs"
          v-on="on"
        >
          {{ $t('new-item', {'item': name} ) }}
        </v-btn>
      </template>
      <v-card>
        <v-card-title>
          <slot name="title"></slot>

        </v-card-title>

        <v-card-text>

          <slot name="middle"></slot>

        </v-card-text>

        <v-card-actions>

          <slot name="actions"></slot>

        </v-card-actions>
      </v-card>
    </v-dialog>
</template>

<script>
import Form from './Form'

export default {
  components: {
    'ue-form': Form
  },
  name: 'ue-form-dialog',
  props: {
    value: {
      type: Boolean,
      default: false
    },
    name: {
      type: String,
      default: 'Item'
    }
  },
  data () {
    return {
      // show: this.dialog,
    }
  },

  computed: {
    show: {
      get () {
        __log(this.value)
        return this.value
      },
      set (value) {
        this.$emit('input', value)
      }
    }
  },

  methods: {
    close () {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    }
  }
}
</script>

<style>

</style>
