---
sidebarPos: 12
sidebarTitle: Create Vue Test
---

# Create Vue Test

> Generate a Vitest test stub for a Vue component or feature.

## Command Information

- **Signature:** `modularous:make:vue:test [name?] [type?] [--importDir=] [--F|force]`
- **Aliases:** `modularous:create:vue:test`, `mod:c:vue:test`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | No | Test name (StudlyCase). Prompted interactively if omitted |
| `type` | No | Test type (e.g. `component`, `composable`). Prompted via select if omitted |

## Options

| Option | Description |
|--------|-------------|
| `--importDir` | Set a subfolder used as the import base path in the generated test |
| `--force` / `-F` | Overwrite the test file if it already exists |

## What It Does

Delegates to `VueTestGenerator`, which resolves the test type and writes a Vitest stub. Available types are determined by the generator's `getTypes()` method. If `name` or `type` are not provided the command prompts interactively.

## Examples

```bash
# Fully interactive
php artisan modularous:make:vue:test

# Provide all arguments
php artisan modularous:make:vue:test ProductCard component

# Force overwrite
php artisan modularous:make:vue:test ProductCard component --force
```

## Related

- [create:test-laravel](./create-test-laravel) — generate a PHPUnit backend test stub
