---
sidebarPos: 10
sidebarTitle: ModularousVite
---

# ModularousVite

`Unusualify\Modularous\Support\ModularousVite`

Extends Laravel's `Illuminate\Foundation\Vite` to point at Modularous own asset manifest instead of the host application's default `public/build/manifest.json`. Used internally by the `@modularousVite` Blade directive and the `Assets` service.

## Defaults

| Property | Value |
|----------|-------|
| `$buildDirectory` | `vendor/modularous` |
| `$manifestFilename` | `modularous-manifest.json` |
| `$integrityKey` | `integrity` |

The resolved asset path is therefore `public/vendor/modularous/<hashed-file>`.

## Behaviour

When the Vite dev server is running (`hot` file present), `__invoke()` injects the standard `@vite/client` and `@vite-plugin-svg-spritemap/client` scripts followed by each entrypoint as a hot-reload module tag.

In production mode, `__invoke()` reads `modularous-manifest.json`, emits `<link rel="modulepreload">` tags for all chunks, and then the actual `<script type="module">` and `<link rel="stylesheet">` tags for each entrypoint.

## Usage

Consume via the `Assets` service or the `@modularousVite` directive — do not instantiate directly:

```blade
{{-- In your layout --}}
@modularousVite(['resources/js/app.js', 'resources/css/app.css'])
```

Or in PHP:

```php
use Unusualify\Modularous\Services\Assets;

echo app(Assets::class)->vite(['resources/js/app.js']);
```

## Related

- [Assets service](/system-reference/backend/services/assets) — higher-level facade over `ModularousVite`
- [Assets commands](/guide/console/build) — `modularous:build` / `modularous:dev`
