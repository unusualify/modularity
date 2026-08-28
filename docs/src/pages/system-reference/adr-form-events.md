---
sidebarPos: 16
sidebarTitle: ADR — Form events
---

# ADR: Form events (`ext` → `formEvents`)

**Status:** Accepted, incremental  
**Date:** 2026-08-28

## Context

Admin inputs declared cross-field behaviour with **`ext`**. Two PHP compilers (trait vs helper) drifted. Vue `handleEvents` dispatched `formEventFormatters` from schema **`event`**.

Problems:

1. **`ext` is overloaded.** Event DSL (`set`, `update`, `lock`, …) shares the key with type aliases (`date`, `time`, `price`, `scroll`, `number`, `relationship`, `morphTo`).
2. **Two PHP compilers** disagreed (`set` used undefined `$targetPropName` in the helper).
3. **Two JS paths:** `handleEvents` + formatters vs legacy `handleInputEvents` switch (permalink/lock).
4. **Translated vs scalar.** `set`/`update` with `modelValue` copied locale maps onto string fields (issue #51).

## Decision

### Canonical config key: `formEvents`

Input config uses **`formEvents`** (pipe string or nested arrays, same shapes as the old `ext` DSL).

### `ext` fallback until v14

If **`formEvents` is absent**, compile event DSL from **`ext`**. When both are present, **`formEvents` wins**. Compiling from `ext` emits a **v13** deprecation (`unusualify/modularous` 13.0). **v14 removes `ext` as an event source.**

### Type aliases stay out of the compiler

`date` | `time` | `price` | `softCurrency` | `scroll` | `number` | `relationship` | `morphTo` are **not** form events. They must be migrated to `type` / hydrate options **before** v14 deletes `ext`.

### Runtime schema

`FormEventCompiler` dual-writes schema **`formEvents[]`** (compiled `formatXxx:args` tokens) and legacy **`event`** (pipe). Vue `resolveFormEventTokens` reads `formEvents` first, then `event`.

### Compiler

One PHP `FormEventCompiler` (`src/Hydrates/FormEventCompiler.php`). `FormSchema::hydrateInputExtension` and `hydrate_input_extension()` call it.

### Later (v14)

1. Remove `ext` event fallback.
2. Drop `ext` entirely after type aliases have a home.
3. Optionally retire `handleInputEvents` (legacy permalink/lock); formatters remain the runtime path.

## Consequences

- New module / package inputs write **`formEvents`**. Host apps may keep `ext` until they migrate; they will see the v13 warning.
- Do not migrate `b2press-app` from this package repo.
- Issue #51 coerce stays independent of the v14 cut.

## Related

- Guide: [Form events](/guide/js/form-events)
- `src/Hydrates/FormEventCompiler.php`
- `vue/src/js/utils/formEvents.js`, `resolveFormEventTokens.js`, `formEventFormatters/`
