---
sidebarPos: 18
sidebarTitle: make:route:permissions
---

# make:route:permissions

> Generate Spatie permission records for a route

**Signature**: `modularous:make:route:permissions`

**Alias**: `modularous:create:route:permissions`

**Category**: Make

---

## Description

Creates the Spatie permission database records for an existing route by calling `RouteGenerator::createRoutePermissions()`. Useful when a route was added manually or renamed without re-running `make:route`.

---

## Usage

```
modularous:make:route:permissions <route>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `route` | yes | Route name (must already exist in the config) |

---

## Examples

```bash
php artisan modularous:make:route:permissions Post
```

```bash
php artisan modularous:make:route:permissions ProductCategory
```

---

## Notes

- The route must be registered in Modularous configuration before running this command.
- Permissions follow the Modularous naming convention: `{route}.index`, `{route}.create`, `{route}.edit`, `{route}.destroy`.

---

## See also

- [make:route](./route) — automatically runs permission creation during scaffold
- [System Reference](/guide/console/make/overview)
