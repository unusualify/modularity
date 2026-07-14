---
sidebarPos: 45
sidebarTitle: Form Summary Item
---
# Form Summary Item

`ue-form-summary-item` renders a single step's summary in the stepper form review panel. It displays a numbered step label, the step title, and a formatted view of the step's model values.

## Usage

This component is rendered automatically by `ue-stepper-form` in the final "Preview & Summary" step. You can also override the default slot for a custom summary:

```html
<ue-stepper-form ...>
  <template #summary-form-1="{ index, title, model }">
    <ue-form-summary-item :index="index" :title="title" :model="model" />
  </template>
</ue-stepper-form>
```

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `index` | `Number` | yes | Zero-based step index. Displayed as `index + 1` |
| `title` | `String` | yes | Step title shown below the step number |
| `model` | `Object` | yes | Step form model values to display. Each key-value pair is rendered as a chip or labelled text |

## Behaviour

- Each key in `model` is iterated. If the value is an array, the first element is used as a label and subsequent elements are displayed inline.
- Non-array primitive values are rendered as read-only outlined `v-btn` chips.
- The step number badge uses a 25%-border-radius avatar styled in the primary colour.
