---
sidebarPos: 23
sidebarTitle: make:vue:input
---

# make:vue:input

> Create a Vue single-file input component

**Signature**: `modularous:make:vue:input`

**Aliases**: `modularous:create:vue:input`, `mod:c:vue:input`

**Category**: Make

---

## Description

Scaffolds a Vue `.vue` file for a custom form input type in the Modularous vendor `vue/src/js/components/inputs/` directory. The file name is the studly-cased component name; the component's `name` attribute uses a `v-input-` kebab-case prefix. The command is a no-op if the file already exists.

---

## Usage

```
modularous:make:vue:input <name>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | Component name (e.g. `ColorPicker`, `RichText`) |

---

## Examples

```bash
php artisan modularous:make:vue:input ColorPicker
# → vue/src/js/components/inputs/ColorPicker.vue
# Component name attribute: v-input-color-picker
```

```bash
php artisan modularous:make:vue:input RichText
# → vue/src/js/components/inputs/RichText.vue
# Component name attribute: v-input-rich-text
```

---

## Output

`vue/src/js/components/inputs/{Name}.vue`

**Stub**: `input-component.vue`

---

## Notes

- This command writes to the Modularous **vendor** path.
- After creating the component, create the matching PHP Hydrate class with [`make:input:hydrate`](./input-hydrate).
- Use [`make:feature`](./feature) to run both commands as part of an interactive wizard.

---

## See also

- [make:input:hydrate](./input-hydrate) — create the matching PHP hydrate class
- [make:vue:test](./vue-test) — create a test for this component
- [make:feature](./feature) — end-to-end wizard
- [System Reference](/guide/console/make/overview)
