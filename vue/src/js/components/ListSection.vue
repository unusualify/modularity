<template>
  <div class="ue-list-section">
    <!-- Table-like structure with flex layout -->
    <div class="list-container" :class="{ 'align-top': verticalAlignTop }">
      <!-- Header row -->
      <div v-if="showHeader" class="list-row header-row" :class="headerClasses">
        <div
          v-for="(header, j) in effectiveHeaders"
          :key="`header-${j}`"
          class="list-cell"
          :class="[colClasses[j] ?? '']"
          :style="getColumnStyle(j)"
        >
          <slot :name="`header.${j}`" v-bind="{ header }">
            {{ header }}
          </slot>
        </div>

        <!-- Actions column header -->
        <div v-if="$slots['row-actions']" class="list-cell actions-cell">
          <slot name="actions-header">
            {{ actionsHeader }}
          </slot>
        </div>
      </div>

      <!-- Title row if provided -->
      <div v-if="title" class="list-row title-row">
        <div class="title-cell">
          <component :is="titleTag" :class="titleClasses">{{ title }}</component>
        </div>
      </div>

      <!-- Data rows -->
      <div
        v-for="(item, i) in items"
        :key="`item-${i}`"
        class="list-row data-row"
        :class="[getRowClass(item, i), {'has-bottom-border': hasRowBottomBorder}]"
      >
        <div
          v-for="(field, j) in itemFields"
          :key="`item-field-${j}`"
          class="list-cell"
          :class="[colClasses[j] ?? '']"
          :style="getColumnStyle(j)"
        >
          <slot :name="`field.${j}`" v-bind="{ value: $lodash.get(item, field), item, index: i }">
            {{ $lodash.get(item, field, '') }}
          </slot>
        </div>

        <!-- Actions cell -->
        <div v-if="$slots['row-actions']" class="list-cell actions-cell">
          <slot name="row-actions" v-bind="{ item, index: i }"></slot>
        </div>
      </div>

      <!-- Empty state message -->
      <div v-if="items.length === 0 && emptyMessage" class="list-row empty-row">
        <div class="empty-message">{{ emptyMessage }}</div>
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
        classes.push('hover-effect');
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

<style scoped>
.ue-list-section {
  width: 100%;
}

.list-container {
  width: 100%;
}

.list-row {
  display: flex;
  width: 100%;
  min-height: 25px;
  padding: 6px 0;
  align-items: center;
}

.header-row {
  font-weight: bold;
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  padding-bottom: 2px;
  min-height: 35px;
}

.title-row {
  min-height: 35px;
}

.title-cell {
  flex: 1;
  padding: 6px 0;
}

.data-row.has-bottom-border {
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.list-cell {
  padding-right: 16px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  min-width: 0; /* Important for text truncation in flex items */
}

.list-cell:last-child {
  padding-right: 0;
}

/* Apply vertical-align: top when the prop is set */
.list-container.align-top .list-row {
  align-items: flex-start;
}

.list-container.align-top .list-cell {
  padding-top: 8px;
}

.actions-cell {
  width: 40px;
  flex: 0 0 40px;
  text-align: right;
}

.empty-row {
  justify-content: center;
}

.empty-message {
  text-align: center;
  padding: 16px 0;
  color: rgba(0, 0, 0, 0.6);
  font-size: 14px;
}

.hover-effect:hover {
  background-color: rgba(0, 0, 0, 0.04);
}
</style>
