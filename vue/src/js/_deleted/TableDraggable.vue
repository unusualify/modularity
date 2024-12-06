<template>
  <v-data-table-server
    class="px-0"
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
    ref="tabledraggable"
  >

    <template v-slot:top>
      <slot name="header" v-bind="{ tableTitle }">
        <ue-title
          :text="tableTitle"
          :classes="['pl-1, ue-table-header']"
        />
      </slot>

      <div v-if="embeddedForm">
        <v-btn class="my-7" @click="createForm">
          {{ $t('ADD NEW')}}
        </v-btn>
        <v-expand-transition>
          <v-row v-show="formActive">
            <v-col cols="12" md="6">
              <v-card class="mb-7" elevation="4">
                <v-card-title>
                  <v-sheet class="d-flex">
                    <v-sheet class="ma-2 pa-2 me-auto"><span>{{ formTitle }}</span></v-sheet>
                    <v-sheet class="ma-2 pa-2">
                      <v-btn
                        class=""
                        variant="text"
                        icon="$close"
                        density="compact"
                        @click="closeForm()"
                      ></v-btn>
                    </v-sheet>
                  </v-sheet>
                </v-card-title>
                <ue-form
                  :has-submit="true"
                  :sticky-button="false"
                  button-text="save"
                  :form-title="formTitle"
                  />
              </v-card>
            </v-col>
          </v-row>
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

    <template #tbody="props">
      <draggable v-model="props.items" itemKey="value" tag="transition-group" :component-data="{name:'fade'}">
        <!-- <v-nodes :vnodes="$refs.tabledraggable.genItems(props.items, props)" /> -->

        <template #item="item">
          <VDataTableRow :index="item.index" :item="item.element" />
        </template>
      </draggable>
    </template>

    <template v-if="hideHeaders" v-slot:headers="{ columns, isSorted, getSortIcon, toggleSort }"></template>

    <template v-slot:bottom="{page, pageCount}">
      <div class="text-right py-12">
        <v-btn
          class="v-btn--icon bg-tertiary rounded px-8 py-2 mr-12"
          :disabled="options.page < 2"
          @click="goPreviousPage"
          >
          <v-icon
            size="small"
            icon="$arrowLeft"
          />
        </v-btn>
        <v-btn
          class="v-btn--icon bg-tertiary rounded px-8 py-2 mr-12"
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
        <ue-recursive-stuff
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
            class="pa-0"
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
import { mergeProps as _mergeProps, createVNode as _createVNode } from 'vue'
// import { VDataTableRows } from 'vuetify/labs/ VDataTableRows'

// import { VDataTableServer, VDataTableRows, VDataTableRow } from 'vuetify/labs/VDataTable'

// import { TableMixin } from '@/mixins'
import { useTable } from '@/hooks'

import draggable from 'vuedraggable'

export default {
  // mixins: [TableMixin],
  components: {
    draggable,
    VNodes: {
      functional: true,
      render: (h, ctx) => ctx.props.vnodes
    }
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
    tableTitle () {
      return __isset(this.customHeader)
        ? this.$lodash.upperCase(this.customHeader)
        : this.$lodash.upperCase(this.$t('list-of-item', [this.name, this.$t('modules.' + this.$lodash.snakeCase(this.name))]))
    }
  },
  methods: {
    createDatatableRow (props, groupedItems) {
      return _createVNode(VDataTableRows, _mergeProps(VDataTableRows.filterProps(props), {
        items: groupedItems
      }))

      return _createVNode(VDataTableRow, itemSlotProps.props, slots)
    }
  },
  mounted () {

  }
}
</script>

<style lang="sass">

</style>
