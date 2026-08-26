---
sidebarPos: 22
sidebarTitle: Overview
sidebarGroupTitle: Settings
outline: deep
---

# Settings Services

Singular settings are stored as JSON sections on `IsSingular` models and read through a shared API. Three facades expose the same read/write surface with different backing stores and routing rules.

| Facade | Accessor | Service | Model | Sections |
|--------|----------|---------|-------|----------|
| [`SystemSettings`](/system-reference/backend/facades/system-settings) | `system.settings` | `Modules\SystemSetting\Services\SystemSettingsService` | `General` | `site`, `social`, `contact`, `seo`, `scripts`, `smtp`, `analytics`, `locale` |
| [`CmsSettings`](/system-reference/backend/facades/cms-settings) | `cms.settings` | `Modules\Cms\Services\CmsSettingsService` | `SiteSetting` | `site`, `social`, `contact`, `seo`, `scripts` |
| [`SiteSettings`](/system-reference/backend/facades/site-settings) | `site.settings` | `Modules\Cms\Services\SiteSettingsService` | (router) | Context-aware — see below |

**Abstract base**: `Unusualify\Modularous\Services\Settings\AbstractSingularSettingsService` — shared by `SystemSettingsService` and `CmsSettingsService`.

## Which facade to use

| Context | Prefer | Why |
|---------|--------|-----|
| Public Blade / frontend layouts | `SiteSettings` | Uses CMS values when filled; falls back to System |
| Admin / panel / console | `SystemSettings` or `SiteSettings::forBackend()` | System is the backend source of truth |
| Explicit CMS-only read/write | `CmsSettings` | No System fallback |
| SMTP, analytics, locale | `SystemSettings` | Not editable on `SiteSetting` |

`smtp`, `analytics`, and `locale` exist only on `General` / SystemSettings. Do not treat them as CMS Site Setting fields.

## Context routing (`SiteSettings`)

```
SiteSettings
├── Frontend (non-panel HTTP)  →  CmsSettings  ──(empty)──►  SystemSettings
├── Backend / panel / console  →  SystemSettings only
└── Forced
    ├── forFrontend() / forCms()     → frontend semantics
    └── forBackend() / forSystem()   → backend semantics
```

Detection (when not forced):

- Console → backend (System only)
- HTTP → CMS layer when `Modularous::isPanelUrl()` is false; otherwise System only

Direct layer access:

```php
SiteSettings::cms();    // CmsSettingsService
SiteSettings::system(); // SystemSettingsService
SiteSettings::usesCmsLayer(); // bool
```

## Shared API

Available on `SystemSettings`, `CmsSettings`, and `SiteSettings` (and on the two singular services).

| Method | Signature | Description |
|--------|-----------|-------------|
| `get` | `(string $key, mixed $default = null, ?string $locale = null): mixed` | Dot-path read with locale / media-leaf expansion |
| `value` | `(string $key, mixed $default = null, ?string $locale = null): mixed` | Alias of `get()` |
| `first` | `(string $key, mixed $default = null, ?string $locale = null): mixed` | Like `get()`; if the result is a list, returns the first element |
| `all` | `(?string $locale = null): array` | Locale-resolved tree of all sections |
| `has` | `(string $key): bool` | Raw key exists in the snapshot (`Arr::has`) |
| `filled` | `(string $key, ?string $locale = null): bool` | Resolved value is non-empty after locale/leaf expansion |
| `set` | `(string $key, mixed $value): void` | Persist via repository, then forget cache |
| `forgetCache` | `(): void` | Clear request + cache snapshots |
| `snapshot` | `(): array` | Cached raw section tree (SMTP password decrypted when present) |

### Media leaf expansion

When a direct path is empty, `get` / `value` expand the last segment through locale (and optional list index):

- `site.logo.frontend` → `site.logo.{locale}.frontend` → `site.logo.{locale}.0.frontend` → …
- `site.favicon.original` / `seo.og_image.original` work the same way

Locale fallback order: request locale → Modularous fallback locale → `*`.

### SiteSettings fallback rules

On the frontend layer:

- `get` / `value` / `first` — if `CmsSettings::filled($key)` use CMS; otherwise System (then `$default`)
- `all` / `snapshot` — `array_replace_recursive(System, filterEmpty(Cms))` so empty CMS leaves do not blank System values
- `has` / `filled` — true if either layer has / is filled
- `set` — writes to CMS only
- `forgetCache` — clears both layers

