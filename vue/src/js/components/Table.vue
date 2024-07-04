<template>
  <v-layout fluid v-resize="onResize">
  <div :class="['ue-datatable__container', noFullScreen ? '' : 'ue-datatable--full-screen' ]">

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
      :class="[tableClasses, fullWidthWrapper ? '' : 'ue-table--narrow-wrapper']"
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
      :loading-text="$t('loading-text')"
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
            :classes="[]"
            padding-reset
            :class="[someSelected ? 'w-50 h-100' : 'w-50 h-100']"
          />
          <v-slide-x-transition :group="true">
              <template v-for="(action, k) in bulkActions" :key="k">
                <v-btn
                  v-if="someSelected && canBulkAction(action)"
                  :icon="(action.icon ? action.icon : `$${action.name}`)"
                  :color="action.color ?? 'primary'"
                  @click="openActionModal(action)"
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
          id="filter-btn-activator"
          v-if="mainFilters.length > 0"
          v-bind="{...filterBtnOptions, ...filterBtnTitle}"
          />

          <v-btn
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
                v-for="(filter, index) in advancedFilters"
                :key="index"
              >
                <component
                  :is="`v-${filter.type}`"
                  multiple
                  :items="filter.items"
                  v-model="advancedFilters[index]['selecteds']"
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
                  {{ $t('save') }}
                </v-btn>
              </v-card-actions>
            </v-card>
          </template>
        </ue-modal>

        <div class="ue-table-top__wrapper">
          <div v-if="embeddedForm && !noForm" class=""
            :style="formStyles">
            <v-expand-transition>
              <v-card class="mb-theme" elevation="4" v-if="formActive">
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

          <ue-modal
            v-model="actionModalActive"
            transition="dialog-bottom-transition"
            width-type="sm"
            persistant
          >
          <template v-slot:body="props" >
              <v-card >
                <v-card-title class="text-h5 text-center" style="word-break: break-word;">
                  <!-- {{ textDescription }} -->
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

          </ue-modal>

          <!-- #deletemodal-->
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
          <slot name="customModal">
            <ue-modal
              ref="customModal"
              v-model="customModalActive"
              scrollable
              transition="dialog-bottom-transition"
              width-type="md"
              >
              <template v-slot:activator="{props}">
                <!-- <v-btn-cta v-bind="props" dark class="mb-theme">
                    CUSTOM MODAL
                </v-btn-cta> -->
              </template>
              <template v-slot:body="props">
                <v-card class="pa-theme">
                  <v-item-group selected-class="bg-primary">
                    <v-container>
                      <v-row>

                        <v-col cols="12" md="6" class="pa-4">
                          <v-item v-slot="{ isSelected, selectedClass, toggle }">
                            <v-card :class="['d-flex align-center bg-primary ue-card-button px-4', selectedClass]" dark height="200" @click="toggle" >
                              <div class="text-h6 font-weight-bold flex-grow-1 text-center" > COMPANY INFORMATION</div>
                            </v-card>
                          </v-item>
                        </v-col>
                        <v-col cols="12" md="6" class="pa-4">
                          <v-item v-slot="{ isSelected, selectedClass, toggle }">
                            <v-card :class="['d-flex align-center bg-cta ue-card-button px-4', selectedClass]" dark height="200" @click="toggle" >
                              <div class="text-h6 font-weight-bold flex-grow-1 text-center" > PRESS RELEASES </div>
                            </v-card>
                          </v-item>
                        </v-col>
                        <v-col cols="12" md="12" class="pa-4">
                          <v-item v-slot="{ isSelected, selectedClass, toggle }">
                            <v-card :class="['d-flex align-center bg-success ue-card-button px-4', selectedClass]" dark height="80" @click="toggle" >
                              <div class="text-h6 font-weight-bold flex-grow-1 text-center"> CREDITS & INVOICES </div>
                            </v-card>
                          </v-item>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-item-group>
                </v-card>
              </template>
            </ue-modal>
          </slot>


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



    </v-data-table-server>


  </div>
  </v-layout>
</template>

<script>
import { makeFormatterProps } from '@/hooks/useFormatter'
import useTable, { makeTableProps } from '@/hooks/useTable'

import ActiveTableItem from '__components/labs/ActiveTableItem.vue'
const { ignoreFormatters } = makeFormatterProps()

export default {
  // mixins: [TableMixin],
  components: {
    ActiveTableItem
  },
  props: {
    ...makeTableProps(),
    ignoreFormatters
  },
  setup (props, context) {

    return {
      ...useTable(props, context)
    }
  },
  data () {
    return {

    }
  },
  mounted () {

    // __log(
    //   // this.$props,
    //   // _.omit(this.$props ?? {}, ['columns']),
    //   // this.$lodash.pick(this.$props ?? {}, ['name', 'fullWidthWrapper']),
    //   Object.values(this.$lodash.omitBy(this.headers, 'actions'))
    // )
  },
  created () {
    // this.$can(this.rowActions[0].can ?? '')
  },
  methods: {

  }
}
</script>

<style lang="sass">
.ue-datatable__container
  width: 100%
  &.ue-datatable--full-screen
    min-height: calc(100vh - (2*$theme-space))
</style>
