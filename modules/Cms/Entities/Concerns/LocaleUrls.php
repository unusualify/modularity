<?php

namespace Modules\Cms\Entities\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Services\CmsUrlRouteRegistry;
use Modules\Cms\Support\CmsFrontPath;
use Unusualify\Modularous\Entities\Traits\HasSlug;
use Unusualify\Modularous\Entities\Traits\IsSingular;

/**
 * CMS slug validation when resolving public paths, and reverse URL helpers for the public language switcher.
 *
 * @method int|string|null getKey()
 * @method string getMorphClass()
 * @method static Builder withPublicUrlRouteForLocale(?string $locale = null)
 */
trait LocaleUrls
{
    /**
     * Limit the query to models that have a synced {@see UrlRoute::KIND_PAGE_PUBLIC} row for the locale
     * (defaults to the active Laravel locale).
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithPublicUrlRouteForLocale(Builder $query, ?string $locale = null): Builder
    {
        $locale ??= (string) app()->getLocale();
        $registry = app(CmsUrlRouteRegistry::class);

        if (! $registry->tableReady()) {
            return $query->whereRaw('0 = 1');
        }

        /** @var static $model */
        $model = $query->getModel();
        $urlRoutesTable = (new UrlRoute)->getTable();

        return $query->whereExists(function ($sub) use ($urlRoutesTable, $model, $locale): void {
            $sub->from($urlRoutesTable)
                ->whereColumn("{$urlRoutesTable}.urlable_id", $model->getQualifiedKeyName())
                ->where("{$urlRoutesTable}.urlable_type", $model->getMorphClass())
                ->where("{$urlRoutesTable}.locale", $locale)
                ->where("{$urlRoutesTable}.kind", UrlRoute::KIND_PAGE_PUBLIC);
        });
    }

    /**
     * Whether this model class participates in CMS public URL building ({@see HasSlug} or {@see IsSingular}).
     */
    public function supportsCmsPublicUrlBuilding(): bool
    {
        $class = static::class;

        return classHasTrait($class, HasSlug::class) || classHasTrait($class, IsSingular::class);
    }

    /**
     * Synced {@see UrlRoute::normalized_path} values keyed by locale for this instance (empty when registry table is missing).
     *
     * @return array<string, string>
     */
    public function syncedPublicRegistryPathsByLocale(): array
    {
        $registry = $this->cmsUrlRouteRegistry();

        if (! $registry->tableReady() || $this->getKey() === null) {
            return [];
        }

        $canonical = app(CanonicalUrlResolverInterface::class);

        /** @var array<string, string> */
        return UrlRoute::query()
            ->where('urlable_type', $this->getMorphClass())
            ->where('urlable_id', $this->getKey())
            ->where('kind', UrlRoute::KIND_PAGE_PUBLIC)
            ->pluck('normalized_path', 'locale')
            ->map(fn ($path) => $canonical->normalizePath((string) $path))
            ->all();
    }

    /**
     * Normalized registry paths keyed by locale: desired paths from {@see CmsUrlRouteRegistry::publicPagePathsByLocale()}
     * merged with synced {@see UrlRoute} rows (synced wins on conflict).
     *
     * @return array<string, string>
     */
    public function publicRegistryPathsByLocale(): array
    {
        if (! $this->supportsCmsPublicUrlBuilding() || ! $this->cmsPublicUrlsEnabled()) {
            return [];
        }

        if ($this->getKey() === null) {
            return [];
        }

        $registry = $this->cmsUrlRouteRegistry();

        if (! $registry->tableReady()) {
            return [];
        }

        /** @var array<string, string> $desired */
        $desired = $registry->publicPagePathsByLocale($this);
        $synced = $this->syncedPublicRegistryPathsByLocale();

        return array_merge($desired, $synced);
    }

    /**
     * Public browser paths (leading slash) keyed by locale for this instance. Omits locales with no dedicated route.
     *
     * @return array<string, string>
     */
    public function publicUrlsByLocale(): array
    {
        $out = [];

        foreach ($this->publicRegistryPathsByLocale() as $locale => $path) {
            $path = trim((string) $path);
            if ($path === '') {
                continue;
            }

            $out[(string) $locale] = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath(
                (string) $locale,
                $path
            );
        }

        return $out;
    }

    /**
     * Absolute public URLs (scheme + host + browser path) keyed by locale. Omits locales with no dedicated route.
     *
     * @return array<string, string>
     */
    public function publicFullUrlsByLocale(): array
    {
        $out = [];

        foreach ($this->publicUrlsByLocale() as $locale => $browserPath) {
            $out[(string) $locale] = url($browserPath);
        }

        return $out;
    }

    /**
     * Absolute public URL for one locale, or null when this instance has no public route there.
     */
    public function publicFullUrlForLocale(?string $locale = null): ?string
    {
        $locale ??= (string) app()->getLocale();
        $browserPath = $this->publicUrlsByLocale()[$locale] ?? null;

        if ($browserPath === null || $browserPath === '') {
            return null;
        }

        return url($browserPath);
    }

    /**
     * Whether this instance has a dedicated public URL for {@code $locale} (desired or synced registry path).
     */
    public function hasPublicUrlForLocale(string $locale): bool
    {
        $paths = $this->publicRegistryPathsByLocale();

        if (! isset($paths[$locale])) {
            return false;
        }

        return trim((string) $paths[$locale]) !== '';
    }

    /**
     * Language-switcher payload: one row per active path-segment locale from {@see CmsLocalizationContract::pathSegmentLocales()}.
     * When this model has no route for a locale, {@code url} falls back to that locale's homepage / root and
     * {@code is_fallback} is set.
     *
     * @return list<array{locale: string, label: string, url: string, available: bool, is_current: bool, is_fallback?: bool}>
     */
    public function localizedUrlAlternates(?string $currentLocale = null): array
    {
        $currentLocale ??= (string) app()->getLocale();
        $localization = app(CmsLocalizationContract::class);
        $locales = $localization->pathSegmentLocales();
        $meta = $localization->supportedLocalesMeta();

        $alternates = [];

        foreach ($locales as $locale) {
            $locale = (string) $locale;
            $available = $this->hasPublicUrlForLocale($locale);
            $isFallback = false;

            if ($available) {
                $paths = $this->publicRegistryPathsByLocale();
                $browserPath = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath(
                    $locale,
                    (string) $paths[$locale]
                );
            } else {
                $browserPath = $this->homepageFallbackBrowserPathForLocale($locale);
                $isFallback = true;
            }

            $row = [
                'locale' => $locale,
                'label' => $this->resolveLocaleLabelForUrlAlternates($locale, $meta),
                'url' => url($browserPath),
                'available' => $available,
                'is_current' => $locale === $currentLocale,
            ];

            if ($isFallback) {
                $row['is_fallback'] = true;
            }

            $alternates[] = $row;
        }

        return $alternates;
    }

    /**
     * Browser path for the locale homepage when this model has no public route there ({@see IsSingular} root binding or locale root).
     */
    protected function homepageFallbackBrowserPathForLocale(string $locale): string
    {
        $registryPath = $this->resolveHomepageRegistryPathForLocale($locale);

        return CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath($locale, $registryPath);
    }

    /**
     * Normalized registry path for a locale's homepage ({@code /} or the synced root {@see UrlRoute} when present).
     */
    protected function resolveHomepageRegistryPathForLocale(string $locale): string
    {
        $registry = $this->cmsUrlRouteRegistry();

        if ($registry->tableReady()) {
            $canonical = app(CanonicalUrlResolverInterface::class);
            $variants = $canonical->normalizedPathRegistryLookupVariants('/');

            $rootRoute = UrlRoute::query()
                ->where('locale', $locale)
                ->where('kind', UrlRoute::KIND_PAGE_PUBLIC)
                ->whereIn('normalized_path', $variants)
                ->orderBy('id')
                ->first(['normalized_path']);

            if ($rootRoute !== null) {
                return $canonical->normalizePath((string) $rootRoute->normalized_path);
            }
        }

        return '/';
    }

    /**
     * @param  array<string, array<string, mixed>>  $supportedMeta
     */
    protected function resolveLocaleLabelForUrlAlternates(string $locale, array $supportedMeta): string
    {
        $meta = $supportedMeta[$locale] ?? null;
        if (is_array($meta)) {
            $name = (string) ($meta['name'] ?? $meta['native'] ?? '');

            if ($name !== '') {
                return $name;
            }
        }

        return strtoupper($locale);
    }

    protected function cmsPublicUrlsEnabled(): bool
    {
        return (bool) modularousConfig('cms_routing.public_pages_enabled', true);
    }

    protected function cmsUrlRouteRegistry(): CmsUrlRouteRegistry
    {
        return app(CmsUrlRouteRegistry::class);
    }
}
