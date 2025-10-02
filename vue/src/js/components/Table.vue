<template>
  <v-layout fluid v-resize="onResize"
    :class="[
      noFullScreen ? 'h-100' : '',
      rounded ? $lodash.isBoolean(rounded) ? 'rounded' : `rounded-${rounded}` : '',
      elevation ? `elevation-${elevation}` : '',
    ]"
    :style="$vuetify.display.lgAndUp ? 'max-height: calc(100vh - 24px)' : 'max-height: calc(100vh - 24px - 64px)'"
    >
    <div :class="['ue-datatable__container', noFullScreen ? 'fill-height' : 'fill-heigh ue-datatable--full-screen' ]">
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
          'px-4 h-100',
          tableClasses,
          rounded ? $lodash.isBoolean(rounded) ? 'rounded' : `rounded-${rounded}` : '',
          fullWidthWrapper ? '' : 'ue-table--narrow-wrapper',
          tableElevation ? `elevation-${tableElevation}` : '',
          striped ? 'ue-datatable--striped' : '',
          roundedRows ? 'ue-datatable--rounded-row' : '',
          hideBorderRow ? 'ue-datatable--no-border-row' : '',
          controlsPosition === 'bottom' || $vuetify.display.smAndDown ? 'ue-datatable--bottom-controls' : ''
        ]"
        id="ue-table"

        :headers="selectedHeaders"
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

        :height="windowSize.y - 64 - 24 - 59 - (hideFooter ? 0 : 76) - ($vuetify.display.mdAndDown ? 80 : 0)"

        :hide-default-header="hideHeaders || ($vuetify.display.smAndDown && !showMobileHeaders)"
        :multi-sort="multiSort"
        :must-sort="mustSort"
        :density="tableDensity ?? 'comfortable'"
        :disable-sort="disableSort"
        :loading="loading"
        :loading-text="$t('Loading... Please wait')"

        :Xmobile="$vuetify.display.smAndDown"
        :mobile-breakpoint="mobileBreakpoint"

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
                    :disabled="loading"
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
                  >
                    <template #prepend>
                      <!-- filter menu -->
                      <v-menu>
                        <template v-slot:activator="{ props }">
                          <!-- filter button -->
                          <v-btn v-if="mainFilters.length > 0 && !hideFilters"
                            id="filter-btn-activator"
                            v-bind="{...filterBtnOptions, ...filterBtnTitle, ...props}"
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


                      <!-- advanced filter menu -->
                      <v-menu
                        :close-on-content-click="false"
                        location="end"
                      >
                        <template v-slot:activator="{ props }">
                          <!-- advanced filter button -->
                          <v-btn v-if="Object.keys(advancedFilters).length > 0 && !hideAdvancedFilters"
                            id="advanced-filter-btn"
                            v-bind="{...filterBtnOptions, ...filterBtnTitle, ...props}"
                            :icon="$vuetify.display.smAndDown ? filterBtnOptions['prepend-icon'] : null"
                            :text="$vuetify.display.smAndDown ? null : 'Filters'"
                            :prepend-icon="$vuetify.display.smAndDown ? null : filterBtnOptions['prepend-icon']"
                            :block="$vuetify.display.mdAndUp ? false : (filterBtnOptions['block'] ?? false)"
                            :density="$vuetify.display.smAndDown ? 'compact' : (filterBtnOptions['density'] ?? 'comfortable')"
                          />
                        </template>
                        <v-card
                          title="Filters"
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
                          <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                              text="Clear"
                              variant="plain"
                              @click="resetAdvancedFilter"
                            ></v-btn>

                            <v-btn
                              color="primary"
                              text="Apply"
                              variant="tonal"
                              @click="changeAdvancedFilter"
                            ></v-btn>
                          </v-card-actions>
                        </v-card>
                      </v-menu>
                    </template>
                    <template #append>
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
                  </TableActions>
                </template>
              </div>
            </v-slide-x-transition>
          </v-toolbar>

          <!-- Loading Progress Bar and Divider -->
          <v-progress-linear
            v-if="hideHeaders && loading"
            class="w-100 mb-4 mt-2"
            color="success"
            indeterminate
            reverse
          ></v-progress-linear>
          <v-divider v-else-if="controlsPosition === 'top' && $vuetify.display.mdAndUp" class="mb-2 mt-2"></v-divider>


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

                  <template v-if="$store.getters.isSuperAdmin" v-slot:options="optionsScope">
                    <v-btn-secondary
                      v-if="optionsScope.isSubmittable"
                      :slim="false"
                      variant="outlined"
                      @click="$refs.UeForm.validate()"
                    >
                      {{ $t('fields.validate') }}
                    </v-btn-secondary>
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
        <template v-if="hideFooter" v-slot:bottom="{page, pageCount}">
        </template>
        <template v-else-if="enableCustomFooter || $vuetify.display.smAndDown" v-slot:bottom="{page, pageCount}">
          <div class="d-flex justify-end py-4">
            <v-container class="max-width text-center">
              <v-pagination v-if="!loading"
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

        <!-- #header actions slot -->
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
                  text="Save"
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

        </template>

        <!-- #formatterColumns -->
        <template v-for="(col, i) in formatterColumns"
          :key="`formatter-${i}`"
          v-slot:[`item.${col.key}`]="{ item }"
        >
          <template v-if="col.formatterName == 'edit' || col.formatterName == 'activate'">
            <v-tooltip :text="item[col.key]" :key="i" :disabled="col.isFormatting">
              <template v-slot:activator="{ props }">
                <span
                  :key="i"
                  v-bind="props"
                  class="pa-0 justify-start text-none text-wrap text-primary darken-1 cursor-pointer"
                  @click="itemAction(item, col.formatterName)"
                >
                  <template v-if="col.isFormatting">
                    <ue-recursive-stuff
                      v-bind="handleFormatter(col.formatter, window.__shorten(item[col.key] ?? '', cellOptions.maxChars))"
                    />
                  </template>
                  <template v-else>
                    {{ window.__isset(item[col.key]) ? window.__shorten(item[col.key], col?.textLength ?? 8) : '' }}
                  </template>
                </span>

                <template v-if="(col.hasCopy ?? false) || col.key.match(/^id|uuid$/)">
                  <ue-copy-text :text="item[col.key]" />
                </template>
              </template>
            </v-tooltip>
          </template>
          <template v-else-if="col.formatterName == 'switch'">
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
          <template v-else-if="col.formatterName == 'dynamic'">
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

        <template v-if="isClickableRows" v-slot:item="itemScope">
          <!-- use original datatable row with slots but add a onClick event to the row -->
          <VDataTableRow
            v-bind="itemScope.props"
            @click="($isset(itemScope.item[clickableItemAttribute]) || endpoints.show) ? itemAction(itemScope.item, 'link') : null"
            style="cursor: pointer;"
          >
            <!-- #formatterColumns -->
            <template v-for="(col, i) in formatterColumns"
              :key="`formatter-${i}`"
              v-slot:[`item.${col.key}`]="{ item }"
            >
              <template v-if="col.formatter == 'edit' || col.formatter == 'activate'">
                <v-tooltip :text="item[col.key]" :key="i">
                  <template v-slot:activator="{ props }">
                    <span
                      :key="i"
                      v-bind="props"
                      class="pa-0 justify-start text-none text-wrap text-primary darken-1 cursor-pointer"
                      @click="itemAction(item, ...col.formatter)"
                    >
                      {{ window.__isset(item[col.key]) ? window.__shorten(item[col.key], item[col.key]?.textLength ?? 8) : '' }}
                    </span>
                    <template v-if="col.key.match(/^id|uuid$/)">
                      <ue-copy-text :text="item[col.key]" />
                    </template>
                  </template>
                </v-tooltip>
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
          </VDataTableRow>
        </template>

        <!-- #item actions slot-->
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
import PaymentService from './inputs/PaymentService.vue'

const { ignoreFormatters } = makeFormatterProps()

export default {
  // mixins: [TableMixin],
  components: {
    ActiveTableItem,
    Draggable,
    VDataTableRow,
    PaymentService,
    TableActions

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
  },

}
</script>

<style lang="sass">
  .ue-datatable__container
    width: 100%

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
</style>
