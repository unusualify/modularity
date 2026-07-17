---
sidebarPos: 1
sidebarTitle: Overview
---

# Facades

Modularous registers 21 Laravel facades, all under the `Unusualify\Modularous\Facades\` namespace. Each facade is an alias to a bound service container entry, providing static-style access to the underlying service class.

## Overview

| Facade | Accessor | Underlying Service |
|--------|----------|--------------------|
| [Modularous](./modularous) | `modularous` | `Unusualify\Modularous\Modularous` |
| [ModularousCache](./modularous-cache) | `modularous.cache` | `ModularousCacheService` |
| [ModularousFinder](./modularous-finder) | `Finder::class` | `Support\Finder` |
| [ModularousLog](./modularous-log) | `modularous.log` | `Illuminate\Log\LogManager` (dedicated channel) |
| [ModularousRoutes](./modularous-routes) | `Support\ModularousRoutes::class` | `Support\ModularousRoutes` |
| [ModularousVite](./modularous-vite) | `Support\ModularousVite::class` | `Support\ModularousVite` |
| [Navigation](./navigation) | `modularous.navigation` | `ModularousNavigation` |
| [Redirect](./redirect) | `modularous.redirect` | `RedirectService` |
| [Utm](./utm) | `modularous.utm` | `UtmParameters` |
| [Filepond](./filepond) | `Filepond` | `Services\Filepond` / `FilepondManager` |
| [Coverage](./coverage) | `coverage.service` | `CoverageService` |
| [CurrencyExchange](./currency-exchange) | `currency.exchange` | `CurrencyExchangeService` |
| [MigrationBackup](./migration-backup) | `migration.backup` | `MigrationBackupService` |
| [RelationshipGraph](./relationship-graph) | `modularous.relationship.graph` | `CacheRelationshipGraph` |
| [Register](./register) | `auth.register` | `RegisterBrokerManager` |
| [HostRouting](./host-routing) | `unusualify.hosting` | `Support\HostRouting` |
| [HostRoutingRegistrar](./host-routing-registrar) | `unusualify.hostRouting` | `Support\HostRouting` |
| [SystemSettings](./system-settings) | `system.settings` | `Modules\SystemSetting\Services\SystemSettingsService` |
| [CmsSettings](./cms-settings) | `cms.settings` | `Modules\Cms\Services\CmsSettingsService` |
| [SiteSettings](./site-settings) | `site.settings` | `Modules\Cms\Services\SiteSettingsService` |
| [UFinder](./u-finder) *(deprecated)* | `Finder::class` | `Support\Finder` |

Settings facades share one API and differ by backing store / context routing — see [Settings Services](/system-reference/backend/services/settings/overview).

## Usage Pattern

All Modularous facades follow standard Laravel facade usage:

```php
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;

// Resolve a module
$module = Modularous::find('Blog');

// Cache a result scoped to a module route
$items = ModularousCache::remember($key, 3600, fn() => $repo->all(), 'Blog', 'posts');
```

Facades are registered in `BaseServiceProvider` and are available everywhere in the Laravel application after the service provider boots.
