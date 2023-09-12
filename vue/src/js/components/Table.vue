<template>
  <v-data-table-server
    :class="[tableClasses]"
    id="ue-table"

    v-model:options="options"
    v-model:page="options.page"
    :items-per-page="options.itemsPerPage"
    v-model:multi-sort="options.multiSort"
    v-model:must-sort="options.mustSort"

    :headers="headers"
    :items="elements"
    :hover="true"

    :items-length="totalElements"
    :item-title="titleKey"
    ref="datatable"
  >

    <template v-slot:top>
      <!-- <div class="text-h8 text-primary font-weight-bold">
        {{ tableHeader }}
      </div> -->
      <slot name="header"
        v-bind="{
          tableHeader
        }"
        >
        <ue-title
          :text="tableHeader"
          :classes="['ue-table-header', 'pt-theme', 'pb-theme']"
          padding-reset
        />
      </slot>

      <div v-if="embeddedForm" class="ue-table-form__embedded">
        <v-btn @click="createForm" class="mb-theme">
          {{ $t('ADD NEW')}}
        </v-btn>
        <v-expand-transition>
          <v-card class="mb-7" elevation="4" v-show="formActive">
            <ue-form
              has-submit
              button-text="save"
              :title="formTitle"
            >
              <template #headerRight>
                <v-btn
                  class=""
                  variant="text"
                  icon="$close"
                  density="compact"
                  @click="closeForm()"
                ></v-btn>
              </template>
            </ue-form>
          </v-card>
          <!-- <v-row v-show="formActive">
            <v-col cols="12" md="6">

            </v-col>
          </v-row> -->
        </v-expand-transition>
      </div>

      <!-- general #dialog -->
      <ue-modal-dialog
        v-model="dialogActive"
        ref="dialog"
        :description="dialogDescription"
        @cancel="resetEditedItem"
        @confirm="deleteRow"
      />
    </template>
    <!-- <template v-slot:top>
      <v-data-table-footer
        :pagination="pagination"
        :options="options"
        @update:options="updateOptions"
        items-per-page-text="$vuetify.dataTable.itemsPerPageText"
      />
    </template> -->

    <template v-if="hideHeaders" v-slot:headers="{ columns, isSorted, getSortIcon, toggleSort }">
      <!-- <tr>
        <template v-for="column in columns" :key="column.key">
          <td>
            <span class="mr-2 cursor-pointer" @click="() => toggleSort(column)">{{ column.title }}</span>
            <template v-if="isSorted(column)">
              <v-icon :icon="getSortIcon(column)"></v-icon>
            </template>
            <v-icon v-if="column.removable" icon="$close" @click="() => remove(column.key)"></v-icon>
          </td>
        </template>
      </tr> -->
    </template>

    <!-- <template v-slot:bottom>
      <div class="text-right pa-10">
        <v-btn-tertiary>
          MANAGE RELEASES
        </v-btn-tertiary>

        <v-pagination
          v-model="options.page"
          :length="totalElements"
        ></v-pagination>

        <v-text-field
          :model-value="options.itemsPerPage"
          class="pa-2"
          label="Items per page"
          type="number"
          min="-1"
          max="15"
          hide-details
          @update:model-value="itemsPerPage = parseInt($event, 10)"
        ></v-text-field>
      </div>
    </template> -->
    <template v-slot:bottom="{page, pageCount}">
      <div class="text-right py-theme">
        <v-btn
          class="v-btn--icon bg-tertiary rounded px-8 py-2 mr-theme"
          :disabled="options.page < 2"
          @click="goPreviousPage"
          >
          <v-icon
            size="small"
            icon="$arrowLeft"
          />
        </v-btn>
        <v-btn
          class="v-btn--icon bg-tertiary rounded px-8 py-2 mr-theme"
          :disabled="options.page >= totalPage"
          @click="goNextPage"
          >
          <v-icon
            size="small"
            icon="$arrowRight"
          />
        </v-btn>
        <!-- <v-pagination
          v-model="options.page"
          :length="pageCount"
        ></v-pagination> -->
      </div>
    </template>

    <template
      v-for="(context, slotName) in slots" v-slot:[slotName]=""
      :key="`customSlot-${slotName}`"
      >
      <div>
        <ue-recursive-shit
          v-for="(configuration, i) in context.elements"
          :key="`tag-0-${i}`"
          :configuration="configuration"
        />
      </div>
    </template>

    <!-- #formatterColumns -->
    <template
      v-for="(col, i) in formatterColumns"
      v-slot:[`item.${col.key}`]="{ item }"
      >
        <template v-if="col.formatter == 'edit'">
          <v-btn
            :key="i"
            class="pa-0 justify-start"
            variant="plain"
            :color="`primary darken-1`"
            @click="editItem(item.raw)"
            >
            {{ item.raw[col.key] }}
          </v-btn>
        </template>
        <template v-else>
          {{ handleFormatter(col.formatter, item.raw[col.key] ) }}
        </template>
    </template>

    <template v-slot:item.actions="{ item }">
      <!-- @click's editItem|deleteItem -->
      <!-- #actions -->
      <v-menu v-if="rowActionsType == 'dropdown' || $vuetify.display.smOnly"
        :close-on-content-click="false"
        open-on-hover
        left
        offset-x
        >
        <template v-slot:activator="{ props }">
          <v-btn icon v-bind="props">
            <v-icon color="green darken-2">
              $list
            </v-icon>
          </v-btn>
        </template>
        <v-list>

          <v-list-item
            v-for="(action, k) in rowActions"
            :key="k"
            @click="$call(action.name + 'Item', item.raw)"
            >
              <v-icon small :color="action.color" left>
                {{ action.icon ? action.icon : '$' + action.name }}
              </v-icon>
              {{ $t(action.name) }}
          </v-list-item>

        </v-list>
      </v-menu>

      <div v-else>

        <v-icon
          v-for="(action, k) in rowActions"
          :key="k"
          small
          class="mr-2"
          @click="$call(action.name + 'Item', item.raw)"
          :color="action.color"
          >
          {{ action.icon ? action.icon : '$' + action.name }}
        </v-icon>
      </div>
    </template>

  </v-data-table-server>
</template>

<script>
import { ref, onMounted, useSlots } from 'vue'
import { VDataTable, VDataTableServer, VDataTableFooter } from 'vuetify/labs/VDataTable'

import { TableMixin } from '../mixins'
import { useTable } from '@/hooks'

export default {
  mixins: [TableMixin],
  components: {
    VDataTable,
    VDataTableFooter,
    VDataTableServer
  },
  props: {

  },
  setup (props, context) {
    const tableDefaults = useTable(props, context)

    return {
      ...tableDefaults
    }
  },
  data () {
    return {

    }
  },
  computed: {
    // tableHeader () {
    //   return __isset(this.customHeader)
    //     ? this.$lodash.upperCase(this.customHeader)
    //     : this.$t(`modules.${this.$lodash.snakeCase(this.name)}`, 1)
    //     // : this.$t(`modules.${this.$lodash.snakeCase(this.name)}`, { n: 3 })
    //     // : this.$lodash.upperCase(this.$t('list-of-item', [this.name, this.$t('modules.' + this.$lodash.snakeCase(this.name))]))
    // }
  },
  methods: {

  },
  mounted () {

  }
}
</script>

<style lang="sass">

</style>
