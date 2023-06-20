<template>
  <v-data-table
    class=""
    id="ue-table"
    v-model:options="options"
    :items-per-page="options.itemsPerPage"
    :headers="headers"
    :items="elements"
    :hover="true"

    :items-length="totalElements"
    :item-title="titleKey"
    ref="datatable"
  >

    <template v-slot:top>
      <v-col class="">
        <div class="text-h8 pt-2 px-2 text-primary font-weight-bold">
          {{ tableTitle }}
        </div>
      </v-col>
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
        {{ handleFormatter(col.formatter, item.raw[col.key] ) }}
        <!-- {{ formatterDate(item.raw[col.key], col.formatter[1] ) }} -->
        <!-- {{ [`formatter${$lodash.startCase($lodash.camelCase(col.formatter[0])).replace(/ /g, '')}`](item.raw[col.key], col.formatter[1]) }} -->
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

  </v-data-table>
</template>

<script>
import { ref, onMounted, useSlots } from 'vue'
import { VDataTable, VDataTableServer, VDataTableFooter } from 'vuetify/labs/VDataTable'
import { useTable } from '@/hooks/table.js'

export default {
  components: {
    VDataTable,
    VDataTableFooter,
    VDataTableServer
  },
  props: {
    name: {
      type: String,
      default: 'Item'
    },
    customTitle: {
      type: String
    },
    titleKey: {
      type: String,
      default: 'name'
    },
    items: {
      type: Array
    },
    hideHeaders: {
      type: Boolean,
      default: false
    },
    columns: {
      type: Array
    },
    inputFields: {
      type: Array
    },
    tableOptions: {
      type: Object
    },
    slots: {
      type: Object,
      default () {
        return {}
      }
    },
    hideDefaultHeader: Boolean,
    hideDefaultFooter: Boolean,
    isRowEditing: Boolean,
    createOnModal: Boolean,
    editOnModal: Boolean
  },
  setup (props, context) {
    const tableDefaults = useTable(props, context)

    return {
      ...tableDefaults
    }
  },
  // mixins: [DatatableMixin],
  data () {
    return {

    }
  },
  computed: {
    tableTitle () {
      return __isset(this.customTitle)
        ? this.$lodash.upperCase(this.customTitle)
        : this.$lodash.upperCase(this.$t('list-of-item', [this.name, this.$t('modules.' + this.$lodash.snakeCase(this.name))]))
    }
  },
  methods: {
    editItem (item) {
      if (this.editOnModal) {
        this.setEditedItem(item)
        this.$refs.formModal.openModal()
      } else {
        const route = this.editUrl.replace(':id', item.id)
        window.open(route)
      }
    },
    deleteItem (item) {
      this.setEditedItem(item)
      this.$refs.dialog.openModal()
    }
  },
  mounted () {

  }
}
</script>

<style lang="sass">

</style>
