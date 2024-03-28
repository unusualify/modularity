<template>
  <v-row>
    <v-col v-for="(block,i) in blocks" :key="`block-${i}`"
      v-bind="$bindAttributes(block.col)"
      v-fit-grid
      >
        <component
          :is="`ue-${block.component}`"
          v-bind="block.attributes"
         ></component>
    </v-col>
  </v-row>
</template>

<script>
export default {
  props: {
    blocks: {
      type: Object,
      default () {
        return {}
      }
    }
  },
  components: {

  },
  data () {
    return {

    }
  },
  created () {

  }
}
</script>

<style lang="sass" scoped>

</style>
