<template>
  <div :class="['ue-datatable__container', noFullScreen ? '' : 'ue-datatable--full-screen' ]">

    <ue-active-table-item
      class=""
      v-model="activeTableItem"
      v-bind="$lodash.pick(this.$props ?? {}, ['name', 'fullWidthWrapper'])"
      :table-headers="Object.values($lodash.omitBy(this.headers, 'actions'))"
      :item-data="activeItemConfiguration"
      @toggle="hideTable= $event"
    >
    </ue-active-table-item>

    <v-data-table-server
      v-if="!hideTable"
      v-bind="$bindAttributes()"
      :class="[tableClasses, fullWidthWrapper ? '' : 'ue-table--narrow-wrapper']"
      id="ue-table"

      :headers="headers"
      :items="elements"
      :hover="true"

      :items-per-page="options.itemsPerPage"
      :search="options.search"
      :page="options.page"

      :items-length="totalElements"
      :item-title="titleKey"
      ref="datatable"
    >
      <template v-slot:top>
        <slot name="header" v-bind="{tableTitle}">
          <!-- <ue-title
            :text="tableTitle"
            :classes="[]"
            padding-reset
          /> -->
          <ue-title
            :text="tableTitle"
            :classes="[]"
            padding-reset
            >
            <template v-slot:default="{ text }">
              <div class="d-flex">
                <div class="me-auto">
                  {{ text }}
                </div>
                <slot name="headerRight"></slot>
              </div>
            </template>
          </ue-title>
        </slot>
        <div class="ue-table-top__wrapper">

          <div v-if="embeddedForm && !noForm" class="ue-table-form__embedded"
            :style="formStyles">
            <v-btn @click="createForm" class="mb-theme">
              {{ $t('ADD NEW')}}
            </v-btn>
            <v-expand-transition>
              <v-card class="mb-theme" elevation="4" v-show="formActive">
                <ue-form has-submit button-text="save" :title="formTitle">
                  <template v-slot:headerRight>
                    <v-btn class="" variant="text" icon="$close" density="compact"
                      @click="closeForm()"
                    ></v-btn>
                  </template>
                </ue-form>
              </v-card>
            </v-expand-transition>
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
                <v-btn-success v-if="createOnModal" v-bind="props" dark class="mb-theme">
                    {{ $t('add-item', {'item': transNameSingular} ) }}
                </v-btn-success>
              </template>
              <template v-slot:body="props">
                <v-card >
                  <v-card-title class="text-h5 grey lighten-2"> </v-card-title>
                  <v-card-text>
                    <ue-form :title="formTitle" :ref="formRef"/>
                  </v-card-text>
                  <v-divider/>
                  <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="error darken-1" text @click="closeForm()">
                          {{ props.textCancel }}
                      </v-btn>
                      <v-btn color="teal darken-1" text @click="confirmFormModal()">
                          {{ $t('save') }}
                      </v-btn>
                  </v-card-actions>

                </v-card>
              </template>
            </ue-modal>
          </slot>

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
        </div>
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
      <template v-if="!noFooter" v-slot:bottom="{page, pageCount}">
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
      <template v-else-if="hideFooter" v-slot:bottom></template>

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
              @click="$call(col.formatter + 'Item', item)"
              >

              {{ item[col.key].length > 40 ? item[col.key].substring(0,40) + '...' : item[col.key] }}
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
                @update:modelValue="switchItem($event, col.key, item)"
                >
                <template v-slot:label></template>
              </v-switch>
          </template>
          <template v-else>
            {{ handleFormatter(col.formatter, item[col.key] ) }}
          </template>
      </template>

      <template v-slot:column.actions="_obj">
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
        <v-menu v-if="rowActionsType == 'dropdown' || $root.isSmAndDown"
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

            <v-list-item
              v-for="(action, k) in rowActions"
              :key="k"
              @click="$call(action.name + 'Item', item)"
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
            @click="$call(action.name + 'Item', item)"
            :color="action.color"
            >
            {{ action.icon ? action.icon : '$' + action.name }}
          </v-icon>
        </div>
      </template>

    </v-data-table-server>

  </div>
</template>

<script>
// import { ref, onMounted, useSlots } from 'vue'
import {
  VDataTableServer
  // VDataTable,
  // VDataTableFooter
} from 'vuetify/labs/VDataTable'

import { makeTableProps, useTable, makeFormatterProps } from '@/hooks'

const { ignoreFormatters } = makeFormatterProps()

export default {
  // mixins: [TableMixin],
  components: {
    // VDataTable,
    // VDataTableFooter,
    VDataTableServer
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
  }
}
</script>

<style lang="sass">
.ue-datatable__container
  width: 100%
  &.ue-datatable--full-screen
    min-height: calc(100vh - (2*$theme-space))
</style>
