---
sidebarPos: 24
sidebarTitle: make:vue:test
---

# make:vue:test

> Create a Vitest/Jest test file for a Vue feature or component

**Signature**: `modularous:make:vue:test`

**Aliases**: `modularous:create:vue:test`, `mod:c:vue:test`

**Category**: Make

---

## Description

Scaffolds a Vitest or Jest test file via `VueTestGenerator`. Both the test name and type are prompted interactively if omitted. Multiple test types are supported (component, composable, utility, store). Use `--importDir` to target a subdirectory when the import path differs from the default.

---

## Usage

```
modularous:make:vue:test [options] [<name>] [<type>]
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | no | Test name (prompted if omitted) |
| `type` | no | Test type: `component`, `composable`, `utility`, `store`, etc. (prompted if omitted) |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--importDir` | | Subdirectory path used in the import statement |
| `--force` | `-F` | Overwrite existing test file |

---

## Examples

### Fully interactive

```bash
php artisan modularous:make:vue:test
# Prompts: test name, test type
```

### Component test with explicit name

```bash
php artisan modularous:make:vue:test VInputColorPicker component
```

### Composable test

```bash
php artisan modularous:make:vue:test UseTagSelector composable
```

### Component from a subdirectory

```bash
php artisan modularous:make:vue:test VInputColorPicker component --importDir=inputs
```

---

## Notes

- The generated file path and import statement depend on the test `type` and the optional `--importDir`.
- Run this after [`make:vue:input`](./vue-input) to immediately scaffold the corresponding test file.
- [`make:feature`](./feature) can trigger this automatically during the feature wizard.

---

## See also

- [make:vue:input](./vue-input) — create the Vue component being tested
- [make:laravel:test](./laravel-test) — create a PHPUnit test instead
- [make:feature](./feature) — wizard that optionally creates both
- [System Reference](/guide/console/make/overview)
