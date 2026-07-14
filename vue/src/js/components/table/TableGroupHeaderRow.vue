<template>
  <tr
    class="v-data-table-group-header-row ue-table-group-header-row"
    :style="{ '--v-data-table-group-header-row-depth': group.depth }"
  >
    <td
      :colspan="columns.length"
      class="ue-table-group-header-td v-data-table__td"
    >
      <div class="ue-table-group-header d-flex flex-nowrap align-center ga-2 w-100">
        <v-checkbox-btn
          v-if="showSelect"
          class="flex-shrink-1 flex-grow-0"
          :model-value="modelValue"
          :indeterminate="indeterminate"
          @update:model-value="selectGroup"
        />
        <v-btn
          class="flex-shrink-0"
          :icon="toggleIcon"
          variant="text"
          density="compact"
          size="small"
          @click="toggleGroup(group)"
        />
        <div class="ue-table-group-header__label d-flex min-width-0 justify-start text-start text-body-2">
          <TableFormatterCell
            v-if="formatterColumn"
            :col="formatterColumn"
            :item="syntheticItem"
            :cell-value="group.value"
            :handle-formatter="handleFormatter"
            :item-action="itemAction"
            :cell-options="cellOptions"
            :clickable-row="false"
            :group-context="true"
            :disable-tooltip="disableFormatterTooltip"
          />
          <span v-else class="text-truncate">{{ group.value }}</span>
        </div>
        <span class="ue-table-group-header__count text-medium-emphasis text-caption flex-shrink-0 text-no-wrap">({{ rowCount }})</span>
      </div>
    </td>
  </tr>
</template>

<script>
import { computed } from 'vue'
import { VBtn } from 'vuetify/lib/components/VBtn/index.js'
import { VCheckboxBtn } from 'vuetify/lib/components/VCheckbox/index.js'
import { useGroupBy } from 'vuetify/lib/components/VDataTable/composables/group.js'
import { useSelection } from 'vuetify/lib/components/VDataTable/composables/select.js'

import TableFormatterCell from '__components/table/TableFormatterCell.vue'

export default {
  name: 'TableGroupHeaderRow',
  components: {
    VBtn,
    VCheckboxBtn,
    TableFormatterCell
  },
  props: {
    group: {
      type: Object,
      required: true
    },
    columns: {
      type: Array,
      required: true
    },
    showSelect: {
      type: Boolean,
      default: false
    },
    formatterColumn: {
      type: Object,
      default: null
    },
    syntheticItem: {
      type: Object,
      required: true
    },
    handleFormatter: {
      type: Function,
      required: true
    },
    itemAction: {
      type: Function,
      required: true
    },
    cellOptions: {
      type: Object,
      required: true
    },
    /** When true, formatter cells skip `v-tooltip` (same as data table mobile row layout). */
    disableFormatterTooltip: {
      type: Boolean,
      default: false
    }
  },
  setup (props) {
    const { extractRows, toggleGroup, isGroupOpen } = useGroupBy()
    const { isSelected, isSomeSelected, select } = useSelection()

    const rows = computed(() => extractRows([props.group]))
    const rowCount = computed(() => rows.value.length)

    const modelValue = computed(() => isSelected(rows.value))
    const indeterminate = computed(() => isSomeSelected(rows.value) && !modelValue.value)

    function selectGroup (v) {
      select(rows.value, v)
    }

    const toggleIcon = computed(() => (isGroupOpen(props.group) ? '$expand' : '$next'))

    return {
      rows,
      rowCount,
      modelValue,
      indeterminate,
      selectGroup,
      toggleGroup,
      toggleIcon
    }
  }
}
</script>
