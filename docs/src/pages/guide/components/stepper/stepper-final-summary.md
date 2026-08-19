---
sidebarPos: 5
sidebarTitle: Stepper Final Summary
---

# StepperFinalSummary

`StepperFinalSummary` is the right-column card rendered on the final step. It provides a structured layout for a body section, a total row, a description, and a **Complete Request** button.

> [!NOTE]
> This is an internal sub-component of `ue-stepper-form`. You do not use it directly. Customise it via the `summary.final.body`, `summary.final.total`, `summary.final.total.label`, and `summary.final.description` slots on `ue-stepper-form`.

## Layout

```
┌─────────────────────────────────┐
│  TOTAL AMOUNT                   │  ← fixed title (bg-primary-darken-2)
│  ─────────────────────────────  │
│  [body slot]                    │  ← line items, pricing breakdown, etc.
├─────────────────────────────────┤
│  [total.label]  [total]         │  ← total row
│  [description]                  │  ← caption text
│  [ COMPLETE REQUEST ]           │  ← submit button
└─────────────────────────────────┘
```

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `loading` | `Boolean` | `false` | Shows a loading spinner on the button and disables it. |
| `isCompleted` | `Boolean` | `false` | Disables the button permanently after a successful submission. |

## Emits

| Event | Payload | Description |
|---|---|---|
| `complete` | — | Emitted when the **Complete Request** button is clicked. Wired to `completeForm()` in `StepperForm`. |

## Slots

All slots are exposed on the parent `ue-stepper-form` via the `summary.final.*` namespace.

### `body`

Content injected between the title divider and the total row. Use this for line items, pricing breakdown tables, or any custom content.

```html
<!-- on ue-stepper-form -->
<template #summary.final.body="{ models, schemas, lastStepModel, finalFormFields, lastFormPreview }">
  <div v-for="item in lastFormPreview" :key="item.id" class="d-flex justify-space-between">
    <span>{{ item.name }}</span>
    <span>{{ item.price_formatted }}</span>
  </div>
</template>
```

| Binding | Description |
|---|---|
| `models` | All step model objects |
| `schemas` | All step schema arrays |
| `lastStepModel` | Currently selected final-step values |
| `finalFormFields` | The `finalFormFields` prop from the parent |
| `lastFormPreview` | Fetched items from the final-form endpoints |

### `total.label`

Overrides the "TOTAL" label text:

```html
<template #summary.final.total.label>
  Grand Total
</template>
```

### `total`

Overrides the total value cell. Receives `{ payload }` (the full merged payload that will be submitted):

```html
<template #summary.final.total="{ payload }">
  <span class="text-headline-small text-white">{{ payload.amount_formatted }}</span>
</template>
```

### `description`

Overrides the caption text below the total row:

```html
<template #summary.final.description>
  All prices are exclusive of VAT. Payment will be processed on confirmation.
</template>
```
