---
sidebarPos: 19
sidebarTitle: SiteSettings
---

# SiteSettings

**Facade**: `Unusualify\Modularous\Facades\SiteSettings`  
**Accessor**: `site.settings`  
**Underlying**: `Modules\Cms\Services\SiteSettingsService`

Context-aware router over CMS and System settings. Prefer this facade in public layouts and when code may run in both frontend and panel contexts. Full guide: [Settings Services](/system-reference/backend/services/settings/overview).

| Context | Behaviour |
|---------|-----------|
| Frontend (non-panel HTTP) | [`CmsSettings`](./cms-settings) with fallback to [`SystemSettings`](./system-settings) |
| Backend / panel / console | [`SystemSettings`](./system-settings) only |

## Methods

### Context

| Method | Signature | Description |
|--------|-----------|-------------|
| `forFrontend` | `(): SiteSettingsService` | Force CMS layer + System fallback |
| `forBackend` | `(): SiteSettingsService` | Force System only |
| `forCms` | `(): SiteSettingsService` | Alias of `forFrontend()` |
| `forSystem` | `(): SiteSettingsService` | Alias of `forBackend()` |
| `usesCmsLayer` | `(): bool` | Whether the CMS layer is active |
| `cms` | `(): CmsSettingsService` | Underlying CMS service |
| `system` | `(): SystemSettingsService` | Underlying System service |

### Shared read/write

Same signatures as the singular services — see [Shared API](/system-reference/backend/services/settings/overview#shared-api): `get`, `value`, `first`, `all`, `has`, `filled`, `set`, `forgetCache`, `snapshot`.

On the CMS layer, `set` writes to CMS only; `forgetCache` clears both layers.

## Usage

```blade
<img src="{{ SiteSettings::value('site.logo.frontend') }}" alt="{{ SiteSettings::get('site.name') }}">
```

```php
use Unusualify\Modularous\Facades\SiteSettings;

// Auto: frontend → CMS+fallback, panel/console → System
$name = SiteSettings::get('site.name', config('app.name'));

// Explicit
$public = SiteSettings::forFrontend()->get('seo.robots_txt');
SiteSettings::forBackend()->set('seo.robots_txt', $body);

// System-only keys still resolve on frontend via fallback
$gtm = SiteSettings::forFrontend()->get('analytics.gtm_id');
```

```php
public function __construct(
    protected \Modules\Cms\Services\SiteSettingsService $siteSettings,
) {}
```

## Notes

- Analytics snippets: [`AnalyticsScripts`](/system-reference/backend/services/settings/overview#analyticsscripts) (reads through this router).
- Empty CMS leaves do not overwrite System values in `all()` / `snapshot()`.
