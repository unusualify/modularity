---
sidebarPos: 10
sidebarTitle: make:request
---

# make:request

> Create a Form Request class for a module

**Signature**: `modularous:make:request`

**Category**: Make

---

## Description

Generates a Form Request extending the configured `base_request`. The `--rules` string is parsed by `ValidatorParser` and inlined as a PHP array into the generated class.

---

## Usage

```
modularous:make:request [options] <module> <request>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | yes | Target module |
| `request` | yes | Request class name (suffix `Request` added automatically) |

### Options

| Option | Description |
|--------|-------------|
| `--rules=` | Validation rules string (e.g. `name:required\|string,email:required\|email`) |

---

## Examples

### Basic request

```bash
php artisan modularous:make:request Blog Post
# → Blog/Http/Requests/PostRequest.php
```

### Request with inline rules

```bash
php artisan modularous:make:request Blog Post \
    --rules="title:required|string|max:255,body:required|string,published_at:nullable|date"
```

---

## Output

`{Module}/Http/Requests/{Request}Request.php`

**Stub**: `route-request.stub`

---

## See also

- [make:route](./route) — generates the request as part of a full scaffold
- [System Reference](/guide/console/make/overview)
