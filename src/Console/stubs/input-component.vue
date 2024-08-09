<template>
  <v-input
    v-model="input"
    hideDetails="auto"
    :variant="boundProps.variant"
    class="$NAME$"
    >
    <template v-slot:default="defaultSlot">

    </template>
  </v-input>
</template>

<script>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

export default {
  name: '$NAME$',
  emits: [...makeInputEmits],
  components: {

  },
  props: {
    ...makeInputProps(),
  },
  setup (props, context) {
    return {
      ...useInput(props, context)
    }
  },
  data: function () {
    return {

    }
  },
  computed: {

  },
  watch: {

  },
  methods: {

  },
  created() {

  }
}
</script>

<style lang="sass">
  .$NAME$


</style>

<style lang="scss">

</style>
