<template>
  <div class="ue-list-section">
    <!-- Table-like structure with flex layout -->
    <div class="ue-list-section__container" :class="{ 'align-top': verticalAlignTop }">
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

      <!-- Title row if provided -->
      <div v-if="title" class="ue-list-section__row ue-list-section__row--title">
        <div class="ue-list-section__cell">
          <component :is="titleTag" :class="titleClasses">{{ title }}</component>
        </div>
      </div>

      <!-- Data rows -->
      <div
        v-for="(item, i) in sortedItems"
        :key="`item-${i}`"
        class="ue-list-section__row ue-list-section__row--data"
        :class="[getRowClass(item, i), {'has-bottom-border': hasRowBottomBorder}]"
      >
        <div
          v-for="(field, j) in itemFields"
          :key="`item-field-${j}`"
          class="ue-list-section__cell"
          :class="[colClasses[j] ?? '']"
          :style="getColumnStyle(j)"
        >
          <slot :name="`field.${j}`" v-bind="{ value: $lodash.get(item, field), item, index: i }">
            {{ $lodash.get(item, field, '') }}
          </slot>
        </div>

        <!-- Actions cell -->
        <div v-if="$slots['row-actions']" class="ue-list-section__cell ue-list-section__cell--actions">
          <slot name="row-actions" v-bind="{ item, index: i }"></slot>
        </div>
      </div>

      <!-- Empty state message -->
      <div v-if="sortedItems.length === 0 && emptyMessage" class="ue-list-section__row ue-list-section__row--empty">
        <div class="ue-list-section__row--empty .ue-list-section__cell">{{ emptyMessage }}</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ue-list-section',
  props: {
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
    }
  },
  computed: {
    effectiveHeaders() {
      // If headers are not provided, use item fields as headers
      return this.headers || this.itemFields.map(field => {
        // Convert camelCase to Title Case
        return field
          .replace(/([A-Z])/g, ' $1')
          .replace(/^./, str => str.toUpperCase());
      });
    },

    sortedItems() {
      // Sort items alphabetically by the first field (usually the name/title)
      return [...this.items].sort((a, b) => {
        const fieldA = this.$lodash.get(a, this.itemFields[0], '')
        const fieldB = this.$lodash.get(b, this.itemFields[0], '')

        // Handle different data types
        if (typeof fieldA === 'string' && typeof fieldB === 'string') {
          return fieldA.localeCompare(fieldB)
        }

        // Fallback to string comparison
        return String(fieldA).localeCompare(String(fieldB))
      })
    },

    totalRatio() {
      // Calculate the total ratio to determine percentages
      if (!this.colRatios || this.colRatios.length === 0) {
        return this.itemFields.length;
      }

      return this.colRatios.reduce((sum, ratio, index) => {
        // Use the provided ratio or default to 1
        const value = ratio || 1;
        return sum + value;
      }, 0);
    }
  },
  methods: {
    getColumnStyle(colIndex) {
      // If explicit width is provided, use it
      if (this.colWidths && this.colWidths[colIndex]) {
        return { width: this.colWidths[colIndex], flexBasis: this.colWidths[colIndex] };
      }

      // If ratio is provided, calculate percentage width
      if (this.colRatios && this.colRatios.length > 0) {
        const ratio = this.colRatios[colIndex] || 1;
        const percentage = (ratio / this.totalRatio) * 100;
        return {
          flex: `${ratio} 0 0`,
          maxWidth: `${percentage}%`
        };
      }

      // Default: equal width for all columns
      const count = this.itemFields.length;
      const actionOffset = this.$slots['row-actions'] ? 1 : 0;
      const percentage = 100 / (count + actionOffset);

      return {
        flex: '1 1 0',
        maxWidth: `${percentage}%`
      };
    },

    getRowClass(item, index) {
      let classes = [this.itemClasses];

      // Apply striped effect if enabled
      if (this.striped && index % 2 === 1) {
        classes.push('bg-grey-lighten-5');
      }

      // Apply hoverable effect if enabled
      if (this.hoverable) {
        classes.push('ue-list-section__row--hoverable');
      }

      // Apply custom row class function if provided
      if (this.rowClassFn) {
        const customClass = this.rowClassFn(item, index);
        if (customClass) {
          classes.push(customClass);
        }
      }

      return classes.join(' ');
    }
  }
}
</script>

<style scoped lang="scss">
.ue-list-section {
  width: 100%;
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
}


</style>
