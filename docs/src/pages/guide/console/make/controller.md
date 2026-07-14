---
sidebarPos: 7
sidebarTitle: make:controller
---

# make:controller

> Create an admin-panel CRUD controller for a module

**Signature**: `modularous:make:controller`

**Category**: Make

---

## Description

Generates a controller that extends the configured `base_controller` (default: Modularous admin base). The class receives the module's namespace, route name, and base controller reference as stub variables.

---

## Usage

```
modularous:make:controller <module> <name>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | yes | Target module |
| `name` | yes | Controller class name (suffix `Controller` added automatically if absent) |

---

## Examples

```bash
php artisan modularous:make:controller Blog Post
# → Blog/Http/Controllers/PostController.php
```

```bash
php artisan modularous:make:controller Blog PostController
# → Blog/Http/Controllers/PostController.php (same result)
```

---

## Output

`{Module}/Http/Controllers/{Name}Controller.php`

**Stub**: `route-controller.stub`

---

## See also

- [make:controller:api](./controller-api) — REST API variant
- [make:controller:front](./controller-front) — front-end variant
- [make:route](./route) — generates controller as part of a full route scaffold
- [System Reference](/guide/console/make/overview)
