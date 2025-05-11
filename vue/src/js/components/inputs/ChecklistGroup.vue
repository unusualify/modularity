<template>
  <v-input
    v-model="selectedItems"
    hideDetails="auto"
    appendIcon="mdi-close"
    :variant="boundProps.variant"
    class="v-input-checklist-group"
    >
    <template v-slot:default="defaultSlot">
      <v-radio-group v-model="selectedGroup" direction="horizontal">
        <v-row>
          <v-col v-for="(details, name) in schema" :key="name">
            <v-btn block class="v-input-checklist-group__btn"
              :variant="selectedGroup == name ? 'elevated' : 'outlined'"
              @click="selectedGroup=name">
              <template #prepend><v-radio :value="name"></v-radio></template>
              {{ details.label.toUpperCase() }}
            </v-btn>
          </v-col>
        </v-row>
      </v-radio-group>
      <div>
        <Checklist v-model="selectedItems" :items="schema[selectedGroup ?? 0]?.items ?? []" />
      </div>
    </template>
  </v-input>
</template>

<script>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
import Checklist from './Checklist.vue'

export default {
  name: 'v-input-checklist-group',
  emits: [...makeInputEmits],
  components: {
    Checklist
  },
  props: {
    ...makeInputProps(),
    modelValue: {
      type: Object,
      default: () => {}
    },
    schema: {
      type: Object,
      default: () => {}
    },
  },
  setup (props, context) {
    return {
      ...useInput(props, context)
    }
  },
  data: function () {
    return {
      selectedGroup: Object.keys(this.schema)[0] ?? 1,
      selectedItems: []
    }
  },
  computed: {

  },
  watch: {
    selectedGroup: {
      handler (value, oldValue) {
        this.selectedItems = []
      }
    },
    selectedItems: {
      handler (value, oldValue) {
        this.$emit('update:modelValue', {
          group: this.selectedGroup,
          items: this.selectedItems
        })
      }
    }
  },
  methods: {

  },
  created() {

  }
}
</script>

<style lang="sass">
  .v-input-checklist-group
    .v-input__control
      display: block


    .v-input-checklist-group__btn
      padding-left: 4px
      justify-content: flex-start !important

      .v-btn__content
        width: 100%


</style>

<style lang="scss">

</style>
