# Schema Type Components

Extract CustomFormBase schema types into dedicated components for maintainability.

## Hydrate Adapter

Backend hydrates (`src/Hydrates/Inputs/*`) output `schema.type = 'input-{kebab}'`. The registry maps these to Vue components via `hydrateTypeMap`. See AGENTS.md § HYDRATE ↔ INPUT ADAPTER.

## Registry

- **builtInTypeMap**: Vuetify primitives (text, checkbox, select, etc.)
- **hydrateTypeMap**: Hydrate output types → VInput components (input-checklist → VInputChecklist)
- **customTypeMap**: Runtime `registerInputType()` calls

```js
import { registerInputType, mapTypeToComponent } from '@/components/inputs/registry'

registerInputType('input-price', 'VInputPrice')
mapTypeToComponent('input-checklist') // => 'VInputChecklist' (from hydrateTypeMap)
```

## InputRenderer

`InputRenderer.vue` resolves schema type to component. Use with a form context that provides:
- bindSchema, setValue, onInput, onEvent, updateInput
- checkExtensionType, searchInputSync, suspendClickAppend
- getInjectedScopedSlots, getKeyInjectSlot

## Extraction Pattern

When extracting a type from CustomFormBase:
1. Create `schema-types/Input{Type}.vue`
2. Register via `registerInputType('type-name', 'InputType')` or add to builtInTypeMap in registry.js
3. Replace the v-else-if block in CustomFormBase with component resolution

When adding a Hydrate-backed input:
1. Create `src/Hydrates/Inputs/{Studly}Hydrate.php` (output `type: 'input-{kebab}'`)
2. Create `inputs/{Studly}.vue` (registers as VInput{Studly})
3. Add to `hydrateTypeMap` in registry.js
   (e.g. `input-source-text` → `VInputSourceText`)

## Extracted Types

- **title** → InputTitle.vue (display-only heading)
