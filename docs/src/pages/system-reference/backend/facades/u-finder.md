---
sidebarPos: 22
sidebarTitle: UFinder (deprecated)
---

# UFinder *(deprecated)*

**Facade**: `Unusualify\Modularous\Facades\UFinder`  
**Accessor**: `Unusualify\Modularous\Support\Finder::class`  
**Underlying**: `Unusualify\Modularous\Support\Finder`

::: warning Deprecated
`UFinder` is deprecated. Use [`ModularousFinder`](./modularous-finder) instead. Both facades resolve to the same underlying `Finder` class — `UFinder` exists only for backwards compatibility.
:::

## Migration

Replace all usages of `UFinder` with `ModularousFinder`:

```php
// Before (deprecated)
use Unusualify\Modularous\Facades\UFinder;
$model = UFinder::getModel('blog_posts');

// After
use Unusualify\Modularous\Facades\ModularousFinder;
$model = ModularousFinder::getModel('blog_posts');
```

All methods are identical. See [ModularousFinder](./modularous-finder) for the full method reference.
