<template>
  <v-row>
    <!-- <v-col v-for="(block,i) in blocks" :key="`block-${i}`"
      v-bind="$bindAttributes(block.col)"
      v-fit-grid
      >
        <component
          :is="`ue-${block.component}`"
          v-bind="block.attributes"
         ></component>
    </v-col> -->
    {{ console.log(blocks) }}
    <ue-recursive-stuff
      v-for="(block, i) in blocks"
      :key="`tag-0-${i}`"
      :configuration="block.widget"
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
