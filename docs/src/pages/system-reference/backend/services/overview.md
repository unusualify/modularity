---
sidebarPos: 1
sidebarTitle: Overview
---

# Services

The `src/Services/` directory contains the top-level service classes that power Modularous internals. All services are bound in the Laravel service container and can be resolved via dependency injection or their dedicated Facades.

## Service Reference

| Service | Facade | Description |
|---------|--------|-------------|
| [Connector](/system-reference/backend/services/connector) | — | Parses connector strings in input configs into data source calls |
| [FilepondManager](/system-reference/backend/services/filepond-manager) | `Filepond` | Manages the full FilePond upload lifecycle |
| [ModularousCacheService](/system-reference/backend/services/modularous-cache-service) | `ModularousCache` | Tag-aware, module-scoped cache layer |
| [RedirectService](/system-reference/backend/services/redirect-service) | `Redirect` | Stores and retrieves post-auth redirect URLs |
| [BroadcastManager](/system-reference/backend/services/broadcast-manager) | — | Extracts WebSocket channel config from event classes |
| [MigrationBackup](/system-reference/backend/services/migration-backup) | `MigrationBackup` | Snapshots table data before destructive migrations |
| [Translation](/system-reference/backend/services/translation) | — | Abstract base driver — scanning, missing-key discovery, source-locale merge |
| [FileTranslation](/system-reference/backend/services/file-translation) | — | File-based driver with cross-path sync (package ↔ app lang files) |
| [MessageStage](/system-reference/backend/services/message-stage) | — | Backed enum for flash message status values |
| [UtmParameters](/system-reference/backend/services/utm-parameters) | `Utm` | Captures and persists UTM tracking parameters |
| [Assets](/system-reference/backend/services/assets) | — | Resolves frontend asset URLs (dev server / manifest) |
| [CurrencyExchangeService](/system-reference/backend/services/currency-exchange-service) | `CurrencyExchange` | Fetches and caches live exchange rates |
| [CacheRelationshipGraph](/system-reference/backend/services/cache-relationship-graph) | — | Builds a model→module-route dependency graph for targeted cache invalidation |
| [CoverageService](/system-reference/backend/services/coverage-service) | `coverage.service` | Parses Clover XML reports; generates coverage reports and PR checks |
| [Settings](/system-reference/backend/services/settings/overview) | `SystemSettings` / `CmsSettings` / `SiteSettings` | Singular system & CMS settings (dot paths, locale, media leaves, context routing) |
