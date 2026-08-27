---
sidebarPos: 3
sidebarTitle: Hydrates
---

# Hydrates

Hydrates transform module config into frontend schema. The backend (PHP) and frontend (Vue) communicate via a **schema contract**: hydrates produce schema; input components consume it.

## Flow

```
Module config (type: 'checklist') → InputHydrator → ChecklistHydrate → schema { type: 'input-checklist', ... }
                                                                              ↓
FormBase/FormBaseField → mapTypeToComponent('input-checklist') → VInputChecklist (Checklist.vue)
```

1. **Module config** defines inputs with `type` (e.g. `checklist`, `select`, `price`)
2. **InputHydrator** resolves: `studlyName($input['type']) . 'Hydrate'` → e.g. `ChecklistHydrate`
3. **Hydrate** sets `$input['type'] = 'input-{kebab}'` and enriches schema (items, endpoint, rules, etc.)
4. **render()** pipeline: `setDefaults()` → `hydrate()` → `hydrateRecords()` → `hydrateRules()` → strips backend-only keys
5. **Frontend** receives schema via Inertia; FormBaseField uses `mapTypeToComponent(type)` → Vue component

## Resolution

| Config type | Hydrate class | Output type (schema) | Vue component |
|-------------|---------------|----------------------|---------------|
| [assignment](/guide/form-inputs/input-assignment) | AssignmentHydrate | input-assignment | VInputAssignment |
| authorize | AuthorizeHydrate | select | v-select (Vuetify) |
| [autocomplete](/guide/form-inputs/input-autocomplete) | AutocompleteHydrate | select / input-select-scroll | v-autocomplete (Vuetify) |
| [browser](/guide/form-inputs/input-browser) | BrowserHydrate | input-browser | VInputBrowser |
| [chat](/guide/form-inputs/input-chat) | ChatHydrate | input-chat | VInputChat |
| [checkbox](/guide/form-inputs/input-checkbox) | CheckboxHydrate | checkbox | v-checkbox (Vuetify) |
| [checklist](/guide/form-inputs/input-checklist) | ChecklistHydrate | input-checklist | VInputChecklist |
| [checklist-group](/guide/form-inputs/input-checklist-group) | ChecklistGroupHydrate | input-checklist-group | VInputChecklistGroup |
| [combobox](/guide/form-inputs/input-combobox) | ComboboxHydrate | combobox / input-select-scroll | v-combobox (Vuetify) |
| [comparison-table](/guide/form-inputs/input-comparison-table) | ComparisonTableHydrate | input-comparison-table | VInputComparisonTable |
| creator | CreatorHydrate | input-browser | VInputBrowser |
| [date](/guide/form-inputs/input-date) | DateHydrate | input-date | VInputDate |
| [file](/guide/form-inputs/input-file) | FileHydrate | input-file | VInputFile |
| filepond | FilepondHydrate | input-filepond | VInputFilepond |
| [filepond-avatar](/guide/form-inputs/input-filepond-avatar) | FilepondAvatarHydrate | input-filepond-avatar | VInputFilepondAvatar |
| [form-tabs (tab-group)](/guide/form-inputs/input-form-tabs) | FormTabsHydrate | input-form-tabs | VInputFormTabs |
| [image](/guide/form-inputs/input-image) | ImageHydrate | input-image | VInputImage |
| [json](/guide/form-inputs/input-json) | JsonHydrate | group | (group layout) |
| [json-repeater](/guide/form-inputs/input-json-repeater) | JsonRepeaterHydrate | input-repeater | VInputRepeater |
| [payment-service](/guide/form-inputs/input-payment-service) | PaymentServiceHydrate | input-payment-service | VInputPaymentService |
| [price](/guide/form-inputs/input-price) | PriceHydrate | input-price | VInputPrice |
| [process](/guide/form-inputs/input-process) | ProcessHydrate | input-process | VInputProcess |
| [radio-group](/guide/form-inputs/input-radio-group) | RadioGroupHydrate | input-radio-group | VInputRadioGroup |
| [repeater](/guide/form-inputs/input-repeater) | RepeaterHydrate | input-repeater | VInputRepeater |
| [relationships](/guide/form-inputs/input-relationships) | RelationshipsHydrate | input-relationships | VInputRelationships ⚠️ |
| select | SelectHydrate | select | v-select (Vuetify) |
| [select-scroll](/guide/form-inputs/input-select-scroll) | SelectScrollHydrate | input-select-scroll | VInputSelectScroll |
| [source-text](/guide/form-inputs/input-source-text) | SourceTextHydrate | input-source-text | VInputSourceText |
| [spread](/guide/form-inputs/input-spread) | SpreadHydrate | input-spread | VInputSpread |
| stateable | StateableHydrate | select | v-select (Vuetify) |
| [switch](/guide/form-inputs/input-switch) | SwitchHydrate | input-switch | VInputSwitch |
| [tag](/guide/form-inputs/input-tag) | TagHydrate | input-tag | VInputTag |
| [tagger](/guide/form-inputs/input-tagger) | TaggerHydrate | input-tagger | VInputTagger |
| ... | ... | input-{kebab} | VInput{Studly} |

**Rule**: `studlyName($input['type']) . 'Hydrate'` → class in `src/Hydrates/Inputs/`

## Hydrate Output Types (registry.js)

| Output type | Vue component |
|-------------|---------------|
| input-assignment | VInputAssignment |
| input-browser | VInputBrowser |
| input-chat | VInputChat |
| input-checklist | VInputChecklist |
| input-checklist-group | VInputChecklistGroup |
| input-comparison-table | VInputComparisonTable |
| input-date | VInputDate |
| input-file | VInputFile |
| input-filepond | VInputFilepond |
| input-filepond-avatar | VInputFilepondAvatar |
| input-form-tabs | VInputFormTabs |
| input-image | VInputImage |
| input-payment-service | VInputPaymentService |
| input-price | VInputPrice |
| input-process | VInputProcess |
| input-radio-group | VInputRadioGroup |
| input-repeater | VInputRepeater |
| input-select-scroll | VInputSelectScroll |
| input-source-text | VInputSourceText |
| input-spread | VInputSpread |
| input-tag | VInputTag |
| input-tagger | VInputTagger |

## Schema Contract

**Common keys** (frontend expects): `name`, `label`, `default`, `rules`, `items`, `itemValue`, `itemTitle`, `col`, `disabled`, `creatable`, `editable`

**Selectable**: `cascadeKey`, `cascades`, `repository`, `endpoint`

**Files**: `accept`, `maxFileSize`, `translated`, `max`

**Hydrate-only** (stripped before frontend): `route`, `model`, `repository`, `cascades`, `connector`

## Adding a New Input

1. **PHP**: Create `src/Hydrates/Inputs/{Studly}Hydrate.php` extending `InputHydrate`
   - Set `$input['type'] = 'input-{kebab}'` in `hydrate()` (or `select` for select-based hydrates like AuthorizeHydrate, StateableHydrate)
   - Define `$requirements` for default schema keys
2. **Vue**: Create `vue/src/js/components/inputs/{Studly}.vue`
   - Use `useInput`, `makeInputProps`, `makeInputEmits` from `@/hooks`
   - Component registers as `VInput{Studly}` via `includeFormInputs` glob
3. **Registry** (optional): Add to `hydrateTypeMap` in `registry.js` for explicit mapping

See the [create-input-hydrate](/guide/console/generators/create-input-hydrate) and [create-vue-input](/guide/console/generators/create-vue-input) commands.
