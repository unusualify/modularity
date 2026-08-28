# Upgrade Guide

## Upgrading To 11 (or your specific version) from 0.x or 10.x

To upgrade Modularous to the latest version, please follow these steps:

### 1. Update Dependencies
Update your `composer.json` to require the new version and run:
```bash
composer update unusualify/modularous:^11
```

### 2. Run the Upgrade Script
We have provided an automated script to handle breaking changes and architectural updates. Run the following command in your terminal:

```bash
php vendor/unusualify/modularous/upgrades/v11.php
```

### 3. Clear Cache
After running the script, it is highly recommended to clear your application cache:

```bash
php artisan optimize:clear
```

## Form events (`ext` → `formEvents`)

### 13.x

Declare cross-field form behaviour with **`formEvents`** (pipe string or nested arrays). Event DSL on **`ext`** still compiles and emits a deprecation (`unusualify/modularous` 13.0). Type aliases (`date`, `time`, `number`, …) stay on `ext`.

### 14.x

**Breaking:** `ext` is no longer compiled as event DSL. Migrate `set` / `update` / `lock` / `toggleInput` / … to `formEvents` before upgrading. Type aliases on `ext` are unchanged until they get a dedicated key.

