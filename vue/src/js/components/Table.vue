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
        :class="[
          'px-4',
          noFullScreen ? '' : 'h-100',
          tableClasses,
          fullWidthWrapper ? '' : 'ue-table--narrow-wrapper',
          striped ? 'ue-datatable--striped' : '',
          roundedRows ? 'ue-datatable--rounded-row' : '',
          hideBorderRow ? 'ue-datatable--no-border-row' : '',
          controlsPosition === 'bottom' || $vuetify.display.smAndDown ? 'ue-datatable--bottom-controls' : ''
        ]"
        id="ue-table"

        :headers="selectedHeaders"

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
        :mobile="$vuetify.display.smAndDown"

        :show-select="$store.getters.isSuperAdmin && showSelect"
        item-value="id"
        v-model="selectedItems"

        @update:options="changeOptions($event)"
      >
      <!-- v-model:options="options" -->
        <template v-slot:top="{ someSelected }">
          <v-toolbar
            v-bind="toolbarOptions"
            :class="[
              'pt-1',
            ]"
          >
            <!-- table title -->
            <div
              :class="[
                controlsPosition === 'bottom' || $vuetify.display.smAndDown ? 'pt-2' : 'flex-grow-1 h-100 d-flex flex-column justify-center',
              ]"
              style="min-width: 20%;"
            >
              <!-- title -->
              <ue-title
                type="subtitle-1"
                color="black"
                :text="tableTitle"
                padding="a-0"
              />
              <!-- subtitle -->
              <ue-title
                v-if="tableSubtitle"
                type="caption"
                weight="medium"
                color="grey-darken-1"
                transform="none"
                padding="a-0"
                :text="tableSubtitle"
              />
            </div>

            <v-divider v-if="controlsPosition === 'bottom' || $vuetify.display.smAndDown" class="my-3"></v-divider>

            <!-- table controls -->
            <v-slide-x-transition :group="true">
              <div
                :class="[
                  'd-flex',
                  controlsPosition === 'bottom' || $vuetify.display.smAndDown ? 'mb-2' : 'justify-end flex-grow-1',

                ]"
              >
                <template v-if="someSelected">
                  <!-- bulk actions -->
                  <template v-for="(action, k) in bulkActions" :key="k">
                    <v-btn
                      v-if="$can(action.name, permissionName)"
                      v-bind="filterBtnOptions"
                      :append-icon="false"
                      :prepend-icon="(action.icon ? action.icon : `$${action.name}`)"
                      :text="window.__headline(action.name)"
                      :color="action.color ?? 'primary'"
                      @click="itemAction(action, action.name)"
                      v-tooltip="$lodash.startCase(action.name)"
                    />
                  </template>
                </template>
                <template v-else>
                  <!-- search field -->
                  <v-text-field
                    v-if="!hideSearchField && isStoreTable && hasSearchableHeader"
                    v-model="searchModel"
                    variant="outlined"
                    :append-inner-iconx="searchModel !== search ? 'mdi-magnify' : null"
                    hide-details
                    density="compact"
                    single-line
                    :placeholder="searchPlaceholder"
                    :class="[
                      'mr-2',
                      controlsPosition === 'bottom' || $vuetify.display.smAndDown ? 'flex-grow-1' : ''
                    ]"
                    :style="[
                      'display: inline',
                      // controlsPosition === 'top' || $vuetify.display.smAndDown ? 'max-width: 300px' : '',
                      'min-width: 100px'
                    ]"
                    @click:append-inner="searchItems()"
                    :disabled="loading"
                    @keydown.enter="searchItems()"
                  >
                    <template #append-inner>
                      <v-btn :disabled="searchModel === search" icon="mdi-magnify" variant="plain" size="compact" color="grey-darken-5" rounded @click="searchItems()" />
                    </template>
                  </v-text-field>

                  <!-- <v-spacer v-else-if="hideSearchField"></v-spacer> -->

                  <!-- filter button -->
                  <v-btn v-if="mainFilters.length > 0"
                    id="filter-btn-activator"
                    v-bind="{...filterBtnOptions, ...filterBtnTitle}"
                    :icon="$vuetify.display.smAndDown ? filterBtnOptions['prepend-icon'] : null"
                    :text="$vuetify.display.smAndDown ? null : filterBtnTitle['text']"
                    :prepend-icon="$vuetify.display.smAndDown ? null : filterBtnOptions['prepend-icon']"
                    :density="$vuetify.display.smAndDown ? 'compact' : (filterBtnOptions['density'] ?? 'comfortable')"
                  />

                  <!-- advanced filter button -->
                  <v-btn v-if="Object.keys(advancedFilters).length > 0"
                    id="advanced-filter-btn"
                    v-bind="{...filterBtnOptions, ...filterBtnTitle}"
                    :icon="$vuetify.display.smAndDown ? filterBtnOptions['prepend-icon'] : null"
                    :text="$vuetify.display.smAndDown ? null : 'Advanced Filter'"
                    :prepend-icon="$vuetify.display.smAndDown ? null : filterBtnOptions['prepend-icon']"
                    :density="$vuetify.display.smAndDown ? 'compact' : (filterBtnOptions['density'] ?? 'comfortable')"
                  />

                  <!-- create button -->
                  <v-btn v-if="$can('create', permissionName) && !noForm && !someSelected && createOnModal"
                    v-bind="addBtnOptions"
                    @click="createForm"
                    :icon="$vuetify.display.smAndDown ? addBtnOptions['prepend-icon'] : null"
                    :text="$vuetify.display.smAndDown ? null : addBtnTitle"
                    :prepend-icon="$vuetify.display.smAndDown ? null : addBtnOptions['prepend-icon']"
                    :density="$vuetify.display.smAndDown ? 'compact' : (addBtnOptions['density'] ?? 'comfortable')"
                  />
                </template>
              </div>
            </v-slide-x-transition>
          </v-toolbar>

          <v-divider v-if="controlsPosition === 'top' && $vuetify.display.mdAndUp" class="mb-4 mt-2"></v-divider>

          <!-- filter menu -->
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

          <!-- advanced filter menu -->
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
              <v-card-text>
                <template v-for="(filters, index) in advancedFilters" :key="index">
                  <component v-for="(filter, ind) in filters"
                    :is="`v-${filter.type}`"
                    v-bind="filter.componentOptions"
                    v-model="filter['selecteds']"
                  />
                </template>
              </v-card-text>
              <!-- <v-row class="justify-center" no-gutters>
                <v-col
                  cols="11"
                  :key="index"
                  v-for="(filters, index) in advancedFilters"
                >
                  <component v-for="(filter, ind) in filters"
                    :is="`v-${filter.type}`"
                    v-bind="filter.componentOptions"
                    v-model="filter['selecteds']"
                  />
                </v-col>
              </v-row> -->
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

          <!-- form modal -->
          <ue-modal
            v-if="!embeddedForm"
            ref="formModal"
            v-model="formActive"

            transition="dialog-bottom-transition"
            :fullscreen="false"
            width-type="lg"
            v-bind="formModalAttributes"
          >
          <template v-slot:body="formModalBodyScope">
            <v-card class="fill-height d-flex flex-column">
              <!-- <v-card-title class="text-h5 grey lighten-2"> </v-card-title> -->
                <ue-form
                  ref="UeForm"

                  form-class="px-6 pt-6 pb-0"
                  fill-height
                  scrollable
                  has-divider
                  no-default-form-padding
                  v-bind="formAttributes"

                  :title="formTitle"
                  :isEditing="editedIndex > -1"
                  :style="formModalBodyScope.isFullActive ? 'height: 90vh !important;' : 'height: 70vh !important;'"
                  :actions="formActions"
                  @action-complete="handleFormActionComplete"
                >

                  <template v-slot:header.left="headerLeftScope">
                    <slot name="form.header.left" v-bind="headerLeftScope">
                      {{ headerLeftScope.title }}
                    </slot>
                  </template>

                  <template v-slot:header.right>
                    <slot name="form.header.right">
                      <v-btn :icon="formModalBodyScope.isFullActive ? 'mdi-fullscreen-exit' : 'mdi-fullscreen'" variant="plain" color="grey-darken-5" size="compact" @click="formModalBodyScope.toggleFullscreen()"/>
                      <v-btn icon="$close" variant="plain" size="compact" color="grey-darken-5" rounded @click="closeForm()" />
                    </slot>
                  </template>

                  <template v-if="$slots['form.right.top']" v-slot:right.top="rightScope">
                    <slot name="form.right.top" v-bind="rightScope">

                    </slot>
                  </template>
                  <template v-if="$slots['form.right.middle']" v-slot:right.middle="rightScope">
                    <slot name="form.right.middle" v-bind="rightScope">

                    </slot>
                  </template>
                  <template v-if="$slots['form.right.bottom']" v-slot:right.bottom="rightScope">
                    <slot name="form.right.bottom" v-bind="rightScope">

                    </slot>
                  </template>

                  <template v-slot:top="formTopScope">
                    <slot name="form.top" v-bind="formTopScope">

                    </slot>
                  </template>
                </ue-form>
                <!-- <v-card-text>
                </v-card-text> -->

                <v-divider class="mx-6 mt-4"/>
                <v-card-actions class="px-6 flex-grow-0">
                  <v-spacer></v-spacer>
                  <v-btn-secondary
                    v-if="$store.getters.isSuperAdmin"
                    :slim="false"
                    variant="outlined"
                    @click="$refs.UeForm.validate()"
                  >
                    {{ $t('Validate') }}
                  </v-btn-secondary>
                  <v-btn-primary
                    :slim="false"
                    variant="elevated"
                    @click="confirmFormModal()"
                    :disabled="!formIsValid"
                    :loading="formLoading"
                  >
                    {{ $t('fields.save') }}
                  </v-btn-primary>
                </v-card-actions>
              </v-card>
            </template>
          </ue-modal>

          <!-- embeddedform modal -->
          <div class="ue-table-top__wrapper">
            <div v-if="embeddedForm && !noForm" class=""
              :style="formStyles">
              <v-expand-transition>
                <v-card class="mb-theme" elevation="4" v-if="formActive">
                  <ue-form
                    has-submit
                    button-text="save"
                    :title="formTitle"
                    ref="form"
                    :isEditing="editedIndex > -1"
                  >
                    <template v-slot:header.left="headerLeftScope">
                      <slot name="form.header.left" v-bind="headerLeftScope">
                        {{ headerLeftScope.title }}
                      </slot>
                    </template>
                    <template v-slot:headerCenter>

                    </template>
                    <template v-slot:header.right>
                      <v-btn class="" variant="text" icon="$close" density="compact"
                        @click="closeForm()"
                      ></v-btn>
                    </template>
                  </ue-form>
                </v-card>
              </v-expand-transition>
            </div>

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
                    <v-btn
                      v-if="modals[activeModal].hideModalCancel"
                      :color="modals[activeModal].color ? modals[activeModal].color : 'blue'"
                      text
                      @click="modals[activeModal].closeAction()"
                    >
                      {{ modals[activeModal].cancelText || props.textCancel }}
                    </v-btn>
                    <!-- <v-btn color="blue" text @click="handleModal('confirm', modal.ref, props.onConfirm)"></v-btn> -->
                    <v-btn
                      :color="modals[activeModal].color ? modals[activeModal].color : 'blue'"
                      text
                      @click="modals[activeModal].confirmAction()"
                    >
                      {{ modals[activeModal].confirmText || props.textConfirm }}
                    </v-btn>
                    <v-spacer />
                  </v-card-actions>
                </v-card>
              </template>
            </ue-modal>

            <!-- show modal -->
            <ue-modal
              v-if="modals['show'].active"
              :ref="modals['show'].ref"
              v-model="modals['show'].active"
              :transition="modals['show'].transition || 'dialog-bottom-transition'"
              :width-type="modals['show'].widthType || 'lg'"
              :persistent="modals['show'].persistent"
              :description-text="modals['show'].description"
            >
              <template v-slot:body="props">
                <v-card class="fill-height d-flex flex-column">
                  <v-card-title>
                    <ue-title
                      padding="a-3"
                      color="grey-darken-5"
                      align="center"
                      justify="space-between"
                    >
                      {{ modals['show'].title }}
                      <template v-slot:right>
                      </template>
                    </ue-title>
                  </v-card-title>

                  <v-divider class="mx-6"/>
                  <v-card-text>
                    <ue-recursive-data-viewer
                      :data="modals['show'].data"
                      :all-array-items-open="false"
                      :all-array-items-closed="false"
                    />
                  </v-card-text>

                  <v-divider class="mx-6 mt-4"/>
                  <v-card-actions class="px-6 flex-grow-0">
                    <v-spacer></v-spacer>
                    <v-btn-primary
                      :slim="false"
                      variant="elevated"
                      @click="modals['show'].cancel()"
                    >
                      {{ $t('fields.close') }}
                    </v-btn-primary>
                  </v-card-actions>
                </v-card>
              </template>
            </ue-modal>

            <!-- custom form modal -->
            <ue-modal
              ref="customFormModal"
              v-model="customFormModalActive"
              :width-type="'lg'"
            >
            <!-- <slot name="systembar">
              test
            </slot> -->
              <ue-form
                ref="customForm"
                v-model="customFormModel"
                v-bind="customFormAttributes"
                fill-height
                scrollable
                has-divider
                no-default-form-padding
                form-class="px-6 pb-0"
                style="height: 90vh !important;"
              >
                <template v-slot:header.right>
                  <v-btn class="ml-auto" variant="text" icon="$close" density="compact" color="deafult"
                    @click="customFormModalActive = false"
                  ></v-btn>
                </template>
              </ue-form>
            </ue-modal>

          </div>

        </template>

        <!-- MARK: DATA-ITERATOR BODY -->
        <template v-slot:body="{ items }" v-if="hasCustomRow" class="ue-datatable__container">
          <v-row no-gutters>
            <v-col
              v-for="(element, i) in items"
              :key="element.id"
              v-bind="customRow.col"
            >
            <!-- // TODO - check if its empty -->
              <component
                :is="`ue-${customRow.name}`"
                :key="element.id"
                :item="element"
                :headers="headers"
                :rowActions = "rowActions"
                @click-action="itemAction"
                @edit-item = "editItem"
              >

                <template v-slot:actions>
                  <div>
                    <div class="d-flex flex-wrap ga-2 justify-sm-end ml-n2 ml-md-0">
                      <template v-for="(action, k) in rowActions" :key="k">
                        <!-- {{ $log(action) }} -->
                        <v-tooltip
                          v-if="itemHasAction(element, action)"
                          :text="$t( action.label ?? $headline(action.name) )"
                          location="top"
                          >
                          <template v-slot:activator="{ props }">
                            <v-btn
                              v-bind="props"
                              :text="action.forceLabel ? $t( action.label ?? $headline(action.name) ) : null"
                              :variant="action.variant ?? 'elevated'"
                              :density="action.density ?? (action.forceLabel ? 'comfortable' : 'compact')"
                              :size="action.size ?? (action.forceLabel ? 'default' : 'default')"
                              :icon="action.forceLabel ? null : (action.icon ? action.icon : '$' + action.name)"
                              :color="action.color ?? 'primary'"
                              :rounded="action.forceLabel ? null : true"
                              @click="itemAction(element, action)"
                              class="text-capitalize"
                            />
                          </template>
                        </v-tooltip>
                      </template>
                    </div>
                  </div>
                </template>
              </component>
              <v-divider v-if="i < items.length - 1" />
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
          :key="`formatter-${i}`"
          v-slot:[`item.${col.key}`]="{ item }"
        >
          <template v-if="col.formatter == 'edit' || col.formatter == 'activate'">
            <v-tooltip :text="item[col.key]" :key="i">
              <template v-slot:activator="{ props }">
                <v-btn
                  :key="i"
                  v-bind="props"
                  class="pa-0 justify-start text-capitalize"
                  variant="plain"
                  :color="`primary darken-1`"
                  @click="itemAction(item, ...col.formatter)"
                >
                  {{ col.key.match(/^id|uuid$/) ? window.__shorten(item[col.key], 8) : item[col.key] }}
                </v-btn>
                <template v-if="col.key.match(/^id|uuid$/)">
                  <ue-copy-text :text="item[col.key]" />
                </template>
              </template>
            </v-tooltip>
          </template>
          <template v-else-if="col.formatter == 'switch'">
            <!-- {{ $log('switch', item, col.key) }} -->
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
          <template v-else-if="col.formatter == 'dynamic'">
            <ue-dynamic-component-renderer
              :subject="item[col.key]"
              :key="item[col.key]"
            >
            </ue-dynamic-component-renderer>
          </template>
          <template v-else>
            <ue-recursive-stuff
              v-bind="handleFormatter(col.formatter, window.__shorten(item[col.key] ?? '', cellOptions.maxChars))"
              :key="item[col.key]"
            />
          </template>
        </template>

        <!-- #header actions slot -->
        <template v-slot:header.actions="_obj">
          <v-menu
            :close-on-content-click="false"
            location="bottom"
            >
            <template v-slot:activator="{ props }">
              <v-btn
                size="large"
                variant="plain"
                color="black"
                icon="mdi-cog-outline"
                v-bind="props"
              />
              <!-- <v-icon
                size="large"
                icon="mdi-cog-outline"
                v-bind="props"
              /> -->
            </template>
            <v-card>
              <v-card-title>
                <v-list class="">
                  <template v-for="(item, index) in headersModel" :key="index">
                    <v-checkbox
                      class="ml-n2"
                      v-if="item.key !== 'actions'"
                      :disabled="headersModel.filter(h => h.key !== 'actions' && h.visible === true).length < 2 && headersModel[index].visible === true"
                      v-model="headersModel[index].visible"
                      :label="item.title"
                      hide-details
                      density="comfortable"
                    />
                  </template>
                </v-list>
              </v-card-title>
              <v-card-actions>
                <v-btn
                  color="primary"
                  text="Save"
                  variant="tonal"
                  @click="applyHeaders"
                  block
                ></v-btn>
              </v-card-actions>
            </v-card>

          </v-menu>
        </template>

        <!-- #item actions slot-->
        <template v-slot:item.actions="{ item }">
          <v-menu v-if="actionShowingType === 'dropdown'"
            :close-on-content-click="false"
            left
            offset-x
            class="action-dropdown"
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
                    {{ $t( action.label ?? $headline(action.name) ) }}
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
              <VDataTableHeaders :mobile="this.datatable.mobile" :color="this.headerOptions.color">
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
  makeTableNamesProps,
  makeTableEndpointsProps,
  makeTableHeadersProps,
  makeTableFormsProps,
  makeTableFiltersProps,
  makeTableItemActionsProps,
  makeTableProps,
  makeDraggableProps,
  makeFormatterProps,
  useTable,
  useDraggable,
} from '@/hooks'

