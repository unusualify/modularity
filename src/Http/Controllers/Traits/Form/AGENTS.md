# Form schema / input events

`FormEventCompiler` compiles input **`formEvents`** (canonical) or **`ext`** (deprecated event DSL) into frontend schema **`formEvents[]`** + legacy **`event`** pipe (`formatSet:…|formatUpdate:…`).

Trait `FormSchema::hydrateInputExtension` and helper `hydrate_input_extension()` both call the compiler. Do not grow a second switch.

## Hard rules

1. Config key is **`formEvents`** (pipe string or nested arrays). If `formEvents` is absent, compile event DSL from **`ext`**. When both are present, **`formEvents` wins**.
2. `ext` type aliases are **not** events: `date`, `time`, `price`, `softCurrency`, `scroll`, `number`, `relationship`, `morphTo`. They skip the compiler. They need a separate home before `ext` can be deleted in v14.
3. Compiling from `ext` emits a v13 deprecation (`unusualify/modularous` 13.0). v14 removes `ext` as an event source.
4. New event method = `FormEventCompiler` match arm that emits `format{Name}:…` **and** a JS formatter (`vue/src/js/utils/formEventFormatters/AGENTS.md`).
5. Schema dual-write: `formEvents` (token array) and `event` (pipe). JS reads `formEvents` first.
6. Host apps (`b2press-app`) are examples only — do not edit them from this package.

## Read before edit

- `docs/src/pages/guide/js/form-events.md`
- `docs/src/pages/system-reference/adr-form-events.md`
- Vue companion: `vue/src/js/utils/formEventFormatters/AGENTS.md`
- Compiler: `src/Hydrates/FormEventCompiler.php`
