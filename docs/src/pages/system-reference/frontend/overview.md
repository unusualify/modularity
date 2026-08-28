---
sidebarPos: 6
sidebarTitle: Frontend
---

# Frontend

## Directory Structure

```
vue/src/js/
├── components/       # inputs, layouts, table, modals, form
├── hooks/            # useForm, useTable, useInput, etc.
├── utils/            # schema, helpers, getFormData
└── store/            # Vuex (config, user, language, etc.)
```

## Component Organization

| Location | Purpose |
|----------|---------|
| `components/inputs/` | Form input components |
| `components/layouts/` | Main, Sidebar, Home |
| `components/table/` | Table, TableActions |
| `components/modals/` | Modal, DynamicModal, ModalMedia |
| `components/customs/` | App-specific overrides (UeCustom*) |
| `components/labs/` | **Experimental** — not guaranteed stable |

## Form Flow

1. **Form.vue** — receives `schema` and `modelValue`, uses `useForm`
2. **FormBase** — iterates over `flatCombinedArraySorted` (flattened schema + model)
3. **FormBaseField** — renders each field by `obj.schema.type`:
   - Special cases: `preview`, `dynamic-component`, `title`, `radio`, `array`, `wrap`/`group`
   - Default: `<component :is="mapTypeToComponent(obj.schema.type)" v-bind="bindSchema(obj)" />`
4. **Input components** — receive `obj.schema` via `bindSchema(obj)`

## Table Flow

1. **Table.vue** — uses `useTable`, passes props to `v-data-table-server`
2. **useTable** — orchestrates:
   - `useTableItem` — edited item, create/edit/delete
   - `useTableHeaders` — column definitions
   - `useTableFilters` — search, main filters, advanced filters
   - `useTableForms` — form modal open/close
   - `useTableItemActions` — row actions
   - `useTableModals` — dialogs
3. **store/api/datatable.js** — axios calls for index, delete, restore, bulk actions

## Input Registry

`components/inputs/registry.js`:

- **builtInTypeMap** — Vuetify primitives (`text` → `v-text-field`, etc.)
- **hydrateTypeMap** — Hydrate output types → custom components
- **customTypeMap** — App-registered via `registerInputType(type, component)`

```js
import { registerInputType, mapTypeToComponent } from '@/components/inputs/registry'
registerInputType('my-input', 'VMyInput')
const component = mapTypeToComponent('my-input') // => 'VMyInput'
```

## Hooks

For the full hook reference including all 38 composables, see **[Vue Hooks](/system-reference/frontend/composables/overview)**.

| Hook | Purpose |
|------|---------|
| useForm | Form state, validation, submit, schema/model sync |
| useFormBaseLogic | Form base logic for FormBase |
| useInput | Input state, modelValue, boundProps from schema |
| useTable | Main table composable |
| useTableItem, useTableHeaders, useTableFilters | Table sub-hooks |
| useValidation | Validation rules, invokeRuleGenerator |
| useCurrency, useCurrencyNumber | Currency formatting |
| useMediaLibrary, useMediaItems | Media selection |
| useConfig, useUser, useLocale | App state |
| [useAlert](/system-reference/frontend/composables/use-alert) | Global alert notifications |
| [useAuthorization](/system-reference/frontend/composables/use-authorization) | Permission and role checks |
| [useCache](/system-reference/frontend/composables/use-cache) | Client-side key-value cache |
| [useCastAttributes](/system-reference/frontend/composables/use-cast-attributes) | Dynamic `$notation` attribute interpolation |
| [useDraggable](/system-reference/frontend/composables/use-draggable) | Sortable drag-and-drop props |
| [useDynamicModal](/system-reference/frontend/composables/use-dynamic-modal) | Inject-based global modal service |
| [useFile](/system-reference/frontend/composables/use-file) / [useImage](/system-reference/frontend/composables/use-image) | File / image media-library inputs |
| [useFilepond](/system-reference/frontend/composables/use-filepond) | FilePond upload props and rules |
| [useFormatter](/system-reference/frontend/composables/use-formatter) | Table column value formatters |
| [useInertiaRequests](/system-reference/frontend/composables/use-inertia-requests) | In-flight Inertia request state |
| [useInputFetch](/system-reference/frontend/composables/use-input-fetch) | Paginated remote fetch for select inputs |
| [useInputHandlers](/system-reference/frontend/composables/use-input-handlers) | Slot-driven input click handlers |
| [useItemActions](/system-reference/frontend/composables/use-item-actions) | Form action buttons |
| [useModal](/system-reference/frontend/composables/use-modal) | Modal open/close, width, fullscreen |
| [useModelValue](/system-reference/frontend/composables/use-model-value) | `v-model` two-way binding helper |
| [useModule](/system-reference/frontend/composables/use-module) | Module i18n name and permission key |
| [useNavigationLayout](/system-reference/frontend/composables/use-navigation-layout) | Topbar / bottom-nav config |
| [useRandKey](/system-reference/frontend/composables/use-rand-key) | Unique component instance key |
| [useRepeater](/system-reference/frontend/composables/use-repeater) | Repeater block state management |
| [useSidebar](/system-reference/frontend/composables/use-sidebar) | Sidebar open/close, rail, resize |
| [useSvg](/system-reference/frontend/composables/use-svg) | SVG symbol utilities |
| [useActiveTableItem](/system-reference/frontend/composables/use-active-table-item) | Active row / detail-panel state |

## Utils

| File | Purpose |
|------|---------|
| schema.js | isViewOnlyInput, processInputs, flattenGroupSchema |
| getFormData.js | getSchema, getModel, getSubmitFormData |
| helpers.js | isset, isObject, dataGet (prefer over window.__*) |
| formEvents.js | `resolveFormEventTokens` (`formEvents[]` then `event`), handleEvents — [guide](/guide/js/form-events) |
| formEventFormatters/ | set, update, filter, lock, … (+ translated/scalar coerce) |

## Store (Vuex)

Modules: config, user, language, alert, media-library, browser, cache, ambient

API modules: `store/api/datatable.js`, `store/api/form.js`, `store/api/media-library.js`

## Schema Contract

See [Hydrates](../hydrates#schema-contract) for common schema keys. Frontend receives schema via Inertia; FormBase flattens and combines with model before rendering.
