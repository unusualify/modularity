---
sidebarPos: 5
sidebarTitle: Upgrading
---

# Upgrading Modularous

This guide covers the general upgrade procedure plus notes on recent releases. For a complete list of changes, see the [CHANGELOG](https://github.com/unusualify/modularous/blob/main/CHANGELOG.md).

## Versioning

Modularous follows **SemVer-ish** versioning while the package is pre-1.0:

- **Minor** (`0.x.y` → `0.(x+1).0`) — may contain breaking changes; always review release notes
- **Patch** (`0.x.y` → `0.x.(y+1)`) — bug fixes and non-breaking additions only

::: warning Pre-1.0
Until `1.0.0`, minor releases may introduce breaking changes. Pin with `~0.58.0` to allow patches but block minors, or `^0.58.0` to allow any compatible minor.
:::

---

## General Upgrade Procedure

Run this sequence for any upgrade (minor or patch):

### 1. Update the package

```bash
composer require unusualify/modularous:^0.58 --update-with-all-dependencies
```

### 2. Republish configuration (if prompted)

```bash
php artisan vendor:publish --tag=modularous-config --force
```

Diff the result against your existing config before committing — `--force` overwrites.

### 3. Re-sync host Laravel configs

```bash
php artisan modularous:update:laravel:configs
```

Patches `config/auth.php` guards and related files. See [update:laravel:configs](/guide/console/update/update-laravel-configs).

### 4. Run migrations

```bash
php artisan modularous:migrate
```

### 5. Republish stubs (if you generate code)

```bash
php artisan modularous:make:stubs --force
```

Only needed if you have published stubs locally. Skip otherwise.

### 6. Rebuild frontend

```bash
npm install
php artisan modularous:build
```

### 7. Clear caches

```bash
php artisan modularous:cache:clear
php artisan config:clear
php artisan view:clear
```

### 8. Smoke test

- Log in as admin
- Load a data-table view
- Create / edit a record on any module
- Run any custom test suite

---

## Version-Specific Notes

### v0.58.0 (2026-03-24)

Feature release with substantial additions — review these before upgrading.

#### New features you can adopt

- **Currency provider interface** — modules can now provide their own currency data source. See [Currency providers](/system-reference/backend/services/currency/overview).
- **2FA login routes** — enable via the auth component config.
- **Command discovery** — `php artisan modularous:list` now scans all registered commands.
- **Route status command** — `php artisan modularous:route:status` lists enable/disable status per module.
- **Ziggy integration** — Laravel routes are exposed to the Vue side via Ziggy.
- **Custom exception class** — `Unusualify\Modularous\Exceptions\*` replaces direct `\Exception` throws.
- **Deferred auth config** — auth component and pages now use deferred config. Re-run `modularous:update:laravel:configs` to pick up defaults.
- **InputRenderer + registry** — dynamic component mapping for form inputs. Existing `registerInputType` calls keep working.

#### Breaking / behaviour changes

- Composition API is now enforced for new Vue components. Existing Options API components keep working; new generator output is Composition.
- Auth views are published separately — run `php artisan vendor:publish --tag=modularous-auth-views` if you customised them.

#### Migration checklist

- [ ] Run the general upgrade procedure above
- [ ] If you rely on currency exchange, either install `SystemPricing` or implement a custom `CurrencyProviderInterface` binding
- [ ] If you customised auth views, republish with `--tag=modularous-auth-views`
- [ ] Review `config/modularous.php` for new keys after `--force` republish

### v0.57.x

Patch releases only — safe to upgrade with the general procedure.

### Older versions

For upgrades across multiple minors (e.g. v0.55 → v0.58), upgrade one minor at a time and run the procedure between each. Skipping minors often surfaces cumulative breaking changes all at once.

---

## Rollback

If an upgrade breaks something and you need to revert:

### 1. Downgrade the package

```bash
composer require unusualify/modularous:0.58.0 --update-with-all-dependencies
# replace 0.58.0 with the previous known-good version
```

### 2. Roll back migrations

```bash
php artisan modularous:migrate:rollback
```

Rollback only undoes the **last batch**. For multiple batches, run repeatedly — or check the migration table and target specific ones.

### 3. Clear all caches

```bash
php artisan modularous:cache:clear
php artisan config:clear
php artisan view:clear
```

### 4. Restore customised files

If `vendor:publish --force` overwrote a customised file and you didn't diff before committing, restore it from git:

```bash
git checkout HEAD -- config/modularous.php
```

---

## Troubleshooting

### Composer resolver complains about conflicts

Run with `--with-all-dependencies` (or `-W`) to allow transitive updates:

```bash
composer require unusualify/modularous:^0.58 -W
```

### Migrations fail on upgrade

Check `database/migrations/` and `modules/*/Database/Migrations/` for duplicate migration names. Modularous migrations are namespaced by module; if a host-app migration collides, rename it.

### Form inputs render as plain text after upgrade

The input registry may not have loaded. Clear and rebuild:

```bash
php artisan modularous:cache:clear
php artisan modularous:build
```

### Echo / broadcast events stop firing

After upgrading broadcasting, re-publish Reverb config and confirm queue workers restarted:

```bash
php artisan config:clear
php artisan queue:restart
php artisan reverb:restart
```

See [Broadcasting troubleshooting](/guide/broadcasting/troubleshooting).

---

## Form events (`ext` → `formEvents`)

Cross-field form DSL moved from **`ext`** to **`formEvents`**. See [Form events](/guide/js/form-events) and [ADR](/system-reference/adr-form-events).

### v13

- Write **`formEvents`** on new and migrated inputs (pipe string or nested arrays).
- Event DSL on **`ext`** still compiles, with a deprecation warning (`unusualify/modularous` 13.0).
- Type aliases stay on `ext`: `date`, `time`, `price`, `softCurrency`, `scroll`, `number`, `relationship`, `morphTo`.

### v14

- **`ext` is no longer an event source.** Inputs that still declare `set` / `update` / `lock` / … on `ext` will not compile those tokens.
- `ext` itself remains only until type aliases have another home.

---

## Before You Upgrade in Production

- [ ] Pin the current version in `composer.json` so a `composer update` doesn't move further
- [ ] Run the upgrade in a staging environment first
- [ ] Take a database snapshot
- [ ] Confirm you have a tested rollback path
- [ ] Schedule during a low-traffic window
- [ ] Keep the previous package cache (`vendor/`) available for fast revert

## See Also

- [CHANGELOG](https://github.com/unusualify/modularous/blob/main/CHANGELOG.md) — full release history
- [Installation Guide](/get-started/installation-guide) — fresh-install instructions
- [Commands / Update](/guide/console/update/overview) — commands involved in upgrading
