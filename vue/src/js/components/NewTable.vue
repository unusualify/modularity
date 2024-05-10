<template>
  <v-card color="#F8F8FF" :elevation=10 :class="['px-5 py-5 custom-table-card', {'data-table-dashboard' : isDashboard}, {'data-table-full-height' : fillHeight} ]">
    <v-card-title style="font-weight: bolder" class="d-flex align-center my-0 py-0 ga-2">
      {{ $t(tableTitle) }}
      <v-spacer></v-spacer>
      <v-text-field
      v-if="!hideSearchField"
      class="me-auto"
      variant ="outlined"
      append-inner-icon="mdi-magnify"
      :placeholder="searchText"
      hide-details
      density="compact"
      single-line
      style="max-width: 30%;"
      v-model="search"
      >

      </v-text-field>
      <slot name="headerBtn">
        <div v-if="embeddedForm && !noForm" class=""
            >
            <v-btn  rounded="lg"  elevation="0" v-if="can('create')" @click="createForm" class="mb-1" :ripple="true">
              <p class="font-weight-bold">{{ $t('ADD NEW')}}</p>
            </v-btn>
          </div>
          <slot v-else-if="(createOnModal || editOnModal) && !noForm" name="formDialog" >
            <ue-modal
              ref="formModal"
              v-model="formActive"
              scrollable
              transition="dialog-bottom-transition"
              width-type="lg"
              >
              <template v-slot:activator="{props}">
                <v-btn-success :ripple="true" v-if="createOnModal" v-bind="props" dark class="mb-1">
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
                        {{ $t('save') }}
                      </v-btn>
                  </v-card-actions>
                </v-card>
              </template>
            </ue-modal>
          </slot>
        <v-btn
        v-if="false"
      variant="outlined"
      rounded="lg"
      size="small"
      ripple="true"
      icon="mdi-filter-outline"
      width = "6%"
    ></v-btn>
      </slot>
    </v-card-title>

    <v-card-subtitle v-if=!!tableSubtitle >{{$t(tableSubtitle)}}</v-card-subtitle>
    <v-card-item>
          <!-- MARK - EMBEDDED CU FORM -->
      <v-expand-transition>
              <v-card class="mb-theme" elevation="4" v-if="formActive && embeddedForm">
                <ue-form has-submit button-text="save" :title="formTitle" ref="form">
                  <template v-slot:headerRight>
                    <v-btn class="" variant="text" icon="$close" density="compact"
                      @click="closeForm()"
                    ></v-btn>
                  </template>
                </ue-form>
              </v-card>
        </v-expand-transition>

          <!-- MARK - DELETE MODAL -->
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
          <!-- MARK - CUSTOM MODAL -->
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
                    <!-- <v-col
                      v-for="n in 3"
                      :key="n"
                      cols="12"
                      md="6"
                      >
                        <v-item v-slot="{ isSelected, selectedClass, toggle }">
                          <v-card
                            :class="['d-flex align-center', selectedClass]"
                            dark
                            height="200"
                            @click="toggle"
                          >
                            <div
                              class="text-h3 flex-grow-1 text-center"
                            >
                              {{ isSelected ? 'Selected' : 'Click Me!' }}
                            </div>
                        </v-card>
                      </v-item>
                    </v-col> -->
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

      <v-divider v-if="!!tableSubtitle"></v-divider>
        <v-data-table-server
        v-if="listDataTable || isDashboard"
        :items="elements"
        :headers="headers"
        :items-length="elements.length"
        :item-title="titleKey"
        density="comfortable"
        :search="search"
        :page="options.page"
        :items-per-page="options.itemsPerPage"
        :item-value="name"
        :loading="loading"
        >


        <template v-slot:headers v-if="hideHeaders">

        </template>

        <template v-slot:bottom>

        </template>

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
          <!-- <template v-slot:activator="{ props }">
            <v-icon
              size="small"
              icon="mdi-cog-outline"
              v-bind="props"
              >
            </v-icon>
          </template> -->
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
              size="small"
              color="primary"
              icon="mdi-cog-outline"
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
                  {{ $t( action.label ??action.name ) }}
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



        </v-data-table-server>

        <!-- MARK - DATA ITERATOR -->
        <v-data-iterator
        v-else-if="listDataIterators"
        :items="elements"
        :page="options.page"
        :search="options.search"
        :items-per-page="options.itemsPerPage"
        :item-value="name"

        >

          <template  v-slot:default="{items}">

            <v-row>
              <v-col
              v-for="(element, i) in items"
              :key="element.raw.id"
              v-bind="iteratorOptions.col"

              >
              <!-- // TODO - check if its empty -->
              <component
                :is="`ue-${iteratorType ?? ''}`"
                :key="element.raw.id"
                :item="element.raw"
                :headers="headers"
                :iteratorOptions="iteratorOptions"
                :rowActions = "rowActions"
                @click-action="itemAction"
              >
              </component>
              </v-col>
            </v-row>

          </template>

        </v-data-iterator>

    </v-card-item>
  </v-card>
</template>

<script>

// IMPORTS
import { makeFormatterProps } from '@/hooks/useFormatter'
import useTable, { makeTableProps } from '@/hooks/useTable'

import ActiveTableItem from '__components/labs/ActiveTableItem.vue'
const { ignoreFormatters } = makeFormatterProps()

export default{
  props:{
    ...makeTableProps(),
    ignoreFormatters
  },
  setup(props, context){
    return {
      ...useTable(props, context)
    }
  }

}

</script>


