---
sidebarPos: 11
sidebarTitle: input
---

# input

**File**: `src/Helpers/input.php`

Form input processing helpers — the PHP side of the Modularous form schema pipeline. These functions hydrate, normalize, and extend input definitions before they are serialized to the frontend.

## Pipeline Overview

```
configure_input()
    ↓
hydrate_input_type()  → merges registered input-type presets
    ↓
hydrate_input_connector()  → resolves connector endpoint/repository
    ↓
format_input()  → type-specific logic (group, wrap, morphTo, polymorphic, title)
    ↓
hydrate_input()  → runs InputHydrator
    ↓
hydrate_input_extension()  → FormEventCompiler (formEvents, or ext fallback until v14)
    ↓
modularous_format_input()  → merges with default input, keys by name
    ↓
modularous_format_inputs()  → maps over schema array
```

## Functions

### `configure_input`

```php
configure_input(array $input): array
```

Normalizes shorthand input config. Numeric keys (flags like `'required'`) are converted to `['required' => true]` key-value pairs. Label keys are auto-translated via `___('form-labels.*')`.

---

### `modularous_default_input`

```php
modularous_default_input(): array
```

Returns the default input configuration from `modularous.default_input` config key. Merged into every input by `modularous_format_input`.

---

### `hydrate_input_type`

```php
hydrate_input_type(array $input): array
```

Looks up `$input['type']` in `modularous.input_types` config and merges the preset defaults with the provided input array. Allows centralized type defaults (e.g. all `select` inputs get `itemValue: 'id'` automatically).

---

### `hydrate_input_connector`

```php
hydrate_input_connector(array &$input, string $moduleName = null, string $routeName = null): void
```

Resolves `connector` shorthand strings to actual `endpoint` URLs or `repository` class references. Connector format:

```
'moduleName:routeName|uri:index'
'moduleName:routeName|repository:list'
```

---

### `hydrate_input_extension`

```php
hydrate_input_extension(array &$input, &$data, &$arrayable, array $inputs): void
```

Delegates to `FormEventCompiler`. Canonical config key is **`formEvents`** (pipe string or nested arrays). If `formEvents` is absent, event DSL on **`ext`** is compiled (deprecated in v13, removed in v14). Type aliases (`date`, `time`, `number`, …) stay on `ext` and skip the compiler.

Supported event methods:

| Pattern | Effect |
|---------|--------|
| `permalink:slug` | Adds a readonly slug input and a `formatPermalink` event |
| `permalinkPrefix:slug` | Adds a `formatPermalinkPrefix` event |
| `lock:url:url` | Adds a `formatLock` event |
| `filter:target:prop` | Resolves a filter endpoint and adds `formatFilter` event |
| `preview:field` | Adds `formatPreview` event |
| `set:target:prop` | Adds `formatSet` event |
| `update:target:prop` | Adds `formatUpdate` event (not on create hydrate) |
| `clearModel:target` | Adds `formatClearModel` event |
| `resetItems:target` | Adds `formatResetItems` event |
| `prependSchema:...` | Adds `formatPrependSchema` event |
| `removeValue:target` | Adds `formatRemoveValue` event |
| `toggleInput:target:value:level` | Adds `formatToggleInput` event |

Compiled tokens are stored on `$input['formEvents']` (array) and `$input['event']` (pipe) for the Vue form engine.

---

### `hydrate_input`

```php
hydrate_input(array $input, $module = null, $routeName = null, $skipQueries = null): array
```

Delegates to `InputHydrator::hydrate()` — the class-based hydration layer that handles relationships, repositories, and query-based item population.

---

### `format_input`

```php
format_input(array $input, ...): array
```

Handles type-specific input processing:
- **`group` / `wrap`**: Builds nested schema with parent name prefixing and default collection
- **`morphTo`**: Builds cascading select inputs for polymorphic foreign key selection
- **`polymorphic`**: Splits into a type combobox and an ID combobox from a `morphs` array
- **`title`**: Applies display defaults (padding, weight, class, color)

Returns `[$processedInput, $isArrayable]`.

---

### `modularous_format_input`

```php
modularous_format_input(array $input, ...): array
```

Full pipeline entry for a single input: resolves closures, calls `format_input`, merges defaults, applies `configure_input`, and returns `['name' => $normalizedInput]`.

---

### `modularous_format_inputs`

```php
modularous_format_inputs(array $inputs, ...): array
```

Maps `modularous_format_input` over an array of input definitions, returning the keyed schema array ready for the frontend.
