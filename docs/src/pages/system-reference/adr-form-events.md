---
sidebarPos: 16
sidebarTitle: ADR — Form events
---

# ADR: Form events (`ext` → `events`)

**Status:** Accepted, incremental  
**Date:** 2026-08-28

## Context

Admin inputs declare cross-field behaviour with **`ext`**. `FormSchema::hydrateInputExtension` (and a drifting copy in `hydrate_input_extension()`) compile that DSL to schema **`event`**. Vue `handleEvents` dispatches `formEventFormatters`.

Problems:

1. **`ext` is overloaded.** Event DSL (`set`, `update`, `lock`, …) shares the key with type aliases (`date`, `time`, `price`, `scroll`, `number`, `relationship`, `morphTo`).
2. **Two PHP compilers** (trait vs helper) already disagree (`set` uses undefined `$targetPropName` in the helper).
3. **Two JS paths:** `handleEvents` + formatters vs legacy `handleInputEvents` switch (permalink/lock).
4. **Translated vs scalar.** `set`/`update` with `modelValue` copied locale maps onto string fields. Issue #51 (title → admin name) needs a coerce step; a later compiler extract is not required for that fix.

## Decision

### Canonical config key: `events`

Future input config uses **`events`** (pipe string or nested arrays, same shapes as today’s `ext` DSL).

### `ext` fallback until v14

If **`events` is absent**, keep compiling event DSL from **`ext`**. When both are present, **`events` wins**. v14 removes `ext` as an event source.

### Type aliases stay out of the compiler

`date` | `time` | `price` | `softCurrency` | `scroll` | `number` | `relationship` | `morphTo` are **not** form events. They must be migrated to `type` / hydrate options **before** v14 deletes `ext`.

### Runtime schema

Today: schema **`event`** (pipe of `formatXxx:args`). Later: schema **`events[]`** plus dual-write `event` until JS reads the array first.

### Later phases (not this change)

1. Extract one PHP `FormEventCompiler`; trait + helper call it.
2. JS dispatcher prefers `input.events`, then `input.event`; retire `handleInputEvents` for permalink/lock.
3. Migrate package inputs from `ext` DSL to `events`.
4. Deprecation signal on DSL-`ext` without `events`.
5. v14: drop event fallback; drop `ext` after type aliases have a home.

### This change

Document the contract. Coerce translated ↔ scalar on `formatSet` / `formatUpdate` `modelValue` writes. Do not extract the compiler or add the `events` key yet.

## Consequences

- New docs and AGENTS tell authors to keep writing `ext` until the compiler lands.
- Host apps remain valid `ext` examples; do not migrate them in the package repo.
- Issue #51 can ship without waiting on v14.

## Related

- Guide: [Form events](/guide/js/form-events)
- `FormSchema::hydrateInputExtension`
- `vue/src/js/utils/formEvents.js`, `formEventFormatters/`
