---
sidebarPos: 1
sidebarTitle: Console Overview
---

# Console Commands Overview

Modularous provides Artisan commands for scaffolding, building, and managing modules. Commands are organized by category.

## Categories

| Category | Description |
|----------|-------------|
| [**Cache** →](/guide/console/cache/overview) | Clear, warm, inspect, and manage module caches |
| [**Coverage** →](/guide/console/coverage/overview) | Analyse Clover reports, generate reports, enforce thresholds, scaffold tests |
| [**Migration** →](/guide/console/migration/overview) | Run and rollback module migrations |
| [**Docs** →](/guide/console/docs/overview) | Audit and generate documentation |
| [**Flush** →](/guide/console/flush/overview) | Flush caches, FilePond uploads, and sessions |
| [**Generators** →](/guide/console/generators/overview) | Scaffold models, controllers, routes, hydrates, Vue inputs, tests |
| [**Make** →](/guide/console/make/overview) | Individual `make:*` command reference with examples and options |
| [**Setup** →](/guide/console/setup/overview) | Installation and development setup |
| [**Sync** →](/guide/console/sync/overview) | Sync model states and translation keys |
| [**Module** →](/guide/console/module/overview) | Fix, remove, and per-route enable/disable/status |
| [**Operations** →](/guide/console/operations/overview) | Process and publish the module operations pipeline _(internal)_ |
| [**Update** →](/guide/console/update/overview) | Patch host-application config files during upgrades _(internal)_ |

## Quick Links

- **Cache**: [cache:clear](/guide/console/cache/cache-clear), [cache:list](/guide/console/cache/cache-list), [cache:warm](/guide/console/cache/cache-warm), [cache:stats](/guide/console/cache/cache-stats), [cache:versions](/guide/console/cache/cache-versions), [cache:graph](/guide/console/cache/cache-graph)
- **Coverage**: [coverage:analyze](/guide/console/coverage/coverage-analyze), [coverage:pr:check](/guide/console/coverage/coverage-pr-check), [coverage:report](/guide/console/coverage/coverage-report), [coverage:generate-tests](/guide/console/coverage/coverage-generate-tests), [coverage:watch](/guide/console/coverage/coverage-watch)
- **Migration**: [migrate](/guide/console/migration/migrate), [migrate:refresh](/guide/console/migration/migrate-refresh), [migrate:rollback](/guide/console/migration/migrate-rollback)
- **Docs**: [docs:audit](/guide/console/docs/docs-audit), [generate:command:docs](/guide/console/docs/generate-command-docs)
- **Flush**: [flush](/guide/console/flush/flush), [flush:filepond](/guide/console/flush/flush-filepond), [flush:sessions](/guide/console/flush/flush-sessions)
- **Make**: [make:module](/guide/console/make/module), [make:route](/guide/console/make/route), [make:model](/guide/console/make/model), [make:migration](/guide/console/make/migration), [make:repository](/guide/console/make/repository), [make:controller](/guide/console/make/controller), [make:controller:api](/guide/console/make/controller-api), [make:controller:front](/guide/console/make/controller-front), [make:request](/guide/console/make/request), [make:event](/guide/console/make/event), [make:listener](/guide/console/make/listener), [make:operation](/guide/console/make/operation), [make:horizon:supervisor](/guide/console/make/horizon-supervisor), [make:stubs](/guide/console/make/stubs), [make:command](/guide/console/make/command), [make:model:trait](/guide/console/make/model-trait), [make:repository:trait](/guide/console/make/repository-trait), [make:route:permissions](/guide/console/make/route-permissions), [make:theme:folder](/guide/console/make/theme-folder), [make:theme](/guide/console/make/theme), [make:input:hydrate](/guide/console/make/input-hydrate), [make:vue:input](/guide/console/make/vue-input), [make:vue:test](/guide/console/make/vue-test), [make:laravel:test](/guide/console/make/laravel-test), [make:feature](/guide/console/make/feature)
- **Generators**: [make:module](/guide/console/generators/make-module), [make:model](/guide/console/generators/make-model), [make:controller](/guide/console/generators/make-controller), [make:controller-api](/guide/console/generators/make-controller-api), [make:controller-front](/guide/console/generators/make-controller-front), [make:route](/guide/console/generators/make-route), [make:migration](/guide/console/generators/make-migration), [make:repository](/guide/console/generators/make-repository), [make:request](/guide/console/generators/make-request), [make:event](/guide/console/generators/make-event), [make:listener](/guide/console/generators/make-listener), [make:operation](/guide/console/generators/make-operation), [make:horizon-supervisor](/guide/console/generators/make-horizon-supervisor), [make:theme](/guide/console/generators/make-theme), [make:stubs](/guide/console/generators/make-stubs), [create:command](/guide/console/generators/create-command), [create:feature](/guide/console/generators/create-feature), [create:input-hydrate](/guide/console/generators/create-input-hydrate), [create:vue-input](/guide/console/generators/create-vue-input), [create:model-trait](/guide/console/generators/create-model-trait), [create:repository-trait](/guide/console/generators/create-repository-trait), [create:route-permissions](/guide/console/generators/create-route-permissions), [create:theme](/guide/console/generators/create-theme), [create:test-laravel](/guide/console/generators/create-test-laravel), [create:vue-test](/guide/console/generators/create-vue-test)
- **Setup**: [install](/guide/console/setup/install), [setup:development](/guide/console/setup/setup-development), [create:database](/guide/console/setup/create-database), [create:superadmin](/guide/console/generators/create-superadmin)
- **Sync**: [sync:states](/guide/console/sync/sync-states), [sync:translations](/guide/console/sync/sync-translations)
- **Module**: [route:enable](/guide/console/module/route-enable), [route:disable](/guide/console/module/route-disable), [route:status](/guide/console/module/route-status), [route:inspect](/guide/console/module/route-inspect), [fix-module](/guide/console/module/fix-module), [remove-module](/guide/console/module/remove-module)
- **Other**: [check-collation](/guide/console/check-collation), [refresh](/guide/console/refresh), [get-version](/guide/console/get-version), [pint](/guide/console/pint), [replace-regex](/guide/console/replace-regex)
- **Operations**: [operations:process](/guide/console/operations/process-operations), [publish:operations](/guide/console/operations/publish-operations)
- **Update**: [update:laravel:configs](/guide/console/update/update-laravel-configs)

See [Backend](/system-reference/backend/overview#console-commands) for a full command list.
