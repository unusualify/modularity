<template>
  <div class="ue-list-section">
    <template v-if="shouldWrapInPanel">
      <v-expansion-panels
        v-model="panelState"
        variant="accordion"
        flat
        class="ue-list-section__expansion-panels"
      >
        <v-expansion-panel class="ue-list-section__panel">
          <v-expansion-panel-title class="px-0 py-2 min-h-0">
            <template v-if="title">
              <component :is="titleTag" :class="titleClasses">{{ title }}</component>
            </template>
            <template v-else>
              <slot name="title-content"></slot>
            </template>
          </v-expansion-panel-title>
          <v-expansion-panel-text class="ue-list-section__panel-text pa-0">
            <div class="ue-list-section__content pt-2">
              <div class="ue-list-section__container" :class="{ 'align-top': verticalAlignTop }">
                <slot name="before-items"></slot>

                <!-- Header row -->
                <div v-if="showHeader" class="ue-list-section__row ue-list-section__row--header" :class="headerClasses">
                  <div
                    v-for="(header, j) in effectiveHeaders"
                    :key="`header-${j}`"
                    class="ue-list-section__cell"
                    :class="[colClasses[j] ?? '']"
                    :style="getColumnStyle(j)"
                  >
                    <slot :name="`header.${j}`" v-bind="{ header }">
                      {{ header }}
                    </slot>
                  </div>

                  <!-- Actions column header -->
                  <div v-if="$slots['row-actions']" class="ue-list-section__cell ue-list-section__cell--actions">
                    <slot name="actions-header">
                      {{ actionsHeader }}
                    </slot>
                  </div>
                </div>

                <!-- Visible items -->
                <div
                  v-for="(item, i) in visibleItems"
                  :key="`item-${i}`"
                  class="ue-list-section__row ue-list-section__row--data"
                  :class="[getRowClass(item, i), {'has-bottom-border': hasRowBottomBorder}]"
                >
                  <template v-if="item._type === 'divider'">
                    <v-divider v-bind="{...dividerAttributes, ...item.props ?? {}}" />
                  </template>
                  <template v-else>
                    <div
                      v-for="(field, j) in itemFields"
                      :key="`item-field-${j}`"
                      class="ue-list-section__cell"
                      :class="[colClasses[j] ?? '']"
                      :style="getColumnStyle(j)"
                    >
                      <slot :name="`field.${j}`" v-bind="{ value: get(item, field, ''), item, index: i }">
                        <template v-if="item.formatters && window.__isObject(item.formatters) && item.formatters[field] && Array.isArray(item.formatters[field]) && item.formatters[field].length > 1">
                          <ue-recursive-stuff
                            v-bind="handleFormatter(item.formatters[field], get(item, field, ''))"
                          />
                        </template>
                        <template v-else>
                          {{ get(item, field, '') }}
                        </template>
                      </slot>
                    </div>
                  </template>

                  <!-- Actions cell -->
                  <div v-if="$slots['row-actions']" class="ue-list-section__cell ue-list-section__cell--actions">
                    <slot name="row-actions" v-bind="{ item, index: i }"></slot>
                  </div>
                </div>

                <!-- Collapsible items -->
                <v-expand-transition>
                  <div v-if="isExpanded && hasCollapsibleItems">
                    <div
                      v-for="(item, i) in collapsibleItems"
                      :key="`item-${i + shrinkAfter}`"
                      class="ue-list-section__row ue-list-section__row--data"
                      :class="[getRowClass(item, i + shrinkAfter), {'has-bottom-border': hasRowBottomBorder}]"
                    >
                      <template v-if="item._type === 'divider'">
                        <v-divider v-bind="{...dividerAttributes, ...item.props ?? {}}" />
                      </template>
                      <template v-else>
                        <div
                          v-for="(field, j) in itemFields"
                          :key="`item-field-${j}`"
                          class="ue-list-section__cell"
                          :class="[colClasses[j] ?? '']"
                          :style="getColumnStyle(j)"
                        >
                          <slot :name="`field.${j}`" v-bind="{ value: get(item, field), item, index: i + shrinkAfter }">
                            <template v-if="item.formatters && window.__isObject(item.formatters) && item.formatters[field] && Array.isArray(item.formatters[field]) && item.formatters[field].length > 1">
                              <ue-recursive-stuff
                                v-bind="handleFormatter(item.formatters[field], get(item, field, ''))"
                              />
                            </template>
                            <template v-else>
                              {{ get(item, field, '') }}
                            </template>
                          </slot>
                        </div>
                      </template>

                      <!-- Actions cell -->
                      <div v-if="$slots['row-actions']" class="ue-list-section__cell ue-list-section__cell--actions">
                        <slot name="row-actions" v-bind="{ item, index: i + shrinkAfter }"></slot>
                      </div>
                    </div>
                  </div>
                </v-expand-transition>

                <!-- Show more/less button -->
                <div v-if="hasCollapsibleItems" class="ue-list-section__row ue-list-section__row--toggle">
                  <div class="ue-list-section__cell">
                    <v-btn
                      variant="text"
                      size="small"
                      color="primary"
                      @click="toggleExpand"
                      :append-icon="isExpanded ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                    >
                      {{ isExpanded ? shrinkText : showMoreText }}
                      <span v-if="!isExpanded" class="ml-1 text-caption">({{ collapsibleNonDividerCount }} {{ moreItemsText }})</span>
                    </v-btn>
                  </div>
                </div>

                <!-- Empty state message -->
                <div v-if="items.length === 0 && emptyMessage" class="ue-list-section__row ue-list-section__row--empty">
                  <div class="ue-list-section__cell">{{ emptyMessage }}</div>
                </div>

                <slot name="after-items"></slot>
              </div>
            </div>
          </v-expansion-panel-text>
        </v-expansion-panel>
      </v-expansion-panels>
    </template>

    <template v-else>
      <div class="ue-list-section__container" :class="{ 'align-top': verticalAlignTop }">
        <!-- Title row if provided -->
        <div v-if="title" class="ue-list-section__row ue-list-section__row--title">
          <div class="ue-list-section__cell">
            <component :is="titleTag" :class="titleClasses">{{ title }}</component>
          </div>
        </div>

        <slot name="before-items"></slot>

        <!-- Header row -->
        <div v-if="showHeader" class="ue-list-section__row ue-list-section__row--header" :class="headerClasses">
          <div
            v-for="(header, j) in effectiveHeaders"
            :key="`header-${j}`"
            class="ue-list-section__cell"
            :class="[colClasses[j] ?? '']"
            :style="getColumnStyle(j)"
          >
            <slot :name="`header.${j}`" v-bind="{ header }">
              {{ header }}
            </slot>
          </div>

          <!-- Actions column header -->
          <div v-if="$slots['row-actions']" class="ue-list-section__cell ue-list-section__cell--actions">
            <slot name="actions-header">
              {{ actionsHeader }}
            </slot>
          </div>
        </div>

        <!-- Visible items -->
        <div
          v-for="(item, i) in visibleItems"
          :key="`item-${i}`"
          class="ue-list-section__row ue-list-section__row--data"
          :class="[getRowClass(item, i), {'has-bottom-border': hasRowBottomBorder}]"
        >
          <template v-if="item._type === 'divider'">
            <v-divider v-bind="{...dividerAttributes, ...item.props ?? {}}" />
          </template>
          <template v-else>
            <div
              v-for="(field, j) in itemFields"
              :key="`item-field-${j}`"
              class="ue-list-section__cell"
              :class="[colClasses[j] ?? '']"
              :style="getColumnStyle(j)"
            >
              <slot :name="`field.${j}`" v-bind="{ value: get(item, field), item, index: i + shrinkAfter }">
                <template v-if="item.formatters && window.__isObject(item.formatters) && item.formatters[field] && Array.isArray(item.formatters[field]) && item.formatters[field].length > 1">
                  <ue-recursive-stuff
                    v-bind="handleFormatter(item.formatters[field], get(item, field, ''))"
                  />
                </template>
                <template v-else>
                  {{ get(item, field, '') }}
                </template>
              </slot>
            </div>
          </template>

          <!-- Actions cell -->
          <div v-if="$slots['row-actions']" class="ue-list-section__cell ue-list-section__cell--actions">
            <slot name="row-actions" v-bind="{ item, index: i }"></slot>
          </div>
        </div>

        <!-- Collapsible items -->
        <v-expand-transition>
          <div v-if="isExpanded && hasCollapsibleItems">
            <div
              v-for="(item, i) in collapsibleItems"
              :key="`item-${i + shrinkAfter}`"
              class="ue-list-section__row ue-list-section__row--data"
              :class="[getRowClass(item, i + shrinkAfter), {'has-bottom-border': hasRowBottomBorder}]"
            >
              <template v-if="item._type === 'divider'">
                <v-divider v-bind="{...dividerAttributes, ...item.props ?? {}}" />
              </template>
              <template v-else>
                <div
                  v-for="(field, j) in itemFields"
                  :key="`item-field-${j}`"
                  class="ue-list-section__cell"
                  :class="[colClasses[j] ?? '']"
                  :style="getColumnStyle(j)"
                >
                  <slot :name="`field.${j}`" v-bind="{ value: get(item, field), item, index: i + shrinkAfter }">
                    <template v-if="item.formatters && window.__isObject(item.formatters) && item.formatters[field] && Array.isArray(item.formatters[field]) && item.formatters[field].length > 1">
                      <ue-recursive-stuff
                        v-bind="handleFormatter(item.formatters[field], get(item, field, ''))"
                      />
                    </template>
                    <template v-else>
                      {{ get(item, field, '') }}
                    </template>
                  </slot>
                </div>
              </template>

              <!-- Actions cell -->
              <div v-if="$slots['row-actions']" class="ue-list-section__cell ue-list-section__cell--actions">
                <slot name="row-actions" v-bind="{ item, index: i + shrinkAfter }"></slot>
              </div>
            </div>
          </div>
        </v-expand-transition>

        <!-- Show more/less button -->
        <div v-if="hasCollapsibleItems" class="ue-list-section__row ue-list-section__row--toggle">
          <div class="ue-list-section__cell">
            <v-btn
              variant="text"
              size="small"
              color="primary"
              @click="toggleExpand"
              :append-icon="isExpanded ? 'mdi-chevron-up' : 'mdi-chevron-down'"
            >
              {{ isExpanded ? shrinkText : showMoreText }}
              <span v-if="!isExpanded" class="ml-1 text-caption">({{ collapsibleNonDividerCount }} {{ moreItemsText }})</span>
            </v-btn>
          </div>
        </div>

        <!-- Empty state message -->
        <div v-if="items.length === 0 && emptyMessage" class="ue-list-section__row ue-list-section__row--empty">
          <div class="ue-list-section__cell">{{ emptyMessage }}</div>
        </div>

        <slot name="after-items"></slot>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, useSlots, ref } from 'vue'
