---
sidebarPos: 1
sidebarTitle: Overview
---

# Forms Overview

Modularous forms are schema-driven. The backend defines inputs; Hydrates transform them into a frontend schema; Vue components consume and render them.

## What Are Hydrates?

**Hydrates** are PHP classes that sit between your module config and the frontend form. Each input type has its own Hydrate class that transforms a raw config array into a well-defined schema object the Vue side can render.

```
Module config  →  InputHydrator  →  XxxHydrate  →  schema  →  Vue component
{ type: 'checklist', ... }              ↓          { type: 'input-checklist', items: [...], ... }
                               resolves class name
```

### What they do?

- **Set defaults** — fill in missing keys (e.g. `itemValue: 'id'`, `itemTitle: 'name'`)  
- **Change the type** — convert `'checklist'` (config alias) → `'input-checklist'` (frontend type)  
- **Load records** — call a repository or connector to populate `items` for selectable inputs  
- **Apply rules** — parse `rules` string and add CSS classes (e.g. `required`)  
- **Strip backend keys** — remove `route`, `model`, `repository`, `cascades`, `connector` before the schema reaches the frontend

### Why they exist?

The Hydrate layer decouples module config syntax from frontend schema syntax. You write concise config in PHP; the Hydrate handles enrichment, fetching, and normalization. The frontend only ever sees a clean, complete schema object.

### Resolution rule

`InputHydrator` resolves the class by: `studlyCase($input['type']) . 'Hydrate'`

| Config `type` | Resolved class |
|---------------|----------------|
| `checklist` | `ChecklistHydrate` |
| `select-scroll` | `SelectScrollHydrate` |
| `filepond-avatar` | `FilepondAvatarHydrate` |

All Hydrate classes live in `src/Hydrates/Inputs/`.

## Render Pipeline

Every Hydrate runs the same pipeline when `render()` is called:

```
setDefaults()     — apply $requirements defaults
hydrate()         — set output type, enrich schema
hydrateRecords()  — load items via repository/connector
hydrateRules()    — parse rules string, add CSS classes
Arr::except()     — strip backend-only keys
```

## Form Rendering Flow

1. **Module config** — define inputs in your module's `config.php`
2. **Controller** — `setupFormSchema()` calls `InputHydrator` before create/edit
3. **Inertia** — hydrated schema + model are passed to the page
4. **Form.vue** — receives `schema` and `modelValue`, uses `useForm`
5. **FormBase** — flattens schema + model into `flatCombinedArraySorted`, iterates over each field
6. **FormBaseField** — renders each field by `obj.schema.type` via `mapTypeToComponent()`
7. **Input components** — receive schema props via `bindSchema(obj)`

## Key Components

| Component | Purpose |
|-----------|---------|
| **Form.vue** | Top-level form; validation, submit, schema/model sync |
| **FormBase** | Iterates over flattened schema; grid layout, slots |
| **FormBaseField** | Renders a single field; resolves type → component |
| **CustomFormBase** | Wrapper with app-specific behavior |

## Schema Structure

Each field in the schema has:

- `type` — Resolved to Vue component (e.g. `input-checklist`, `text`, `select`)
- `name` — Field name (binds to model)
- `label` — Display label
- `col` — Grid column span
- `rules` — Validation rules
- `default` — Default value

**Backend-only keys** (stripped before frontend): `route`, `model`, `repository`, `cascades`, `connector`

## Config → Component Reference

