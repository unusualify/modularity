<template>
    <v-range-slider
        v-model="input"
        :label="obj.schema.label"
        v-bind="obj.schema"
    ></v-range-slider>
</template>

<script>
import { InputMixin } from '@/mixins'
import { useInput } from '@/hooks'

export default {
  mixins: [InputMixin],
  name: 'ue-custom-input-range',

  created () {

  },
  computed: {

  }
}
</script>
