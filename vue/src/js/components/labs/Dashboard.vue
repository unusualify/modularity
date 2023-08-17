<template>
  <v-row>
    <!-- <v-col xxl="6" xl="8" lg="8" cols="12" v-fit-grid>
      <ue-customboard-information></ue-customboard-information>
    </v-col> -->
    <v-col
      v-for="(block,i) in blocks"
      :key="`block-${i}`"

      v-bind="{
        ...$bindAttributes(block.col)
      }"
      v-fit-grid
      >
      <v-sheet class="">
        <component
          :is="`ue-${block.component}`"
          v-bind="block.attributes"
         ></component>
      </v-sheet>
    </v-col>
    <!-- <v-col xxl="4" xl="4" lg="4" cols="12">
      <v-sheet class="h-100">
        <div class="text-h6 pa-4 text-primary font-weight-bold">Recent Revisions</div>

      </v-sheet>
    </v-col>
    <v-col xxl="4" xl="8" lg="8" cols="12">
      <v-sheet class="h-100">
        <div class="text-h6 pa-4 text-primary font-weight-bold">Recently Published</div>
      </v-sheet>
    </v-col>
    <v-col xxl="4" xl="4" lg="4" cols="12">
      <v-sheet class="h-100">
        <div class="text-h6 pa-4 text-primary font-weight-bold">News</div>
      </v-sheet>
    </v-col> -->
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