import { get } from 'lodash-es'

import { useFormatter } from '@/hooks'

const props = defineProps({
  title: {
    type: String,
  },
  titleTag: {
    type: String,
    default: 'h3'
  },
  titleClasses: {
    type: String,
    default: 'text-body-1 font-weight-medium'
  },
  itemClasses: {
    type: String,
    default: 'text-body-2'
  },
  headerClasses: {
    type: String,
    default: 'text-body-2 font-weight-bold'
  },
  items: {
    type: Array,
    required: true
  },
  itemFields: {
    type: Array,
    default: () => ['name']
  },
  headers: {
    type: Array,
    default: null
  },
  showHeader: {
    type: Boolean,
    default: false
  },
  colClasses: {
    type: Array,
    default: () => []
  },
  colWidths: {
    type: Array,
    default: () => []
  },
  colRatios: {
    type: Array,
    default: () => []
  },
  rowClassFn: {
    type: Function,
    default: null
  },
  emptyMessage: {
    type: String,
    default: 'No items to display'
  },
  striped: {
    type: Boolean,
    default: false
  },
  hoverable: {
    type: Boolean,
    default: false
  },
  hasRowBottomBorder: {
    type: Boolean,
    default: false
  },
  actionsHeader: {
    type: String,
    default: ''
  },
  verticalAlignTop: {
    type: Boolean,
    default: false
  },
  dividerAttributes: {
    type: Object,
    default: () => ({})
  },
  // Collapsible props
  collapsible: {
    type: Boolean,
    default: false
  },
  collapseLimit: {
    type: Number,
    default: null
  },
  shrinkAfter: {
    type: Number,
    default: 20
  },
  showMoreText: {
    type: String,
    default: 'Show more'
  },
  shrinkText: {
    type: String,
    default: 'Show less'
  },
  moreItemsText: {
    type: String,
    default: 'more items'
  },
  modelValue: {
    type: [Array, String, Number],
    default: undefined
  }
})

