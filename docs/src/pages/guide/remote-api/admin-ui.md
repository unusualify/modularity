---
sidebarPos: 7
sidebarTitle: Admin UI
---

# Remote API Admin UI

Remote API integrates with Modularous forms (link picker) and data tables (sync toolbar).

## Form Input: `remote-api`

### Module Config

```php
'inputs' => [
    'remote_id' => [
        'type' => 'remote-api',
        'label' => 'Remote record',
        'name' => 'remote_id',
        // Optional named catalog from connector catalogs.*
        // 'catalog' => 'regions',
    ],
],
```

### Hydrate → Vue Pipeline

| Stage | Output |
|-------|--------|
| Config `type: remote-api` | `RemoteApiHydrate` |
| Hydrated schema | `type: input-remote-api` |
| Vue registry | `VInputRemoteApi` (`RemoteApi.vue`) |

Hydrate adds:

- `itemValue` — Resolved from mapping (default remote JSON `id` key).
- `itemTitle` — From `catalog_title_key` config (default `name`).
- `items` — Prefetched catalog when not running in console.
- `catalogTotal` — Row count for incomplete-hydrate detection.
- `catalogEndpoint` — URL to `listRemoteCatalog` for async fetch.

### Frontend Behavior (`RemoteApi.vue`)

- Renders a `v-select` bound to virtual `remote_id`.
- On mount, if hydrated items are empty or `catalogTotal` exceeds loaded rows, fetches `catalogEndpoint` via GET.
- Validates completeness: warns in console if response rows < expected total.
- Prepends a “Please Select” row when items exist.

### Catalog API

`GET {panel}/{module}/{route}/list-remote-catalog?catalog={key}`

Response:

```json
{
  "data": [ { "id": 1, "name": "Example" } ],
  "meta": { "total": 1 }
}
```

Requires repository `RemoteApiSourceTrait` and enabled connector.

## Table Toolbar Actions

When the repository uses `RemoteApiSourceTrait` and connector `enabled` is true, `ManageRemoteApiSync::setTableActionsManageRemoteApiSync()` appends actions from `getRemoteApiActionSchema()`.

## Edit Form Actions

When the same trait and connector are active, `RemoteApiSourceTrait::getFormActionsRemoteApiSourceTrait()` registers record-level actions on the edit form (not create):

| Action key | Schema name | Scope | HTTP |
|------------|-------------|-------|------|
| `sync_record` | `syncRemote` | **form (edit)** | `PUT …/sync-remote/{id}` |
| `preview` | `previewRemote` | **form (edit)** | `PUT …/preview-remote/{id}` |

Form actions are merged in `FormActions::preloadFormActions()` via `repository->getFormActions()`. Endpoints use route names with `:id` placeholders resolved by `FormActions::getFormActions()`.

- Shown only when editing (`creatable: false`) and the record has a linked `remote_id`.
- `syncRemote` reloads the form on success (`reloadOnSuccess: true`).
- `previewRemote` opens a modal with structured remote payload fields via `responseModalAttributes`, `responseDisplay`, `responseFields`, and `useItemActions`.

Table-scoped actions (`sync_all`, `clear_cache`) remain toolbar-only (`scope: table`).

Configured via connector `actions` array:

| Action key | Schema name | Scope | HTTP |
|------------|-------------|-------|------|
| `sync_record` | `syncRemote` | Row / **form (edit)** | `PUT …/sync-remote/{id}` |
| `sync_all` | `syncRemoteAll` | **table** | `POST …/sync-remote-all` |
| `clear_cache` | `clearRemoteCache` | **table** | `POST …/clear-remote-cache` |
| `preview` | `previewRemote` | Row / **form (edit)** | `PUT …/preview-remote/{id}` |

Table-scoped actions include confirmation modals (translated via `messages.remote-api.*` lang keys).

Example generated action shape:

```php
[
    'name' => 'syncRemoteAll',
    'label' => 'Sync all from remote',
    'icon' => 'mdi-sync',
    'color' => 'primary',
    'scope' => 'table',
    'type' => 'request',
    'method' => 'post',
    'endpoint' => '{panel}.{module}.{route}.syncRemoteAll',
    'hasConfirmation' => true,
    'confirmationModalAttributes' => [ /* … */ ],
]
```

`useItemActions` handles confirmation via `useDynamicModal` when `hasConfirmation` is true.

## Preview / Payload Display

`previewRemote` returns the full remote JSON in `data`, plus optional structured display metadata:

```json
{
  "data": { "id": 12, "name": "Europe", "description": "Regional package", "packages": [ … ] },
  "display_mode": "fields",
  "display": [
    { "key": "name", "label": "Name", "value": "Europe" },
    { "key": "description", "label": "Description", "value": "Regional package" }
  ]
}
```

The edit-form action includes:

| Action key | Purpose |
|------------|---------|
| `responseDisplay` | `fields` (default) or `raw` (full JSON) |
| `responseFields` | Field map copied from connector preview config |

Configure per connector:

```php
'preview' => [
    'display' => 'fields', // or 'raw'
    'fields' => [
        ['key' => 'name', 'label' => 'Name'],
        ['key' => 'description', 'label' => 'Description'],
        ['key' => 'packageable.name', 'label' => 'Region'],
    ],
],
```

`AbstractRemoteApiConnector::customizePreviewResponseDisplay()` can reshape server-side display rows after values are extracted from the payload. The frontend prefers `response.data.display` when present, otherwise resolves values from `responseFields` + `data`.

### `syncRemoteAll` Response

Success message summarizes created/updated/skipped counts. JSON includes:

```json
{
  "message": "Synced 12 remote records (2 created, 10 updated).",
  "data": { "created": 2, "updated": 10, "skipped": 0, "total": 12, … },
  "meta": {
    "http_requests": { "total": 1, "by_url": { … } },
    "skipped_records": []
  }
}
```

### `clearRemoteCache` Response

```json
{ "message": "Remote API cache cleared." }
```

Flushes the full connector cache tag/version — use after remote bulk updates.

## Controller Requirements

`BaseController` already uses `ManageRemoteApiSync`. Module controllers extend `BaseController` (or `PanelController`); no extra trait import needed per module.

Routes are registered automatically in `RouteServiceProvider` when the controller implements the action methods (always true via `BaseController`).

## Disabling UI Surfaces

| Goal | How |
|------|-----|
| Hide all Remote API UI | `'enabled' => false` in connector config |
| Hide sync buttons only | Remove `sync_all` / `clear_cache` from `actions` |
| Keep link picker, no sync | `'actions' => []` but leave `enabled` true and form input |

Misconfigured connectors (missing base URL) fail closed — no table actions, hydrate skips connector schema.

## Row-Level Sync

Record-level `syncRemote` and `previewRemote` actions appear automatically on the **edit form** when `sync_record` / `preview` are listed in connector `actions`. They require a linked `remote_id` and are hidden on create.

To expose per-row sync on the index table instead, add a custom row action pointing to `syncRemote`:

```php
// Example manual row action in module table config
[
    'type' => 'request',
    'method' => 'put',
    'endpoint' => '/panel/module-name/entity-name/sync-remote/:id',
    'label' => 'Sync from remote',
]
```
