<template>
  <v-input
    v-model="input"
    hideDetails="auto"
    :disabled="disabled"
    class="v-input-checklist"
  >
    <template v-slot:default>
      <!--
        Isolate leaf controls from VForm. Each nested v-checkbox/v-input would
        otherwise register (~items.length fields) and keep form validity at null.
      -->
      <checklist-form-isolation class="w-100 d-flex flex-wrap" style="max-width: 100%;">
        <div
          v-if="label"
          class="d-flex flex-column mb-2 mr-md-3"
          :style="[
            'max-width: 100%;',
            (flexColumn && $vuetify.display.mdAndUp) ? 'flex: 0 1 auto;' : 'flex: 1 0 100%;'
          ]"
        >
          <ue-title v-if="label" padding="" data-test="title" :color="labelColor" transform="none">
            <span v-html="label"></span>
          </ue-title>
          <ue-title
            v-if="subtitle"
            padding=""
            type="caption"
            weight="medium"
            transform="none"
            :color="subtitleColor"
            data-test="title"
          >
            <span v-html="subtitle"></span>
          </ue-title>
        </div>

        <v-divider
          v-if="(flexColumn && $vuetify.display.mdAndUp) && (label || subtitle)"
          vertical
          class="mr-3 border-current"
        />

        <!-- treeview -->
        <v-row
          v-if="isTreeview"
          :style="[
            flexColumn && $vuetify.display.mdAndUp ? 'flex: 1 0 70%;' : 'flex: 1 1;'
          ]"
        >
          <v-col v-bind="{ ...treeviewCols }">
            <v-list
              v-model:opened="openedGroups"
              :class="['d-flex flex-column', `ga-${groupExpandGap}`]"
            >
              <template
                v-for="(group, key) in groupedItems"
                :key="`group-${group.name ?? key}`"
              >
                <!-- group items -->
                <template v-if="$isset(group.items) && group.items.length > 0">
                  <v-list-group
                    class="pl-0"
                    collapse-icon=""
                    expand-icon=""
                    :value="group.name"
                  >
                    <template v-slot:activator="{ props: activatorProps, isOpen }">
                      <div
                        v-if="!noGroupAllSelectable"
                        class="d-flex align-center ue-checklist-checkbox"
                        v-bind="activatorProps"
                      >
                        <v-icon
                          v-if="!chunkField"
                          class="mr-1"
                          :icon="!isOpen ? '$expand' : '$collapse'"
                        />
                        <v-checkbox-btn
                          :label="group[itemTitle]"
                          color="success"
                          density="compact"
                          :indeterminate="isIndeterminateGroup(group)"
                          :model-value="isAllSelected(group)"
                          :readonly="isMandatoryItem(group) || readonly"
                          @update:model-value="updatedParent($event, group)"
                        >
                          <template #label="{ label: groupLabel, props: labelProps }">
                            <label v-bind="labelProps">{{ groupLabel }}</label>
                          </template>
                        </v-checkbox-btn>
                      </div>
                      <ue-title
                        v-else
                        :text="group.title ?? group[itemTitle]"
                        type="body-1"
                        color="grey-darken-5"
                        weight="bold"
                        justify="space-between"
                        v-bind="{ ...groupExpandTitleProps, ...activatorProps }"
                      >
                        <template v-slot:right>
                          <div class="d-flex align-center">
                            <v-icon :icon="!isOpen ? '$expand' : '$collapse'" />
                          </div>
                        </template>
                      </ue-title>
                      <v-divider v-if="hasGroupBottomDivider" class="mt-0" />
                    </template>

                    <!-- Only mount open group children (large permission lists) -->
                    <template v-if="isGroupOpened(group.name)">
                      <v-list-item
                        v-if="chunkField"
                        style="padding-inline-start: 0px !important;"
                      >
                        <v-row
                          no-gutters
                          :style="[!flexColumn ? 'flex: 1 0 60%;' : '']"
                        >
                          <v-col
                            v-for="item in group.items"
                            :key="`checkbox-${item[itemValue]}`"
                            v-bind="checkboxCol"
                            class="pb-0 pr-0"
                          >
                            <div :class="getCheckboxContainerClasses(item)">
                              <v-checkbox-btn
                                data-test="checkbox"
                                :model-value="input"
                                :value="item[itemValue]"
                                :disabled="isItemDisabled(item)"
                                :color="checkboxColor ?? color ?? 'primary'"
                                :label="item[itemTitle]"
                                :class="getCheckboxClasses(item)"
                                :readonly="isMandatoryItem(item) || isProtected(item[itemValue]) || readonly"
                                @update:model-value="onSelectionChange"
                              >
                                <template v-slot:label="{ label: itemLabel, props: labelProps }">
                                  <span :style="{ fontSize: labelFontSize }" v-bind="labelProps">{{ itemLabel }}</span>
                                </template>
                              </v-checkbox-btn>
                            </div>
                          </v-col>
                        </v-row>
                      </v-list-item>

                      <v-list-item
                        v-for="item in group.items"
                        v-else
                        :key="`checkbox-${item.id}`"
                        class="pl-0"
                      >
                        <v-checkbox-btn
                          data-test="checkbox"
                          class="ue-checklist-checkbox"
                          :model-value="input"
                          :label="item.name"
                          :value="item.id"
                          :disabled="isItemDisabled(item)"
                          color="success"
                          density="compact"
                          :readonly="isMandatoryItem(item) || readonly"
                          @update:model-value="onSelectionChange"
                        >
                          <template v-slot:label="{ label: itemLabel, props: labelProps }">
                            <span :style="{ fontSize: labelFontSize }" v-bind="labelProps">{{ itemLabel }}</span>
                          </template>
                        </v-checkbox-btn>
                      </v-list-item>
                    </template>
                  </v-list-group>
                </template>

                <!-- single item -->
                <template v-else>
                  <v-list-item class="pl-0">
                    <v-checkbox-btn
                      :model-value="input"
                      :label="group[itemTitle]"
                      :value="group[itemValue]"
                      :disabled="isItemDisabled(group)"
                      :readonly="isMandatoryItem(group) || isProtected(group[itemValue]) || readonly"
                      color="success"
                      density="compact"
                      @update:model-value="onSelectionChange"
                    >
                      <template v-slot:label="{ label: itemLabel, props: labelProps }">
                        <span :style="{ fontSize: labelFontSize }" v-bind="labelProps">{{ itemLabel }}</span>
                      </template>
                    </v-checkbox-btn>
                  </v-list-item>
                </template>
              </template>
            </v-list>
          </v-col>
        </v-row>

        <!-- standard checkbox list -->
        <v-row
          v-else
          :style="getRowStyles()"
          :no-gutters="$vuetify.display.xs"
          class="align-stretch"
        >
          <v-col
            v-for="item in flattenedItems"
            :key="`checkbox-${item[itemValue]}`"
            cols="12"
            sm="6"
            md="4"
            lg="3"
            v-bind="checkboxCol"
          >
            <v-input-checkbox-card
              v-if="isCard"
              :model-value="input"
              :value="item[itemValue]"
              :class="[getCheckboxClasses(item), 'h-100']"
              :color="checkboxColor ?? color ?? 'primary'"
              :title="item[itemTitle]"
              :description="item.description"
              :disabled="($attrs.disabled ?? false) || isItemDisabled(item)"
              :readonly="isMandatoryItem(item) || isProtected(item[itemValue]) || readonly"
              :checkbox-on-right="checkboxOnRight"
              :stats="getCardStats(item)"
              :active-title-color="activeTextColor"
              @update:model-value="onSelectionChange"
            />
            <div
              v-else
              :class="getCheckboxContainerClasses(item)"
            >
              <v-checkbox-btn
                data-test="checkbox"
                :model-value="input"
                :value="item[itemValue]"
                :disabled="($attrs.disabled ?? false) || isItemDisabled(item)"
                :color="checkboxColor ?? color ?? 'primary'"
                :label="item[itemTitle]"
                :class="getCheckboxClasses(item)"
                :readonly="isMandatoryItem(item) || isProtected(item[itemValue]) || readonly"
                @update:model-value="onSelectionChange"
              >
                <template v-slot:label="{ label: itemLabel, props: labelProps }">
                  <span :style="{ fontSize: labelFontSize }" v-bind="labelProps">{{ itemLabel }}</span>
                </template>
              </v-checkbox-btn>
            </div>
          </v-col>
        </v-row>
      </checklist-form-isolation>
    </template>
  </v-input>
