---
sidebarPos: 44
sidebarTitle: Form Actions & Events
---
# Form Actions & Events

`ue-form-actions` and `ue-form-events` are the two sub-components that render the action and event button bars inside `ue-form`. They are also used standalone in data table toolbars and detail pages.

## `ue-form-actions`

Renders a horizontal group of action buttons from an `actions` configuration object. Supports plain buttons, publish switches, inline form modals, and badge overlays.

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `modelValue` | `Object` | yes | The current data record (used to build modal form models and pass to action handlers) |
| `actions` | `Object` | yes | Map of action definitions keyed by name (see Action Shape below) |
| `isEditing` | `Boolean` | — | Whether the context is an edit form — forwarded to inline form modals |

### Action Shape

```js
{
  type: 'button',           // 'button' | 'modal' | 'publish'
  label: 'Export',          // button label
  icon: 'mdi-export',       // MDI icon (makes it icon-only unless forceLabel: true)
  color: 'primary',
  endpoint: '/api/...',     // required for type: 'modal'
  schema: [...],            // ue-form schema for type: 'modal' inline form
  formTitle: 'Edit Notes',  // title for the inline form modal
  modalAttributes: {},      // props forwarded to ue-modal for type: 'modal'
  formAttributes: {},       // props forwarded to ue-form for type: 'modal'
  tooltip: 'Export CSV',    // tooltip text (defaults to label)
  disabled: false,
  badge: { content: 3, color: 'error' }, // optional v-badge config
}
```

### Events

| Event | Description |
|-------|-------------|
| `actionComplete` | Emitted after an inline form modal submits successfully |

### Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `prepend` | `{ item, isEditing }` | Content placed before the action buttons |
| `append` | `{ item, isEditing }` | Content placed after the action buttons |

---

## `ue-form-events`

Renders a row of event buttons (segmented controls, dropdowns, or toggle groups) defined by an `events` array. Used in `ue-form` to present contextual state-changing options alongside the main form.

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `events` | `Array` | yes | Array of event definitions (see Event Shape below) |
| `modelValue` | `Object` | yes | Current form model — event selections are read from and written to this |
| `formItem` | `Object` | — | The raw form field descriptor |

### Event Shape

```js
{
  name: 'status',           // key written to modelValue
  type: 'chip-group',       // 'chip-group' | 'select' | etc.
  label: 'Status',
  items: [
    { value: 'active', label: 'Active' },
    { value: 'draft',  label: 'Draft'  },
  ],
  itemValue: 'value',
  itemTitle: 'label',
}
```
