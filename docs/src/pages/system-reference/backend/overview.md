---
sidebarPos: 5
sidebarTitle: Backend
---

# Backend

Reference for every PHP layer that ships with Modularous. Sections below are ordered alphabetically — each one points at the directory under `src/` it documents and the matching reference page under `system-reference/backend/`.

| Section | Source path | Reference |
|---------|-------------|-----------|
| [Activators](#activators) | `src/Activators/` | [Activators →](/system-reference/backend/activators/overview) |
| [Brokers](#brokers) | `src/Brokers/` | [Brokers →](/system-reference/backend/brokers/overview) |
| [Console Commands](#console-commands) | `src/Console/` | [Console guide →](/guide/console/overview) |
| [Controllers](#controllers) | `src/Http/Controllers/` | [Controllers →](/system-reference/backend/http/controllers/overview) |
| [Entities](#entities) | `src/Entities/` | [Entities →](/system-reference/backend/entities/overview) |
| [Entity Traits](#entity-traits) | `src/Entities/Traits/` | [Entity Traits →](/system-reference/backend/entity-traits/overview) |
| [Events & Listeners](#events-listeners) | `src/Events/`, `src/Listeners/` | [Events & Listeners →](/system-reference/backend/events/overview) |
| [Facades](#facades) | `src/Facades/` | [Facades →](/system-reference/backend/facades/overview) |
| [Form Requests](#form-requests) | `src/Http/Requests/` | [Form Requests →](/system-reference/backend/http/request/overview) |
| [Generators](#generators) | `src/Generators/` | [Generators →](/system-reference/backend/generators/overview) |
| [Helpers](#helpers) | `src/Helpers/` | [Helpers →](/system-reference/backend/helpers/overview) |
| [Middleware](#middleware) | `src/Http/Middleware/` | [Middleware →](/system-reference/backend/http/middleware/overview) |
| [Notifications](#notifications) | `src/Notifications/` | [Notifications →](/system-reference/backend/notifications/overview) |
| [Providers](#providers) | `src/Providers/` | [Providers →](/system-reference/backend/providers/overview) |
| [Repository Traits](#repository-traits) | `src/Repositories/Traits/` | [Repository Traits →](/system-reference/backend/repository-traits/overview) |
| [Scheduled Jobs](#scheduled-jobs) | `src/Schedulers/` | [Scheduled Jobs →](/system-reference/backend/scheduled-jobs/overview) |
| [Services](#services) | `src/Services/` | [Services →](/system-reference/backend/services/overview) |
| [Support](#support) | `src/Support/` | [Support →](/system-reference/backend/support/overview) |
| [View Composers](#view-composers) | `src/Http/ViewComposers/` | [View Composers →](/system-reference/backend/http/view-composers/overview) |

---

## Architecture at a glance

Modularous layers a Laravel package on top of `nWidart/laravel-modules` and adds a CRUD-aware admin pipeline that every module inherits. The backend is organised as a stack of composable layers — bootstrap on top, persistence at the bottom — with cross-cutting services sitting alongside.

```
┌──────────────────────────────────────────────────────────────────┐
│  Bootstrap        Providers · Activators · Facades · Helpers     │
│                   ──────────────────────────────────────────────  │
│  HTTP entry       Middleware → Form Requests → Controllers       │
│                   ──────────────────────────────────────────────  │
│  Domain           Repositories ↔ Repository Traits               │
│                   Entities ↔ Entity Traits                       │
│                   ──────────────────────────────────────────────  │
│  Reactions        Events → Listeners → Notifications             │
│                   ──────────────────────────────────────────────  │
│  Background       Scheduled Jobs · Console Commands              │
│                   ──────────────────────────────────────────────  │
│  Cross-cutting    Services · Support · View Composers · Brokers  │
│                   Generators (build-time only)                   │
└──────────────────────────────────────────────────────────────────┘
```

### Typical request lifecycle

A normal admin CRUD request walks through most of these layers in order:

1. **Providers** — `ModularousProvider` boots child providers (`BaseServiceProvider`, `RouteServiceProvider`, `AuthServiceProvider`, …) and binds every container entry the rest of the stack depends on.
2. **Activators** — `ModularousActivator` and `ModuleActivator` short-circuit the request if the target module or route is disabled.
3. **Middleware** — the `web` / `admin` / `api` pipelines run guards (`Authenticate`, `Authorization`, `Hostable`, `Language`, `Navigation`, `HandleInertiaRequests`, …).
4. **Form Request** — a `BaseFormRequest` subclass authorizes the user and validates input before the controller method runs.
5. **Controller** — `BaseController` resolves the active route, applies traits (`ManageIndexAjax`, `ManageInertia`, `ManagePrevious`, `ManageSingleton`, `ManageTranslations`), renders the form schema, and delegates persistence to `$this->repository`.
6. **Repository (+ Traits)** — the base `Repository` invokes every applicable [repository trait](#repository-traits) (`prepareFieldsBeforeSave{Trait}`, `afterSave{Trait}`, …) around the Eloquent `save()`.
7. **Entity (+ Traits)** — Eloquent models compose [entity traits](#entity-traits) for slugs, positions, soft-delete, translations, media, files, repeaters, blocks, processes, state, payments, presenters, and broadcasting context.
8. **Events & Listeners** — model lifecycle and auth events fire; `Listener::handle()` resolves the matching `{EventName}Notification` and dispatches it when `modularous.mail.enabled = true`.
9. **View Composers** — for Inertia/Blade responses, composers inject shared props (current user, navigation, uploader configs, localization, URLs).
10. **Response** — Inertia/JSON/Blade payload returns to the client; broadcasted events propagate over Echo channels.

### How the layers reference each other

| If you are working on… | You typically touch… |
|------------------------|----------------------|
| A new admin route | [Providers](#providers) → [Generators](#generators) → [Controllers](#controllers) → [Form Requests](#form-requests) → [Entities](#entities) → [Repository Traits](#repository-traits) |
| Authentication / registration | [Middleware](#middleware) → [Controllers](#controllers) (Auth) → [Brokers](#brokers) → [Notifications](#notifications) → [Events & Listeners](#events-listeners) |
| Caching / invalidation | [Services](#services) (`ModularousCacheService`, `CacheRelationshipGraph`) → [Repository Traits](#repository-traits) (Logic) → [Console Commands](#console-commands) (`cache:*`) |
| File / media uploads | [Controllers](#controllers) (Filepond / FileLibrary / MediaLibrary) → [Form Requests](#form-requests) → [Services](#services) (FilepondManager, FileLibrary, MediaLibrary, Uploader) → [Entity Traits](#entity-traits) (HasFileponds, HasFiles, HasImages) |
| Multi-tenant routing | [Providers](#providers) (`RouteServiceProvider`) → [Support](#support) (`HostRouting`) → [Middleware](#middleware) (`Hostable`) → [Facades](#facades) (`HostRouting`) |
| Real-time / chat | [Events & Listeners](#events-listeners) → [Notifications](#notifications) → [Services](#services) (BroadcastManager) → [Scheduled Jobs](#scheduled-jobs) (ChatableScheduler) |

→ Architecture deep-dives also live under the [system reference](/system-reference/architecture) — cache, broadcasting, host routing, and module conventions are documented there.

---

## Activators

**Directory**: `src/Activators/` · **Namespace**: `Unusualify\Modularous\Activators`

Activators persist and resolve enable/disable state. Modularous splits activation into two layers — module-level (whether a module loads at all) and route-level (whether a specific route action inside a module is enabled).

| Class | Persisted in | Purpose |
|-------|--------------|---------|
| [`ModularousActivator`](/system-reference/backend/activators/modularous-activator) | `modules_statuses.json` + cache | Stores and resolves module-level statuses (`enabled` / `disabled`) |
| [`ModuleActivator`](/system-reference/backend/activators/module-activator) | `routes_statuses.json` per module | Stores and resolves route-level statuses inside a module |

**CLI integration**: [`route:disable`](/guide/console/module/route-disable), [`route:enable`](/guide/console/module/route-enable), [`route:status`](/guide/console/module/route-status) read and mutate these files.

→ [Activators reference](/system-reference/backend/activators/overview)

---

## Brokers

**Directory**: `src/Brokers/` · **Namespace**: `Unusualify\Modularous\Brokers`

Powers the email-verification-based registration flow behind the [`Register` facade](/system-reference/backend/facades/register). Mirrors Laravel's password broker design, adapted for verification tokens.

| Class | Role |
|-------|------|
| [`RegisterBroker`](/system-reference/backend/brokers/register-broker) | Executes verification-link and registration-token operations |
| [`RegisterBrokerManager`](/system-reference/backend/brokers/register-broker-manager) | Resolves named broker instances and selects the default broker |
| [`TokenRepositoryInterface`](/system-reference/backend/brokers/token-repository-interface) | Contract for email-based token repository methods |

**Flow**: controller → `Register::broker()` → `RegisterBrokerManager` → `RegisterBroker` → `TokenRepositoryInterface`.

→ [Brokers reference](/system-reference/backend/brokers/overview)

---

## Console Commands

**Directory**: `src/Console/` · **Namespace**: `Unusualify\Modularous\Console`

Discovered via `CommandDiscovery::discover()` in `BaseServiceProvider`. Every command extends a `BaseCommand`, which adds module-aware option resolution and consistent output formatting on top of `Illuminate\Console\Command`.

| Category | Path | Commands |
|----------|------|----------|
| Cache | `Console/Cache/` | [`cache:clear`](/guide/console/cache/cache-clear), [`cache:list`](/guide/console/cache/cache-list), [`cache:warm`](/guide/console/cache/cache-warm), [`cache:stats`](/guide/console/cache/cache-stats), [`cache:versions`](/guide/console/cache/cache-versions), [`cache:graph`](/guide/console/cache/cache-graph) |
| Coverage | `Console/Coverage/` | `coverage:report`, `coverage:methods` |
| Docs | `Console/Docs/` | Docs scaffolding helpers |
| Flush | `Console/Flush/` | [`flush`](/guide/console/flush/flush), [`flush:filepond`](/guide/console/flush/flush-filepond), [`flush:sessions`](/guide/console/flush/flush-sessions) |
| Make | `Console/Make/` | `make:controller`, `make:controller-api`, `make:event`, `make:feature`, `make:input-hydrate`, `make:laravel-test`, `make:listener`, `make:migration`, `make:model`, `make:module`, `make:operation`, `make:repository`, `make:repository-trait`, `make:request`, `make:route`, `make:route-permissions`, `make:stubs`, `make:theme`, `make:vue-input`, `make:vue-test` |
| Migration | `Console/Migration/` | `migrate`, `migrate:refresh`, `migrate:rollback` |
| Module | `Console/Module/` | `module:fix`, `module:remove`, `route:disable`, `route:enable`, [`route:status`](/guide/console/module/route-status) |
| Operations | `Console/Operations/` | Workflow / batch-operation runners |
| Setup | `Console/Setup/` | `create-superadmin`, [`create:database`](/guide/console/setup/create-database), `install`, `setup:dev` |
| Sync | `Console/Sync/` | [`sync:states`](/guide/console/sync/sync-states), [`sync:translations`](/guide/console/sync/sync-translations) |
| Top-level | `Console/` | [`build`](/guide/console/build), `composer:merge`, `composer:scripts`, `db:check-collation`, `dev`, [`pint`](/guide/console/pint), `refresh`, `replace:regex`, `version` |
| Update | `Console/Update/` | Module-update commands |

→ Per-command usage is documented in the [Console guide](/guide/console/overview).

---

## Controllers

**Directory**: `src/Http/Controllers/` · **Namespace**: `Unusualify\Modularous\Http\Controllers`

The controller hierarchy is **CoreController → PanelController → BaseController**. Modules extend `BaseController` and configure form schemas, with each layer adding more module-aware behaviour.

| Layer | Purpose |
|-------|---------|
| [`CoreController`](/system-reference/backend/http/controllers/core-controller) | Base HTTP controller — shared response helpers |
| [`PanelController`](/system-reference/backend/http/controllers/panel-controller) | Route/model resolution, index options, authorization, `$this->repository` |
| [`BaseController`](/system-reference/backend/http/controllers/base-controller) | View prefix, form schema, index/create/edit flow, `setupFormSchema()` |

**`BaseController` traits**: `ManageIndexAjax`, `ManageInertia`, `ManagePrevious`, `ManageSingleton`, `ManageTranslations`.

**Request flow**: `preload()` → `addWiths()` → `setupFormSchema()` → `index()` / `create()` / `edit()` → `respondToIndexAjax()` for AJAX.

**Concrete controllers** (selection): `ApiController`, `ChatController`, `DashboardController`, `FileLibraryController`, `FilepondController`, `MediaLibraryController`, `MetricController`, `ProcessController`, `ProfileController`, `TagController`, `UiPreferencesController`, plus auth-scoped controllers under `Http/Controllers/Auth/`.

→ [Full Controllers reference](/system-reference/backend/http/controllers/overview)

---

## Entities

**Directory**: `src/Entities/` · **Namespace**: `Unusualify\Modularous\Entities`

~30 Eloquent models for users, content, files, processes, state, and communication, plus supporting `Casts/`, `Enums/`, `Interfaces/`, `Mutators/`, `Observers/`, `Scopes/`, `Traits/`, and `Translations/` subfolders. Module-generated models extend `Model` and gain soft-deletes, tagging, caching, presenter support, and trait composition out of the box.

| Group | Models | Reference |
|-------|--------|-----------|
| **Base classes** | Model, Revision | [Model →](/system-reference/backend/entities/model) · [Revision →](/system-reference/backend/entities/revision) |
| **Communication** | Chat, ChatMessage | [Chat →](/system-reference/backend/entities/chat) · [ChatMessage →](/system-reference/backend/entities/chat-message) |
| **Content building blocks** | Block, Repeater, Tag, Tagged | [Block →](/system-reference/backend/entities/block) · [Repeater →](/system-reference/backend/entities/repeater) · [Tag →](/system-reference/backend/entities/tag) |
| **Files & Media** | File, Filepond, Media, TemporaryFilepond | [File →](/system-reference/backend/entities/file) · [Filepond →](/system-reference/backend/entities/filepond) · [Media →](/system-reference/backend/entities/media) |
| **Other** | Feature, NestedsetCollection, RelatedItem | [Feature →](/system-reference/backend/entities/feature) · [NestedsetCollection →](/system-reference/backend/entities/nestedset-collection) · [RelatedItem →](/system-reference/backend/entities/related-item) |
| **Process & Workflow** | Assignment, Authorization, CreatorRecord, Process, ProcessHistory | [Assignment →](/system-reference/backend/entities/assignment) · [Authorization →](/system-reference/backend/entities/authorization) · [Process →](/system-reference/backend/entities/process) |
| **State & Data** | Setting, Singleton, Spread, State, Stateable | [Singleton →](/system-reference/backend/entities/singleton) · [State →](/system-reference/backend/entities/state) · [Stateable →](/system-reference/backend/entities/stateable) |
| **Users & Auth** | Company, Profile, User, UserOauth | [Company →](/system-reference/backend/entities/company) · [Profile →](/system-reference/backend/entities/profile) · [User →](/system-reference/backend/entities/user) |

**Enums**: AssignmentStatus, PaymentStatus, Permission, ProcessStatus, RoleTeam, UserRole.

→ [Full Entities reference](/system-reference/backend/entities/overview)

---

## Entity Traits

**Directory**: `src/Entities/Traits/` · **Namespace**: `Unusualify\Modularous\Entities\Traits`

A library of traits (top-level + nested under `Auth/`, `Core/`, `Secondary/`) covering relationships, accessors, scopes, lifecycle hooks, and broadcasting context. Each trait composes onto an entity to add a self-contained behaviour without subclassing.

| Group | Examples | Reference |
|-------|----------|-----------|
| **Auth** | CanRegister, HasOauth | [Auth →](/system-reference/backend/entity-traits/auth/overview) |
| **Core (top-level)** | HasPosition, HasPresenter, HasSlug, HasSpreadable, HasStateable, HasUuid | [Core →](/system-reference/backend/entity-traits/core/overview) |
| **Media** | HasFileponds, HasFiles, HasImages | [Media →](/system-reference/backend/entity-traits/media/overview) |
| **Model behavior** | HasPosition, HasPresenter, HasSlug, HasSpreadable, HasStateable, HasUuid | [Model behavior →](/system-reference/backend/entity-traits/model-behavior/overview) |
| **Payment** | HasPayment, HasPriceable | [Payment →](/system-reference/backend/entity-traits/payment/overview) |
| **Processes** | HasProcesses, Processable | [Processes →](/system-reference/backend/entity-traits/processes/overview) |
| **Relationships** | Assignable, Chatable, HasAuthorizable, HasCreator | [Relationships →](/system-reference/backend/entity-traits/relationships/overview) |
| **Repeaters** | HasRepeaters | [Repeaters →](/system-reference/backend/entity-traits/repeaters/overview) |
| **Secondary** | HasBlocks, HasNesting, HasRelated, HasRevisions | [Secondary →](/system-reference/backend/entity-traits/secondary/overview) |
| **Singletons** | IsHostable, IsSingular | [Singletons →](/system-reference/backend/entity-traits/singletons/overview) |
| **Translation** | HasTranslation, IsTranslatable | [Translation →](/system-reference/backend/entity-traits/translation/overview) |

→ [Full Entity Traits reference](/system-reference/backend/entity-traits/overview)

---

## Events & Listeners

**Directory**: `src/Events/`, `src/Listeners/` · **Namespace**: `Unusualify\Modularous\Events`, `Unusualify\Modularous\Listeners`

Events fire at lifecycle boundaries (model created/updated, user registered, state changed). Listeners react and, when mail is enabled, dispatch the matching notification.

| Class | Role | Page |
|-------|------|------|
| [`Listener`](/system-reference/backend/events/listener) | Abstract base for listeners — resolves notification class from event name | Class reference |
| [`ModelEvent`](/system-reference/backend/events/model-event) | Abstract base — composes `EventChanges`, `EventStateable`, `EventUrls`, `EventUser` traits and provides broadcasting defaults | Class reference |
| [User events](/system-reference/backend/events/user-events) | `ModularousUserRegistered`, `ModularousUserRegistering`, `ModularousUserVerification`, `VerifiedEmailRegister` | Auth events |

**Event traits** (under `events/traits/`): [`EventChanges`](/system-reference/backend/events/traits/event-changes), [`EventStateable`](/system-reference/backend/events/traits/event-stateable), [`EventUrls`](/system-reference/backend/events/traits/event-urls), [`EventUser`](/system-reference/backend/events/traits/event-user). Each is auto-set up in `ModelEvent`'s constructor and its public properties are serialized into broadcast payloads.

→ [Full Events reference](/system-reference/backend/events/overview)
→ Real-time setup, Echo integration, testing, and troubleshooting are covered in the [Broadcasting guide](/guide/broadcasting/overview).

---

## Facades

**Directory**: `src/Facades/` · **Namespace**: `Unusualify\Modularous\Facades`

21 Laravel facades providing static-style access to bound services. Each facade aliases a container entry to a concrete service class.

**Selected facades**: [`Coverage`](/system-reference/backend/facades/coverage), [`CurrencyExchange`](/system-reference/backend/facades/currency-exchange), [`Filepond`](/system-reference/backend/facades/filepond), [`HostRouting`](/system-reference/backend/facades/host-routing), [`MigrationBackup`](/system-reference/backend/facades/migration-backup), [`Modularous`](/system-reference/backend/facades/modularous), [`ModularousCache`](/system-reference/backend/facades/modularous-cache), [`ModularousRoutes`](/system-reference/backend/facades/modularous-routes), [`ModularousVite`](/system-reference/backend/facades/modularous-vite), [`Navigation`](/system-reference/backend/facades/navigation), [`Redirect`](/system-reference/backend/facades/redirect), [`Register`](/system-reference/backend/facades/register), [`RelationshipGraph`](/system-reference/backend/facades/relationship-graph), [`SiteSettings`](/system-reference/backend/facades/site-settings), [`SystemSettings`](/system-reference/backend/facades/system-settings), [`CmsSettings`](/system-reference/backend/facades/cms-settings), [`Utm`](/system-reference/backend/facades/utm).

Settings deep-dive: [Settings Services](/system-reference/backend/services/settings/overview).

→ [Full Facades reference](/system-reference/backend/facades/overview)

---

## Form Requests

**Directory**: `src/Http/Requests/` · **Namespace**: `Unusualify\Modularous\Http\Requests`

Form Request classes that validate, authorize, and shape incoming requests for module endpoints. Extend Laravel's `FormRequest` with module-aware authorization and translation hooks.

| Class | Purpose |
|-------|---------|
| [`BaseFormRequest`](/system-reference/backend/http/request/base-form-request) | Shared validation/authorization plumbing |
| [`FileRequest`](/system-reference/backend/http/request/file-request) | File upload validation |
| [`MediaRequest`](/system-reference/backend/http/request/media-request) | Media upload validation |
| [`OAuthRequest`](/system-reference/backend/http/request/oauth-request) | OAuth callback payload validation |
| [`Request`](/system-reference/backend/http/request/request) | Generic module-aware base form request |
| [`StorePermissionRequest`](/system-reference/backend/http/request/store-permission-request) | Permission creation |
| [`StoreRoleRequest`](/system-reference/backend/http/request/store-role-request) | Role creation |

→ [Full Form Requests reference](/system-reference/backend/http/request/overview)

---

## Generators

**Directory**: `src/Generators/` · **Namespace**: `Unusualify\Modularous\Generators`

Scaffolding engine behind `make:*` and `make:route` commands. Produces the full PHP and JS file set for new module routes plus test scaffolding for both backend and frontend.

```
Generator (abstract)                ← NwidartGenerator + ReplacementTrait
├── RouteGenerator                  ← full-stack route scaffolding (primary)
├── StubsGenerator                  ← stub-only regeneration (fix/patch)
├── VueTestGenerator                ← Vitest/Jest test scaffolding
└── LaravelTestGenerator            ← PHPUnit test scaffolding
```

| Generator | Responsibility |
|-----------|---------------|
| [`Generator`](/system-reference/backend/generators/generator) | Abstract base — module resolution, config path helpers |
| [`LaravelTestGenerator`](/system-reference/backend/generators/laravel-test-generator) | PHPUnit Unit or Feature test scaffolding |
| [`RouteGenerator`](/system-reference/backend/generators/route-generator) | Full set of files for a new module route (model, migration, controller, repository, request, translations, permissions) |
| [`StubsGenerator`](/system-reference/backend/generators/stubs-generator) | Selective stub regeneration with `only` / `except` lists |
| [`VueTestGenerator`](/system-reference/backend/generators/vue-test-generator) | Vue component / composable / utility / store test scaffolding |

→ [Full Generators reference](/system-reference/backend/generators/overview)

---

## Helpers

**Directory**: `src/Helpers/` · **Namespace**: global functions

15 PHP helper files exposing 100+ global functions. Loaded by Composer autoload, available everywhere.

| File | What it covers |
|------|----------------|
| [`array`](/system-reference/backend/helpers/array) | Array shape transforms |
| [`column`](/system-reference/backend/helpers/column) | Column metadata helpers |
| [`component`](/system-reference/backend/helpers/component) | Frontend component resolution |
| [`composer`](/system-reference/backend/helpers/composer) | Composer JSON parsing |
| [`connector`](/system-reference/backend/helpers/connector) | External-service connector helpers |
| [`db`](/system-reference/backend/helpers/db) | Schema introspection (`hasColumn`, `hasIndex`, etc.) |
| [`format`](/system-reference/backend/helpers/format) | Currency / date / number formatting |
| [`front`](/system-reference/backend/helpers/front) | Frontend URL/asset helpers |
| [`i18n`](/system-reference/backend/helpers/i18n) | Locale and translation helpers |
| [`input`](/system-reference/backend/helpers/input) | Input schema / hydrate helpers |
| [`media`](/system-reference/backend/helpers/media) | Image URL / disk resolution |
| [`migrations`](/system-reference/backend/helpers/migrations) | Migration build helpers |
| [`module`](/system-reference/backend/helpers/module) | `modularousConfig()`, `module_path()`, `currentModule()` |
| [`router`](/system-reference/backend/helpers/router) | Route resolution helpers |
| [`sources`](/system-reference/backend/helpers/sources) | Module path discovery |

→ [Full Helpers reference](/system-reference/backend/helpers/overview)

---

## Middleware

**Directory**: `src/Http/Middleware/` · **Namespace**: `Unusualify\Modularous\Http\Middleware`

14 middleware classes registered via `BaseServiceProvider` and grouped into pipelines for admin, auth, and API routes.

| Middleware | Purpose |
|-----------|---------|
| [`Authenticate`](/system-reference/backend/http/middleware/authenticate) | Modularous-aware auth guard |
| [`Authorization`](/system-reference/backend/http/middleware/authorization) | Permission/role enforcement |
| [`CompanyRegistration`](/system-reference/backend/http/middleware/company-registration) | Company onboarding gate |
| [`HandleInertiaRequests`](/system-reference/backend/http/middleware/handle-inertia-requests) | Inertia shared props |
| [`Hostable`](/system-reference/backend/http/middleware/hostable) | Host-based routing resolution |
| [`Impersonate`](/system-reference/backend/http/middleware/impersonate) | User impersonation gate |
| [`Language`](/system-reference/backend/http/middleware/language) | Locale resolution from URL/header |
| [`LoadLocalizedConfig`](/system-reference/backend/http/middleware/load-localized-config) | Locale-specific config loading |
| [`Log`](/system-reference/backend/http/middleware/log) | Request logging |
| [`Navigation`](/system-reference/backend/http/middleware/navigation) | Sidebar nav assembly |
| [`RedirectIfAuthenticated`](/system-reference/backend/http/middleware/redirect-if-authenticated) | Guest-only routes |
| [`Redirector`](/system-reference/backend/http/middleware/redirector) | Configured redirect rules |
| [`TeamsPermission`](/system-reference/backend/http/middleware/teams-permission) | Team-aware permission resolution |
| [`Utm`](/system-reference/backend/http/middleware/utm) | UTM parameter capture |

→ [Full Middleware reference](/system-reference/backend/http/middleware/overview)

---

## Notifications

**Directory**: `src/Notifications/` · **Namespace**: `Unusualify\Modularous\Notifications`

| Group | Classes | Page |
|-------|---------|------|
| **Auth notifications** | `EmailVerification`, `GeneratePasswordNotification`, `ResetPasswordNotification` | [Auth notifications →](/system-reference/backend/notifications/auth-notifications) |
| **Feature base** | `FeatureNotification` | [FeatureNotification →](/system-reference/backend/notifications/feature-notification) |
| **System notifications** | 11 system notification classes (model lifecycle, payments, assignments, chat, state changes) | [System notifications →](/system-reference/backend/notifications/system-notifications) |

Notifications are dispatched by `Listener::handle()` when `modularous.mail.enabled = true` and the listener resolves a `{EventName}Notification` class.

→ [Full Notifications reference](/system-reference/backend/notifications/overview)

---

## Providers

**Directory**: `src/Providers/` · **Namespace**: `Unusualify\Modularous\Providers`

Service providers wire the package into the host Laravel app. Bind services, publish config, register routes, hook view composers, wire the scheduler, and discover commands.

| Provider | Role |
|----------|------|
| [`AuthServiceProvider`](/system-reference/backend/providers/auth-service-provider) | Modularous policies and gates |
| [`BaseServiceProvider`](/system-reference/backend/providers/base-service-provider) | Core bindings, command discovery, view composers, scheduler, middleware aliases |
| [`CoverageServiceProvider`](/system-reference/backend/providers/coverage-service-provider) | Coverage analyzer + commands binding |
| [`ModularousProvider`](/system-reference/backend/providers/modularous-provider) | Top-level provider — registers all child providers |
| [`ModuleServiceProvider`](/system-reference/backend/providers/module-service-provider) | Per-module provider auto-generated for each module |
| [`RouteServiceProvider`](/system-reference/backend/providers/route-service-provider) | Module route registration with host/permission groups |
| [`ServiceProvider`](/system-reference/backend/providers/service-provider) | Abstract base with config publishing helpers |
| [`TelescopeServiceProvider`](/system-reference/backend/providers/telescope-service-provider) | Telescope integration when present |

→ [Full Providers reference](/system-reference/backend/providers/overview)

---

## Repository Traits

**Directory**: `src/Repositories/Traits/` · **Namespace**: `Unusualify\Modularous\Repositories\Traits`

Repository traits extend the base `Repository` class with domain-specific persistence logic. While [Entity Traits](/system-reference/backend/entity-traits/overview) define model-level behaviour, repository traits handle **how data flows in and out** — form field hydration, after-save side effects, table filters, caching.

Every repository trait follows a naming convention that the base `Repository` discovers and invokes at the right lifecycle stage:

| Convention | When called |
|------------|-------------|
| `setColumns{Trait}` | Boot — registers form input names this trait manages |
| `prepareFieldsBeforeCreate{Trait}` | Before insert |
| `prepareFieldsBeforeSave{Trait}` | Before any save |
| `beforeSave{Trait}` / `afterSave{Trait}` | Around the Eloquent `save()` call |
| `hydrate{Trait}` | Sets in-memory relationships before save (preview/validation) |
| `getFormFields{Trait}` | Populates form fields when editing |
| `afterDelete{Trait}` / `afterRestore{Trait}` | Soft-delete + restore hooks |
| `filter{Trait}` / `order{Trait}` | Index query scopes / ordering |
| `getTableFilters{Trait}` | Filter tab definitions for the data table UI |
| `getFormActions{Trait}` | Form action button definitions |

| Group | Page |
|-------|------|
| Content (Blocks, Repeaters) | [Content →](/system-reference/backend/repository-traits/content) |
| Logic helpers (caching, schema, query building) | [Logic →](/system-reference/backend/repository-traits/logic/overview) |
| Media (Files, Images, Filepond) | [Media →](/system-reference/backend/repository-traits/media) |
| OAuth | [OAuth →](/system-reference/backend/repository-traits/oauth) |
| Payment | [Payment →](/system-reference/backend/repository-traits/payment) |
| Processes | [Processes →](/system-reference/backend/repository-traits/processes) |
| Relationships | [Relationships →](/system-reference/backend/repository-traits/relationships) |
| State | [State →](/system-reference/backend/repository-traits/state) |

→ [Full Repository Traits reference](/system-reference/backend/repository-traits/overview)

---

## Scheduled Jobs

**Directory**: `src/Schedulers/` · **Namespace**: `Unusualify\Modularous\Schedulers`

Background jobs auto-discovered from `src/Schedulers/*.php` via `CommandDiscovery` and registered against Laravel's `Schedule` inside `BaseServiceProvider::boot()` (no `Console\Kernel.php` is required in the host app).

| Command | Class | Cadence | Purpose |
|---------|-------|---------|---------|
| `modularous:fileponds:scheduler` | [`FilepondsScheduler`](/system-reference/backend/scheduled-jobs/fileponds-scheduler) | Daily | Cleans up orphaned `temporary_fileponds` rows + their files |
| `modularous:scheduler:chatable` | [`ChatableScheduler`](/system-reference/backend/scheduled-jobs/chatable-scheduler) | Every minute | Aggregates unread chat messages and dispatches `UnreadChatMessage` notifications |
| `telescope:prune` | (Laravel Telescope) | Daily | Prunes Telescope entries older than 168 hours |

Both Modularous schedulers can also be run manually as Artisan commands. Output goes to the `scheduler` log channel; the host server only needs the standard `* * * * * php artisan schedule:run` cron entry.

→ [Full Scheduled Jobs reference](/system-reference/backend/scheduled-jobs/overview)

---

## Services

**Directory**: `src/Services/` · **Namespace**: `Unusualify\Modularous\Services`

Bound in the service container; injected via constructor or accessed through their dedicated [Facades](#facades). Each group has its own reference page.

| Group | Members | Page |
|-------|---------|------|
| **Cache concerns** | CacheHelpers, CacheInvalidation, CacheTags | [Cache Concerns →](/system-reference/backend/services/cache-concerns/overview) |
| **Core services** | Assets, BroadcastManager, Connector, CoverageService, CurrencyExchangeService, FilepondManager, FileTranslation, MessageStage, MigrationBackup, ModularousCacheService, RedirectService, Translation, UtmParameters | [Services →](/system-reference/backend/services/overview) |
| **Currency** | NullCurrencyProvider, SystemPricingCurrencyProvider | [Currency →](/system-reference/backend/services/currency/overview) |
| **Settings** | AbstractSingularSettingsService, SystemSettingsService, CmsSettingsService, SiteSettingsService | [Settings →](/system-reference/backend/services/settings/overview) |
| **FileLibrary** | Disk, FileService | [FileLibrary →](/system-reference/backend/services/file-library/overview) |
| **MediaLibrary** | Glide, Imgix, Local, TwicPics drivers | [MediaLibrary →](/system-reference/backend/services/media-library/overview) |
| **Uploader** | SignAzureUpload, SignS3Upload, SignUploadListener | [Uploader →](/system-reference/backend/services/uploader/overview) |
| **View services** | ModularousNavigation, UComponent, UWidget, UWrapper | [View Services →](/system-reference/backend/services/view/overview) |

**Cache support**: [`CacheRelationshipGraph`](/system-reference/backend/services/cache-relationship-graph) drives cross-entity cache invalidation. The CLI face is [`cache:graph`](/guide/console/cache/cache-graph).

→ [Full Services reference](/system-reference/backend/services/overview)

---

## Support

**Directory**: `src/Support/` · **Namespace**: `Unusualify\Modularous\Support`

Stateless utility classes used across the codebase for command discovery, schema parsing, route grouping, and host-based routing.

| Class | Purpose |
|-------|---------|
| [`CommandDiscovery`](/system-reference/backend/support/command-discovery) | Scan glob paths and return instantiable `Command` FQCNs |
| [`CoverageAnalyzer`](/system-reference/backend/support/coverage-analyzer) | Parse Clover XML and report per-method coverage |
| [`Decomposers`](/system-reference/backend/support/decomposers) | Parse schema/relation/validation strings for generators |
| [`FileLoader`](/system-reference/backend/support/file-loader) | Translation file loader with multi-path support |
| [`Finder`](/system-reference/backend/support/finder) | Resolve model/repository by table name or route name |
| [`HostRouting / HostRouteRegistrar`](/system-reference/backend/support/host-routing) | Multi-tenant host-based route groups |
| [`Migrations\SchemaParser`](/system-reference/backend/support/migrations-schema-parser) | Render migration `$table->…` PHP from schema strings |
| [`ModularousRoutes`](/system-reference/backend/support/modularous-routes) | Route group options and middleware alias registration |
| [`ModularousVite`](/system-reference/backend/support/modularous-vite) | Vite integration for the Modularous asset manifest |
| [`RegexReplacement`](/system-reference/backend/support/regex-replacement) | Batch regex find-and-replace across a directory tree |

→ [Full Support reference](/system-reference/backend/support/overview)

---

## View Composers

**Directory**: `src/Http/ViewComposers/` · **Namespace**: `Unusualify\Modularous\Http\ViewComposers`

Inject shared variables into Blade / Inertia layouts. Wired in `BaseServiceProvider::registerViewComposers()`.

| Composer | Views | Condition |
|----------|-------|-----------|
| [`ActiveNavigation`](/system-reference/backend/http/view-composers/active-navigation) | layouts with sidebar | Always |
| [`CurrentUser`](/system-reference/backend/http/view-composers/current-user) | `admin.*`, `{baseKey}::*` | `enabled.users-management` = true |
| [`FilesUploaderConfig`](/system-reference/backend/http/view-composers/files-uploader-config) | master/app-inertia layouts | `enabled.file-library` = true |
| [`Localization`](/system-reference/backend/http/view-composers/localization) | master/auth/app-inertia layouts | Always |
| [`MediasUploaderConfig`](/system-reference/backend/http/view-composers/medias-uploader-config) | master/app-inertia layouts | `enabled.media-library` = true |
| [`Urls`](/system-reference/backend/http/view-composers/urls) | `*` | Always |

→ [Full View Composers reference](/system-reference/backend/http/view-composers/overview)

---

## Cross-cutting concerns

A few capabilities don't live in a single section — they thread through several layers at once. Subsections below are ordered alphabetically; use them as a map to find every place a given concern shows up.

### Authentication, authorization & registration

Auth is split between the request pipeline (middleware), the Spatie permission stack (gates/policies), and a verification-token broker for self-service registration.

| Layer | Component |
|-------|-----------|
| Brokers | [`RegisterBroker`, `RegisterBrokerManager`, `TokenRepositoryInterface`](/system-reference/backend/brokers/overview) |
| Controllers | `Http/Controllers/Auth/*` |
| Entities & Traits | `Company`, `Profile`, `User`, `UserOauth`; `CanRegister`, `HasOauth` |
| Enums | `Permission`, `RoleTeam`, `UserRole` |
| Form Requests | `OAuthRequest`, `StorePermissionRequest`, `StoreRoleRequest` |
| Middleware | [`Authenticate`](/system-reference/backend/http/middleware/authenticate), [`Authorization`](/system-reference/backend/http/middleware/authorization), [`CompanyRegistration`](/system-reference/backend/http/middleware/company-registration), [`Impersonate`](/system-reference/backend/http/middleware/impersonate), [`RedirectIfAuthenticated`](/system-reference/backend/http/middleware/redirect-if-authenticated), [`TeamsPermission`](/system-reference/backend/http/middleware/teams-permission) |
| Notifications | `EmailVerification`, `GeneratePasswordNotification`, `ResetPasswordNotification` |
| Providers | [`AuthServiceProvider`](/system-reference/backend/providers/auth-service-provider) |

### Caching

Modularous ships a versioned, tag-aware cache built on top of Laravel's cache. Invalidation propagates through a relationship graph so editing a model also flushes anything that depends on it.

| Layer | Component |
|-------|-----------|
| Bind / boot | `BaseServiceProvider` (registers `ModularousCacheService`, `CacheRelationshipGraph`) |
| CLI | [`cache:clear`](/guide/console/cache/cache-clear), [`cache:graph`](/guide/console/cache/cache-graph), [`cache:list`](/guide/console/cache/cache-list), [`cache:stats`](/guide/console/cache/cache-stats), [`cache:versions`](/guide/console/cache/cache-versions), [`cache:warm`](/guide/console/cache/cache-warm) |
| Concerns | [`CacheHelpers`, `CacheInvalidation`, `CacheTags`](/system-reference/backend/services/cache-concerns/overview) |
| Day-to-day API | [`ModularousCache`](/system-reference/backend/facades/modularous-cache) facade, [`CacheRelationshipGraph`](/system-reference/backend/services/cache-relationship-graph) |
| Repository hooks | [Repository Traits → Logic](/system-reference/backend/repository-traits/logic/overview) (`afterSave{Trait}`, `afterDelete{Trait}`) |

→ Full deep-dive: [ModularousCacheService](/system-reference/backend/services/modularous-cache-service).

### File & media uploads

Three independent upload pipelines share the same general shape (Form Request → Service → Entity Trait).

| Pipeline | Controller | Service | Entity Trait | Repository Trait |
|----------|-----------|---------|--------------|------------------|
| Direct cloud uploads | — | [Uploader](/system-reference/backend/services/uploader/overview) (`SignAzureUpload`, `SignS3Upload`) | — | — |
| File library | `FileLibraryController` | [FileLibrary](/system-reference/backend/services/file-library/overview) | `HasFiles` | [Media](/system-reference/backend/repository-traits/media) |
| Filepond (chunked uploads) | `FilepondController` | [`FilepondManager`](/system-reference/backend/services/overview) | `HasFileponds` | [Media](/system-reference/backend/repository-traits/media) |
| Media library (image variants) | `MediaLibraryController` | [MediaLibrary](/system-reference/backend/services/media-library/overview) | `HasImages` | [Media](/system-reference/backend/repository-traits/media) |

### Module scaffolding & generation

Build-time only — these run from the CLI to produce or regenerate code.

| Layer | Component |
|-------|-----------|
| CLI | `make:*` commands ([Console Commands](#console-commands)) |
| Generators | [`Generator`, `LaravelTestGenerator`, `RouteGenerator`, `StubsGenerator`, `VueTestGenerator`](#generators) |
| Support | [`Decomposers`](/system-reference/backend/support/decomposers), [`Migrations\SchemaParser`](/system-reference/backend/support/migrations-schema-parser), [`RegexReplacement`](/system-reference/backend/support/regex-replacement) |

→ End-to-end walkthrough: [Modules reference](/system-reference/modules).

### Multi-tenant / host-based routing

When the same module needs to serve different domains with different middleware or permission groups.

| Layer | Component |
|-------|-----------|
| Entity Traits | `IsHostable`, `IsSingular` |
| Facade | [`HostRouting`](/system-reference/backend/facades/host-routing) |
| Middleware | [`Hostable`](/system-reference/backend/http/middleware/hostable) |
| Provider | [`RouteServiceProvider`](/system-reference/backend/providers/route-service-provider) |
| Support | [`HostRouting / HostRouteRegistrar`](/system-reference/backend/support/host-routing), [`ModularousRoutes`](/system-reference/backend/support/modularous-routes) |

### Real-time broadcasting

Model lifecycle and chat events broadcast over Laravel Echo when broadcasting is enabled.

| Layer | Component |
|-------|-----------|
| Base classes | [`Listener`](/system-reference/backend/events/listener), [`ModelEvent`](/system-reference/backend/events/model-event) (with [event traits](/system-reference/backend/events/overview)) |
| Entity Traits | `Chatable`, `HasProcesses`, `HasStateable`, `Processable` |
| Notifications | System notifications (model lifecycle, payments, assignments, chat, state changes) |
| Scheduled job | [`ChatableScheduler`](/system-reference/backend/scheduled-jobs/chatable-scheduler) |
| Service | `BroadcastManager` |

→ Setup, channel naming, Echo client, testing, and troubleshooting: [Broadcasting guide](/guide/broadcasting/overview).

---

→ Looking for something not covered above? Browse the [system reference index](/system-reference/overview) or jump straight into the per-section overview pages linked from each heading.
