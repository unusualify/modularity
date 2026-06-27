---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Make
---

# Make Commands

The `make:*` group scaffolds every layer of a Modularous application — from a full module skeleton to individual traits, Vue components, and test files. Every command lives under `src/Console/Make/` and extends `BaseCommand`.

## Module & Route Scaffold

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:module](./module) | `modularous:make:module` | Bootstrap a complete module (nWidart skeleton + first route) |
| [make:route](./route) | `modularous:make:route` | Add a new route to an existing module |
| [make:stubs](./stubs) | `modularous:make:stubs` | Selectively regenerate stub files for an existing route |

## Models & Data

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:model](./model) | `modularous:make:model` | Eloquent model with optional traits, relations, and companion models |
| [make:migration](./migration) | `modularous:make:migration` | Database migration (create, pivot, morph-pivot, add, drop) |
| [make:repository](./repository) | `modularous:make:repository` | Repository class bound to a module model |
| [make:model:trait](./model-trait) | `modularous:make:model:trait` | Reusable entity trait (`Has{Name}.php`) |
| [make:repository:trait](./repository-trait) | `modularous:make:repository:trait` | Reusable repository trait (`{Name}Trait.php`) |

## Controllers & Requests

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:controller](./controller) | `modularous:make:controller` | Admin-panel CRUD controller |
| [make:controller:api](./controller-api) | `modularous:make:controller:api` | REST API controller |
| [make:controller:front](./controller-front) | `modularous:make:controller:front` | Front-end (public-facing) controller |
| [make:request](./request) | `modularous:make:request` | Form Request with inline validation rules |
| [make:route:permissions](./route-permissions) | `modularous:make:route:permissions` | Generate Spatie permission records for a route |

## Events & Async

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:event](./event) | `modularous:make:event` | Laravel Event class (broadcasting, deferred dispatch) |
| [make:listener](./listener) | `modularous:make:listener` | Laravel Listener class (queued, after-commit) |
| [make:operation](./operation) | `modularous:make:operation` | One-time operation file for the operations pipeline |
| [make:horizon:supervisor](./horizon-supervisor) | `modularous:make:horizon:supervisor` | Supervisor `.conf` for Laravel Horizon |

## Console

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:command](./command) | `modularous:make:command` | New Artisan command class inside the Modularous vendor path |

## Themes & Frontend

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:theme:folder](./theme-folder) | `modularous:make:theme:folder` | Scaffold a custom theme working folder |
| [make:theme](./theme) | `modularous:make:theme` | Promote a custom theme into the built-in theme set |
| [make:input:hydrate](./input-hydrate) | `modularous:make:input:hydrate` | PHP Hydrate class for a Vue input component |
| [make:vue:input](./vue-input) | `modularous:make:vue:input` | Vue single-file input component |

## Tests

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:laravel:test](./laravel-test) | `modularous:make:laravel:test` | PHPUnit Feature or Unit test file |
| [make:vue:test](./vue-test) | `modularous:make:vue:test` | Vitest/Jest test file for a Vue component or composable |

## Composite Wizard

| Command | Signature | Description |
|---------|-----------|-------------|
| [make:feature](./feature) | `modularous:make:feature` | Interactive wizard that orchestrates multiple make commands |

---

## Common Workflows

### Scaffold a brand-new module

```bash
php artisan modularous:make:module Blog --schema="title:string,body:text" --addTranslation
```

### Add a second route to an existing module

```bash
php artisan modularous:make:route Blog Post --schema="title:string,published_at:timestamp:nullable"
```

### Create a standalone model + migration

```bash
php artisan modularous:make:model Tag Blog --soft-delete
php artisan modularous:make:migration create_blog_tags_table Blog --fields="tag_id:unsignedBigInteger"
```

### Scaffold a Vue input feature end-to-end

```bash
php artisan modularous:make:feature
# Responds to all prompts interactively
```

> For the class internals of these commands see [System Reference → Console → Make](/guide/console/make/overview).
