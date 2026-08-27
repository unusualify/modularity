# Form schema / input events

`FormSchema::hydrateInputExtension` compiles input `ext` into frontend schema `event` (`formatSet:…|formatUpdate:…`).

## Hard rules

1. Today the config key is **`ext`**. Canonical **future** key is **`events`**; when `events` is absent, keep reading event DSL from `ext` until v14.
2. `ext` type aliases are **not** events: `date`, `time`, `price`, `softCurrency`, `scroll`, `number`, `relationship`, `morphTo`. They must not go through the event compiler. They need a separate home before `ext` can be deleted in v14.
3. Do not grow a second compiler in `src/Helpers/input.php` `hydrate_input_extension()` — it already drifts from this trait (undefined `$targetPropName` / `modelNotation` on `set`). Later phase: one `FormEventCompiler`.
4. New event method = switch case here that emits `format{Name}:…` **and** a JS formatter (`vue/src/js/utils/formEventFormatters/AGENTS.md`).
5. Pipe string (`a|b`) and nested arrays (`[['update', 'slugs', …]]`) are both valid `ext` / future `events` shapes.
6. Host apps (`b2press-app`) are examples only — do not edit them from this package.

## Read before edit

- `docs/src/pages/guide/js/form-events.md`
- `docs/src/pages/system-reference/adr-form-events.md`
- Vue companion: `vue/src/js/utils/formEventFormatters/AGENTS.md`