const emit = defineEmits(['update:modelValue'])

const slots = useSlots()

const { handleFormatter } = useFormatter()

const internalPanelState = ref(undefined)
const isExpanded = ref(false)

const panelState = computed({
  get: () => props.modelValue !== undefined ? props.modelValue : internalPanelState.value,
  set: (val) => {
    internalPanelState.value = val
    emit('update:modelValue', val)
  }
})

// Determines if the entire section should be wrapped in an expansion panel
const shouldWrapInPanel = computed(() => {
  if (props.collapsible) return true
  if (props.collapseLimit !== null && props.items.length > props.collapseLimit) return true
  return false
})

// Count only non-divider items
const nonDividerItemsCount = computed(() => {
  return props.items.filter(item => item._type !== 'divider').length
})

// Determines if items should have show more/less functionality
const hasCollapsibleItems = computed(() => {
  return nonDividerItemsCount.value > props.shrinkAfter
})

const visibleItems = computed(() => {
  if (!hasCollapsibleItems.value) {
    return props.items
  }

  // Take items until we have shrinkAfter non-divider items
  let nonDividerCount = 0
  let visibleIndex = 0

  for (let i = 0; i < props.items.length; i++) {
    if (props.items[i]._type !== 'divider') {
      nonDividerCount++
    }
    visibleIndex = i + 1

    if (nonDividerCount >= props.shrinkAfter) {
      break
    }
  }

  return props.items.slice(0, visibleIndex)
})

