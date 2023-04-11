<template>
  <v-data-table
    :headers="headers"
    :items="elements"
    
    class="elevation-1"
    :loading="loading"
    :options.sync="options"
    :server-items-length="totalElements"
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
  >
    <template v-slot:top>
      <v-toolbar
        flat
      >
        <!-- #title.left-top -->
        <v-toolbar-title>
          {{ $tc('list-of-item', [name, $tc('modules.' + name.toLowerCase() )] ) }}
          <!-- {{ $t('errors.missingMessage') }} -->
        </v-toolbar-title>

        <v-divider class="mx-4" inset vertical></v-divider>

        <!-- #search input -->
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
        >
        </v-text-field>

        <v-divider class="mx-4" inset vertical></v-divider>
        
        <!-- #language selector -->
        <v-toolbar-title v-show="false">
          <!-- {{ $tc('list') }}
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
          <template v-slot:activator="{ on, attrs, value }">
            <v-btn
              v-bind="attrs"
              v-on="on"
              class="rounded-xs"
              color="primary"
              dark
              :ripple="false"
            >
              {{ mainFilters.at(0).name }}
              <v-spacer></v-spacer>
              <v-icon right :style="{ transform: value ? 'rotate(-180deg)' : 'rotate(0)' }">mdi-chevron-down</v-icon>
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
              <template v-slot:activator="{on,attrs}">
                <ue-btn
                    v-if="createOnModal"
                    v-bind="attrs"
                    v-on="on"
                    color="success"
                    dark
                    >
                    {{ $t('new-item', {'item': name} ) }}
                </ue-btn>
              </template>
          </ue-modal-form>
        </slot>

        <ue-btn
          v-if="!createOnModal"
          :href="createUrl" 
          target="_blank"
          color="success"
          dark
          >
          {{ $t('new-item', {'item': name} ) }}
        </ue-btn>

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
      <v-menu v-if="rowActionsType == 'dropdown' || $vuetify.breakpoint.smOnly" 
        :close-on-content-click="false"
        open-on-hover
        left
        offset-x
        >
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" v-on="on">
            <v-icon color="green darken-2">
              $list
            </v-icon>
          </v-btn>
        </template>
        <v-list>

          <v-list-item
            v-for="(action, k) in rowActions"
            :key="k"
            @click="handleFunctionCall(action.name + 'Item', item)"
            >
              <v-icon small :color="action.color" left>           
                {{ action.icon ? action.icon : '$' + action.name }}
              </v-icon>
              {{ $tc(action.name) }}
          </v-list-item>

        </v-list>
      </v-menu>

      <div v-else="">
        <v-icon
          v-for="(action, k) in rowActions"
          :key="k"
          small
          class="mr-2"
          @click="handleFunctionCall(action.name + 'Item', item)"
          :color="action.color"
          >
          {{ action.icon ? action.icon : '$' + action.name }}
        </v-icon>
      </div>
    </template>

    <template v-slot:no-data>
      <v-btn
        color="primary"
        @click="initialize"
      >
        Reset
      </v-btn>
    </template>

    <!-- #formatterColumns -->
    <template 
      v-for="(col, i) in formatterColumns" 
      v-slot:[`item.${col.value}`]="{ header, value }"
      >
        {{ handleFunctionCall(header.formatter, value ) }}
        <!-- {{ [header.formatter](value) }} -->

    </template>

    <!-- #edit-dialog for editableColumns-->
    <template 
      v-for="(header, k) in editableColumns" 
      v-slot:[`item.${header.value}`]="props"
      >
        <v-edit-dialog
          :key="k"
          :return-value.sync="props.item[header.value]"
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
    </template>

    <template slot="headers" slot-scope="props">
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
    </template>


  </v-data-table>
</template>

<script>
  import { mapState } from 'vuex'
  import { DATATABLE, FORM, ALERT } from '@/store/mutations'
  import ACTIONS from '@/store/actions'

  import { DatatableMixin } from '@/mixins'

  export default {
    mixins: [DatatableMixin],
    data: function() {
      return {
        formModalActive: false,
        dialogActive: false,

        langs: ['tr', 'en'],

        cellInput: '',

      }
    },
    computed: {
      dialogDescription(){
        return this.$t('confirm-deletion', {
          route: this.transName.toLowerCase(), 
          name: this.editedItem[this.titleKey]
        })
      },
      transName(){
        return this.$t('modules.'+this.name.toLowerCase())
      }
    },
    created(){
      // __log(this.createOnModal, this.editOnModal)
      // __log( 
      //   window[process.env.JS_APP_NAME].ENDPOINTS.create
        
      // )
    },  

    watch: {
      formModalActive (val) {
        val || this.resetEditedItem()
      },
      dialogActive (val) {
        val || this.resetEditedItem()
      },
    },

    methods: {      
      deleteRow: function () {
        let self = this;

        this.$store.dispatch(ACTIONS.DELETE_ITEM, { id: this.editedItem.id, callback: () => {
            this.$refs.dialog.closeModal();
          }, errorCallback: () => {
            
          }
        })
      },
      columnChanged(value) {
        this.cellInput = value
      },
      /**
       * @param {string} key - related key of object
       */
      updateCell ( key) {
        this.$store.commit(ALERT.CLEAR_ALERT)

        if( this.editedItem[key] !== this.cellInput ){
          let data = {
            id: this.editedItem.id,
            [key]: this.cellInput,
            // reload: false
          };

          this.$store.dispatch(ACTIONS.SAVE_FORM, { item: data })
        }
      },

      changeSort (column) {
        if (this.pagination.sortBy === column) {
          this.pagination.descending = !this.pagination.descending
        } else {
          this.pagination.sortBy = column
          this.pagination.descending = false
        }
      },
      columnValueList(val) {
        return this.elements.map(d => d[val])
      }

    },
  }
</script>