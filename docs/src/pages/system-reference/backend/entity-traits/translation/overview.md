---
sidebarPos: 13
sidebarTitle: Translation Traits
sidebarGroupTitle: Translation Traits
---

# Translation Traits

Two traits handle multi-locale content. `HasTranslation` provides full translation storage backed by `astrotomic/laravel-translatable`. `IsTranslatable` is a lightweight detection helper. Opt-in SEO/robots/sitemap metadata is layered via `HasTranslatableMetadata`.

| Trait | Description |
|-------|-------------|
| [HasTranslation](./has-translation) | Multi-locale content via `astrotomic/laravel-translatable` with Modularous overrides |
| [IsTranslatable](./is-translatable) | Single-method helper that detects whether a model uses translations |
| `HasTranslatableMetadata` | Opt-in SEO + robots + sitemap fields (`src/Entities/Traits/HasTranslatableMetadata.php`) |

## Repository companion trait

When an entity (or content concern) uses **`HasTranslatableMetadata`**, its repository **must** also use **`Unusualify\Modularous\Repositories\Traits\TranslatableMetadataTrait`**.

SEO/metadata admin form inputs are appended via the repository (`appendFormSchemaTranslatableMetadataTrait`). Entity-only usage stores columns but breaks admin/form behavior.

On translated models, put `TranslationsTrait` **before** `TranslatableMetadataTrait` on the repository. See [Content Repository Traits](../../repository-traits/content#translatablemetadatatrait).
