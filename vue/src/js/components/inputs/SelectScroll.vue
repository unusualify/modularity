<template>
  <component
    :is="componentType"
    v-bind="$bindAttributes()"

    v-model="input"
    :class="['v-input-select-scroll']"
    :items="elements"
    :label="label"
    @update:search="searched"
    :no-filter="noFilter"
    @input.native="getItemsFromApi"
    :multiple="multiple"
    :return-object="$attrs.returnObject ?? returnObject ?? false"
    :item-value="itemValue"
    :item-title="itemTitle"

    :loading="loading"
    :readonly="$attrs.readonly || readonly || loading"

    :rules="rules"
  >
    <template v-slot:append-item>
      <div v-if="lastPage > 0 && lastPage > page" v-intersect="endIntersect" />
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
      noFilter: this.componentType == 'v-autocomplete' ? true : null,
    }
  },
  methods: {
    endIntersect(entries, observer, isIntersecting) {
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
