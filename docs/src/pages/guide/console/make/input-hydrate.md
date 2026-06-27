---
sidebarPos: 22
sidebarTitle: make:input:hydrate
---

# make:input:hydrate

> Create a PHP Hydrate class for a Vue input component

**Signature**: `modularous:make:input:hydrate`

**Aliases**: `modularous:create:input:hydrate`, `mod:c:input:hydrate`

**Category**: Make

---

## Description

Creates an Input Hydrate class in `src/Hydrates/Inputs/`. Hydrate classes are responsible for transforming data between the database representation and the format expected by a Vue input component. The command is a no-op if the class already exists (shows a warning instead).

---

## Usage

```
modularous:make:input:hydrate <name>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | Input / component name (studly-cased; `Hydrate` suffix added automatically) |

---

## Examples

```bash
php artisan modularous:make:input:hydrate ColorPicker
# → src/Hydrates/Inputs/ColorPickerHydrate.php
```

```bash
php artisan modularous:make:input:hydrate rich-text
# → src/Hydrates/Inputs/RichTextHydrate.php
```

---

## Output

`src/Hydrates/Inputs/{Name}Hydrate.php`

**Stub**: `input-hydrate.stub`

---

## Notes

- This command writes to the Modularous **vendor** path.
- Pair with [`make:vue:input`](./vue-input) to create both the Vue component and its PHP hydrate class.
- Use [`make:feature`](./feature) to create both in one interactive wizard.

---

## See also

- [make:vue:input](./vue-input) — create the Vue input component
- [make:feature](./feature) — wizard that can create both together
- [System Reference](/guide/console/make/overview)