| Config type | Hydrate class | Output type | Vue component |
|-------------|---------------|-------------|---------------|
| [assignment](/guide/form-inputs/input-assignment) | AssignmentHydrate | input-assignment | VInputAssignment |
| [autocomplete](/guide/form-inputs/input-autocomplete) | AutocompleteHydrate | select / input-select-scroll | v-autocomplete |
| [browser](/guide/form-inputs/input-browser) | BrowserHydrate | input-browser | VInputBrowser |
| [chat](/guide/form-inputs/input-chat) | ChatHydrate | input-chat | VInputChat |
| [checkbox](/guide/form-inputs/input-checkbox) | CheckboxHydrate | checkbox | v-checkbox |
| [checklist](/guide/form-inputs/input-checklist) | ChecklistHydrate | input-checklist | VInputChecklist |
| [checklist-group](/guide/form-inputs/input-checklist-group) | ChecklistGroupHydrate | input-checklist-group | VInputChecklistGroup |
| [combobox](/guide/form-inputs/input-combobox) | ComboboxHydrate | combobox / input-select-scroll | v-combobox |
| [comparison-table](/guide/form-inputs/input-comparison-table) | ComparisonTableHydrate | input-comparison-table | VInputComparisonTable |
| [date](/guide/form-inputs/input-date) | DateHydrate | input-date | VInputDate |
| [file](/guide/form-inputs/input-file) | FileHydrate | input-file | VInputFile |
| [filepond-avatar](/guide/form-inputs/input-filepond-avatar) | FilepondAvatarHydrate | input-filepond-avatar | VInputFilepondAvatar |
| [form-tabs](/guide/form-inputs/input-form-tabs) | FormTabsHydrate | input-form-tabs | VInputFormTabs |
| [image](/guide/form-inputs/input-image) | ImageHydrate | input-image | VInputImage |
| [json](/guide/form-inputs/input-json) | JsonHydrate | group | (group layout) |
| [json-repeater](/guide/form-inputs/input-json-repeater) | JsonRepeaterHydrate | input-repeater | VInputRepeater |
| [payment-service](/guide/form-inputs/input-payment-service) | PaymentServiceHydrate | input-payment-service | VInputPaymentService |
| [price](/guide/form-inputs/input-price) | PriceHydrate | input-price | VInputPrice |
| [process](/guide/form-inputs/input-process) | ProcessHydrate | input-process | VInputProcess |
| [radio-group](/guide/form-inputs/input-radio-group) | RadioGroupHydrate | input-radio-group | VInputRadioGroup |
| [repeater](/guide/form-inputs/input-repeater) | RepeaterHydrate | input-repeater | VInputRepeater |
| [select-scroll](/guide/form-inputs/input-select-scroll) | SelectScrollHydrate | input-select-scroll | VInputSelectScroll |
| [source-text](/guide/form-inputs/input-source-text) | SourceTextHydrate | input-source-text | VInputSourceText |
| [spread](/guide/form-inputs/input-spread) | SpreadHydrate | input-spread | VInputSpread |
| [switch](/guide/form-inputs/input-switch) | SwitchHydrate | input-switch | VInputSwitch |
| [tag](/guide/form-inputs/input-tag) | TagHydrate | input-tag | VInputTag |
| [tagger](/guide/form-inputs/input-tagger) | TaggerHydrate | input-tagger | VInputTagger |

## FormBase Slots

FormBase provides slots for customization:

- `form-top`, `form-bottom` — Form-level
- `{type}-top`, `{type}-bottom` — By schema type (e.g. `input-checklist-top`)
- `{key}-top`, `{key}-bottom` — By field name
- `{type}-item`, `{key}-item` — Override field rendering

## Adding a New Input

1. **PHP** — Create `src/Hydrates/Inputs/{Studly}Hydrate.php` extending `InputHydrate`
   - Set `$input['type'] = 'input-{kebab}'` in `hydrate()`
   - Define `$requirements` for default schema keys
2. **Vue** — Create `vue/src/js/components/inputs/{Studly}.vue`
   - Use `useInput`, `makeInputProps`, `makeInputEmits` from `@/hooks`
   - Component auto-registers as `VInput{Studly}` via `includeFormInputs` glob
3. **Registry** (optional) — Add to `hydrateTypeMap` in `registry.js` for explicit mapping

See the [create-input-hydrate](/guide/console/generators/create-input-hydrate) and [create-vue-input](/guide/console/generators/create-vue-input) commands.
