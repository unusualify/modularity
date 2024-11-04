<template>
  <v-layout fluid v-resize="onResize">
    <v-data-table-server
      v-bind="$bindAttributes()"

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
      :footer-propss="{
        showFirstLastPage: true,
        firstIcon: 'mdi-arrow-collapse-left',
        lastIcon: 'mdi-arrow-collapse-right',
        nextIcon: 'mdi-chevron-right',
        prevIcon: 'mdi-chevron-left'
      }"

      :disable-pagination="false"
      :disable-sort="false"
      :loading-text="$t('fields.loading-text')"

      v-model="selectedItems"
      show-selected
      sticky
      fixed-header
      fixed-footer

      :height="windowSize.y - 64 - 24 - 59 - 36"

      density="comfortable"
    >
      <template v-slot:top>
        <v-toolbar
          flat
        >
          <!-- #title.left-top -->
          <v-toolbar-title>
            <!-- {{ $t('fields.list-of-item', [name, $t('modules.' + $lodash.snakeCase(name) )] ) }} -->
            <slot name="header" v-bind="{tableTitle}">
              <ue-title
                :text="tableTitle"
                :classes="['ue-table-header', 'pt-12', 'pb-12']"
              />
            </slot>
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
            <!-- {{ $t('fields.list') }}
            {{ $n(100.77, 'currency') }} -->
            {{ $t('fields.language-select') }}
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
                {{ `${filterActive.name} (${filterActive.number})` }}
                <v-spacer></v-spacer>
                <v-icon right :style="{ transform: isActive ? 'rotate(-180deg)' : 'rotate(0)' }">mdi-chevron-down</v-icon>
              </v-btn>
            </template>
            <v-list>
              <v-list-item
                v-for="(filter, index) in mainFilters"
                :key="index"
                v-on:click.prevent="filterStatus(filter.slug)"
              >
                <v-list-item-title>{{ filter.name + '(' + filter.number+ ')'}} </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>

          <v-divider class="mx-4" inset vertical></v-divider>

          <v-spacer></v-spacer>

          <!-- #form dialog -->
          <slot v-if="(createOnModal || editOnModal) && !noForm" name="formDialog" >
            <!-- <ue-modal-form
                ref="formModal"
                v-model="formActive"
                :route-name="name"
                >
                <template v-slot:title>

                </template>
                <template v-slot:activator="{props}">
                  <v-btn-success
                      v-if="createOnModal"
                      v-bind="props"
                      dark
                      >
                      {{ $t('add-item', {'item': name} ) }}
                  </v-btn-success>
                </template>
            </ue-modal-form> -->
            <ue-modal
                ref="formModal"
                v-model="formActive"
                scrollable
                transition="dialog-bottom-transition"
                width-type="lg"
                >
                <template v-slot:activator="{props}">
                  <v-btn-success v-if="can('create') && createOnModal" v-bind="props" dark>
                      {{ $t('add-item', {'item': transNameSingular} ) }}
                  </v-btn-success>
                </template>
                <template v-slot:body="props">
                  <v-card >
                    <v-card-title class="text-h5 grey lighten-2"> </v-card-title>

                    <v-card-text>
                      <ue-form
                        ref="form"
                        :title="formTitle"
                        />
                    </v-card-text>

                    <v-divider/>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="error darken-1" text @click="closeForm()">
                            {{ props.textCancel }}
                        </v-btn>
                        <v-btn color="teal darken-1"
                          text
                          @click="confirmFormModal()"
                          :disabled="!formIsValid"
                          :loading="formLoading"
                          >
                          {{ $t('fields.save') }}
                        </v-btn>
                    </v-card-actions>

                  </v-card>
                </template>
            </ue-modal>
          </slot>

          <v-btn-success v-if="!createOnModal" :href="createUrl" target="_blank" dark>
            {{ $t('add-item', {'item': transNameSingular} ) }}
          </v-btn-success>

          <!-- #deletemodal-->
          <!-- <ue-modal-dialog
            v-model="deleteModalActive"
            ref="dialog"
            :description="dialogDescription"
            @cancel="resetEditedItem"
            @confirm="deleteRow"
          >
          </ue-modal-dialog> -->
          <ue-modal
            ref="deleteModal"
            v-model="deleteModalActive"
            transition="dialog-bottom-transition"
            width-type="sm"
            >
            <template v-slot:body="props" >
              <v-card >
                <v-card-title class="text-h5 text-center" style="word-break: break-word;">
                  <!-- {{ textDescription }} -->
                </v-card-title>
                <v-card-text class="text-center" style="word-break: break-word;" >
                  {{ deleteQuestion }}
                </v-card-text>
                <v-divider/>
                <v-card-actions>
                  <v-spacer/>
                  <v-btn color="blue" text @click="closeDeleteModal()"> {{ props.textCancel }}</v-btn>
                  <v-btn color="blue" text @click="deleteRow()"> {{ props.textConfirm }}</v-btn>
                  <v-spacer></v-spacer>
                </v-card-actions>
              </v-card>
            </template>
          </ue-modal>

        </v-toolbar>
      </template>

      <template v-slot:header.actions="_obj">
        <v-menu
          :close-on-content-click="false"
          open-on-hover
          left
          >
          <template v-slot:activator="{ props }">
            <v-icon
              size="large"
              icon="mdi-cog-outline"
              v-bind="props"
              >
            </v-icon>
          </template>
          <!-- <v-list>
            <v-list-item>
              <v-checkbox label="Name"></v-checkbox>
            </v-list-item>
            <v-list-item>
              <v-checkbox label="Guard Name"></v-checkbox>
            </v-list-item>
          </v-list> -->
        </v-menu>
      </template>
      <template v-slot:item.actions="{ item }">
        <!-- @click's editItem|deleteItem -->
        <!-- #actions -->
        <v-menu v-if="rowActionsType == 'dropdown' || isSmAndDown"
          :close-on-content-click="false"
          open-on-hover
          left
          offset-x
          >
          <template v-slot:activator="{ props }">
            <v-icon
              size="large"
              color="primary"
              icon="$list"
              v-bind="props"
              >
            </v-icon>
          </template>
          <v-list>
            <template v-for="(action, k) in rowActions" :key="k">
              <v-list-item
                v-if="itemHasAction(item, action)"
                @click="itemAction(item, action.name)"
                >
                  <v-icon small :color="action.color" left>
                    {{ action.icon ? action.icon : '$' + action.name }}
                  </v-icon>
                  {{ $t(action.name) }}
              </v-list-item>
            </template>
          </v-list>
        </v-menu>

        <div v-else>
          <template v-for="(action, k) in rowActions" :key="k">
            <v-tooltip
              v-if="itemHasAction(item, action)"
              :text="$t( action.label ?? action.name )"
              location="top"
              >
              <template v-slot:activator="{ props }">
                <v-icon
                  small
                  class="mr-2"
                  @click="itemAction(item, action)"
                  :color="action.color"
                  v-bind="props"
                  >
                  {{ action.icon ? action.icon : '$' + action.name }}
                </v-icon>
              </template>
            </v-tooltip>
          </template>
        </div>
      </template>

      <template v-slot:no-data v-if="search != ''">
        <div class="w-100 d-flex justify-center my-5">
          <v-btn
            color="primary"
            @click="initialize"
          >
            {{ $t('fields.reset') }}
          </v-btn>
        </div>
      </template>

      <!-- #formatterColumns -->
      <template
        v-for="(col, i) in formatterColumns"
        v-slot:[`item.${col.key}`]="{ item }"
        >
        <template v-if="col.formatter == 'edit' || col.formatter == 'activate'">
          <v-btn
            :key="i"
            class="pa-0 justify-start"
            variant="plain"
            :color="`primary darken-1`"
            @click="$call(col.formatter + 'Item', item)"
            >
            {{ item[col.key] }}
          </v-btn>
          </template>
          <template v-else-if="col.formatter == 'switch'">
            <v-switch
              :key="i"
              :model-value="item[col.key]"
              color="success"
              :true-value="1"
              false-value="0"
              hide-details
              @update:modelValue="itemAction(item, 'switch', $event, col.key)"
              >
              <template v-slot:label></template>
            </v-switch>
          </template>
          <template v-else>
            <ue-recursive-stuff
              v-bind="handleFormatter(col.formatter, item[col.key])"
              :key="item[col.key]"
              />
          </template>
      </template>

      <!-- #edit-dialog for editableColumns-->
      <!-- <template
        v-for="(header, k) in editableColumns"
        v-slot:[`item.${header.value}`]="props"
        :key="k"
        >
          <v-edit-dialog
            v-model:return-value="props.item[header.value]"
            :save-text="$t('fields.save')"
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
  </v-layout>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import ACTIONS from '@/store/actions'

import useTable, { makeTableProps } from '@/hooks/useTable'
import { ALERT } from '@/store/mutations'

export default {
  setup (props, context) {
    return {
      ...useTable(props, context)
    }
  },
  props: {
    ...makeTableProps()
  },
  data: function () {
    return {

      langs: ['tr', 'en'],
      cellInput: ''

    }
  },
  computed: {
    ...mapState({
      // datatable module
      // headers: state => state.datatable.headers,
      // loading: state => state.datatable.loading,
      // // elements: state => state.datatable.data,

      // // form module
      // inputs: state => state.form.inputs,
      // editedItem: state => state.form.editedItem,
      // formLoading: state => state.form.loading,
      // formErrors: state => state.form.errors
    })
  },
  created () {
    // __log(this.rowActions)
  },
  watch: {

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
