<template>
  <v-layout fluid v-resize="onResize"
    :class="[
      noFullScreen ? 'd-flex flex-column flex-grow-1 min-height-0' : '',
      rounded ? $lodash.isBoolean(rounded) ? 'rounded' : `rounded-${rounded}` : '',
      elevation ? `elevation-${elevation}` : '',
    ]"
    :style="noFullScreen
      ? 'min-height: 0; max-height: 100%;'
      : ($vuetify.display.lgAndUp ? 'max-height: calc(100vh - 24px)' : 'max-height: calc(100vh - 24px - 64px)')"
    >
    <div :class="['ue-datatable__container', noFullScreen ? 'd-flex flex-column flex-grow-1 min-height-0' : 'fill-heigh ue-datatable--full-screen' ]">
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
        :class="[
          noFullScreen ? 'px-4 d-flex flex-column flex-grow-1 min-height-0' : 'px-4 h-100',
          options.groupBy?.length ? 'ue-table--has-group-by' : '',
          $store.getters.isSuperAdmin && showSelect ? 'ue-table--has-row-select' : '',
          tableClasses,
          rounded ? $lodash.isBoolean(rounded) ? 'rounded' : `rounded-${rounded}` : '',
          fullWidthWrapper ? '' : 'ue-table--narrow-wrapper',
          tableElevation ? `elevation-${tableElevation}` : '',
          striped ? 'ue-datatable--striped' : '',
          roundedRows ? 'ue-datatable--rounded-row' : '',
          hideBorderRow ? 'ue-datatable--no-border-row' : '',
          controlsPosition === 'bottom' || $vuetify.display.smAndDown ? 'ue-datatable--bottom-controls' : '',
          fixedLastColumn ? 'ue-datatable--fixed-last-column' : '',
          isDraggableActive ? 'ue-table--draggable' : '',
        ]"
        id="ue-table"

        :headers="headersForDataTable"
        :fixed-header="fixedHeader"

        :sticky="sticky"
        :items="elements"
        :hover="true"

        :items-per-page-options="itemsPerPageOptions"
        :items-per-page="options.itemsPerPage"
        :search="options.search"
        :page="options.page"

        :items-length="totalNumberOfElements"
        :item-title="titleKey"
        ref="datatable"

        :height="noFullScreen
          ? '100%'
          : (windowSize.y - 64 - 24 - 59 - (hideTableFooter ? 0 : 76) - ($vuetify.display.mdAndDown ? 80 : 0))"

        :hide-default-header="hideHeaders || ($vuetify.display.smAndDown && !showMobileHeaders)"
        :hide-default-body="isDraggableActive"
        :hide-default-footer="hideTableFooter"
        :multi-sort="multiSort"
        :must-sort="mustSort"
        :group-by="options.groupBy"
        :density="tableDensity ?? 'comfortable'"
        :disable-sort="disableSort"
        :loading="isTableBusy"
        :loading-text="$t('Loading... Please wait')"

        :Xmobile="$vuetify.display.smAndDown"
        :mobile-breakpoint="mobileBreakpoint"

        :show-select="$store.getters.isSuperAdmin && showSelect"
        :item-selectable="() => !isTableBusy"
        item-value="id"
        v-model="selectedItems"
        :row-props="dataTableRowProps"

        @update:options="changeOptions($event)"
      >
      <!-- v-model:options="options" -->
        <template v-slot:top="{ someSelected }">
          <v-toolbar
            v-bind="toolbarOptions"
            :class="[
              'pt-3',
              $vuetify.display.smAndUp ? 'd-flex' : '',
            ]"
          >
            <!-- table title -->
            <div
              :class="[
                controlsPosition === 'bottom' || $vuetify.display.smAndDown ? '' : 'flex-lg-1-1-100 h-100 d-flex flex-column',
              ]"
              style="min-width: 33%;"
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

            <v-divider v-if="controlsPosition === 'bottom' || $vuetify.display.smAndDown" class="my-2"></v-divider>

            <!-- table controls -->
            <v-slide-x-transition :group="true">
              <div
                key='table-controls'
                :class="[
                  'd-flex ga-2 align-md-center',
                  controlsPosition === 'bottom' || $vuetify.display.smAndDown ? 'mb-2' : 'flex-1-1-100 justify-end',
                  $vuetify.display.smAndDown ? 'flex-column' : '',
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
                      :disabled="isTableBusy"
                      @click="itemAction(action, action.name)"
                      v-tooltip="$lodash.startCase(action.name)"
                    />
                  </template>
                </template>
                <template v-else>
                  <!-- search field -->
                  <v-text-field
                    v-if="!hideSearchField && hasSearchableHeader"
                    id="search-field"
                    ref="searchField"
                    v-model="searchModel"
                    variant="outlined"
                    :append-inner-iconx="searchModel !== search ? 'mdi-magnify' : null"
                    hide-details
                    density="compact"
                    single-line
                    :placeholder="searchPlaceholder"
                    :class="[
                      controlsPosition === 'bottom' || !$vuetify.display.xs ? 'flex-sm-grow-1' : '',
                    ]"
                    :style="[
                      'display: inline',
                      // controlsPosition === 'top' || $vuetify.display.smAndDown ? 'max-width: 300px' : '',
                      'min-width: 200px',
                      !(controlsPosition === 'bottom' || $vuetify.display.smAndDown) ? 'max-width: 250px' : '',
                    ]"
                    @click:append-inner="searchItems"
                    :disabled="isTableBusy"
                    @keydown.enter="searchItems"

                  >
                    <template #append-inner>
                      <v-btn :disabled="searchModel === search" icon="mdi-magnify" variant="plain" size="compact" color="grey-darken-5" rounded @click="searchItems()" />
                    </template>
                  </v-text-field>

                  <!-- <v-spacer v-else-if="hideSearchField"></v-spacer> -->
                  <!-- <v-spacer v-if="$vuetify.display.mdAndUp && !(!hideSearchField && hasSearchableHeader)"></v-spacer> -->

                  <TableActions
                    :class="$vuetify.display.mdAndUp ? 'flex-grow-0 flex-shrink-0' : ''"
                    :actions="actions"
                    @action-complete="handleFormActionComplete"
                    @update:loading="setActionsLoading"
                  >
                    <template #prepend>
                      <!-- filter menu -->
                      <v-menu>
                        <template v-slot:activator="{ props }">
                          <!-- filter button -->
                          <v-btn v-if="mainFilters.length > 0 && !hideFilters"
                            id="filter-btn-activator"
                            v-bind="{...filterBtnOptions, ...filterBtnTitle, ...props}"
                            :disabled="isTableBusy"
                            :icon="$vuetify.display.smAndDown ? filterBtnOptions['prepend-icon'] : null"
                            :Xtext="$vuetify.display.smAndDown ? null : filterBtnTitle['text']"
                            :text="filterBtnTitle['text']"
                            :prepend-icon="$vuetify.display.smAndDown ? null : filterBtnOptions['prepend-icon']"
                            :block="$vuetify.display.smAndUp ? false : (filterBtnOptions['block'] ?? false)"
                            :density="$vuetify.display.smAndDown ? 'compact' : (filterBtnOptions['density'] ?? 'comfortable')"

                          />
                        </template>
                        <v-list>
                          <v-list-item
                            v-for="(filter, index) in mainFilters"
                            :key="index"
                            v-on:click.prevent="changeFilter(filter.slug)"
                            :class="[
                              filter.slug === activeFilterSlug ? 'bg-primary' : '',
                              filter.class ?? ''
                            ]"
                          >
                            <v-list-item-title>{{ filter.name + '(' + filter.number+ ')' }} </v-list-item-title>
                          </v-list-item>
                        </v-list>
                      </v-menu>

                    </template>
                    <template #append>
                      <!-- create button -->
                      <v-btn v-if="$can('create', permissionName) && !noForm && !someSelected && createOnModal"
                        v-bind="addBtnOptions"
                        :disabled="isTableBusy"
                        @click="createForm"
                        :icon="$vuetify.display.smAndDown ? addBtnOptions['prepend-icon'] : null"
                        :text="$vuetify.display.smAndDown ? null : addBtnTitle"
                        :prepend-icon="$vuetify.display.smAndDown ? null : addBtnOptions['prepend-icon']"
                        :density="$vuetify.display.smAndDown ? 'compact' : (addBtnOptions['density'] ?? 'comfortable')"
                      />
                    </template>
                  </TableActions>
                </template>
              </div>
            </v-slide-x-transition>
          </v-toolbar>

          <!-- Loading Progress Bar and Divider -->
          <v-progress-linear
            v-if="hideHeaders && isTableBusy"
            class="w-100 mb-4 mt-2"
            color="success"
            indeterminate
            reverse
          ></v-progress-linear>

          <v-divider v-else-if="controlsPosition === 'top' && $vuetify.display.mdAndUp" class="mb-2 mt-2"></v-divider>

          <div class="d-flex mb-2">
            <!-- advanced filter menu -->
            <!-- Active filters chips (place this before or after the filter button) -->
            <v-menu
              v-model="advancedFilterMenuOpen"
              :close-on-content-click="false"
              location="end"
            >
              <template v-slot:activator="{ props }">
                <!-- advanced filter button -->
                <v-btn
                  v-if="Object.keys(advancedFilters).length > 0 && !hideAdvancedFilters"
                  id="advanced-filter-btn"
                  v-bind="{...filterBtnOptions, ...filterBtnTitle, ...props}"
                  :disabled="isTableBusy"
                  :icon="$vuetify.display.smAndDown ? 'mdi-filter-variant' : null"
                  :text="$vuetify.display.smAndDown ? null : $t('Filters')"
                  :prepend-icon="$vuetify.display.smAndDown ? null : 'mdi-filter-variant'"
                  :block="$vuetify.display.mdAndUp ? false : (filterBtnOptions['block'] ?? false)"
                  :density="$vuetify.display.smAndDown ? 'compact' : (filterBtnOptions['density'] ?? 'comfortable')"
                >
                  <!-- Active filter count badge -->
                  <template v-if="activeFilterCount > 0" v-slot:append>
                    <v-badge
                      :content="activeFilterCount"
                      color="error"
                      inline
                    />
                  </template>
                </v-btn>
              </template>

              <v-card
                min-width="40vw"
                max-width="50vw"
                :min-height="$vuetify.display.smAndDown ? '60vh' : undefined"
              >
                <!-- Header with close button -->
                <v-card-title class="d-flex align-center justify-space-between">
                  <div class="d-flex flex-column">
                    <span>{{ $t('Filters') }}</span>
                    <!-- <div v-if="activeFilterCount > 0" class="text-body-small text-medium-emphasis flex-grow-1 flex-shrink-0">
                      {{ $t('{count} active filter(s)', { count: activeFilterCount }) }}
                    </div> -->
                  </div>
                  <v-btn
                    icon="mdi-close"
                    variant="text"
                    size="small"
                    @click="advancedFilterMenuOpen = false"
                  />
                </v-card-title>

                <v-divider />

                <!-- Filter content with categorized sections -->
                <v-card-text class="pa-0">
                  <v-expansion-panels
                    v-model="expandedPanels"
                    multiple
                    variant="accordion"
                  >
                    <v-expansion-panel
                      v-for="(filters, category) in advancedFilters"
                      :key="category"
                      :value="category"
                    >
                      <!-- Category header -->
                      <v-expansion-panel-title>
                        <div class="d-flex align-center justify-space-between w-100 pr-4">
                          <span class="text-body-large font-weight-medium">
                            {{ getCategoryLabel(category) }}
                          </span>
                          <v-chip
                            v-if="getActiveCategoryFilterCount(category) > 0"
                            size="small"
                            color="primary"
                            variant="flat"
                          >
                            {{ getActiveCategoryFilterCount(category) }}
                          </v-chip>
                        </div>
                      </v-expansion-panel-title>

                      <!-- Category filters -->
                      <v-expansion-panel-text>
                        <v-row density="compact">
                          <v-col
                            v-for="(filter, index) in filters"
                            :key="`${category}-${index}`"
                            cols="12"
                            :md="filter.fullWidth ? 12 : 6"
                          >
                            <component
                              :is="`v-${filter.type}`"
                              v-bind="filter.componentOptions"
                              v-model="filter.selecteds"
                              :density="filter.componentOptions?.density ?? 'comfortable'"
                              hide-details="auto"
                            />
                          </v-col>
                        </v-row>
                      </v-expansion-panel-text>
                    </v-expansion-panel>
                  </v-expansion-panels>

                  <!-- Empty state -->
                  <div
                    v-if="Object.keys(advancedFilters).length === 0"
                    class="text-center pa-8 text-medium-emphasis"
                  >
                    <v-icon size="64" class="mb-4">mdi-filter-off</v-icon>
                    <p>{{ $t('No filters available') }}</p>
                  </div>
                </v-card-text>

                <v-divider />

                <!-- Footer actions -->
                <!-- flexcolumn on mobile -->
                <v-card-actions class="px-4 py-3 d-flex justify-end flex-column flex-sm-row">
                  <!-- Active filters summary -->
                  <v-btn
                    :text="$t('Clear All')"
                    variant="text"
                    :disabled="activeFilterCount === 0"
                    @click="resetAdvancedFilter"
                  />

                  <v-btn
                    color="primary"
                    :text="$t('Apply Filters')"
                    variant="elevated"
                    @click="changeAdvancedFilter"
                  />
                  <div class="d-flex ga-2 flex-grow-0 flex-shrink-1">
                  </div>
                </v-card-actions>
              </v-card>
            </v-menu>
            <div v-if="activeFilterCount > 0" class="d-flex flex-wrap ga-2 mb-2 ml-2">
              <template v-for="(categoryFilters, category) in activeAdvancedFilters" :key="category">
                <v-chip
                  v-for="(value, slug) in categoryFilters"
                  :key="`${category}-${slug}`"
                  x-closable
                  size="small"
                  color="primary"
                  variant="tonal"
                  >
                  <!-- @click:close="removeFilter(category, slug)" -->
                  <span class="text-body-small">
                    {{ getFilterLabel(category, slug) }}:
                    <strong>{{ formatFilterValue(category, slug) }}</strong>
                  </span>
                </v-chip>
              </template>

              <v-btn
                v-if="activeFilterCount > 1"
                size="small"
                variant="text"
                color="error"
                @click="resetAdvancedFilter"
              >
                {{ $t('Clear All') }}
              </v-btn>
            </div>
          </div>

          <!-- form modal -->
          <ue-modal v-if="!embeddedForm"
            ref="formModal"
            v-model="formActive"

            transition="dialog-bottom-transition"
            :fullscreen="false"
            width-type="lg"
            v-bind="formModalAttributes"
          >
            <template v-slot:body="formModalBodyScope">
              <v-card class="fill-height d-flex flex-column py-4">
                <ue-form
                  ref="UeForm"
                  form-class="px-4"
                  fill-height
                  scrollable
                  has-divider
                  no-default-form-padding

                  :modelValue="editedItem"
                  v-bind="formAttributes"
                  :title="{
                    ...formAttributes.title ?? {},
                    text: formTitle,
                  }"
                  :schema="formSchema"
                  :subtitle="formSubtitle"
                  :isEditing="editedIndex > -1"
                  :languages="languages"
                  :style="formModalBodyScope.isFullActive ? 'height: 95vh !important;' : 'height: 70vh !important;'"
                  :actions="formActions"
                  :actionUrl="editedIndex > -1 ? endpoints.update.replace(':id', editedItem.id) : endpoints.store"
                  has-submit
                  :button-text="editedIndex > -1 ? $t('fields.update') : $t('fields.create')"
                  @action-complete="handleFormActionComplete"
                  @submitted="handleFormSubmission"
                >

                  <template v-slot:header.left="headerLeftScope" v-if="$slots['form.header.left']">
                    <slot name="form.header.left" v-bind="headerLeftScope">
                      {{ headerLeftScope.title }}
                    </slot>
                  </template>

                  <template v-slot:header.right>
                    <slot name="form.header.right">
                      <div class="d-flex align-start">
                        <v-btn :icon="formModalBodyScope.isFullActive ? 'mdi-fullscreen-exit' : 'mdi-fullscreen'" variant="plain" color="grey-darken-5" size="compact" @click="formModalBodyScope.toggleFullscreen()"/>
                        <v-btn icon="$close" variant="plain" size="compact" color="grey-darken-5" rounded @click="closeForm()" />
                      </div>
                    </slot>
                  </template>

                  <template v-if="$slots['form.top']" v-slot:top="topScope">
                    <slot name="form.top" v-bind="topScope">

                    </slot>
                  </template>

                  <template v-if="$slots['form.bottom']" v-slot:bottom="bottomScope">
                    <slot name="form.bottom" v-bind="bottomScope">

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

                  <template v-if="$slots['form.actions.prepend']" v-slot:actions.prepend="actionsScope">
                    <slot name="form.actions.prepend" v-bind="actionsScope">

                    </slot>
                  </template>

                  <template v-if="$slots['form.actions.append']" v-slot:actions.append="actionsScope">
                    <slot name="form.actions.append" v-bind="actionsScope">

                    </slot>
                  </template>

                </ue-form>
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

            <!-- dialog modal -->
            <ue-modal v-model="modals['dialog'].active"
              :ref="modals['dialog'].ref"
              :transition="'dialog-bottom-transition'"
              :width-type="'sm'"

              v-bind="modals['dialog'].modalAttributes ?? {}"
            >
            </ue-modal>

            <!-- show modal -->
            <ue-modal v-if="modals['show'].active"
              :ref="modals['show'].ref"
              v-model="modals['show'].active"
              :transition="modals['show'].transition || 'dialog-bottom-transition'"
              :width-type="modals['show'].widthType || 'lg'"
              :persistent="modals['show'].persistent"
              :description="modals['show'].description"
              :title="modals['show'].title"
              has-fullscreen-button
              has-close-button
              no-confirm-button
              has-title-divider
              cancel-text="Close"
              :reject-button-attributes="{
                variant: 'elevated',
                color: 'primary',
              }"
              scrollable
            >
              <template v-slot:body.description>
                <div>
                  <ue-recursive-data-viewer
                    :data="modals['show'].data"
                    :all-array-items-open="false"
                    :all-array-items-closed="false"
                  />
                </div>
              </template>
            </ue-modal>

            <!-- custom form modal -->
            <ue-modal v-model="customFormModalActive"
              ref="customFormModal"
              width-type="lg"
              persistent
              description-body-class="d-flex flex-column fill-height w-100 pa-4"
              no-default-body-padding
              no-actions
              has-close-button
              scrollable
              v-bind="customFormModalAttributes"
            >
              <!-- <slot name="systembar">
                test
              </slot> -->
              <template v-slot:body.description>
                <ue-form
                  ref="customForm"
                  v-model="customFormModel"
                  :title="null"
                  fill-height
                  scrollable
                  no-default-form-padding
                  style="height: 80vh !important;"
                  v-bind="customFormAttributes"
                  :languages="languages"
                >
                  <!-- <template v-slot:header.right>
                    <v-btn class="ml-auto" variant="text" icon="$close" density="compact" color="deafult"
                      @click="customFormModalActive = false"
                    ></v-btn>
                  </template> -->
                </ue-form>
              </template>
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
                :name="name"
                :titlePrefix="titlePrefix"
                :titleKey="titleKey"

                :item="element"
                :headers="headers"
                :rowActions="rowActions"
                @click-action="itemAction"
              >

                <template v-slot:actions>
                  <div>
                    <div class="d-flex flex-wrap ga-2 justify-sm-end ml-n2 ml-md-0">
                      <template v-for="(action, k) in rowActions" :key="k">
                        <v-tooltip
                          v-if="itemHasAction(element, action)"
                          :text="$t( action.label ?? $headline(action.name) )"
                          location="top"
                          >
                          <template v-slot:activator="{ props }">
                            <v-btn
                              v-bind="props"
                              :disabled="isTableBusy"
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
        <template v-if="hideTableFooter" v-slot:bottom="{page, pageCount}">
        </template>
        <template v-else-if="enableCustomFooter || $vuetify.display.smAndDown" v-slot:bottom="{page, pageCount}">
          <div class="d-flex justify-end py-4">
            <v-container class="max-width text-center">
              <v-pagination v-if="!isTableBusy"
                v-model="options.page"
                :length="totalNumberOfPages"

                density="compact"
                size="small"
                :total-visible="$vuetify.display.smAndDown ? 1 : 3"
                show-first-last-page
                v-bind="footerProps"
              >
                <template #first="{ onClick, disabled, icon }">
                  <v-btn
                    v-bind="defaultPaginationButtonProps"
                    icon="mdi-chevron-double-left"
                    @click="onClick"
                    :disabled="disabled"
                  />
                </template>
                <template #prev="{ onClick, disabled, icon }">
                  <v-btn
                    v-bind="defaultPaginationButtonProps"
                    :icon="icon"
                    @click="onClick"
                    :disabled="disabled"
                  />
                </template>
                <!-- <template #item>
                  <v-menu>
                    <template #activator="{ props }">
                      <v-btn
                        v-bind="{ ...props, ...defaultPaginationButtonProps }"
                        :icon="options.page"
                        @click="onClick"
                        :disabled="disabled"
                      >
                        {{ options.page }}
                      </v-btn>
                    </template>
                    <v-list
                      class="overflow-y-auto"
                      max-height="200"
                      >
                      <v-list-item v-for="page in availablePages" :key="page" @click="options.page = page">
                        {{ page }}
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </template> -->
                <template #next="{ onClick, disabled, icon }">
                  <v-btn
                    v-bind="defaultPaginationButtonProps"
                    :icon="icon"
                    @click="onClick"
                    :disabled="disabled"
                  />
                </template>
                <template #last="{ onClick, disabled, icon }">
                  <v-btn
                    v-bind="defaultPaginationButtonProps"
                    icon="mdi-chevron-double-right"
                    @click="onClick"
                    :disabled="disabled"
                  />
                </template>
              </v-pagination>
              <v-progress-circular v-else
                width="3"
                size="small"
                indeterminate
              ></v-progress-circular>
            </v-container>
          </div>
        </template>

        <!-- Custom Slots -->
        <template v-for="(context, slotName) in slots" v-slot:[slotName]
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

        <!-- #header actions slot: column visibility (cog) -->
        <template v-slot:header.actions="_obj">
          <v-menu v-if="!(hideHeaders || (hideMobileActions && $vuetify.display.xs))"
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
            </template>
            <v-card>
              <v-card-title>
                <v-list class="">
                  <template v-for="(item, index) in headersModel" :key="index">
                    <v-checkbox v-if="item.key !== 'actions' && !($vuetify.display.xs && item.noMobile === true)"
                      v-model="headersModel[index].visible"
                      color="primary"
                      class="ml-n2"
                      :disabled="headersModel.filter(h => h.key !== 'actions' && h.visible === true).length < 2 && headersModel[index].visible === true"
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
                  :text="$t('Save')"
                  variant="tonal"
                  @click="applyHeaders"
                  block
                ></v-btn>
              </v-card-actions>
            </v-card>
          </v-menu>
        </template>

        <!-- #formattable headers -->
        <template v-for="(header, i) in formattableHeaders"
          :key="`formattable-header-${i}`"
          v-slot:[`header.${header.key}`]="headerScope"
        >
          {{ headerScope.column.title }}
          <v-tooltip v-if="header.searchable && !hideSearchField" :text="$t('Search')">
            <template v-slot:activator="{ props }">
              <v-icon
                v-bind="props"
                color="medium-emphasis"
                size="small"
                icon="mdi-table-search"

                @click="$refs.searchField.focus()"
              ></v-icon>
            </template>
          </v-tooltip>
          <v-tooltip v-if="header.removable" :text="$t('Remove Column')">
            <template v-slot:activator="{ props }">
              <v-icon
                v-if="header.removable"
                color="medium-emphasis"
                size="small"
                icon="$close"
                @click="removeHeader(header.key)"
                v-bind="props"
              ></v-icon>
            </template>
          </v-tooltip>
          <v-tooltip
            v-if="header.groupable === true"
            :text="isGroupActiveForKey(header.key) ? $t('Clear grouping') : $t('Group by this column')"
          >
            <template v-slot:activator="{ props: groupToggleProps }">
              <v-icon
                v-bind="groupToggleProps"
                size="small"
                icon="mdi-format-list-group"
                :color="isGroupActiveForKey(header.key) ? 'success' : 'medium-emphasis'"
                @click.stop="toggleGroupByColumn(header.key)"
              />
            </template>
          </v-tooltip>

        </template>

        <template v-if="isDraggableActive" v-slot:header.data-table-drag-handle>
          <v-tooltip :text="$t('fields.medias.reorder', 'Reorder')" location="top">
            <template v-slot:activator="{ props: dragHeaderProps }">
              <v-icon
                v-bind="dragHeaderProps"
                size="small"
                color="medium-emphasis"
                icon="mdi-drag-vertical"
              />
            </template>
          </v-tooltip>
        </template>

        <template v-if="isDraggableActive" v-slot:item.data-table-drag-handle>
          <v-tooltip :text="$t('fields.medias.reorder', 'Reorder')" location="top">
            <template v-slot:activator="{ props: dragHandleProps }">
              <v-icon
                v-bind="dragHandleProps"
                class="drag__handle ue-table__drag-handle"
                size="small"
                color="medium-emphasis"
                icon="mdi-drag-vertical"
              />
            </template>
          </v-tooltip>
        </template>

        <template v-slot:header.data-table-group>
          <div class="d-inline-flex align-center ga-1 flex-nowrap">
            <span>{{ $t('Group') }}</span>
            <v-tooltip
              v-if="isGroupingActive"
              :text="$t('Clear grouping')"
            >
              <template v-slot:activator="{ props: clearGroupProps }">
                <v-icon
                  v-bind="clearGroupProps"
                  size="small"
                  color="medium-emphasis"
                  icon="mdi-ungroup"
                  @click.stop="clearGroupBy"
                />
              </template>
            </v-tooltip>
          </div>
        </template>

        <!-- Full-width group bar: one colspan cell so flex uses horizontal space (replaces default multi-td row) -->
        <template v-slot:group-header="slotProps">
          <TableGroupHeaderRow
            :key="`group-header_${slotProps.item.id}`"
            :group="slotProps.item"
            :columns="slotProps.columns"
            :show-select="$store.getters.isSuperAdmin && showSelect"
            :formatter-column="formatterColumnForGroupKey(slotProps.item.key)"
            :synthetic-item="syntheticItemForGroup(slotProps.item)"
            :handle-formatter="handleFormatter"
            :item-action="itemAction"
            :cell-options="cellOptions"
            :disable-formatter-tooltip="isDataTableMobile"
          />
        </template>

        <!-- Formatter columns: item.<key> on v-data-table-server so VDataTableRows forwards slots to VDataTableRow (custom v-slot:item breaks item.actions). -->
        <template
          v-for="(col, i) in formatterColumns"
          :key="`formatter-${i}`"
          v-slot:[`item.${col.key}`]="slotProps"
        >
          <TableFormatterCell
            :col="col"
            :item="slotProps.item"
            :handle-formatter="handleFormatter"
            :item-action="itemAction"
            :cell-options="cellOptions"
            :clickable-row="isClickableRows"
            :disable-tooltip="isDataTableMobile"
          />
        </template>

        <template v-slot:item.actions="{ item }">
          <template v-if="!( (hideMobileActions && $vuetify.display.xs) || (visibleRowActions.length === 0) )">
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
                <template v-for="(action, k) in visibleRowActions" :key="k">
                  <v-list-item v-if="itemHasAction(item, action)"
                    :class="action.class ?? ''"
                    :disabled="isTableBusy"
                    @click="itemAction(item, action)"
                    >
                      <v-icon small :color="action.iconColor" left>
                        {{ action.icon }}
                      </v-icon>
                      {{ $t( action.label ) }}
                  </v-list-item>
                </template>
              </v-list>
            </v-menu>

            <div v-else>
              <template v-for="(action, k) in visibleRowActions" :key="k">
                <v-tooltip v-if="itemHasAction(item, action)"
                  :text="$t( action.label )"
                  location="top"
                  :disabled="action.is !== 'v-icon'"
                  :class="action.class ?? ''"
                  >
                  <template v-slot:activator="{ props }">
                    <component :is="action.is"
                      @click="itemAction(item, action)"
                      v-bind="{
                        ...(action.hasTooltip ? props : {}),
                        ...(action.componentProps ?? {}),
                        disabled: isTableBusy || (action.componentProps?.disabled ?? false),
                      }"
                    >
                      <template #prepend>
                        <v-icon small :color="action.iconColor" left :icon="action.icon" />
                      </template>
                      <template v-if="action.is !== 'v-icon'">
                        {{ $t( action.label ) }}
                      </template>
                      <template v-else>
                        {{ action.icon }}
                      </template>
                    </component>
                  </template>
                </v-tooltip>
              </template>
            </div>
          </template>
        </template>

        <!-- MARK: Infinite Scroll Triggering Component -->
        <template v-slot:body.append>
            <v-card v-intersect="onIntersect" v-if="enableInfiniteScroll"/>
            <v-progress-circular :indeterminate="isTableBusy" v-if="enableInfiniteScroll && isTableBusy"></v-progress-circular>
        </template>

        <template v-if="isDraggableActive" v-slot:tbody>
          <Draggable
            v-model="elements"
            item-key="id"
            v-bind="dragOptions"
            tag="tbody"
            class="v-data-table__tbody"
            role="rowgroup"
            @update:modelValue="sortElements"
          >
            <template #item="itemSlot">
              <VDataTableRow
                :index="itemSlot.index"
                :item="draggableItems[itemSlot.index]"
                :mobile="datatable?.mobile ?? isDataTableMobile"
              >
                <template
                  v-for="(_, name) in datatable?.$slots ?? {}"
                  :key="name"
                  v-slot:[name]="slotData"
                >
                  <component
                    :is="datatable.$slots[name]"
                    v-bind="{
                      ...slotData,
                      item: elements[itemSlot.index],
                    }"
                  />
                </template>
              </VDataTableRow>
            </template>
          </Draggable>
        </template>

      </v-data-table-server>
    </div>
  </v-layout>
