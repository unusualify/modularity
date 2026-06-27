---
sidebarPos: 8
sidebarTitle: make:controller:api
---

# make:controller:api

> Create a REST API controller for a module

**Signature**: `modularous:make:controller:api`

**Category**: Make

---

## Description

Generates an API controller in the module's `Http/Controllers/API/` path. The stub is wired up with the module namespace, studly and lower-case module name, and the controller class name.

---

## Usage

```
modularous:make:controller:api <module> <name>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | yes | Target module |
| `name` | yes | Controller class name (suffix `Controller` added automatically if absent) |

---

## Examples

```bash
php artisan modularous:make:controller:api Blog Post
# → Blog/Http/Controllers/API/PostController.php
```

---

## Output

`{Module}/Http/Controllers/API/{Name}Controller.php`

**Stub**: `route-controller-api.stub`

---

## See also

- [make:controller](./controller) — admin-panel variant
- [make:controller:front](./controller-front) — front-end variant
- [System Reference](/guide/console/make/overview)
