<template>
  <v-data-table
    :headers="headers"
    :items="elements"

    class="elevation-1"
    :loading="loading"

    @update:options="options"

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
          {{ $t('list-of-item', [name, $t('modules.' + name.toLowerCase )] ) }}
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
          <!-- {{ $t('list') }}
          {{ $n(100.77, 'currency') }} -->
          {{ $t('language-select') }}
          <select v-model="$i18n.locale">
            <option v-for="(lang, i) in langs" :key="`Lang${i}`" :value="lang">
              {{ lang }}
            </option>
          </select>
        </v-toolbar-title>

        <v-divider class="mx-4" inset vertical></v-divider>

        <v-spacer></v-spacer>

        <!-- #form dialog -->
        <slot
          name="FormDialog"
          >
          <ue-modal-form
              ref="formModal"
              v-model="formModalActive"
              :route-name="name"
              :inputs="inputs"

              :edited-item="editedItem"
              @update-item="emitEditedItem"

              @confirm="confirmForm"
              >
          </ue-modal-form>
        </slot>

        <!-- general #dialog -->
        <ue-modal-dialog
          ref="dialog"
          v-model="dialogActive"
          @cancel="resetEditedItem"
          @confirm="confirmDialog"
          :description="dialogDescription"
        >
        </ue-modal-dialog>

      </v-toolbar>
    </template>

    <template v-slot:item.actions="{ item }">

      <!-- @click's editItem|deleteItem -->
      <!-- #actions -->
      <v-menu v-if="rowActionsType == 'dropdown'"
        :close-on-content-click="false"
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
            @click="handleFunctionCall(action + 'Item', item)"

            >
              <v-icon small> {{ '$' + action }} </v-icon>
              {{ $t(action) }}
          </v-list-item>

        </v-list>
      </v-menu>

      <div v-else="">
        <v-icon
          v-for="(action, k) in rowActions"
          :key="k"
          small
          class="mr-2"
          @click="handleFunctionCall(action + 'Item', item)"
          >
          {{ '$' + action }}
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
      v-slot:[`item.${col.value}`]="{ col, value }"
      >
        <!-- {{ handleFunctionCall(header.formatter, value ) }} -->
        <!-- {{ [header.formatter](value) }} -->
        {{ $d(new Date(value), 'long') }}
    </template>

    <!-- #edit-dialog for columnEditables -->
    <template
      v-for="(e, k) in columnEditables"
      v-slot:[`item.${e.value}`]="props"
      >
        <v-edit-dialog
          :key="k"
          v-model:return-value="props.item[e.value]"
          :save-text="$t('save')"
          @save="updateCell(e.value)"
          @cancel="cancelColumnEdit"
          @open="openColumnEdit(props.item)"
          @close="closeColumnEdit"

          persistent
          large

        >
          {{ props.item[e.value] }}
          <template v-slot:input>
            <v-text-field
              :value="props.item[e.value]"
              @input="columnChanged"
              @keyup.enter="updateCell(e.value)"
              label="Edit"
              single-line
              counter
            >
            </v-text-field>
          </template>
        </v-edit-dialog>
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
      return this.$t('modules.' + this.name.toLowerCase())
    }
  },

  watch: {
    formModalActive (val) {
      val || this.resetEditedItem()
    },
    dialogActive (val) {
      val || this.resetEditedItem()
    },
    editedItem (val) {
      // console.log('editedItem watcher', this.editedItem )
    }
  },

  beforeCreate () {
  },

  created () {

  },

  mounted () {
  },

  methods: {
    editItem (item) {
      this.editedIndex = this.elements.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.formModalActive = true
    },
    emitEditedItem (v) {
      this.editedItem = v
    },
    confirmForm (editedItem) {
      this.$store.commit(FORM.UPDATE_FORM_LOADING, true)

      __log(editedItem)

      this.$store.dispatch(ACTIONS.SAVE_FORM, editedItem).then(() => {
        this.$nextTick(function () {
          // this.$store.dispatch(ACTIONS.GET_DATATABLE)
          this.formModalActive = false
        })
      }, (errorResponse) => {
        // this.$store.commit(NOTIFICATION.SET_NOTIF, {
        //   message: 'Your content can not be edited, please retry',
        //   variant: 'error'
        // })
      })
    },

    deleteItem (item) {
      this.setEditedItem(item)
      this.openDialog()
    },

    openDialog () {
      this.dialogActive = true
    },
    confirmDialog () {
      this.delete(this.editedItem)
    },
    delete: function (item) {
      this.$store.dispatch(ACTIONS.DELETE_ITEM, item)
    },

    columnChanged (value) {
      this.cellInput = value
    },

    openColumnEdit (item) {
      // this.resetEditedItem();
      this.setEditedItem(item)
      // this.$store.commit(ALERT.SET_ALERT, {
      //   variant: 'info',
      //   message: 'Dialog Opened'
      // })
      // this.$root.$refs.alert.info('Dialog Opened');
    },
    cancelColumnEdit () {
      this.resetEditedItem()
      // this.$store.commit(ALERT.SET_ALERT, {
      //   variant: 'warning',
      //   message: 'cancelled'
      // })
      // this.$root.$refs.alert.error('Cancelled!')
    },
    closeColumnEdit () {
      // this.resetEditedItem();
    },
    /**
       * @param {string} key - related key of object
       */
    updateCell (key) {
      this.$store.commit(ALERT.CLEAR_ALERT)

      if (this.editedItem[key] !== this.cellInput) {
        const data = {
          id: this.editedItem.id,
          [key]: this.cellInput
          // reload: false
        }

        this.$store.dispatch(ACTIONS.SAVE_FORM, data)
        //   .then(() => {
        //     this.$nextTick(function () {
        //       // this.$store.commit(ALERT.SET_ALERT, {
        //       //   variant: 'success',
        //       //   message: this.$t('saved', 2, [key])
        //       // })

        //     })
        // }, (errorResponse) => {
        //   this.$store.commit(ALERT.SET_ALERT, {
        //     message: 'Your content can not be edited, please retry',
        //     variant: 'error'
        //   });
        // })
      }
    }

  }
}
</script>
