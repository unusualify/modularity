---
sidebarPos: 2
sidebarTitle: CmsSettings
---

# CmsSettings

**Facade**: `Unusualify\Modularous\Facades\CmsSettings`  
**Accessor**: `cms.settings`  
**Underlying**: `Modules\Cms\Services\CmsSettingsService`  
**Model**: `Modules\Cms\Entities\SiteSetting` (`IsSingular`)

Frontend/CMS-facing settings singleton. No System fallback — use [`SiteSettings`](./site-settings) when empty CMS keys should fall through to System. Full guide: [Settings Services](/system-reference/backend/services/settings/overview).

## Sections

`site`, `social`, `contact`, `seo` only.

`smtp`, `analytics`, and `locale` are **not** editable on `SiteSetting` — use [`SystemSettings`](./system-settings).

## Methods

Shared singular API — see [Shared API](/system-reference/backend/services/settings/overview#shared-api).

| Method | Signature | Description |
|--------|-----------|-------------|
| `get` / `value` | `(string $key, mixed $default = null, ?string $locale = null): mixed` | Dot-path read with locale / media-leaf expansion |
| `first` | `(string $key, mixed $default = null, ?string $locale = null): mixed` | First list element when applicable |
| `all` | `(?string $locale = null): array` | Locale-resolved section tree |
| `has` | `(string $key): bool` | Key exists in snapshot |
| `filled` | `(string $key, ?string $locale = null): bool` | Resolved value is non-empty |
| `set` | `(string $key, mixed $value): void` | Persist + forget cache |
| `forgetCache` | `(): void` | Clear cached snapshot |
| `snapshot` | `(): array` | Raw cached sections |

## Usage

```php
use Unusualify\Modularous\Facades\CmsSettings;

CmsSettings::set('site.name', 'Public site name');
$tagline = CmsSettings::get('site.tagline');

if (! CmsSettings::filled('seo.default_meta_title')) {
    // CMS empty — SiteSettings::forFrontend() would fall back to System
}
```

## Notes

- Seed empty CMS from System with [`DuplicateSystemSettingsToCmsSettings`](/system-reference/backend/services/settings/overview#duplicatesystemsettingstocmssettings).
- Cache key: `cms_settings.snapshot` (`modularous.cms_settings.cache_ttl`).
