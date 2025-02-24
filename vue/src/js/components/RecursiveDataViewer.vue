<template>
  <template v-if="Array.isArray(data)">
    <v-card variant="flat" class="mb-2">
      <v-card-title class="py-2 px-4">
        <v-icon icon="mdi-code-brackets" class="mr-2" size="small" />
        <span class="text-body-2">Array ({{ data.length }} items)</span>
      </v-card-title>

      <div class="array-items">
        <div v-for="(item, index) in data" :key="index" class="array-item">
          <v-card variant="flat" class="mb-1">
            <ue-recursive-data-viewer
              :data="item"
              :array-index="0"
              :object-depth="index === 0 ? objectDepth : objectDepth + 1"
              :all-array-items-closed="allArrayItemsClosed"
              :all-array-items-open="allArrayItemsOpen"
              :all-objects-closed="allObjectsClosed"
              :all-objects-open="allObjectsOpen"
            />

          </v-card>
        </div>
      </div>
    </v-card>
  </template>

  <template v-else-if="typeof data === 'object' && data !== null">
    <v-card variant="flat" class="mb-2">
      <v-card-title
        class="d-flex align-center py-2 px-4 collapsible-header text-body-2"
        @click="isExpanded = !isExpanded"
      >
        <!-- <div v-if="objectTitle" class="json-key-title">{{ objectTitle }}</div> -->
        <v-icon
          :icon="isExpanded ? 'mdi-chevron-down' : 'mdi-chevron-right'"
          class="mr-2"
          size="small"
        />
        <v-icon icon="mdi-code-braces" class="mr-2" size="small" />
        <span class="">
          Object ({{ Object.keys(data).length }} properties)
        </span>
      </v-card-title>

      <v-expand-transition>
        <div v-show="isExpanded">
          <v-table density="compact" class="json-table">
            <tbody>
              <tr v-for="(value, key) in data" :key="key" class="json-row">
                <!-- <td class="json-key" v-if="!window.__isObject(value)">{{ key }}</td> -->
                <td class="json-key">{{ key }}</td>
                <td class="json-value">
                  <ue-recursive-data-viewer
                    :data="value"
                    :array-index="0"
                    :object-depth="objectDepth + 1"
                    :all-array-items-closed="allArrayItemsClosed"
                    :all-array-items-open="allArrayItemsOpen"
                    :all-objects-closed="allObjectsClosed"
                    :all-objects-open="allObjectsOpen"

                    :object-titlex="window.__isObject(value) ? key : null"
                  />
                </td>
              </tr>
            </tbody>
          </v-table>
        </div>
      </v-expand-transition>
    </v-card>
  </template>

  <template v-else>
    <span>{{ data }}</span>
  </template>
  <!-- <div>
  </div> -->
</template>

<script>
  export default {
    name: 'ue-recursive-data-viewer',
    props: {
      data: {
        type: [Array, Object, String, Number, Boolean],
        required: true
      },
      allArrayItemsClosed: {
        type: Boolean,
        default: false
      },
      allArrayItemsOpen: {
        type: Boolean,
        default: false
      },
      arrayIndex: {
        type: Number,
        default: null
      },
      objectDepth: {
        type: Number,
        default: 0
      },
      allObjectsClosed: {
        type: Boolean,
        default: false
      },
      allObjectsOpen: {
        type: Boolean,
        default: false
      },
      objectTitle: {
        type: String,
        default: null
      }
    },
    data() {
      return {
        isExpanded: this.determineInitialExpanded(),
        expandedItems: new Set(this.determineInitialExpandedItems())
      }
    },
    methods: {
      determineInitialExpanded() {
        if (Array.isArray(this.data)) {
          if (this.allArrayItemsClosed) return false;
          if (this.allArrayItemsOpen) return true;
          return this.arrayIndex === 0;
        }
        if (typeof this.data === 'object' && this.data !== null) {
          if (this.allObjectsClosed) return false;
          if (this.allObjectsOpen) return true;
          // Expand first object regardless of array position
          return this.objectDepth === 0;
        }
        return true;
      },
      determineInitialExpandedItems() {
        if (this.allArrayItemsClosed) return [];
        if (this.allArrayItemsOpen) return Array.from({ length: this.data.length }, (_, i) => i);
        return [0]; // Only first item expanded by default
      },
      toggleItem(index) {
        if (this.expandedItems.has(index)) {
          this.expandedItems.delete(index);
        } else {
          this.expandedItems.add(index);
        }
      },
      isItemExpanded(index) {
        return this.expandedItems.has(index);
      },
      getPreview(item) {
        if (Array.isArray(item)) return `Array(${item.length})`;
        if (typeof item === 'object' && item !== null) return 'Object';
        if (typeof item === 'string') return `"${item.substring(0, 30)}${item.length > 30 ? '...' : ''}"`;
        return String(item);
      }
    }
  }
</script>

<style scoped>
.json-table {
  font-family: monospace;
}

.json-key-title {
  color: #881391;
  white-space: nowrap;
}
.json-key {
  color: #881391;
  white-space: nowrap;
  vertical-align: top;
  padding-top: 10px !important;
  width: 1%;
  padding-right: 16px !important;
}

.json-value {
  width: 99%;
}

.collapsible-header {
  cursor: pointer;
  background-color: #f5f5f5;
}

.collapsible-header:hover {
  background-color: #eeeeee;
}

:deep(.v-table) {
  background: transparent !important;
}

:deep(.v-table .v-table__wrapper > table > tbody > tr:not(:last-child) > td),
:deep(.v-table .v-table__wrapper > table > tbody > tr:not(:last-child) > th) {
  border-bottom: none;
}

.array-items {
  padding: 0 8px;
}

.array-item:last-child {
  margin-bottom: 8px;
}

.array-index {
  color: #b58900;
  margin-right: 8px;
  font-family: monospace;
}

.array-preview {
  color: #666;
  font-size: 0.9em;
}

.collapsible-header {
  cursor: pointer;
  background-color: #f5f5f5;
  border-radius: 4px;
}

.collapsible-header:hover {
  background-color: #eeeeee;
}
</style>
