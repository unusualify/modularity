---
sidebarPos: 19
sidebarTitle: make:stubs
---

# make:stubs

> Selectively regenerate stub files for an existing route

**Signature**: `modularous:make:stubs`

**Category**: Make

---

## Description

Re-runs stub generation for an existing module route without creating migrations or running them. Delegates to `StubsGenerator` which writes only the PHP class files whose stub types match the `--only` / `--except` filters. Use this to fix outdated controller or repository files after a config change.

---

## Usage

```
modularous:make:stubs [options] <module> <route>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | yes | Target module |
| `route` | yes | Route name |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--only=` | | Comma-separated list of stub types to include |
| `--except=` | | Comma-separated list of stub types to exclude |
| `--force` | `-f` | Overwrite existing files |
| `--fix` | | Fix model config errors |

---

## Examples

### Regenerate all stubs for a route

```bash
php artisan modularous:make:stubs Blog Post --force
```

### Regenerate only the controller stubs

```bash
php artisan modularous:make:stubs Blog Post --only=controller,controller-api
```

### Regenerate all except migration

```bash
php artisan modularous:make:stubs Blog Post --except=migration
```

---

## Notes

- Stub type names match the generator config keys (e.g. `model`, `controller`, `repository`, `request`, `migration`).
- Use `make:module --just-stubs` to regenerate stubs across all routes of a module at once.
- Template files live under `src/Console/stubs/`. **Remake** commands (`remake:cmr`, `remake:revisions`, …) use the same directory and `Nwidart\Modules\Support\Stub` rendering — never inline file bodies in Command classes (see `src/Console/AGENTS.md`).

---

## See also

- [make:route](./route) — full route scaffold including migrations
- [make:module](./module) — `--just-stubs` flag for bulk stub refresh
- [System Reference](/guide/console/make/overview)
