<template>
  <component
    :is="componentType"
    v-bind="$bindAttributes()"

    v-model="input"
    :class="['v-input-select-scroll']"
    :items="elements"
    :label="label"
    @update:search="searched"
    @input.native="getItemsFromApi"
    :multiple="multiple"
    :return-object="$attrs.returnObject ?? returnObject ?? false"
    :item-value="itemValue"
    :item-title="itemTitle"

    :loading="loading"
    :readonly="$attrs.readonly || readonly || elements.length === 0"
    :hide-no-data="loading"
    :no-filter="noFilter"

    :rules="rules"
  >
    <template v-slot:append-item>
      <div v-if="activeLastPage > 0 && activeLastPage >= nextPage" v-intersect="handleIntersect" />
    </template>
    <template
      v-for="(context, slotName) in $slots" v-slot:[slotName]="slotScope"
      :key="`customSlot-${slotName}`"
      >
      <slot :name="slotName" v-bind="slotScope" />
    </template>
  </component>
</template>

<script>
import { useInput, makeInputProps, makeInputEmits, useInputFetch, makeInputFetchProps } from '@/hooks'

export default {
  name: 'v-input-select-scroll',
  emits: [...makeInputEmits],

  props: {
    ...makeInputProps(),
    ...makeInputFetchProps(),
    rules: {
      type: Array,
      default: () => []
    },
    componentType: {
      type: String,
      default: 'v-autocomplete'
    },
  },
  setup (props, context) {
    const inputHook = useInput(props, context)
    const inputFetchHook = useInputFetch(props, {
      ...context,
      input: inputHook.input
    })

    return {
      ...inputHook,
      ...inputFetchHook
    }
  },
  data () {
    return {
      noFilter: this.activeLastPage > 0 && this.activeLastPage >= this.nextPage,
    }
  },
  methods: {
    handleIntersect(isIntersecting, entries, observer) {
      if (isIntersecting) {
        this.getItemsFromApi()
      }
    },
    makeReference (key) {
      return `${key}-${states.id}`
    },

  },
  computed: {
  },
  created () {
    this.getItemsFromApi()
  },
  watch: {

  }
}
</script>

<style lang="sass">

</style>