</template>

<script>
import Draggable from 'vuedraggable'
import { VDataTableRows } from 'vuetify/lib/components/VDataTable/index.mjs'
import { VDataTableRow } from 'vuetify/lib/components/VDataTable/index.mjs'
import TableActions from '__components/table/TableActions.vue'

import {
  makeTableProps,
  makeDraggableProps,
  makeFormatterProps,
  useTable,
  useDraggable,
} from '@/hooks'

import {
  makeTableNamesProps,
  makeTableFiltersProps,
  makeTableHeadersProps,
  makeTableFormsProps,
  makeTableItemActionsProps,
  makeTableActionsProps,
  makeTableModalsProps,
} from '@/hooks/table'

import ActiveTableItem from '__components/labs/ActiveTableItem.vue'
import TableFormatterCell from '__components/table/TableFormatterCell.vue'
import TableGroupHeaderRow from '__components/table/TableGroupHeaderRow.vue'

const { ignoreFormatters } = makeFormatterProps()

export default {
  components: {
    ActiveTableItem,
    Draggable,
    VDataTableRow,
    TableActions,
    TableFormatterCell,
    TableGroupHeaderRow
  },
  props: {
    ...makeTableNamesProps(),
    ...makeTableFiltersProps(),
    ...makeTableHeadersProps(),
    ...makeTableFormsProps(),
    ...makeTableItemActionsProps(),
    ...makeTableActionsProps(),
    ...makeTableModalsProps(),
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
    formatterColumnForGroupKey (key) {
      return this.formatterColumns.find((c) => c.key === key) ?? null
    },
    syntheticItemForGroup (group) {
      const k = group?.key
      const v = group?.value
      if (!k) {
        return {}
      }
      return { [k]: v, id: group.id ?? `group-${k}` }
    }
  },

}
</script>

