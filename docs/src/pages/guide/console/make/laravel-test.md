---
sidebarPos: 25
sidebarTitle: make:laravel:test
---

# make:laravel:test

> Scaffold a PHPUnit Feature or Unit test file for a module

**Signature**: `modularous:make:laravel:test`

**Alias**: `modularous:create:laravel:test`

**Category**: Make

---

## Description

Creates a PHPUnit test file using `LaravelTestGenerator`. Accepts a module and test name. Pass `--unit` to generate a Unit test; the default is a Feature test.

---

## Usage

```
modularous:make:laravel:test [options] <module> <test>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | yes | Target module |
| `test` | yes | Test class name |

### Options

| Option | Description |
|--------|-------------|
| `--unit` | Generate as a PHPUnit Unit test instead of Feature test |

---

## Examples

### Feature test

```bash
php artisan modularous:make:laravel:test Blog PostControllerTest
```

### Unit test

```bash
php artisan modularous:make:laravel:test Blog PostRepositoryTest --unit
```

---

## Notes

- This command uses `LaravelTestGenerator` — refer to the generator documentation for output path and stub details.

---

## See also

- [make:vue:test](./vue-test) — create a Vitest test for Vue
- [System Reference](/guide/console/make/overview)
