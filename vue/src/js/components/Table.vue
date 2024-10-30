<template>
  <!-- <v-layout fluid v-resize="onResize"> -->
    <div :class="['ue-datatable__container', noFullScreen ? '' : 'fill-height ue-datatable--full-screen_' ]">

      <ActiveTableItem
        class=""
        v-model="activeTableItem"
        v-bind="$lodash.pick(this.$props ?? {}, ['name', 'fullWidthWrapper'])"
        :table-headers="Object.values($lodash.omitBy(this.headers, 'actions'))"
        :item-data="activeItemConfiguration"
        @toggle="hideTable= $event"
      >
      </ActiveTableItem>

      <v-data-table-server
        v-if="!hideTable"
        v-bind="{...$bindAttributes(), ...footerProps}"
        :class="[noFullScreen ? '' : 'h-100', tableClasses, fullWidthWrapper ? '' : 'ue-table--narrow-wrapper']"
        id="ue-table"

        :headers="headers"
        :sticky="sticky"
        :items="elements"
        :hover="true"

        :items-per-page="options.itemsPerPage"
        :search="options.search"
        :page="options.page"

        :items-length="totalElements"
        :item-title="titleKey"
        ref="datatable"

        :height="windowSize.y - 64 - 24 - 59 - 36"

        :hide-default-header="hideHeaders"
        :multi-sort="multiSort"
        :must-sort="mustSort"
        :density="tableDensity ?? 'comfortable'"
        :disable-sort="disableSort"
        :loading="loading"
        :loading-text="$t('Loading... Please wait')"
        :mobile="isSmAndDown"

        :show-select="showSelect"
        item-value="id"
        v-model="selectedItems"


        @update:options="changeOptions($event)"
      >
      <!-- v-model:options="options" -->
        <template v-slot:top="{ someSelected }">
          <v-toolbar
            v-bind="toolbarOptions"
          >
            <ue-title
              :text="tableTitle"
              :subTitle="tableSubtitle"
              :class="[someSelected ? 'w-33 h-100' : 'w-33 h-100']"
            />
            <v-slide-x-transition :group="true">
                <template v-for="(action, k) in bulkActions" :key="k">
                  <v-btn
                    v-if="someSelected && canBulkAction(action)"
                    :icon="(action.icon ? action.icon : `$${action.name}`)"
                    :color="action.color ?? 'primary'"
                    @click="itemAction(action, action.name)"
                    v-tooltip="$lodash.startCase(action.name)"
                    small
                    left
                  />
                </template>
            </v-slide-x-transition>

            <v-text-field
              v-if="!hideSearchField"
              class="px-3"
              variant="outlined"
              append-inner-icon="mdi-magnify"
              :placeholder="searchText"
              hide-details
              density="compact"

              style="max-width: 30%; display: inline;"
              single-line
              v-model="search"
            />
            <v-spacer v-else-if="hideSearchField"></v-spacer>

            <v-btn
              v-if="mainFilters.length > 0"
              id="filter-btn-activator"
              v-bind="{...filterBtnOptions, ...filterBtnTitle}"
              />
            <v-btn
              v-if="Object.keys(advancedFilters).length > 0"
              id="advanced-filter-btn"
              v-bind="{...filterBtnOptions, ...filterBtnTitle}"
              text="Advanced Filter"
            />

            <v-btn v-if="can('create') && !noForm && !someSelected" v-bind="addBtnOptions" @click="createForm" :text="addBtnTitle"/>

          </v-toolbar>

          <v-menu
            activator="#filter-btn-activator"
            >
              <v-list>
                <v-list-item
                  v-for="(filter, index) in mainFilters"
                  :key="index"
                  v-on:click.prevent="filterStatus(filter.slug)"
                >
                  <v-list-item-title>{{ filter.name + '(' + filter.number+ ')' }} </v-list-item-title>
                </v-list-item>
              </v-list>
          </v-menu>

          <v-menu
            activator="#advanced-filter-btn"
            :close-on-content-click="false"
            :location="end"

          >
            <v-card
              title="Advanced Filter"
              min-width="40vw"
              max-width="50vw"
            >
              <v-row  class="justify-center" no-gutters>
                <v-col
                  cols="11"
                  v-for="(filters, index) in advancedFilters"
                  :key="index"
                >
                  <component v-for="(filter, ind) in filters"
                    :is="`v-${filter.type}`"
                    v-bind="filter.componentOptions"
                    v-model="filter['selecteds']"
                  />
                </v-col>
              </v-row>
              <v-card-actions>
                <v-spacer></v-spacer>

                <v-btn
                  text="Clear"
                  variant="plain"
                  @click="clearAdvancedFilter"
                ></v-btn>

                <v-btn
                  color="primary"
                  text="Save"
                  variant="tonal"
                  @click="submitAdvancedFilter"
                ></v-btn>
              </v-card-actions>
            </v-card>
          </v-menu>

          <ue-modal
            ref="formModal"
            v-model="formActive"
            scrollable
            transition="dialog-bottom-transition"
            width-type="lg"
            v-if="!embeddedForm"
          >
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

          <div class="ue-table-top__wrapper">
            <div v-if="embeddedForm && !noForm" class=""
              :style="formStyles">
              <v-expand-transition>
                <v-card class="mb-12" elevation="4" v-if="formActive">
                  <ue-form has-submit button-text="save" :title="formTitle" ref="form">
                    <template v-slot:headerRight>
                      <v-btn class="" variant="text" icon="$close" density="compact"
                        @click="closeForm()"
                      ></v-btn>
                    </template>
                  </ue-form>
                </v-card>
              </v-expand-transition>
            </div>

            <!-- <ue-modal
              v-model="actionModalActive"
              transition="dialog-bottom-transition"
              width-type="sm"
              persistant
            >
            <template v-slot:body="props" >
                <v-card >
                  <v-card-title class="text-h5 text-center" style="word-break: break-word;">
                    {{ textDescription }}
                  </v-card-title>
                  <v-card-text class="text-center" style="word-break: break-word;" >
                    {{ actionDialogQuestion }}
                  </v-card-text>
                  <v-divider/>
                  <v-card-actions>
                    <v-spacer/>
                    <v-btn color="blue" text @click="closeActionModal()"> {{ props.textCancel }}</v-btn>
                    <v-btn color="blue" text @click="confirmAction()"> {{ props.textConfirm }}</v-btn>
                    <v-spacer></v-spacer>
                  </v-card-actions>
                </v-card>
              </template>

            </ue-modal> -->

            <!-- #deletemodal-->
            <!-- <ue-modal
              ref="deleteModal"
              v-model="deleteModalActive"
              transition="dialog-bottom-transition"
              width-type="sm"
              >
              <template v-slot:body="props" >
                <v-card >
                  <v-card-title class="text-h5 text-center" style="word-break: break-word;">
                    {{ textDescription }}
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
            </ue-modal> -->
            <!-- custom modal -->
            <ue-modal
              ref="customModal"
              v-model="customModalActive"
              :transition="modals[activeModal] || 'dialog-bottom-transition'"
              :width-type="modals[activeModal].widthType || 'sm'"
              :persistent="modals[activeModal].persistent"
              :description-text="modals[activeModal].content"
            >
            <template #body="props">
              <v-card>
                <v-card-title class="text-h5 text-center" style="word-break: break-word;"
                  v-if="modals[activeModal].title">
                  <!-- {{ modal.title }} -->
                </v-card-title>
                <v-icon
                  v-if="modals[activeModal].img"
                  :icon="modals[activeModal].icon"
                  style="margin:auto; border:4px solid;border-radius:50%;padding:32px;"
                  size="32"
                  :color="modals[activeModal].color"/>

                <v-card-text class="text-center" style="word-break: break-word;">
                  {{ modals[activeModal].content }}
                </v-card-text>
                <v-divider />
                <v-card-actions>
                  <v-spacer />
                  <v-btn :color="modals[activeModal].color ? modals[activeModal].color : 'blue'" text @click="modals[activeModal].closeAction()">
                    {{ modals[activeModal].cancelText || props.textCancel }}
                  </v-btn>
                  <!-- <v-btn color="blue" text @click="handleModal('confirm', modal.ref, props.onConfirm)"></v-btn> -->
                  <v-btn :color="modals[activeModal].color ? modals[activeModal].color : 'blue'" text @click="modals[activeModal].confirmAction()">
                    {{ modals[activeModal].confirmText || props.textConfirm }}
                  </v-btn>
                  <v-spacer />
                </v-card-actions>
              </v-card>
            </template>
          </ue-modal>
          <ue-modal
            ref="customFormModal"
            v-model="customFormModalActive"
            :width-type="'lg'"
          >
            <ue-form
              ref="customForm"
              v-model="customFormModel"
              v-bind="customFormAttributes"
            >
            </ue-form>
          </ue-modal>

          </div>

        </template>

        <!-- MARK: DATA-ITERATOR BODY -->
        <template v-slot:body="{ items }" v-if="enableIterators" class="ue-datatable__container">
            <v-row>
              <v-col
              v-for="(element, i) in items"
              :key="element.id"
              v-bind="customRowComponent.col"
              >
              <!-- // TODO - check if its empty -->
                <component
                  :is="`ue-${customRowComponent.iteratorComponent}`"
                  :key="element.id"
                  :item="element"
                  :headers="headers"
                  :rowActions = "rowActions"
                  @click-action="itemAction"
                  @edit-item = "editItem"
                >
                </component>
              </v-col>
            </v-row>
        </template>

        <!-- MARK PAGINATION BUTTONS -->

        <template v-if="enableCustomFooter" v-slot:bottom="{page, pageCount}">
          <div class="d-flex justify-end">
            <v-pagination
            v-model="options.page"
            :length="pageCount"
            v-bind="footerProps"
          />
          </div>
        </template>


        <!-- Custom Slots -->
        <template
          v-for="(context, slotName) in slots" v-slot:[slotName]
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
            <template v-if="col.formatter == 'edit' || col.formatter == 'activate'">
              <v-btn
                :key="i"
                class="pa-0 justify-start"
                variant="plain"
                :color="`primary darken-1`"
                @click="itemAction(item, ...col.formatter)"
                >
                <!-- {{ item[col.key].length > 40 ? item[col.key].substring(0,40) + '...' : item[col.key] }} -->
                {{ window.__shorten(item[col.key]) }}
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
                  @update:modelValue="itemAction(item, 'switch', $event, col.key )"
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
          </v-menu>
        </template>
        <template v-slot:item.actions="{ item }">
          <!-- @click's editItem|deleteItem -->
          <!-- #actions -->

          <v-menu v-if="rowActionsType === 'dropdown' || isSmAndDown"
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
                  @click="itemAction(item, action)"
                  >
                    <v-icon small :color="action.color" left>
                      {{ action.icon ? action.icon : '$' + action.name }}
                    </v-icon>
                    {{ $t( action.label ?? action.name ) }}
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

        <!-- MARK: Infinite Scroll Triggering Component -->
        <template v-slot:body.append>
            <v-card v-intersect="onIntersect" v-if="enableInfiniteScroll"/>
            <v-progress-circular :indeterminate="loading" v-if="enableInfiniteScroll && loading"></v-progress-circular>
        </template>


        <template v-slot:default v-if="draggable">
          <thead>
            <slot :name="headers">
              <VDataTableHeaders :mobile="this.datatable.mobile">
                <template v-for="(_, name) in this.datatable.$slots" v-slot:[name]="slotData">
                  <slot :name="name" v-bind="slotData">
                    <component
                      :is="this.datatable.$slots[name]"
                      v-bind="slotData"
                    />
                  </slot>

                </template>
              </VDataTableHeaders>
            </slot>
          </thead>

          <Draggable
            :model-value="elements"
            item-key="position"
            v-bind="dragOptions"
            tag="tbody"
            class="v-data-table__tbody"
            @update:modelValue="sortElements"
          >
            <template #item="itemSlot">
              <VDataTableRow :item="draggableItems[itemSlot.index]" :mobile="this.datatable.mobile">
                <template v-for="(_, name) in this.datatable.$slots" v-slot:[name]="slotData">
                  <slot :name="name" v-bind="slotData">
                    <component
                      :is="this.datatable.$slots[name]",
                      v-bind="{
                        ...slotData,
                        ...{
                          item: elements[itemSlot.index]
                        }
                      }"
                    />
                  </slot>
                </template>
              </VDataTableRow>
            </template>
          </Draggable>
        </template>


      </v-data-table-server>

    </div>
  <!-- </v-layout> -->