const collapsibleItems = computed(() => {
  if (!hasCollapsibleItems.value) {
    return []
  }
  return props.items.slice(visibleItems.value.length)
})

const collapsibleNonDividerCount = computed(() => {
  return collapsibleItems.value.filter(item => item._type !== 'divider').length
})

const effectiveHeaders = computed(() => {
  // If headers are not provided, use item fields as headers
  return props.headers || props.itemFields.map(field => {
    // Convert camelCase to Title Case
    return field
      .replace(/([A-Z])/g, ' $1')
      .replace(/^./, str => str.toUpperCase());
  });
})

const totalRatio = computed(() => {
  // Calculate the total ratio to determine percentages
  if (!props.colRatios || props.colRatios.length === 0) {
    return props.itemFields.length;
  }

  return props.colRatios.reduce((sum, ratio) => {
    // Use the provided ratio or default to 1
    const value = ratio || 1;
    return sum + value;
  }, 0);
})

const getColumnStyle = (colIndex) => {
  // If explicit width is provided, use it
  if (props.colWidths && props.colWidths[colIndex]) {
    return { width: props.colWidths[colIndex], flexBasis: props.colWidths[colIndex] };
  }

  // If ratio is provided, calculate percentage width
  if (props.colRatios && props.colRatios.length > 0) {
    const ratio = props.colRatios[colIndex] || 1;
    const percentage = (ratio / totalRatio.value) * 100;
    return {
      flex: `${ratio} 0 0`,
      maxWidth: `${percentage}%`
    };
  }

  // Default: equal width for all columns
  const count = props.itemFields.length;
  const actionOffset = slots['row-actions'] ? 1 : 0;
  const percentage = 100 / (count + actionOffset);

  return {
    flex: '1 1 0',
    maxWidth: `${percentage}%`
  };
}

const getRowClass = (item, index) => {
  let classes = [props.itemClasses];

  // Apply striped effect if enabled
  if (props.striped && index % 2 === 1) {
    classes.push('bg-grey-lighten-5');
  }

  // Apply hoverable effect if enabled
  if (props.hoverable) {
    classes.push('ue-list-section__row--hoverable');
  }

  // Apply custom row class function if provided
  if (props.rowClassFn) {
    const customClass = props.rowClassFn(item, index);
    if (customClass) {
      classes.push(customClass);
    }
  }

  return classes.join(' ');
}

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value
}
</script>

<style scoped lang="scss">
  .ue-list-section {
    width: 100%;

    :deep(.ue-list-section__expansion-panels) {
      background-color: transparent;

      .v-expansion-panel {
        background-color: transparent;

        &::before {
          display: none;
        }
      }

      .v-expansion-panel-title {
        padding: 0;
        min-height: unset;

        &:hover > .v-expansion-panel-title__overlay {
          opacity: 0;
        }

        .v-expansion-panel-title__icon {
          margin-inline-start: 8px;
        }
      }

      .v-expansion-panel-text__wrapper {
        padding: 0;
      }
    }

    .ue-list-section__container {
      width: 100%;
    }

    .ue-list-section__row {
      display: flex;
      width: 100%;
      min-height: 25px;
      padding: 6px 0;
      align-items: center;
    }

    .ue-list-section__row--header {
      font-weight: bold;
      border-bottom: 1px solid rgba(0, 0, 0, 0.12);
      padding-bottom: 2px;
      min-height: 35px;
    }

    .ue-list-section__row--title {
      min-height: 35px;
    }

    .ue-list-section__cell--title {
      flex: 1;
      padding: 6px 0;
    }

    .ue-list-section__row--data.has-bottom-border {
      border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }

    .ue-list-section__cell {
      padding-right: 16px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      min-width: 0; /* Important for text truncation in flex items */
    }

    .ue-list-section__cell:last-child {
      padding-right: 0;
    }

    /* Apply vertical-align: top when the prop is set */
    .ue-list-section__container.align-top .ue-list-section__row {
      align-items: flex-start;
    }

    .ue-list-section__container.align-top .ue-list-section__cell {
      padding-top: 8px;
    }

    .ue-list-section__cell--actions {
      width: 40px;
      flex: 0 0 40px;
      text-align: right;
    }

    .ue-list-section__row--empty {
      justify-content: center;
    }

    .ue-list-section__row--empty .ue-list-section__cell {
      text-align: center;
      padding: 16px 0;
      color: rgba(0, 0, 0, 0.6);
      font-size: 14px;
    }

    .ue-list-section__row--hoverable:hover {
      background-color: rgba(0, 0, 0, 0.04);
    }

    .ue-list-section__row--toggle {
      justify-content: flex-start;
      padding-top: 8px;
      padding-bottom: 8px;

      .ue-list-section__cell {
        padding: 0;
      }
    }
  }
</style>
