---
sidebarPos: 2
sidebarTitle: Content Traits
---

# Content Repository Traits

These traits handle slug persistence, JSON spread attributes, tagging, multi-locale translations, and translatable SEO/metadata at the repository level. They pair with the corresponding Entity Traits (`HasSlug`, `HasSpreadable`, `IsTranslatable`, `HasTranslation`, `HasTranslatableMetadata`).

---

## SlugsTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\SlugsTrait`

Persists locale-aware URL slugs after save, removes them on delete, restores them on restore, and provides slug-based model lookup methods.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `afterSaveSlugsTrait` | For each locale, creates or updates the slug record via `$object->updateOrNewSlug()`. Respects the model's `$slugAttributes` property and derives the `active` flag from translation state. |
| `afterDeleteSlugsTrait` | Soft-deletes all associated slug records |
| `afterRestoreSlugsTrait` | Restores all soft-deleted slug records |
| `getFormFieldsSlugsTrait` | Populates `fields['translations']['slug'][$locale]` from the model's slug records. Picks the active slug per locale (falls back to the only available slug). |

### Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `existsSlug` | `(string $slug, array $with, array $withCount, array $scopes): ?Model` | Looks up a published, visible model by slug. Falls back to inactive slugs (sets `$item->redirect = true`) and fallback locale slugs. |
| `existsSlugPreview` | `(string $slug, array $with, array $withCount): ?Model` | Looks up a model by inactive slug only — used for preview/draft URLs |
| `getSlugParameters` | `($object, $fields, $slug): array` | Merges the model's `slugAttributes` into the slug array for compound slug generation |

### Slug Resolution Order

`existsSlug()` follows this lookup chain:

1. Active slug in the current locale (published + visible scopes applied)
2. Inactive slug in the current locale → `redirect = true`
3. Active slug in the fallback locale (if `translatable.use_property_fallback` is enabled) → `redirect = true`

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\SlugsTrait;

class PageRepository extends Repository
{
    use SlugsTrait;
}

// Resolve a page by slug (returns null or sets redirect flag)
$page = $repo->existsSlug('about-us', ['medias'], [], ['section' => 'main']);

// Preview an unpublished slug
$draft = $repo->existsSlugPreview('upcoming-feature');
```

---

## SpreadableTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\SpreadableTrait`

