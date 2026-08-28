# Form event formatters

PHP `FormEventCompiler` writes schema `formEvents[]` + legacy `event` (pipe of `formatXxx:args`). Runtime: `handleEvents` / `handleMultiFormEvents` in `formEvents.js` resolve tokens via `resolveFormEventTokens` (prefer `formEvents`, then `event`) and look up this folder.

## Hard rules

1. New event method = PHP `FormEventCompiler` case **and** `format{Name}.js` **and** export in `index.js`. Do not add cases to the legacy `handleInputEvents` switch.
2. Formatter signature: `(args, model, schema, input, index = null, preview = [])`. `args` is already split; `getInputToFormat` mutates it with `shift()`.
3. `set` runs on create hydrate and on input. `update` (and `clearModel`) skip when `valueChanged` is false (`nonRunEventsOnCreate`).
4. Target path is **schema notation** (`wrap1.schema.slugs`). `modelValue` / `model` writes the model; anything else writes a schema prop.
5. Translated model values are locale maps `{ en, tr, … }`; scalars are strings. Cross-shape copies must go through `coerceFormEventValue` — never `_.set` an object onto a scalar field.
6. Translated → scalar uses the **fallback** locale by default (not the language tab). Optional trailing token: `en`, `tr`, `fallback`, or `locale.en`. Skip the write when that locale’s string did not change.
7. Do not revive the old `formatSet` translations TODO (`notation` / `inputToFormat` were undefined).

## Read before edit

- `docs/src/pages/guide/js/form-events.md`
- `docs/src/pages/system-reference/adr-form-events.md`
- PHP companion: `src/Http/Controllers/Traits/Form/AGENTS.md`
- Compiler: `src/Hydrates/FormEventCompiler.php`
