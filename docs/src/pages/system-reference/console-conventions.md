---
sidebarPos: 11
sidebarTitle: Console Conventions
---

# Console Command Conventions

Class names must reflect their command signature. Convert signature parts to PascalCase and append `Command`.

## Naming Rules

| Signature Part | Class Name Part | Example |
|----------------|-----------------|---------|
| `modularous:make:module` | MakeModuleCommand | make + module |
| `modularous:cache:clear` | CacheClearCommand | cache + clear |
| `modularous:route:disable` | RouteDisableCommand | route + disable |

## Semantic Rules

### `modularous:make:*` — Artifact generators

Commands that scaffold or generate files. All live in `Console/Make/`.

- **Class:** `Make*Command` (e.g. `MakeModuleCommand`, `MakeControllerCommand`)
- **Examples:** `make:module`, `make:controller`, `make:migration`

### `modularous:create:*` — Runtime creation

Commands that create runtime records (DB entries, users).

- **Class:** `Create*Command` (e.g. `CreateSuperAdminCommand`)
- **Examples:** `create:superadmin`

### Other namespaces

| Namespace | Pattern | Example |
|-----------|---------|---------|
| `modularous:cache:*` | Cache*Command | CacheClearCommand |
| `modularous:migrate:*` | Migrate*Command | MigrateCommand |
| `modularous:flush:*` | Flush*Command | FlushCommand |
| `modularous:route:*` | Route*Command | RouteDisableCommand |
| `modularous:sync:*` | Sync*Command | SyncTranslationsCommand, SyncRemoteApiCommand |
| `modularous:replace:*` | Replace*Command | ReplaceRegexCommand |

## Class Naming by Folder

| Folder | Pattern | Example |
|--------|---------|---------|
| Console/ (root) | *Command | BuildCommand, ReplaceRegexCommand |
| Make/ | Make*Command | MakeModuleCommand |
| Cache/ | Cache*Command | CacheClearCommand |
| Migration/ | Migrate*Command | MigrateCommand |
| Module/ | *Command | RouteDisableCommand |
| Roles/ | Roles*Command | RolesLoadCommand |
| Setup/ | *Command | InstallCommand, CreateSuperAdminCommand |
| Seed/ | Seed*Command | SeedPaymentCommand |
| Sync/ | Sync*Command | SyncTranslationsCommand |
| Operations/ | *Command | ProcessOperationsCommand |
| Flush/ | Flush*Command | FlushCommand |
| Update/ | Update*Command | UpdateLaravelConfigsCommand |
| Docs/ | Generate*Command | GenerateCommandDocsCommand |
| Schedulers/ | *Command | (package root) |

## Command Mapping

| Signature | Class |
|-----------|-------|
| modularous:make:* | Make*Command |
| modularous:create:superadmin | CreateSuperAdminCommand |
| modularous:create:database | CreateDatabaseCommand |
| modularous:install | InstallCommand |
| modularous:setup:development | SetupModularousDevelopmentCommand |
| modularous:cache:list | CacheListCommand |
| modularous:cache:clear | CacheClearCommand |
| modularous:cache:versions | CacheVersionsCommand |
| modularous:cache:graph | CacheGraphCommand |
| modularous:cache:stats | CacheStatsCommand |
| modularous:cache:warm | CacheWarmCommand |
| modularous:flush | FlushCommand |
| modularous:flush:sessions | FlushSessionsCommand |
| modularous:flush:filepond | FlushFilepondCommand |
| modularous:route:disable | RouteDisableCommand |
| modularous:route:enable | RouteEnableCommand |
| modularous:fix:module | FixModuleCommand |
| modularous:remove:module | RemoveModuleCommand |
| modularous:replace:regex | ReplaceRegexCommand |
| modularous:db:check-collation | CheckDatabaseCollationCommand |
