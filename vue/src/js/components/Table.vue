<template>
  <v-data-table
    class="elevation-1 ue-table"

    v-model:options="options"
    :items-per-page="options.itemsPerPage"
    :headers="headers"
    :items="elements"

    :items-length="totalElements"
    :item-title="titleKey"
    ref="datatable"
  >

    <!-- <template v-slot:top>
      <v-data-table-footer
        :pagination="pagination"
        :options="options"
        @update:options="updateOptions"
        items-per-page-text="$vuetify.dataTable.itemsPerPageText"
      />
    </template> -->

    <template v-slot:bottom>
      <div class="text-center pt-2">
        <v-pagination
          v-model="options.page"
          :length="totalElements"
        ></v-pagination>
        <!-- <v-text-field
          :model-value="options.itemsPerPage"
          class="pa-2"
          label="Items per page"
          type="number"
          min="-1"
          max="15"
          hide-details
          @update:model-value="itemsPerPage = parseInt($event, 10)"
        ></v-text-field> -->
      </div>
    </template>

  </v-data-table>
</template>

<script>
import { ref, onMounted, useSlots } from 'vue'
import { VDataTable, VDataTableServer, VDataTableFooter } from 'vuetify/labs/VDataTable'
import { DatatableMixin } from '@/mixins'

export default {
  setup () {

  },
  mixins: [DatatableMixin],
  components: {
    VDataTable,
    VDataTableFooter,
    VDataTableServer
  },
  data () {
    return {

    }
  },
  mounted () {
    __log(
      // this.$refs.datatable,
      // this.$refs.datatable.$slots,
      this.options
      // this.$refs.datatable.$slots.default()
    )
  }
}
</script>

<style lang="sass">
.ue-table
  .v-table__wrapper
    > table
      > thead,
        tbody
        > tr
          > td,
            th
            border-right: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
            border-bottom: thin solid rgba(var(--v-border-color), var(--v-border-opacity))
</style>
