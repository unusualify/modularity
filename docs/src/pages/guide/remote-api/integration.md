---
sidebarPos: 9
sidebarTitle: Integration Guide
outline: deep
---

# Integrating Remote API into a Module

Step-by-step checklist to add Remote API sync to a module entity. Replace placeholders with your module and route names.

## Prerequisites

- Module enabled with standard entity, repository, controller, and route config.
- External JSON API compatible with Modularous list/show pagination (`data`, `meta.total`, etc.) or adjust `response.*_path` settings.
- Bearer token and base URL available via env or connector config.

## Step 1 — Run Migration

Ensure `um_remote_api_sources` exists:

```bash
php artisan migrate
```

Table name: `config('modularous.tables.remote_api_sources')` → `um_remote_api_sources`.

## Step 2 — Configure Environment

```env
MODULAROUS_REMOTE_API_BASE_URL=https://api.example.com/api/v1
MODULAROUS_REMOTE_API_TOKEN=your-token
MODULAROUS_REMOTE_API_CACHE_TTL=3600
```

## Step 3 — Generate Remote API Classes

```bash
php artisan modularous:make:remote-api-adapter ModuleName entity_name \
    --endpoint=entities
```

Edit `modules/ModuleName/RemoteApi/EntityNameRemoteApiConnector.php`:

1. Set correct `endpoint`, `show_endpoint`, and `response` paths for your API envelope.
2. Expand `mapping` for fields you want mirrored in `synced_attributes`.
3. Add `fields` rules (`local` vs synced) and `sync.preserve_local_fields`.
4. Tune `actions` for admin toolbar visibility.
5. Add optional `catalogs` for secondary pick lists.

Customize `EntityNameRemoteApiAdapter` for complex transforms in `transformField` / `afterMap`.

## Step 4 — Entity Trait

```php
namespace Modules\ModuleName\Entities;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Entities\Traits\HasRemoteApiSource;

class EntityName extends Model
{
    use HasRemoteApiSource;

    protected $fillable = [
        'name',
        'published',
        // local columns only — not remote_id
    ];

    protected array $remoteApiFillable = [
        'remote_id',
        // virtual synced keys edited in admin forms
    ];
}
```

## Step 5 — Repository Trait

```php
namespace Modules\ModuleName\Repositories;

use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\RemoteApiSourceTrait;

class EntityNameRepository extends Repository
{
    use RemoteApiSourceTrait;

    public function __construct()
    {
        $this->model = \Modules\ModuleName\Entities\EntityName::class;
    }
}
```

`RemoteApiSourceTrait` uses `Moduleable` to resolve module name and route name for the connector factory.

## Step 6 — Form Input

In module route config `inputs`:

```php
'remote_id' => [
    'type' => 'remote-api',
    'label' => 'Link to remote record',
    'name' => 'remote_id',
],
```

Rebuild admin assets if needed:

```bash
php artisan modularous:build
```

## Step 7 — Controller (Optional Overrides)

Module controllers extend `BaseController`, which already includes `ManageRemoteApiSync`. Routes for `syncRemote`, `syncRemoteAll`, `clearRemoteCache`, `previewRemote`, and `listRemoteCatalog` register automatically.

Override only if you need custom authorization or response shaping.

## Step 8 — API Transformer (Optional)

Expose merged remote fields in API responses:

```php
public function toArray($request): array
{
    return $this->resource->toRemoteApiArray();
}
```

Or merge selectively in your `Resource` class.

## Step 9 — Verify Configuration

```bash
php artisan modularous:sync-remote-api ModuleName entity_name --dry-run
php artisan modularous:sync-remote-api-catalog ModuleName entity_name --dry-run
```

Fix `RemoteApiConfigurationException` messages (missing base URL, endpoint, etc.) before live sync.

## Step 10 — First Sync

```bash
# Link records in admin via remote-id combobox, then:
php artisan modularous:sync-remote-api ModuleName entity_name

# Or sync one remote row into a new local record:
php artisan modularous:sync-remote-api ModuleName entity_name --id=1
```

Use admin **Sync all from remote** for the same batch operation from the index table.

## Connector Config Checklist

| Setting | Verify |
|---------|--------|
| `enabled` | `true` |
| `base_url` / env | Resolves to reachable API root |
| `endpoint` / `show_endpoint` | Match remote routes |
| `response.list_path` | Matches paginated list JSON |
| `response.item_path` | Matches show JSON |
| `response.meta_path` | Has `current_page`, `last_page`, `total` |
| `mapping.remote_id` | Points to stable remote primary key |
| `sync.import_new_from_list` | Intended import behavior |
| `actions` | Desired admin buttons |

## Mapping Example

Mirror a remote name into synced JSON while keeping a local editable title:

```php
'mapping' => [
    'remote_id' => 'id',
    'remote_payload' => '@raw',
    'remote_synced_at' => '@now',
    'synced_name' => 'name',
    'name' => 'name',
],
'fields' => [
    'name' => ['local' => true],
    'synced_name' => ['sync' => true],
],
'sync' => [
    'preserve_local_fields' => ['name'],
],
```

First sync writes `name` locally; later remote renames update `synced_name` on the side table but preserve the edited local `name`.

## Named Catalogs

For a secondary pick list (e.g. regions):

```php
'catalogs' => [
    'regions' => [
        'endpoint' => 'regions',
        'list_path' => 'data.data',
    ],
],
```

Form input:

```php
'region_remote_id' => [
    'type' => 'remote-api',
    'catalog' => 'regions',
    'name' => 'region_remote_id',
],
```

Extend `$remoteApiFillable` on the entity accordingly.

## Troubleshooting

| Symptom | Check |
|---------|-------|
| No table sync buttons | Repository uses `RemoteApiSourceTrait`; connector `enabled`; valid base URL |
| Empty combobox | `listRemoteCatalog` route; token; `catalog_http.query`; cache flush |
| `incompletePaginatedList` | `meta_path`, API `total`, network timeouts |
| Rate limit errors | Raise outgoing limits or slow scheduler |
| Stale data after remote change | `clearRemoteCache` or lower cache TTL |
| Virtual field saved on wrong table | Entity must use `HasRemoteApiSource`; do not add `remote_id` to parent migration |

## File Summary

| File | Role |
|------|------|
| `RemoteApi/{Route}RemoteApiConnector.php` | Configuration + fetch hooks |
| `RemoteApi/{Route}RemoteApiAdapter.php` | Field transforms |
| `RemoteApi/{Route}RemoteApiDto.php` | Optional typed DTO |
| `Entities/{Entity}.php` | `HasRemoteApiSource` |
| `Repositories/{Entity}Repository.php` | `RemoteApiSourceTrait` |
| Module route config | `remote-api` input, local fields |

No package core edits required — all extension happens in the module `RemoteApi/` folder and standard module artifacts.
