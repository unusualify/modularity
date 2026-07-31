# Console Command Naming Conventions

## Rule: Class Name ↔ Signature Compatibility

**Class names must reflect their command signature.** Convert signature parts to PascalCase and append `Command`.

| Signature Part | Class Name Part | Example |
|----------------|-----------------|---------|
| `modularous:make:module` | MakeModuleCommand | make + module |
| `modularous:cache:clear` | CacheClearCommand | cache + clear |
| `modularous:route:disable` | RouteDisableCommand | route + disable |
| `modularous:replace:regex` | ReplaceRegexCommand | replace + regex |

## Semantic Rules

### `modularous:make:*` — Artifact generators
Commands that **scaffold or generate files**. All live in `Console/Make/`.

- **Class:** `Make*Command` (e.g. `MakeModuleCommand`, `MakeControllerCommand`)
- **Examples:** `make:module`, `make:controller`, `make:migration`

### `modularous:create:*` — Runtime creation
Commands that **create runtime records** (DB entries, users).

- **Class:** `Create*Command` (e.g. `CreateSuperAdminCommand`)
- **Examples:** `create:superadmin`

### Other namespaces
- `modularous:cache:*` → `Cache*Command` (CacheClearCommand, CacheListCommand)
- `modularous:migrate:*` → `Migrate*Command` (MigrateCommand, MigrateRefreshCommand)
- `modularous:flush:*` → `Flush*Command` (FlushCommand, FlushSessionsCommand)
- `modularous:route:*` → `Route*Command` (RouteDisableCommand, RouteEnableCommand)
- `modularous:sync:*` → `Sync*Command` (SyncTranslationsCommand, SyncStatesCommand)
- `modularous:replace:*` → `Replace*Command` (ReplaceRegexCommand)

## Class Naming Pattern by Folder

| Folder    | Pattern            | Example                     |
|-----------|--------------------|-----------------------------|
| Make/     | `Make*Command`     | MakeModuleCommand           |
| Setup/    | `*Command`         | CreateSuperAdminCommand, InstallCommand |
| Cache/    | `Cache*Command`    | CacheClearCommand           |
| Migration/| `Migrate*Command`  | MigrateCommand              |
| Flush/    | `Flush*Command`    | FlushCommand, FlushSessionsCommand |
| Module/   | `*Command`         | RouteDisableCommand, FixModuleCommand |
| Sync/     | `Sync*Command`     | SyncTranslationsCommand      |
| Docs/     | `Generate*Command` | GenerateCommandDocsCommand  |

## Full Command Mapping (Signature ↔ Class)

| Signature | Class |
|-----------|-------|
| modularous:make:* | Make*Command |
| modularous:create:superadmin | CreateSuperAdminCommand |
| modularous:create:database | CreateDatabaseCommand |
| modularous:create:error-page-defaults | CreateErrorPageDefaultsCommand (ErrorPage module) |
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
