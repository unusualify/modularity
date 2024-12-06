<template>
  <v-combobox
    ref="VInput"
    v-model="input"
    :items="items"
    :loading="loading"
    :item-value="itemValue"
    :item-title="itemTitle"
    :multiple="multiple"
    @updatex:search="handleSearch"
    @keydown.enter="handleEnter"

    class="v-input-tag"
  >
  </v-combobox>
</template>

<script>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
import { FORM } from '@/store/mutations'
import { useStore } from 'vuex'
import api from '@/store/api/form'

import { ref, computed, toRefs, toRef } from 'vue'

export default {
  name: 'v-input-tag',
  emits: [...makeInputEmits],
  props: {
    ...makeInputProps(),
    taggable: {
      type: String,
      default: null
    },
    items: {
      type: Array,
      default: []
    },
    multiple: {
      type: Boolean,
      default: false
    },
    endpoint: {
      type: String,
      required: null
    },
    updateEndpoint: {
      type: String,
      required: null
    },
    itemValue: {
      type: String,
      default: 'value'
    },
    itemTitle: {
      type: String,
      default: 'label'
    }
  },

  setup(props, context) {
    const store = useStore()

    const loading = ref(false)

    if(!store.state.form.taggableItems[props.taggable]) {
      store.commit(FORM.SET_TAGGABLE_ITEMS, { taggable_type: props.taggable, items: props.items ?? [] })
    }

    const items = computed(() => {
      return store.state.form.taggableItems[props.taggable]
    })

    return {
      ...useInput(props, context),
      loading,
      items,
    }
  },

  methods: {
    async handleEnter() {

      const value = this.input

      if(Array.isArray(value)) return
      if (!value || this.items.includes(value)) return
      if (!value || this.items.find(item => item[this.itemValue] === value || item[this.itemTitle] === value)) return

      try {
        this.loading = true
        let self = this
        api.put(this.updateEndpoint, { value: value, taggable: this.taggable }, function(res) {
          if(res.status === 200) {
            self.$store.commit(FORM.ADD_TAGGABLE_ITEM, { taggable_type: self.taggable, item: {
              [self.itemValue]: res.data.id,
              [self.itemTitle]: value
            }})
          }

        })
        return

      } catch (error) {
        console.error('Error creating new item:', error)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style lang="sass">
  // .v-input-tag


</style>
