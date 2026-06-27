---
sidebarTitle: useActiveTableItem
---

# useActiveTableItem

Manages the selected row in a table and the associated detail/side panel. When a row is selected (`modelValue`), a detail panel opens; clicking outside or calling `closeItemDetails` clears the selection.

**File:** `vue/src/js/hooks/useActiveTableItem.js`  
**Props factory:** `makeActiveTableItemProps`

---

## Usage

```js
import { useActiveTableItem, makeActiveTableItemProps } from '@/hooks'

const props = defineProps({ ...makeActiveTableItemProps() })
const {
  item,
  modalStatus,
  activeKey,
  activeBlock,
  items,
  selectNested,
  clickOutside,
  closeItemDetails
} = useActiveTableItem(props, context)
```

```html
<!-- In a data-table -->
<v-data-table
  v-model="item"
  :items="tableItems"
  @click:outside="clickOutside"
/>

<!-- Detail panel -->
<v-navigation-drawer v-model="modalStatus">
  <div v-if="activeBlock">{{ activeBlock }}</div>
</v-navigation-drawer>
```

## Props (via `makeActiveTableItemProps`)

Extends `makeModelValueProps` plus:

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `String\|Number\|Object\|Boolean` | — | The currently selected row (two-way via `v-model`) |
| `tableHeaders` | `Array` | `[]` | Table header definitions |
| `itemData` | `Object` | `{}` | A map of data blocks keyed by a string; accessed via `activeKey` |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `item` | `ComputedRef` | Two-way proxy for `modelValue` — set to `null` to deselect |
| `modalStatus` | `ComputedRef<Boolean>` | True when a row is selected and the detail panel is open |
| `modalOpened` | `Ref<Boolean>` | Whether the modal has been opened at least once |
| `modalActive` | `Ref<Boolean>` | Whether the modal content area is active |
| `activeKey` | `Ref<String\|null>` | The currently selected nested data block key |
| `activeBlock` | `ComputedRef<any>` | `itemData[activeKey]` — the resolved nested data |
| `items` | `ComputedRef<Array>` | `[item.value]` when a row is selected, `[]` otherwise |
| `selectNested` | `(key) => void` | Set `activeKey` and emit `toggle(true)` |
| `clickOutside` | `(event) => void` | Clear `item` and `activeKey` |
| `closeItemDetails` | `(key?) => void` | Close the nested block view; emit `toggle(false)` |

## Reactive behaviour

| Trigger | Effect |
|---------|--------|
| `item` changes to a non-null value | `modalActive` becomes `true` |
| `item` becomes `null` | `modalActive` becomes `false` |
| `activeKey` is set | `modalActive` becomes `false` (switches to nested view) |

## See Also

- [useModelValue](/system-reference/frontend/composables/use-model-value) — `makeModelValueProps` is used here
- [Data Tables](/guide/components/shared/data-tables)