Moves form fields marked as `spreadable` into and out of the model's JSON `Spread` morph record. This trait bridges the gap between flat form fields and the `HasSpreadable` entity trait's JSON storage.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `setColumnsSpreadableTrait` | Registers inputs matching `type: *spread` |
| `prepareFieldsBeforeSaveSpreadableTrait` | Moves spreadable fields from the flat `$fields` array into `$fields[$spreadableSavingKey]` and removes them from the top level |
| `beforeSaveSpreadableTrait` | Creates the `Spread` record if missing, merges spreadable field values into the JSON content, then sets the spread attribute on the model |
| `getFormFieldsSpreadableTrait` | Reads the `Spread` content and excludes spreadable input keys (those are surfaced as top-level fields by the entity trait's `__get`) |

### Data Flow

```
Form Submit
  ├─ prepareFieldsBeforeSave: flat fields → grouped into spread_payload
  └─ beforeSave: spread_payload merged into Spread JSON record

Form Load (getFormFields)
  └─ Spread JSON → spread_payload field (minus spreadable input keys)
      └─ Entity trait __get surfaces individual keys as model attributes
```

### Helper Method

| Method | Signature | Description |
|--------|-----------|-------------|
| `getSpreadableInputKeys` | `(array $schema): array` | Filters schema inputs where `spreadable === true` and returns their names |

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\SpreadableTrait;

class ProductRepository extends Repository
{
    use SpreadableTrait;
}

// Form schema with spreadable fields:
// ['type' => 'text', 'name' => 'meta_title', 'spreadable' => true]
// ['type' => 'textarea', 'name' => 'meta_description', 'spreadable' => true]
//
// These are stored in the Spread JSON record, not as database columns.
```

---

## TagsTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\TagsTrait`

Handles tag synchronization (create, remove, bulk), locale-aware tags, tag querying, and data table filtering by tag.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `setColumnsTagsTrait` | Registers inputs matching `type: tagger` |
| `afterSaveTagsTrait` | Syncs tags on the model. Supports translated (per-locale) tags via `setLocaleTags()`, flat tags via `setTags()`, and bulk tagging via `tag()`/`untag()`. |
| `getFormFieldsTagsTrait` | Loads tags from the model, grouped by locale for translated inputs or as a flat name list otherwise |
| `filterTagsTrait` | Adds a relation filter scope for `tag_id` → `tags` relationship |

### After-Save Logic

The trait distinguishes two modes:

**Standard save** — when `bulk_tags` is absent:
- If `translated` → calls `$object->setLocaleTags($value, $locale)` per locale.
- Otherwise → calls `$object->setTags($fields['tags'])`.

**Bulk save** — when `bulk_tags` is present (used in multi-select table operations):
- Computes the difference between `previous_common_tags` and new `bulk_tags`.
- Calls `$object->untag($removed)` then `$object->tag($newTags)`.

### Query Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getTags` | `(string $query, array $ids, bool $translated, ?callable $map): Collection` | Fetches tags ordered by usage count. Optionally filters by slug search, taggable IDs, locale grouping, and a custom map callback. |
| `getTagsList` | `(): Collection` | Returns `[{label, value}]` pairs for select dropdowns — only tags with `count > 0` |

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\TagsTrait;

class ArticleRepository extends Repository
{
    use TagsTrait;
}

// Fetch all tags for dropdown
$tags = $repo->getTagsList();

// Search tags matching "tech"
$results = $repo->getTags('tech');

// Get locale-grouped tags
$grouped = $repo->getTags('', [], true);
```

---

## TranslationsTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\TranslationsTrait`

Handles the full translation lifecycle: preparing per-locale fields before save, hydrating translated fields for form editing, filtering/searching across translations, and ordering by translated columns.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `setColumnsTranslationsTrait` | Registers inputs where `translated === true` |
| `prepareFieldsBeforeCreateTranslationsTrait` | Delegates to `prepareFieldsBeforeSave` for new records |
| `prepareFieldsBeforeSaveTranslationsTrait` | Restructures flat translated fields into per-locale arrays with `active` flags based on `translationLanguages` |
| `getFormFieldsTranslationsTrait` | Loads `$object->translations` and maps each translated attribute back into `fields['translations'][$attribute][$locale]` |
| `filterTranslationsTrait` | Adds `orWhereHas('translations', ...)` for search terms that match translatable attributes |
| `orderTranslationsTrait` | Joins the translations table and orders by the translated column for the current locale |

### Field Preparation Flow

```
Input:  fields['title']['en'] = 'Hello', fields['title']['fr'] = 'Bonjour'
        fields['translationLanguages'] = [{value: 'en', published: true}, ...]

Output: fields['en'] = {active: true, title: 'Hello'}
        fields['fr'] = {active: false, title: 'Bonjour'}
```

The `active` flag is derived from:
1. The `translationLanguages` published state.
2. If no language is published, the first locale is auto-published.

### Search Behavior

When scopes contain `searches` with field names matching translated attributes, the trait adds an `orWhereHas('translations', ...)` clause with `LIKE` matching, then removes those attributes from the main scopes to prevent duplicate filtering.

### Ordering Behavior

When an order column is a translated attribute:
1. Joins the translations table.
2. Filters by the current locale.
3. Orders by the translated column.
4. Selects only the main table columns to avoid ambiguity.

### Published Scopes

| Method | Returns | Description |
|--------|---------|-------------|
| `getPublishedScopesTranslationsTrait` | `['withActiveTranslations']` | Used by slug/front-end resolution to only show records with active translations |

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\TranslationsTrait;

class ArticleRepository extends Repository
{
    use TranslationsTrait;
}

// Translated fields are automatically restructured before save
// and hydrated back into form fields on edit.
```

---

## TranslatableMetadataTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\TranslatableMetadataTrait`

**Pairs with**: [`HasTranslatableMetadata`](../entity-traits/translation/overview#repository-companion-trait)

Appends default SEO / robots / sitemap form inputs when the model uses `HasTranslatableMetadata`. Some metadata features flow through this repository hook — **do not** add the entity trait without this companion.

### Convention

- Always add this trait when the entity uses `HasTranslatableMetadata`.
- On translated models, use `TranslationsTrait` **before** `TranslatableMetadataTrait` so translation save / form pipelines run in the expected order.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `appendFormSchemaTranslatableMetadataTrait` | Returns `TranslatableMetadata::defaultFormInputs()` when the model has `HasTranslatableMetadata`; otherwise `[]` |

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\TranslatableMetadataTrait;
use Unusualify\Modularous\Repositories\Traits\TranslationsTrait;

class ArticleRepository extends Repository
{
    use TranslationsTrait, TranslatableMetadataTrait;
}
```