On the backend layer, every method delegates to SystemSettings only.

---

## Usage examples

### Blade (frontend)

```blade
{{-- Logo / favicon with media leaf expansion --}}
<img src="{{ SiteSettings::value('site.logo.frontend') }}" alt="{{ SiteSettings::get('site.name', config('app.name')) }}">
<link rel="icon" href="{{ SiteSettings::value('site.favicon.original') ?: asset('favicon.ico') }}">

{{-- SEO defaults --}}
<title>{{ SiteSettings::get('seo.default_meta_title', config('app.name')) }}</title>
<meta name="description" content="{{ SiteSettings::get('seo.default_meta_description', '') }}">

{{-- Social repeater --}}
@foreach (SiteSettings::get('social', []) as $link)
    <a href="{{ $link['url'] ?? '#' }}">{{ $link['platform'] ?? '' }}</a>
@endforeach

{{-- Global third-party scripts (layout shell already emits these) --}}
{!! app(\Modules\Cms\Support\CustomScripts::class)->headHtml() !!}
{!! app(\Modules\Cms\Support\CustomScripts::class)->bodyHtml() !!}
```

### System-only (SMTP / backend)

```php
use Unusualify\Modularous\Facades\SystemSettings;

$host = SystemSettings::get('smtp.host');
$timezone = SystemSettings::get('locale.timezone', 'UTC');
```

```blade
{{-- Admin favicon from System Settings --}}
<link rel="icon" href="{{ SystemSettings::value('site.favicon.original') }}">
```

### PHP — facade vs injection

```php
use Unusualify\Modularous\Facades\SiteSettings;
use Unusualify\Modularous\Facades\SystemSettings;
use Modules\Cms\Services\SiteSettingsService;
use Modules\SystemSetting\Services\SystemSettingsService;

// Facade
$name = SiteSettings::get('site.name', config('app.name'));
SystemSettings::set('smtp.host', 'smtp.example.com');

// Constructor injection
public function __construct(
    protected SiteSettingsService $siteSettings,
    protected SystemSettingsService $systemSettings,
) {}

public function logoUrl(): ?string
{
    return $this->siteSettings->forFrontend()->value('site.logo.frontend');
}
```

### Frontend vs backend routing

```php
use Unusualify\Modularous\Facades\SiteSettings;

// Force public-site semantics (CMS → System fallback)
$publicName = SiteSettings::forFrontend()->get('site.name');

// Force admin / System-only semantics
SiteSettings::forBackend()->set('seo.robots_txt', $body);

// Aliases
SiteSettings::forCms();    // === forFrontend()
SiteSettings::forSystem(); // === forBackend()
```

### Fallback: empty CMS → System

```php
SystemSettings::set('site.name', 'System Name');
SystemSettings::set('site.email', 'system@example.com');

// CMS empty → reads System
SiteSettings::forFrontend()->get('site.name');  // "System Name"
SiteSettings::forFrontend()->get('site.email'); // "system@example.com"

CmsSettings::set('site.name', 'Cms Name');

// Filled CMS key wins; still-empty keys keep System
SiteSettings::forFrontend()->get('site.name');  // "Cms Name"
SiteSettings::forFrontend()->get('site.email'); // "system@example.com"

// Backend ignores CMS
SiteSettings::forBackend()->get('site.name');   // "System Name"
```

Analytics keys are System-only; frontend `SiteSettings` still resolves them via System fallback:

```php
SystemSettings::set('analytics.gtm_id', 'GTM-XXXXXXX');
SiteSettings::forFrontend()->get('analytics.gtm_id'); // "GTM-XXXXXXX"
CmsSettings::filled('analytics.gtm_id');              // false
```

### Set + cache invalidation

```php
use Unusualify\Modularous\Facades\CmsSettings;
use Unusualify\Modularous\Facades\SystemSettings;

SystemSettings::set('contact.support_email', 'support@example.com');
// set() persists through the repository and calls forgetCache()

CmsSettings::set('site.tagline', 'Welcome');
CmsSettings::forgetCache(); // also safe to call explicitly

// Repository saves (admin forms) also forget system.settings / cms.settings / site.settings caches
```

Cache keys: `system_settings.snapshot`, `cms_settings.snapshot`. TTLs:

