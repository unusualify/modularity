<template>
  <div>
    <ue-modal
      ref="customModal"
      v-model="modalActive"
      scrollable
      transition="dialog-bottom-transition"
      width-type="md"
      @click:outside="clickOutside"
      >
      <template v-slot:body="props">
        <v-card class="pa-theme">
          <v-item-group selected-class="bg-primary">
            <v-container>
              <v-row>
                <v-col
                  v-for="(block, key) in itemData"
                  :key="key"
                  v-bind="$bindAttributes( !!block.clickBlock ? block.clickBlock.col : {cols: 12})"
                  >
                    <v-item v-slot="{ isSelected, selectedClass, toggle }">
                      <template v-if="!!block.clickBlock && !!block.clickBlock.elements">
                        <ue-recursive-stuff :configuration="block.clickBlock.elements" @click="selectNested(key)">
                          {{ block.title }}
                        </ue-recursive-stuff>
                      </template>
                      <v-btn v-else
                        @click="selectNested(key)"
                        class="w-100"
                        style="max-width: unset;text-transform: uppercase;"
                        >
                        {{ block.title }}
                      </v-btn>
                  </v-item>
                </v-col>
                <!-- <v-col cols="12" md="6" class="pa-4">
                  <v-item v-slot="{ isSelected, selectedClass, toggle }">
                    <v-card :class="['d-flex align-center bg-primary ue-card-button px-4', selectedClass]" dark height="200" @click="selectNested(1)" >
                      <div class="text-h6 font-weight-bold flex-grow-1 text-center" > COMPANY INFORMATION</div>
                    </v-card>
                  </v-item>
                </v-col>
                <v-col cols="12" md="6" class="pa-4">
                  <v-item v-slot="{ isSelected, selectedClass, toggle }">
                    <v-card :class="['d-flex align-center bg-cta ue-card-button px-4', selectedClass]" dark height="200" @click="selectNested(2)" >
                      <div class="text-h6 font-weight-bold flex-grow-1 text-center" > PRESS RELEASES </div>
                    </v-card>
                  </v-item>
                </v-col>
                <v-col cols="12" md="12" class="pa-4">
                  <v-item v-slot="{ isSelected, selectedClass, toggle }">
                    <v-card :class="['d-flex align-center bg-success ue-card-button px-4', selectedClass]" dark height="80" @click="selectNested(3)" >
                      <div class="text-h6 font-weight-bold flex-grow-1 text-center"> CREDITS & INVOICES </div>
                    </v-card>
                  </v-item>
                </v-col> -->
              </v-row>
            </v-container>
          </v-item-group>
        </v-card>
      </template>
    </ue-modal>
    <template v-if="!!item && !modalActive && activeBlock">
      <ue-table
        v-bind="{...$bindAttributes(), tableClasses: $parent.tableClasses}"

        :custom-title="activeBlock.title"
        class="mb-theme"
        :is-row-editing="false"
        :embedded-form="false"
        :create-on-modal="false"
        :edit-on-modal="false"
        :row-actions="[]"
        :columns="tableHeaders"
        :items="items"
        :table-options="{
          page: 1,
          itemsPerPage: 1,
          multiSort: false,
          mustSort: false
        }"
        hide-footer
        no-footer
        no-form
        no-full-screen
        :ignore-formatters="['activate']"
        >
        <template v-slot:headerRight>
          <v-btn class="bg-grey-darken-1"
            color="white"
            variant="tonal"
            icon="$close" density="compact"
            @click="closeItemDetails()"
          >
            <v-icon icon="$close" size="small"></v-icon>
          </v-btn>
        </template>
      </ue-table>
      <div>
        <ue-recursive-stuff :configuration="activeBlock.elements"/>
      </div>
    </template>
  </div>
</template>

<script>
import { makeActiveTableItemProps, useActiveTableItem } from '__hooks'

export default {
  props: {
    ...makeActiveTableItemProps()
  },
  setup (props, context) {
    // const { item, items, modalActive, modalStatus, selectNested, clickOutside, closeItemDetails } = useActiveTableItem(props, context)
    return {
      // item,
      // items,
      // modalActive,
      // modalStatus,
      // selectNested,
      // clickOutside,
      // closeItemDetails

      ...useActiveTableItem(props, context)
    }
  },
  // methods: {
  //   // toggle (column) {
  //   //   if (this.pagination.sortBy === column) {
  //   //     this.pagination.descending = !this.pagination.descending
  //   //   } else {
  //   //     this.pagination.sortBy = column
  //   //     this.pagination.descending = false
  //   //   }
  //   // },
  // }
  created () {

  }
}
</script>

<style lang="sass" scoped>

</style>