import ActiveTableItem from '__components/labs/ActiveTableItem.vue'
import PaymentService from './inputs/PaymentService.vue'

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
    ...makeTableNamesProps(),
    ...makeTableEndpointsProps(),
    ...makeTableFiltersProps(),
    ...makeTableHeadersProps(),
    ...makeTableFormsProps(),
    ...makeTableItemActionsProps(),
    ...makeTableProps(),
    ...makeDraggableProps(),
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
    document.documentElement.style.setProperty('--table-header-color', this.headerOptions.color);

    this.$nextTick(() => {
      if (this.$refs.datatable) {
        this.datatable = this.$refs.datatable;
      }
    });

    this.initialize()
  },
  created () {

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

.v-table
  &.ue-datatable
    &--bottom-controls
      .v-toolbar__content
        display: block
        height: unset !important

    &--no-border-row
      .v-table__wrapper
        > table
          > tbody
            > tr:not(:last-child)
              > td,
              > th
                border: none!important

    &--rounded-row
      th
        background-color: var(--table-header-color) //TODO: table action border must be variable
      tr
        // &:first-child
        td,th
          &:first-child
            border-bottom-left-radius: 8px
            border-top-left-radius: 8px

          &:last-child
            border-bottom-right-radius: 8px
            border-top-right-radius: 8px

    &--striped
      tr
        &:nth-of-type(2n)
          background-color: rgba(140,160,167, .2) //TODO: table action border must be variable

  .action-dropdown
    .v-overlay__content
      border: 1px solid #49454F !important //TODO: table action border must be variable
</style>