| Config | Env | Default |
|--------|-----|---------|
| `modularous.system_settings.cache_ttl` | `MODULAROUS_SYSTEM_SETTINGS_CACHE_TTL` | `3600` |
| `modularous.cms_settings.cache_ttl` | `MODULAROUS_CMS_SETTINGS_CACHE_TTL` | same as system |

---

## Related support

### `ApplySmtpMailConfig`

`Modules\SystemSetting\Support\ApplySmtpMailConfig` — opt-in override of Laravel mail from SystemSettings SMTP.

- Disabled by default; prefer `.env` `MAIL_*`
- Enable with `MODULAROUS_SYSTEM_SETTINGS_MAIL_OVERRIDE=true` (`modularous.system_settings.mail_override_enabled`)
- Applies only when `smtp.host` is non-empty — sets `mail.default` to `smtp` and merges host / port / username / password, plus optional `from` address / name
- Encryption helper maps `tls` / `smtp` → Laravel scheme `smtp`, and `ssl` / `smtps` → `smtps`
- Booted from `SystemSettingServiceProvider`

```env
MODULAROUS_SYSTEM_SETTINGS_MAIL_OVERRIDE=true
```

### `AnalyticsScripts`

`Modules\SystemSetting\Support\AnalyticsScripts` — builds GTM / optional GA4 snippets.

- Reads via `SiteSettingsService` (frontend CMS → System fallback; analytics values live on System)
- Enablement: DB `analytics.enabled` when set; otherwise `modularous.system_settings.analytics_enabled_default` / `MODULAROUS_ANALYTICS_ENABLED`
- Validates IDs (`GTM-…`, `G-…`)

```blade
{!! app(\Modules\SystemSetting\Support\AnalyticsScripts::class)->headHtml() !!}
{{-- immediately after <body> --}}
{!! app(\Modules\SystemSetting\Support\AnalyticsScripts::class)->bodyOpenHtml() !!}
```

Or via `SiteSettings` / `SystemSettings` directly:

```php
SiteSettings::get('analytics.gtm_id');
SystemSettings::get('analytics.enabled');
```

### `CustomScripts`

`Modules\Cms\Support\CustomScripts` — emits admin-supplied global head/body HTML.

- Reads `scripts.head` / `scripts.body` via `SiteSettingsService` (frontend CMS → System fallback)
- Trims strings; non-string values are ignored
- Layout builder shell injects head HTML after CMP/GTM and before `<base>` / CSS / `$headHtml`
- Body HTML is injected after `$footerHtml` (and before extra layout JS). Hosts may append `$afterCustomBodyHtml` after that slot
- Values are raw HTML (trusted admins only) — same trust model as `seo.json_schema`

```blade
{{-- after AnalyticsScripts::headHtml(), before <base> --}}
{!! app(\Modules\Cms\Support\CustomScripts::class)->headHtml() !!}
{{-- after $footerHtml, before extra layout JS --}}
{!! app(\Modules\Cms\Support\CustomScripts::class)->bodyHtml() !!}
```

```php
SiteSettings::get('scripts.head');
CmsSettings::set('scripts.body', '<script src="https://example.com/pixel.js"></script>');
```

### `DuplicateSystemSettingsToCmsSettings`

`Modules\Cms\Support\DuplicateSystemSettingsToCmsSettings` — one-time seed that copies `General` sections into an empty `SiteSetting` (CMS sections only: `site`, `social`, `contact`, `seo`, `scripts`). Skips when CMS already has content. Invoked from `CmsServiceProvider` boot (and host operations when needed).

```php
app(\Modules\Cms\Support\DuplicateSystemSettingsToCmsSettings::class)->duplicateIfNeeded();
```

---

## Registration

| Binding | Provider |
|---------|----------|
| `SystemSettingsService` → `system.settings` | `Modules\SystemSetting\Providers\SystemSettingServiceProvider` |
| `CmsSettingsService` → `cms.settings` | `Modules\Cms\Providers\CmsServiceProvider` |
| `SiteSettingsService` → `site.settings` | `Modules\Cms\Providers\CmsServiceProvider` |

Composer aliases: `SystemSettings`, `CmsSettings`, `SiteSettings` → `Unusualify\Modularous\Facades\*`.

## Facade pages

- [SystemSettings](/system-reference/backend/facades/system-settings)
- [CmsSettings](/system-reference/backend/facades/cms-settings)
- [SiteSettings](/system-reference/backend/facades/site-settings)
