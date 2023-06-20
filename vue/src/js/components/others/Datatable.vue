<template>
  <v-data-table-server
    class="elevation-1 "
    :headers="headers"
    :items="elements"
    :loading="loading"

    v-model:options="options"
    :items-per-page="options.itemsPerPage"
    v-model:multi-sort="options.multiSort"
    v-model:must-sort="options.mustSort"

    :items-length="totalElements"
    :item-title="titleKey"

    :search="search"
    :hide-default-header="hideDefaultHeader"
    :hide-default-footer="hideDefaultFooter"
    :footer-props="{
      showFirstLastPage: true,
      firstIcon: 'mdi-arrow-collapse-left',
      lastIcon: 'mdi-arrow-collapse-right',
      nextIcon: 'mdi-chevron-right',
      prevIcon: 'mdi-chevron-left'
    }"

    :disable-pagination="false"
    :disable-sort="false"

    v-model="selectedItems"
    show-selected

    :loading-text="$t('loading-text')"

  >
    <template v-slot:top>
      <v-toolbar
        flat
      >
        <!-- #title.left-top -->
        <v-toolbar-title>
          {{ $t('list-of-item', [name, $t('modules.' + $lodash.snakeCase(name) )] ) }}
          <!-- {{ $t('errors.missingMessage') }} -->
        </v-toolbar-title>

        <v-divider class="mx-4" inset vertical></v-divider>

        <!-- #search input -->
        <v-text-field
          v-model="search"
          append-inner-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
          density="compact"
          variant="solo"
        >
        </v-text-field>

        <v-divider class="mx-4" inset vertical></v-divider>

        <!-- #language selector -->
        <v-toolbar-title v-show="false">
          <!-- {{ $t('list') }}
          {{ $n(100.77, 'currency') }} -->
          {{ $t('language-select') }}
          <select v-model="$i18n.locale">
            <option v-for="(lang, i) in langs" :key="`Lang${i}`" :value="lang">
              {{ lang }}
            </option>
          </select>
        </v-toolbar-title>

        <!-- Custom Filters -->
        <v-menu offset-y rounded="xs" open-on-hover>
          <template v-slot:activator="{ props, isActive }">
            <v-btn
              v-bind="props"
              variant="elevated"
            >
              {{ mainFilters.at(0).name }}
              <v-spacer></v-spacer>
              <v-icon right :style="{ transform: isActive ? 'rotate(-180deg)' : 'rotate(0)' }">mdi-chevron-down</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item
              v-for="(item, index) in mainFilters"
              :key="index"
            >
              <v-list-item-title>{{ item.name}}</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>

        <v-divider class="mx-4" inset vertical></v-divider>

        <v-spacer></v-spacer>

        <!-- #form dialog -->
        <slot
          v-if="createOnModal || editOnModal"
          name="FormDialog"
          >
          <ue-modal-form
              ref="formModal"
              v-model="formModalActive"
              :route-name="name"
              >
              <template v-slot:activator="{props}">
                <v-btn-success
                    v-if="createOnModal"
                    v-bind="props"
                    dark
                    >
                    {{ $t('new-item', {'item': name} ) }}
                </v-btn-success>
              </template>
          </ue-modal-form>
        </slot>

        <v-btn-success
          v-if="!createOnModal"
          :href="createUrl"
          target="_blank"
          dark
          >
          {{ $t('new-item', {'item': name} ) }}
        </v-btn-success>

        <!-- general #dialog -->
        <ue-modal-dialog
          v-model="dialogActive"
          ref="dialog"
          :description="dialogDescription"
          @cancel="resetEditedItem"
          @confirm="deleteRow"
        >
        </ue-modal-dialog>

      </v-toolbar>
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
            @click="handleFunctionCall(action.name + 'Item', item.raw)"
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
          @click="handleFunctionCall(action.name + 'Item', item.raw)"
          :color="action.color"
          >
          {{ action.icon ? action.icon : '$' + action.name }}
        </v-icon>
      </div>
    </template>

    <template v-slot:no-data>
      <div class="w-100 d-flex justify-center">
        <v-btn
          color="primary"
          @click="initialize"
        >
          Reset
        </v-btn>
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

    <!-- #edit-dialog for editableColumns-->
    <!-- <template
      v-for="(header, k) in editableColumns"
      v-slot:[`item.${header.value}`]="props"
      :key="k"
      >
        <v-edit-dialog
          v-model:return-value="props.item[header.value]"
          :save-text="$t('save')"
          @open="setEditedItem(props.item)"
          @cancel="resetEditedItem()"
          @close="resetEditedItem()"
          @save="updateCell(header.value)"

          persistent
          large

        >
          {{ props.item[header.value] }}
          <template v-slot:input>
            <v-text-field
              :value="props.item[header.value]"
              @input="columnChanged"
              @keyup.enter="updateCell(header.value)"
              label="Edit"
              single-line
              counter
            >
            </v-text-field>
          </template>
        </v-edit-dialog>
    </template> -->

    <!-- <template v-for="header in headers" v-slot:[`column.${header.key}`]="{ column }">
        <v-icon v-if="header.removable" icon="$close" @click="() => hideColumn(header.key)"></v-icon>
        <span class="mr-2">{{ header.title }}</span>
    </template> -->
    <!-- <template v-slot:headers="{ columns }">
      <tr>
        <template v-for="column in columns" :key="column.key">
          <td>
            <v-icon v-if="column.removable" icon="$close" @click="() => hideColumn(column.key)"></v-icon>
            <span class="mr-2">{{ column.title }}</span>
            <v-icon
              key="icon"
              v-if="column.removable"
              class="v-data-table-header__sort-icon"
              :icon="getSortIcon(column.key)"
            />
          </td>
        </template>
      </tr>
    </template> -->
    <!-- <template v-slot:headers="{props}">
      <tr>
        <th>
          <v-checkbox
            :input-value="props.all"
            :indeterminate="props.indeterminate"
            primary
            hide-details
            @click.native="toggleAll"
          ></v-checkbox>
        </th>
        <th
          v-for="header in props.headers"
          :key="header.text"
          :class="['column sortable', pagination.descending ? 'desc' : 'asc', header.value === pagination.sortBy ? 'active' : '']"
          @click="changeSort(header.value)"
        >
          <v-icon small>arrow_upward</v-icon>
          {{ header.text }}
        </th>
      </tr>
      <tr class="grey lighten-3">
        <th>
          <v-icon>$filter_list</v-icon>
        </th>
        <th
          v-for="header in props.headers"
          :key="header.text"
          >
          <div v-if="filters.hasOwnProperty(header.value)">
            <v-select flat hide-details small multiple clearable
              :items="columnValueList(header.value)" v-model="filters[header.value]"
              >

            </v-select>
          </div>
        </th>
      </tr>
    </template> -->

  </v-data-table-server>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import { VDataTable, VDataTableServer } from 'vuetify/labs/VDataTable'
