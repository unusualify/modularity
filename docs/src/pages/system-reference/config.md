---
sidebarPos: 7
sidebarTitle: Config
---

# Configuration System

Modularous uses a layered configuration system. Understanding the layers helps when customizing or debugging.

## Configuration Layers

### 1. merges (Package Defaults)

**Location**: `config/merges/*.php`  
**Loaded**: At bootstrap (BaseServiceProvider::registerBaseConfigs)  
**Key**: `modularous.{filename}` (e.g. `modularous.services`, `modularous.roles`)

Package defaults that do not depend on the translator. Merged recursively with `array_merge_recursive_preserve()`.

**Files**: api, artisan_runner, cache, composer, default_form_action, default_form_attributes, default_header, default_input, default_table_action, default_table_attributes, enabled, file_library, glide, imgix, input_types, laravel-relationship-map, mail, media_library, notifications, paths, payment, remote_api, schemas, services, stubs, system_console, tables, traits

### 2. defers (Localized Config)

**Location**: `config/defers/*.php`  
**Loaded**: Per request via `LoadLocalizedConfig` middleware (runs in `modularous.core` group)  
**Key**: `modularous.{filename}`

Config that needs the translator (e.g. `__()`, `___()`). Loaded after the translator is available.

**Files**: auth_component, auth_pages, form_drafts, navigation, ui_settings, widgets

### 3. publishes (App Overrides)

**Location**: Published to `config/` via `php artisan vendor:publish --tag=modularous-config`  
**Loaded**: Standard Laravel config loading

App-level overrides. Published files take precedence when merged.

**Common published configs**: `config/modularous.php`, `config/modules.php`, `config/permission.php`, `config/auth.php`

### 4. App Override Path

**Location**: `base_path('modularous/*.php')`  
**Loaded**: By `LoadLocalizedConfig` middleware when files exist

Optional app-specific config files that override deferred config.

## Base Config

**File**: `config/config.php`  
**Key**: `modularous` (via `$baseKey`)

Core package settings: app_url, admin paths, theme, enabled features, etc.

## Currency Provider

**Config**: `modularous.currency_provider`  
**Env**: `MODULAROUS_CURRENCY_PROVIDER`

Optional FQCN of a class implementing `CurrencyProviderInterface`. When null, Modularous uses `SystemPricingCurrencyProvider` if the SystemPricing module is present, else `NullCurrencyProvider`.

## Paths

**Config**: `modularous.paths` (from merges/paths.php)

Defines base paths for modules, vendor assets, and published resources.
