---
sidebarPos: 14
sidebarTitle: Overview
sidebarGroupTitle: Sync
---

# Sync Commands

Synchronise runtime state that drifts over time — model states and translation keys. These are **safe, idempotent** maintenance commands: re-running them won't duplicate data, only reconcile what's missing.

| Command | Signature | Description |
|---------|-----------|-------------|
| [sync:states](./sync-states) | `modularous:sync:states` | Sync model state values to their current definitions |
| [sync:translations](./sync-translations) | `modularous:sync:translations` | Sync translation keys across all registered locales |

## Common Workflows

### After editing state definitions

When you add a new value to a `HasStateable` model's state enum or rename an existing state:

```bash
php artisan modularous:sync:states
```

Adds missing state rows, preserves existing records' state assignments, and leaves historical state transitions intact. See [HasStateable](/system-reference/backend/entity-traits/overview) for the trait itself.

### After adding translation keys or locales

```bash
php artisan modularous:sync:translations
```

Walks every translated model / attribute and creates missing translation rows for each registered locale (e.g. when you added `de` after `en` was already populated). Existing translations are left untouched.

### On deploy

Add both to your deploy script to cover schema changes that introduced new states or enabled a new locale:

```bash
php artisan modularous:migrate
php artisan modularous:sync:states
php artisan modularous:sync:translations
```

## Related

- [Module Features](/guide/module-features/overview) — includes `HasStateable` and `HasTranslations`
- [Database commands](../migration/overview) — the prerequisite for sync
