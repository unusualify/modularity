---
sidebarPos: 2
sidebarTitle: Form events
---

# Form events (`ext`)

Cross-field form behaviour is declared on an input as **`ext`**. PHP compiles it to schema **`event`**. Vue `handleEvents` runs `vue/src/js/utils/formEventFormatters` when the source field hydrates or changes.

New module code will switch to an **`events`** config key later (`ext` stays as fallback until v14). Write **`ext`** today. See [ADR — Form events](/system-reference/adr-form-events).

## Pipeline

```
config ext  →  hydrateInputExtension  →  schema.event  →  handleEvents  →  formatXxx.js
```

Trigger:

1. `getModel` calls `handleEvents(..., valueChanged = false)` (create hydrate).
2. User input → `useForm.handleInput` → `handleEvents(..., true)`.

`update` and `clearModel` **do not** run on create hydrate. Use `update` when copying from a field the user is typing (e.g. title → admin name) so an edit form does not overwrite a stored scalar.

## Config shapes

Pipe string:

```php
'ext' => 'update:wrap1.schema.slugs:slugSourceValue:modelValue',
```

Several methods:

```php
'ext' => 'update:wrap1.schema.slugs:slugSourceValue:modelValue|update:wrap1.schema.name:modelValue:modelValue',
```

Nested array (same as PressRelease / PaymentService):

```php
'ext' => [
    ['toggleInput', 'transfer_details', 'items.*.transfer_details_toggleInputValue'],
],
```

## Methods

| `ext` method | Schema `event` | Typical target |
|--------------|----------------|----------------|
| `set` | `formatSet` | Schema prop or `modelValue` (also on create hydrate) |
| `update` | `formatUpdate` (same JS as `set`) | Same, **only** when the source value changed |
| `filter` | `formatFilter` | Load/filter another input’s `items` |
| `lock` | `formatLock` | Disable / placeholder from parent |
| `permalink` / `permalinkPrefix` | `formatPermalink*` | Slug prefix |
| `preview` | `formatPreview` | Preview fields |
| `clearModel` | `formatClearModel` | Reset model (not on create) |
| `resetItems` | `formatResetItems` | Empty items |
| `prependSchema` | `formatPrependSchema` | Inject schema |
| `removeValue` | `formatRemoveValue` | Drop a value |
| `toggleInput` | `formatToggleInput` | `d-none` / rules |

`ext` values that are **not** events (do not put these in `events` later): `date`, `time`, `price`, `scroll`, `number`, `relationship`, `morphTo`.

## Targets

The first argument after the method is **schema notation**, not always the model key:

- Nested wrap: `wrap1.schema.slugs`, `wrap1.schema.name`
- Schema property: `slugSourceValue` (slug input reads this; it is not `model.slugs`)
- Model field: second argument `modelValue` (or `model`)

Cms Page title → slug:

```php
['type' => 'text', 'name' => 'title', 'translated' => true, 'ext' => 'update:slugs:slugSourceValue:modelValue'],
['type' => 'slug', 'name' => 'slugs', 'translated' => true, …],
```

Blog title → slug **and** scalar admin name (`update` so edit does not clobber `name`). Trailing `fallback` copies **only** the fallback locale (usually `en`); typing `tr` / `nl` does not change `name`:

```php
'ext' => 'update:wrap1.schema.slugs:slugSourceValue:modelValue|update:wrap1.schema.name:modelValue:modelValue:fallback',
```

Pin a locale with `en`, `tr`, or `locale.en` instead of `fallback`.

## Translated vs scalar

`getModel` stores translated fields as `{ en: '…', tr: '…', nl: '…' }` and non-translated fields as strings.

When `set` / `update` writes **`modelValue`**:

| Source | Target | Result |
|--------|--------|--------|
| locale map | string | **Fallback** locale only (`getFallbackContentLocale`), or the trailing locale token. Empty fallback does not copy another language. Write is skipped when that string is unchanged. |
| string | locale map | Write onto the **active** language tab only; other locales stay |
| same shape | same | Pass through |

Do not assign a locale object to a scalar field. That path is `coerceFormEventValue` in `formEventFormatters/helpers.js`.

## Adding a method

1. PHP `FormSchema::hydrateInputExtension` case → `format{Name}:…`
2. `vue/src/js/utils/formEventFormatters/format{Name}.js`
3. Export from `index.js`

Do not add cases to the legacy `handleInputEvents` switch.

## Related

- [JS utilities overview](./overview)
- ADR: [Form events](/system-reference/adr-form-events)
- Agent rules: `vue/src/js/utils/formEventFormatters/AGENTS.md`, `src/Http/Controllers/Traits/Form/AGENTS.md`