</template>

<script>
import Draggable from 'vuedraggable'
import { VDataTableRows } from 'vuetify/lib/components/VDataTable/index.mjs'
import { VDataTableRow } from 'vuetify/lib/components/VDataTable/index.mjs'

import {
  useTable,
  makeTableProps,
  useDraggable,
  makeDraggableProps,
  makeFormatterProps,
} from '@/hooks'

import ActiveTableItem from '__components/labs/ActiveTableItem.vue'
import PaymentService from './inputs/PaymentService.vue'
import { useStore } from 'vuex'

const { ignoreFormatters } = makeFormatterProps()

export default {
  // mixins: [TableMixin],
  components: {
    ActiveTableItem,
    Draggable,
    VDataTableRow,
    PaymentService

  },
  props: {
    ...makeDraggableProps(),
    ...makeTableProps(),
    ...ignoreFormatters
  },
  setup (props, context) {
    return {
      ...useDraggable(props, context),
      ...useTable(props, context),
    }
  },
  data () {
    return {
      datatable: {},
    }
  },
  mounted () {

    this.$nextTick(() => {
      if (this.$refs.datatable) {
        this.datatable = this.$refs.datatable;
      }
    });
    // __log(
    //   // this.$props,
    //   // _.omit(this.$props ?? {}, ['columns']),
    //   // this.$lodash.pick(this.$props ?? {}, ['name', 'fullWidthWrapper']),
    //   Object.values(this.$lodash.omitBy(this.headers, 'actions'))
    // )
  },
  created () {
    // this.$can(this.rowActions[0].can ?? '')

    // const store = useStore();
    // if(store._state.data.datatable.customModal){
    //   __removeQueryParams(['customModal[description]', 'customModal[color]']);
    // }
  },
  methods: {
  },

}
</script>

<style lang="sass">
.ue-datatable__container
  width: 100%
  &.ue-datatable--full-screen
    min-height: calc(100vh - (2*12 * $spacer))
</style>
