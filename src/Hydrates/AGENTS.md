# Hydrates — Agent Rules

Hydrates turn module input config into frontend schema. Vue inputs consume that schema.

## Flow

```
Module config (type: 'checklist') → InputHydrator → ChecklistHydrate
  → schema { type: 'input-checklist', ... }
  → FormBaseField → mapTypeToComponent → VInputChecklist
```

## Hard rules

1. New input type = PHP Hydrate **and** Vue `components/inputs/{Studly}.vue` together.
2. Resolve class: `studlyName($input['type']) . 'Hydrate'` under `src/Hydrates/Inputs/`.
3. In `hydrate()`, set `$input['type'] = 'input-{kebab}'` (unless mapping to a stock Vuetify type like `select`).
4. Define `$requirements` for default schema keys; strip backend-only keys before frontend: `route`, `model`, `repository`, `cascades`, `connector`.
5. Vue side: `useInput`, `makeInputProps`, `makeInputEmits` from `@/hooks`; registers as `VInput{Studly}`. Optional: `hydrateTypeMap` in `registry.js`.

## Scaffold

```bash
php artisan modularous:make:input-hydrate {Name}
# + vue input (make:vue-input or manual)
```

## Read before edit

- `docs/src/pages/system-reference/hydrates.md`
- `docs/src/pages/guide/form-inputs/overview.md`
- Frontend companion: `vue/src/js/AGENTS.md`