import ACTIONS from '@/store/actions'

import { DatatableMixin } from '@/mixins'
import { useTable } from '@/hooks/table.js'

export default {
  components: {
    // VDataTable,
    VDataTableServer
  },
  setup (props, context) {
    const tableDefaults = useTable(props, context)

    return {
      ...tableDefaults
    }
  },
  props: {
    name: {
      type: String,
      default: 'Item'
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
    hideDefaultHeader: Boolean,
    hideDefaultFooter: Boolean,
    isRowEditing: Boolean,
    createOnModal: Boolean,
    editOnModal: Boolean
  },
  data: function () {
    return {
      formModalActive: false,
      dialogActive: false,

      langs: ['tr', 'en'],
      cellInput: ''

    }
  },
  computed: {
    dialogDescription () {
      return this.$t('confirm-deletion', {
        route: this.transName.toLowerCase(),
        name: this.editedItem[this.titleKey]
      })
    },
    transName () {
      return this.$t('modules.' + this.$lodash.snakeCase(this.name))
    },
    ...mapState({
      // datatable module
      // headers: state => state.datatable.headers,
      loading: state => state.datatable.loading,
      // elements: state => state.datatable.data,

      // form module
      inputs: state => state.form.inputs,
      editedItem: state => state.form.editedItem,
      formLoading: state => state.form.loading,
      formErrors: state => state.form.errors
    })
  },
  created () {

  },
  watch: {
    formModalActive (val) {
      val || this.resetEditedItem()
    },
    dialogActive (val) {
      val || this.resetEditedItem()
    }
  },
  methods: {
    changeSort (column) {
      if (this.pagination.sortBy === column) {
        this.pagination.descending = !this.pagination.descending
      } else {
        this.pagination.sortBy = column
        this.pagination.descending = false
      }
    },
    columnValueList (val) {
      return this.elements.map(d => d[val])
    },
    columnChanged (value) {
      this.cellInput = value
    },
    deleteItem (item) {
      this.setEditedItem(item)
      this.$refs.dialog.openModal()
    },
    deleteRow: function () {
      this.$store.dispatch(ACTIONS.DELETE_ITEM, {
        id: this.editedItem.id,
        callback: () => {
          this.$refs.dialog.closeModal()
        },
        errorCallback: () => {

        }
      })
    },
    editItem (item) {
      if (this.editOnModal) {
        this.setEditedItem(item)
        this.$refs.formModal.openModal()
      } else {
        const route = this.editUrl.replace(':id', item.id)
        window.open(route)
      }
    },
    handleFunctionCall (functionName, ...val) {
      return this[functionName](...val)
    },
    hideColumn (key) {
      this.headers = this.headers.filter(header => header.key !== key)
    },
    updateCell (key) {
      this.$store.commit(ALERT.CLEAR_ALERT)

      if (this.editedItem[key] !== this.cellInput) {
        const data = {
          id: this.editedItem.id,
          [key]: this.cellInput
          // reload: false
        }

        this.$store.dispatch(ACTIONS.SAVE_FORM, { item: data })
      }
    }
  }
}
</script>
