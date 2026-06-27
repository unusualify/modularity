---
sidebarPos: 3
sidebarTitle: Database
---

# Remote API Database

Remote link data is stored in a polymorphic side table; the parent entity keeps only local columns.

## Table: `um_remote_api_sources`

Config key: `modularous.tables.remote_api_sources` (default `um_remote_api_sources` in `config/merges/tables.php`).

Migration: `database/migrations/default/2026_06_21_120000_create_modularous_remote_api_sources_table.php`

| Column | Type | Purpose |
|--------|------|---------|
| `id` | bigint | Primary key |
| `sourceable_type` | string | Morph type (entity class) |
| `sourceable_id` | bigint | Morph id (local record) |
| `remote_id` | unsigned bigint, nullable, indexed | ID in the external API |
| `synced_attributes` | json, nullable | Mirror fields not stored on the parent table |
| `remote_payload` | json, nullable | Last raw API row (`@raw` mapping) |
| `remote_synced_at` | timestamp, nullable | Last successful sync time |
| `created_at` / `updated_at` | timestamps | Standard Laravel timestamps |

**Unique constraint:** `(sourceable_type, remote_id)` — one local morph type cannot link the same remote id twice.

## Entity Model: `HasRemoteApiSource`

Apply the trait to any entity that should link to a remote record:

```php
use Unusualify\Modularous\Entities\Traits\HasRemoteApiSource;

class EntityName extends Model
{
    use HasRemoteApiSource;

    // Optional: extend virtual fillable keys beyond remote_id
    protected array $remoteApiFillable = [
        'remote_id',
        'synced_name',
    ];
}
```

### Relationship

```php
$entity->remoteApiSource; // MorphOne RemoteApiSource
$entity->getRemoteApiId(); // Reads virtual or persisted remote_id
```

### Virtual Attributes

On retrieve, `RemoteApiSourceableObserver` calls `hydrateRemoteApiAttributes()` to merge `synced_attributes` and `remote_id` onto the model for forms and transformers.

On save, virtual keys are:

1. Persisted to `RemoteApiSource` via `persistRemoteApiVirtualAttributes()`.
2. Stripped from the parent row via `stripRemoteApiVirtualAttributes()` and excluded from `getDirty()`.

This keeps CMS-only columns on the entity table and API mirror fields on the side table.

### Presentation

```php
$entity->toRemoteApiArray();
// Merges synced_attributes + parent attributes + remote_id, remote_payload, remote_synced_at
```

## `RemoteApiSource` Model

Location: `src/Entities/RemoteApiSource.php`

- Observed by `RemoteApiSourceObserver` — touches the parent quietly on save.
- `toMergedAttributes()` — helper used by `RemoteApiSynchronizer` when diffing existing state.

## Attribute Partitioning

During sync, `RemoteApiAttributePartition` splits mapped attributes:

| Destination | Criteria |
|-------------|----------|
| **Local** (`$partition['local']`) | Column marked `fields.{col}.local => true`, or in `preserve_local_fields`, and present in model `$fillable` |
| **Remote** (`$partition['remote']`) | Everything else → `remote_id`, `synced_attributes`, `remote_payload`, `remote_synced_at` |

Existing local values win for preserved fields even when the remote payload changes.

## Deletion

`RemoteApiSourceableObserver::forceDeleting` deletes the morph `RemoteApiSource` when the parent entity is force-deleted.

## Admin Form Field

Link a record in the admin UI with a `remote-api` input (see [Admin UI](./admin-ui)):

```php
'remote_id' => [
    'type' => 'remote-api',
    'label' => 'Remote record',
    'name' => 'remote_id',
],
```

Saving the form sets the virtual `remote_id`; the observer persists it on `um_remote_api_sources`.
