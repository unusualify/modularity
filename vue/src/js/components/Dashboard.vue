<template>
  <v-row class="h-100">
    <ue-recursive-stuff
      v-for="(block, i) in blocks"
      :key="`tag-0-${i}`"
      :configuration="block"
    />
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
  setup (props, context) {
    return;
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
