---
sidebarPos: 20
sidebarTitle: SystemSettings
---

# SystemSettings

**Facade**: `Unusualify\Modularous\Facades\SystemSettings`  
**Accessor**: `system.settings`  
**Underlying**: `Modules\SystemSetting\Services\SystemSettingsService`  
**Model**: `Modules\SystemSetting\Entities\General` (`IsSingular`)

Backend / system source of truth for global settings. Full usage guide: [Settings Services](/system-reference/backend/services/settings/overview).

## Sections

`site`, `social`, `contact`, `seo`, `smtp`, `analytics`, `locale`, `down_presets`

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
use Unusualify\Modularous\Facades\SystemSettings;

$host = SystemSettings::get('smtp.host');
SystemSettings::set('locale.timezone', 'Europe/Amsterdam');

$favicon = SystemSettings::value('site.favicon.original');
```

```blade
<link rel="icon" href="{{ SystemSettings::value('site.favicon.original') }}">
```

## Notes

- Prefer `SystemSettings` (or `SiteSettings::forBackend()`) for SMTP, analytics, and locale — those sections are not on `SiteSetting`.
- Optional mail override: [ApplySmtpMailConfig](/system-reference/backend/services/settings/overview#applysmtpmailconfig) via `MODULAROUS_SYSTEM_SETTINGS_MAIL_OVERRIDE`.
- Cache key: `system_settings.snapshot` (`modularous.system_settings.cache_ttl`).