<style lang="sass">
  // Fixed width for Vuetify `data-table-group` column (chevron / indent); stable across expand/collapse clicks
  #ue-table.ue-table--has-group-by
    &:not(.ue-table--has-row-select)
      .v-data-table__th:first-child,
      .v-data-table__td:first-child
        width: 100px !important
        min-width: 100px !important
        max-width: 100px !important
    &.ue-table--has-row-select
      .v-data-table__th:nth-child(2),
      .v-data-table__td:nth-child(2)
        width: 100px !important
        min-width: 100px !important
        max-width: 100px !important

  #ue-table.ue-table--draggable
    .ue-table__drag-handle
      cursor: grab
      opacity: 0.45
      transition: opacity 0.15s ease, color 0.15s ease

    tr:hover .ue-table__drag-handle
      opacity: 1
      color: rgb(var(--v-theme-primary)) !important

    .sortable-chosen .ue-table__drag-handle,
    .sortable-drag .ue-table__drag-handle
      cursor: grabbing
      opacity: 1

  #ue-table
    .v-data-table-group-header-row
      > td
        vertical-align: middle
        text-align: start !important

    .ue-table-group-header-td
      padding: 8px 16px

    .ue-table-group-header
      min-height: 40px
      justify-content: flex-start !important

  // Striped tables: do not apply alternating grey to full-width group header row
  #ue-table.ue-datatable--striped
    .v-data-table-group-header-row > td
      background: rgb(var(--v-theme-surface)) !important

  .ue-datatable__container
    width: 100%

    &.d-flex
      flex: 1 1 auto
      min-height: 0

    // &.ue-datatable--full-screen
    //   height: calc(100vh - (2*8 * $spacer))

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
          background: rgb(var(--v-theme-grey-lighten-5)) !important //TODO: table action border must be variable
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
            td
              background-color: rgb(var(--v-theme-grey-lighten-6)) !important //TODO: table action border must be variable


    .action-dropdown
      .v-overlay__content
        border: 1px solid #49454F !important //TODO: table action border must be variable

    &.ue-datatable--fixed-last-column
      .v-table__wrapper
        table
          th, td
            &:last-child
              position: sticky !important
              right: 0
              background-color: rgb(var(--v-theme-surface))
              box-shadow: -1px 0 4px -2px rgba(0, 0, 0, 0.15)
</style>