</template>

<script>
  import { computed, defineComponent, h, provide, ref, toRef, watch } from 'vue'
  import { FormKey } from 'vuetify/lib/composables/form.js'
  import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

  /**
   * Prevent nested VInput/VCheckbox from registering with the parent VForm.
   * Outer Checklist v-input remains the sole form participant.
   */
  const ChecklistFormIsolation = defineComponent({
    name: 'ChecklistFormIsolation',
    setup (_, { slots, attrs }) {
      provide(FormKey, null)

      return () => h('div', attrs, slots.default?.())
    }
  })

  export default {
    name: 'v-input-checklist',
    components: {
      ChecklistFormIsolation
    },
    emits: [...makeInputEmits],
    props: {
      ...makeInputProps(),
      color: {
        type: String,
        default: null
      },
      labelColor: {
        type: String,
        default: 'grey-darken-1'
      },
      labelFontSize: {
        type: String,
        default: '0.825rem'
      },
      subtitleColor: {
        type: String,
        default: 'grey-darken-1'
      },
      activeTextColor: {
        type: String,
        default: null
      },
      checkboxColor: {
        type: String,
        default: null
      },
      disabled: {
        type: Boolean,
        default: false
      },
      readonly: {
        type: Boolean,
        default: false
      },
      subtitle: {
        type: String,
        default: null
      },
      itemValue: {
        type: String,
        default: 'id'
      },
      itemTitle: {
        type: String,
        default: 'name'
      },
      items: {
        type: Array,
        default: () => []
      },
      orderBy: {
        type: String,
        default: null
      },
      orderByDirection: {
        type: String,
        default: 'asc'
      },
      isTreeview: {
        type: Boolean,
        default: false
      },
      chunkCharacter: {
        type: String,
        default: '_'
      },
      chunkTitleKey: {
        type: String,
        default: 'name'
      },
      chunkField: {
        type: String,
        default: null
      },
      truncateItemLabel: {
        type: Boolean,
        default: false
      },
      flexColumn: {
        type: Boolean,
        default: true
      },
      checkboxHighlighted: {
        type: Boolean,
        default: false
      },
      checkboxHighlightedColor: {
        type: String,
        default: 'grey-lighten-5'
      },
      checkboxPosition: {
        type: String,
        default: 'right'
      },
      checkboxCol: {
        type: Object,
        default: () => ({
          cols: 3,
          sm: 6,
          md: 4,
          lg: 3,
        })
      },
      noGroupAllSelectable: {
        type: Boolean,
        default: false
      },
      hasGroupBottomDivider: {
        type: Boolean,
        default: true
      },
      openAllGroups: {
        type: Boolean,
        default: false
      },
      closeAllGroups: {
        type: Boolean,
        default: false
      },
      rawRules: {
        type: [String, Array],
        default: null
      },
      max: {
        type: [Number, String],
        default: null
      },
      mandatory: {
        type: String,
        default: null
      },
      isCard: {
        type: Boolean,
        default: false
      },
      cardStats: {
        type: Array,
        default: () => []
      },
      groupExpandTitleProps: {
        type: Object,
        default: () => ({})
      },
      groupExpandGap: {
        type: String,
        default: '4'
      },
    },
    setup (props, context) {
      const maxSelectable = computed(() => {
        let max = props.max

        if (window.__isString(max)) {
          max = parseInt(max)
        } else if (!max && window.__isString(props.rawRules)) {
          max = props.rawRules.match(/max:\d+/)?.[0].split(':')[1]
        }
        return max ?? 999
      })

      const protectedValues = ref(props.protectInitialValue ? props.modelValue : [])

      const isProtected = (id) => {
        return protectedValues.value.includes(id)
      }

      /**
       * Pure constraint helper — must not emit. Called from the input getter
       * on every read, so keep it cheap and reference-stable when unchanged.
       */
      const constrainInput = (rawInput) => {
        let input = Array.isArray(rawInput) ? rawInput : []
        let mandatoryItems = null
        let changed = false

        if (props.mandatory) {
          mandatoryItems = props.items.filter((item) => __data_get(item, props.mandatory, false))

          if (props.max) {
            const max = parseInt(props.max)
            if (mandatoryItems.length > max) {
              mandatoryItems = mandatoryItems.slice(0, max)
            }
          }

          if (mandatoryItems.length > 0) {
            const previousInput = Array.isArray(rawInput) ? rawInput : []
            const mandatoryItemsIds = mandatoryItems.map(item => item[props.itemValue])
            const missingMandatoryItems = mandatoryItemsIds.filter(id => !previousInput.includes(id))

            if (missingMandatoryItems.length > 0) {
              input = [...new Set([...previousInput, ...mandatoryItemsIds])]
              changed = true
            }
          }
        }

        if (maxSelectable.value > 1 && input.length > maxSelectable.value) {
          if (mandatoryItems && mandatoryItems.length > 0) {
            const mandatoryItemsIds = mandatoryItems.map(item => item[props.itemValue])
            const mandatorySelectedItems = input.filter(id => mandatoryItemsIds.includes(id))
            const nonMandatorySelectedItems = input.filter(id => !mandatoryItemsIds.includes(id))
            const remainingSlots = maxSelectable.value - mandatorySelectedItems.length
            const limitedNonMandatoryItems = remainingSlots > 0
              ? nonMandatorySelectedItems.slice(0, remainingSlots)
              : []

            input = [...mandatorySelectedItems, ...limitedNonMandatoryItems]
          } else {
            input = [...input].sort((a, b) => a - b).slice(0, maxSelectable.value)
          }
          changed = true
        }

        return { input, changed }
      }

      const initializeInput = (rawInput) => {
        return constrainInput(rawInput).input
      }

      const openedGroups = ref([])
      const groupsInitialized = ref(false)

      const flattenedItems = computed(() => {
        const items = Array.isArray(props.items) ? [...props.items] : []

        if (props.orderBy) {
          items.sort((a, b) => {
            try {
              if (props.orderByDirection === 'asc') {
                return a[props.orderBy].localeCompare(b[props.orderBy])
              }
              return b[props.orderBy].localeCompare(a[props.orderBy])
            } catch (error) {
              return 0
            }
          })
        }

        return items
      })

      const inputApi = useInput(props, {
        ...context,
        initializeInput
      })

      const selectedSet = computed(() => {
        const value = inputApi.input.value
        return new Set(Array.isArray(value) ? value : [])
      })

      const canSelectMore = computed(() => {
        return !props.disabled && (
          !maxSelectable.value ||
          (Array.isArray(inputApi.input.value) && inputApi.input.value.length < maxSelectable.value)
        )
      })

      const onSelectionChange = (value) => {
        inputApi.input.value = value
      }

      // Sync mandatory / max constraints to parent once when they actually change
      watch(
        () => [props.modelValue, props.items, props.mandatory, maxSelectable.value],
        () => {
          const { input, changed } = constrainInput(props.modelValue ?? props.default ?? [])
          if (changed) {
            context.emit('update:modelValue', input)
          }
        },
        { immediate: true, deep: false }
      )

      return {
        ...inputApi,
        openedGroups: toRef(openedGroups),
        groupsInitialized,
        maxSelectable,
        isProtected,
        flattenedItems,
        selectedSet,
        canSelectMoreComputed: canSelectMore,
        onSelectionChange
      }
    },

    methods: {
      isGroupOpened (name) {
        return this.openedGroups.includes(name)
      },
      isSelectedValue (value) {
        return this.selectedSet.has(value)
      },
      isAllSelected (group) {
        const ids = group.items.map((item) => item.id)
        return ids.length > 0 && ids.every(v => this.selectedSet.has(v))
      },
      isIndeterminateGroup (group) {
        const ids = group.items.map((item) => item.id)
        const some = ids.some(v => this.selectedSet.has(v))
        return some && !ids.every(v => this.selectedSet.has(v))
      },
      updatedParent (value, group) {
        const ids = group.items.map((item) => item.id)
        const current = Array.isArray(this.input) ? [...this.input] : []

        if (!value) {
          this.input = current.filter((id) => !ids.includes(id))
          return
        }

        if (this.maxSelectable) {
          const newItemsCount = ids.filter(id => !current.includes(id)).length
          if (current.length + newItemsCount > this.maxSelectable) {
            return
          }
        }

        const next = new Set(current)
        ids.forEach((id) => next.add(id))
        this.input = [...next]
      },
      getRowStyles () {
        const baseStyle = 'flex: 1 1;'
        if (this.flexColumn && this.$vuetify.display.mdAndUp) {
          return `${baseStyle} flex: 1 1 60%;`
        }
        return baseStyle
      },
      getCheckboxContainerClasses (item) {
        const isSelected = this.isSelectedValue(item[this.itemValue])
        return [
          'd-flex align-center rounded-sm',
          this.checkboxOnRight ? 'pl-2' : '',
          this.checkboxOnRight && isSelected ? 'checked' : '',
          this.checkboxHighlighted && isSelected ? `bg-${this.checkboxHighlightedColor}` : '',
        ]
      },
      isSelectedItem (item) {
        return this.isSelectedValue(item[this.itemValue])
      },
      getCheckboxClasses (item) {
        const isSelected = this.isSelectedItem(item)

        return [
          'flex-shrink-0 flex-grow-0',
          isSelected ? `v-input-checklist__checkbox--selected ${this.activeTextColor ? `text-${this.activeTextColor}` : ''}` : '',
          this.truncateItemLabel ? 'v-input-checklist__checkbox--truncate' : '',
          this.checkboxOnLeft ? 'rounded-sm' : 'v-input-checklist__checkbox--right',
          this.checkboxOnLeft && isSelected ? 'checked' : '',
        ]
      },
      canSelectMore () {
        return this.canSelectMoreComputed
      },
      isItemDisabled (item) {
        return !this.canSelectMoreComputed && !this.isSelectedValue(item[this.itemValue])
      },
      isMandatoryItem (item) {
        return Boolean(__data_get(item, this.mandatory, false))
      },
      isGroupOpen (index) {
        if (this.openAllGroups) {
          return true
        }
        if (this.closeAllGroups) {
          return false
        }
        return index === 0
      },
      getCardStats (item) {
        return this.cardStats.map((stat) => ({
          ...stat,
          value: item[stat.key]
        }))
      },
      syncOpenedGroups (groups) {
        if (this.closeAllGroups) {
          this.openedGroups = []
          return
        }
        if (groups.length === 0) {
          this.openedGroups = []
          return
        }
        if (this.openAllGroups) {
          this.openedGroups = groups.map((group) => group.name)
          return
        }

        // Prefer groups that already have selections (edit role), but cap how many
        // mount at once so large permission catalogs stay responsive.
        const selected = this.selectedSet
        if (selected.size > 0) {
          const withSelection = groups
            .filter((group) => Array.isArray(group.items) && group.items.some((item) => selected.has(item.id)))
            .map((group) => group.name)

          if (withSelection.length > 0) {
            this.openedGroups = withSelection.slice(0, 3)
            return
          }
        }

        // Large catalogs: headers only until the user expands a group.
        if (Array.isArray(this.items) && this.items.length > 80) {
          this.openedGroups = []
          return
        }

        this.openedGroups = [groups[0].name]
      }
    },

    computed: {
      checkboxOnRight () {
        return this.checkboxPosition === 'right'
      },
      checkboxOnLeft () {
        return this.checkboxPosition === 'left'
      },
      groupedItems () {
        const groups = {}

        if (!this.items || !Array.isArray(this.items) || this.items.length === 0) {
          return []
        }

        for (let i = 0; i < this.items.length; i++) {
          const source = this.items[i]

          if (this.chunkField) {
            const groupName = source[this.chunkField]
            const checklistTitle = source[this.chunkTitleKey]

            if (Object.prototype.hasOwnProperty.call(groups, groupName)) {
              groups[groupName].items.push({
                id: source[this.itemValue] ?? source.id,
                name: checklistTitle
              })
            } else {
              groups[groupName] = {
                name: this.$lodash.startCase(this.$lodash.camelCase(groupName)),
                title: groupName,
                items: [{
                  id: source[this.itemValue] ?? source.id,
                  name: checklistTitle
                }]
              }
            }
          } else {
            const splitted = String(source[this.chunkTitleKey] ?? '').split(this.chunkCharacter)

            if (splitted.length > 1) {
              const groupName = splitted[0]
              const checklistTitle = splitted.slice(1).join(this.chunkCharacter)
              if (Object.prototype.hasOwnProperty.call(groups, groupName)) {
                if (__isset(groups[groupName].id)) delete groups[groupName].id

                groups[groupName].items.push({
                  id: source[this.itemValue] ?? source.id,
                  name: this.$lodash.startCase(this.$lodash.camelCase(checklistTitle))
                })
              } else {
                groups[groupName] = {
                  name: this.$lodash.startCase(this.$lodash.camelCase(groupName)),
                  title: groupName,
                  items: [{
                    id: source[this.itemValue] ?? source.id,
                    name: this.$lodash.startCase(this.$lodash.camelCase(checklistTitle))
                  }]
                }
              }
            } else {
              const groupName = 'alpha'
              if (Object.prototype.hasOwnProperty.call(groups, groupName)) {
                if (__isset(groups[groupName].id)) delete groups[groupName].id
                groups[groupName].items.push({
                  id: source[this.itemValue] ?? source.id,
                  name: this.$lodash.startCase(this.$lodash.camelCase(source[this.chunkTitleKey]))
                })
              } else {
                groups[groupName] = {
                  name: this.$t('General Permissions'),
                  items: [{
                    id: source[this.itemValue] ?? source.id,
                    name: this.$lodash.startCase(this.$lodash.camelCase(source[this.chunkTitleKey]))
                  }]
                }
              }
            }
          }
        }

        const array = Object.values(groups)

        array.sort(function (left, right) {
          return left.hasOwnProperty('items') ? 1 : (right.hasOwnProperty('items') ? -1 : 0)
        })

        if (this.orderBy) {
          for (let i = 0; i < array.length; i++) {
            if (!array[i].items) continue
            array[i].items.sort((a, b) => {
              if (this.orderByDirection === 'asc') {
                return a[this.orderBy].localeCompare(b[this.orderBy])
              }
              return b[this.orderBy].localeCompare(a[this.orderBy])
            })
          }
        }

        return array
      },
      disabledCheckbox () {
        return this.$attrs.disabled || (!this.canSelectMore() && !Array.isArray(this.input))
      },
      hasMandatoryItems () {
        return this.items.some((item) => __data_get(item, this.mandatory, false))
      },
      treeviewCols () {
        return !this.chunkField ? {
          lg: 6,
          md: 8,
          sm: 12
        } : {
          cols: 12
        }
      }
    },

    watch: {
      groupedItems: {
        immediate: true,
        handler (groups) {
          if (this.closeAllGroups) {
            this.openedGroups = []
            this.groupsInitialized = true
            return
          }

          if (this.openAllGroups) {
            this.syncOpenedGroups(groups)
            this.groupsInitialized = true
            return
          }

          // Seed defaults when first groups arrive (e.g. async items), but do not
          // reset expansions the user already toggled.
          if (!this.groupsInitialized || (groups.length > 0 && this.openedGroups.length === 0)) {
            this.syncOpenedGroups(groups)
            this.groupsInitialized = true
          }
        }
      },
      openAllGroups (value) {
        if (value) {
          this.syncOpenedGroups(this.groupedItems)
        }
      },
      closeAllGroups (value) {
        if (value) {
          this.openedGroups = []
        }
      }
    }
  }
</script>

<style lang="sass">
  .v-input-checklist
    .v-checkbox,
    .v-checkbox-btn
      max-width: 100%
      .v-input__control
        max-width: 100%
        .v-checkbox-btn
          max-width: 100%
          flex: 1 1 100%
          > label
            flex: 1 0
            width: calc(100% - 40px)

    .v-checkbox-btn
      max-width: 100%
      flex: 1 1 100%
      > label
        flex: 1 0
        width: calc(100% - 40px)

    &__checkbox
      &--right
        width: 100%
        &.v-checkbox-btn,
        .v-checkbox-btn
          flex-direction: row-reverse
          flex: 1 1 100%

      &--selected
        &.v-checkbox-btn,
        .v-checkbox-btn
          > label
            font-weight: 600

      &--truncate
        &.v-checkbox-btn,
        .v-checkbox-btn
          > label
            white-space: nowrap !important
            overflow: hidden !important
            text-overflow: ellipsis !important

    .v-input--horizontal .v-input__prepend
        margin-inline-end: 0px

</style>
