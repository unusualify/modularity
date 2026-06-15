<?php

namespace Modules\Cms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Entities\CmsSitemapableItem;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Support\CmsFrontPath;
use Modules\Cms\Support\CmsParentSegmentRegistryGate;
use Modules\Cms\Support\CmsPublicSiteUrl;
use Unusualify\Modularous\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularous\Entities\Traits\HasTranslation;
use Unusualify\Modularous\Entities\Traits\IsSingular;

/**
 * Discovers public page URLs from {@see UrlRoute} (kind = page_public) and builds sitemap.org XML
 * (with {@code xhtml:link} hreflang when multiple locales share the same urlable).
 */
final class CmsSitemapBuildService
{
    public function __construct(
        private CanonicalUrlResolverInterface $canonical,
        private CmsLocalizationContract $cmsLocalization,
    ) {}

    /**
     * @return list<array{loc: string, lastmod: string|null, changefreq: string, priority: string, key: string, hreflang: string}>
     */
    public function buildEntryDtos(): array
    {
        $dtos = [];

        $this->eachIncludedSitemapLine(
            function (
                UrlRoute $route,
                Model $model,
                string $groupKey,
                array $override,
                string $loc,
                ?string $lastmod,
                string $locale
            ) use (&$dtos): void {

                $dtos[] = [
                    'loc' => $loc,
                    'lastmod' => $lastmod,
                    'changefreq' => $override['changefreq'],
                    'priority' => $override['priority'],
                    'key' => $groupKey,
                    'hreflang' => $this->hreflangForLocale($locale),
                    'locale' => $locale,
                ];
            }
        );

        return $this->attachAlternatesToDtos($dtos);
    }

    /**
     * One row per included {@link UrlRoute} line (before hreflang alternates), for the admin sitemap panel.
     *
     * @return list<array{sort: int, url_route_id: int, group_key: string, urlable_type: string, urlable_id: int, locale: string, normalized_path: string, loc: string, lastmod: string|null, changefreq: string, priority: string, sitemapable_item_id: int|null}>
     */
    public function getPanelItemRows(): array
    {
        $idsByGroup = $this->loadSitemapableItemIdsByGroup();
        $rows = [];
        $sort = 0;

        $this->eachIncludedSitemapLine(
            function (
                UrlRoute $route,
                Model $model,
                string $groupKey,
                array $override,
                string $loc,
                ?string $lastmod,
                string $locale
            ) use (&$rows, &$sort, $idsByGroup): void {
                $rows[] = [
                    'sort' => ++$sort,
                    'url_route_id' => (int) $route->getKey(),
                    'group_key' => $groupKey,
                    'urlable_type' => (string) $route->urlable_type,
                    'urlable_id' => (int) $route->urlable_id,
                    'locale' => $locale,
                    'normalized_path' => (string) $route->normalized_path,
                    'loc' => $loc,
                    'lastmod' => $lastmod,
                    'changefreq' => $override['changefreq'],
                    'priority' => $override['priority'],
                    'sitemapable_item_id' => $idsByGroup[$groupKey] ?? null,
                ];
            }
        );

        return $rows;
    }

    /**
     * Invokes the callback for each (route, model) that would be emitted as a sitemap {@code <url>} line
     * (same filter rules as {@see buildEntryDtos} before hreflang alternates are attached).
     *
     * @param callable(UrlRoute, Model, string, array{changefreq: string, priority: string}, string, string|null, string): void $callback
     *                                                                                                                                    route, model, groupKey, override, loc, lastmod, locale
     */
    private function eachIncludedSitemapLine(callable $callback): void
    {
        if (! Schema::hasTable((new UrlRoute)->getTable())) {
            return;
        }

        $routes = UrlRoute::query()
            ->where('kind', UrlRoute::KIND_PAGE_PUBLIC)
            ->orderBy('urlable_type')
            ->orderBy('urlable_id')
            ->orderBy('locale')
            ->get();

        if ($routes->isEmpty()) {
            return;
        }

        $overrides = $this->loadOverrides();
        $grouped = $routes->groupBy(fn (UrlRoute $r) => $r->urlable_type . ':' . $r->urlable_id);

        foreach ($grouped as $groupKey => $group) {
            /** @var Collection<int, UrlRoute> $group */
            $first = $group->first();
            if ($first === null) {
                continue;
            }
            $class = $first->urlable_type;
            if (! is_string($class) || ! is_a($class, Model::class, true)) {
                continue;
            }

            if (! CmsParentSegmentRegistryGate::allowsModelClass($class)) {
                continue;
            }
            $ids = $group->pluck('urlable_id')->unique()->values();
            $valid = $this->loadValidModels($class, $ids);

            if ($valid->isEmpty()) {
                continue;
            }
            $override = $this->matchOverride($overrides, $class, (int) $ids->first());

            /** @var UrlRoute $route */
            foreach ($group as $route) {
                $id = (int) $route->urlable_id;
                $model = $valid->get($id) ?? $valid->get((string) $id);
                if ($model === null) {
                    continue;
                }
                $locale = (string) $route->locale;
                if (! $this->shouldIncludeInSitemapForLocale($model, $locale)) {
                    continue;
                }
                $path = (string) $route->normalized_path;
                $browser = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath(
                    $locale,
                    $path,
                    $this->canonical
                );
                $loc = CmsPublicSiteUrl::absoluteUrlForPath($browser);
                $lastmod = $this->resolveLastmod($model);
                $callback($route, $model, (string) $groupKey, $override, $loc, $lastmod, $locale);
            }
        }
    }

    /**
     * Morph group key (FQCN:id) => {@link CmsSitemapableItem} id.
     *
     * @return array<string, int>
     */
    private function loadSitemapableItemIdsByGroup(): array
    {
        $sitemapId = (int) modularousConfig('cms_sitemap.default_sitemap_id', 1);
        $t = (new CmsSitemapableItem)->getTable();
        if (! Schema::hasTable($t)) {
            return [];
        }
        $out = [];
        CmsSitemapableItem::query()
            ->where('sitemap_id', $sitemapId)
            ->get()
            ->each(function (CmsSitemapableItem $row) use (&$out): void {
                $out[$row->sitemapable_type . ':' . (string) $row->sitemapable_id] = (int) $row->id;
            });

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $annotated Pre-built rows (e.g. tests); if empty, runs {@see buildEntryDtos()}.
     */
    public function toXmlString(array $annotated = []): string
    {
        if ($annotated === []) {
            $annotated = $this->buildEntryDtos();
        }

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">',
        ];

        foreach ($annotated as $row) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . $this->xml($row['loc'] ?? '') . '</loc>';
            if (! empty($row['lastmod'])) {
                $lines[] = '    <lastmod>' . $this->xml((string) $row['lastmod']) . '</lastmod>';
            }
            if (! empty($row['changefreq'])) {
                $lines[] = '    <changefreq>' . $this->xml((string) $row['changefreq']) . '</changefreq>';
            }
            if (isset($row['priority']) && $row['priority'] !== '' && $row['priority'] !== null) {
                $lines[] = '    <priority>' . $this->xml((string) $row['priority']) . '</priority>';
            }
            $alts = $row['alternates'] ?? [];
            if (is_array($alts)) {
                foreach ($alts as $alt) {
                    if (! is_array($alt) || ! isset($alt['href'], $alt['hreflang'])) {
                        continue;
                    }
                    $lines[] = '    <xhtml:link rel="alternate" hreflang="'
                        . $this->xml((string) $alt['hreflang']) . '" href="'
                        . $this->xml((string) $alt['href']) . '" />';
                }
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines) . "\n";
    }

    public function buildXml(): string
    {
        return $this->toXmlString();
    }

    /**
     * @return array{changefreq: string, priority: string}
     */
    private function defaultOverride(): array
    {
        $defaults = (array) modularousConfig('cms_sitemap.defaults', []);

        $changefreq = (string) data_get($defaults, 'changefreq', 'weekly');
        $priority = data_get($defaults, 'priority', 0.5);
        if (is_numeric($priority)) {
            $priority = number_format((float) $priority, 1, '.', '');
        } else {
            $priority = '0.5';
        }

        return ['changefreq' => $changefreq, 'priority' => (string) $priority];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int|string, Model>
     */
    private function loadValidModels(string $class, Collection $ids): Collection
    {
        $keyName = (new $class)->getKeyName();
        $q = $class::query()->whereIn($keyName, $ids->all());
        foreach (['published', 'visible'] as $name) {
            $method = 'scope' . Str::studly($name);
            if (method_exists($class, $method)) {
                $q->scopes([$name]);
            }
        }

        return $q->get()->keyBy(fn (Model $m) => $m->getKey());
    }

    private function shouldIncludeInSitemapForLocale(Model $model, string $locale): bool
    {

        if (classHasTrait($model, HasTranslation::class)) {
            $t = $model->translate($locale);
            if ($t === null) {
                return false;
            }

            if (property_exists($t, 'sitemap_include') || isset($t->sitemap_include)) {
                return (bool) $t->sitemap_include;
            }
        } elseif (classHasTrait($model, HasTranslatableMetadata::class) && classHasTrait($model, IsSingular::class)) {

            if ((property_exists($model, 'sitemap_include') || isset($model->sitemap_include)) && is_array($model->sitemap_include) && Arr::isAssoc($model->sitemap_include)) {
                return (bool) $model->sitemap_include[$locale] ?? true;
            }
        }

        return true;
    }

    private function resolveLastmod(Model $model): ?string
    {
        $t = $model->updated_at;
        if ($t === null) {
            return null;
        }

        return $t->toIso8601String();
    }

    private function hreflangForLocale(string $locale): string
    {
        $locale = str_replace('_', '-', $locale);
        if (str_contains($locale, '-')) {
            $parts = explode('-', $locale, 2);

            return mb_strtolower($parts[0]) . '-' . mb_strtoupper($parts[1] ?? '');
        }

        return mb_strtolower($locale);
    }

    /**
     * @return array<string, array{changefreq: string, priority: string}>
     */
    private function loadOverrides(): array
    {
        $sitemapId = (int) modularousConfig('cms_sitemap.default_sitemap_id', 1);
        $t = (new CmsSitemapableItem)->getTable();
        if (! Schema::hasTable($t)) {
            return [];
        }
        $defaults = $this->defaultOverride();
        $out = [];
        CmsSitemapableItem::query()
            ->where('sitemap_id', $sitemapId)
            ->get()
            ->each(function (CmsSitemapableItem $row) use (&$out, $defaults): void {
                $key = $row->sitemapable_type . ':' . (string) $row->sitemapable_id;
                $p = $row->priority !== null ? number_format((float) $row->priority, 1, '.', '') : $defaults['priority'];
                $out[$key] = [
                    'changefreq' => $row->changefreq !== null && $row->changefreq !== '' ? (string) $row->changefreq : $defaults['changefreq'],
                    'priority' => $p,
                ];
            });

        return $out;
    }

    /**
     * @param array<string, array{changefreq: string, priority: string}> $overrides
     * @return array{changefreq: string, priority: string}
     */
    private function matchOverride(array $overrides, string $class, int $id): array
    {
        $key = $class . ':' . $id;
        $def = $this->defaultOverride();
        if (isset($overrides[$key])) {
            return $overrides[$key];
        }

        return $def;
    }

    /**
     * @param list<array{loc: string, lastmod: string|null, changefreq: string, priority: string, key: string, hreflang: string, locale: string}> $dtos
     * @return list<array<string, mixed>>
     */
    private function attachAlternatesToDtos(array $dtos): array
    {
        if ($dtos === []) {
            return [];
        }
        $defaultLocale = $this->cmsLocalization->defaultLocale();
        $byKey = collect($dtos)->groupBy('key');
        $out = [];
        foreach ($byKey as $rows) {
            $coll = $rows->values();
            $alternates = [];
            foreach ($coll as $r) {
                if (! is_array($r) || ! isset($r['loc'], $r['hreflang'])) {
                    continue;
                }
                $alternates[] = ['href' => (string) $r['loc'], 'hreflang' => (string) $r['hreflang']];
            }
            $xDefault = $coll->firstWhere('locale', $defaultLocale);
            if ($xDefault === null && $coll->isNotEmpty()) {
                $xDefault = $coll->first();
            }
            $xDefaultLoc = is_array($xDefault) && isset($xDefault['loc']) ? (string) $xDefault['loc'] : null;
            if ($xDefaultLoc !== null && $xDefaultLoc !== '') {
                $alternates[] = ['href' => $xDefaultLoc, 'hreflang' => 'x-default'];
            }
            foreach ($coll as $r) {
                if (is_array($r)) {
                    $r['alternates'] = $alternates;
                    $out[] = $r;
                }
            }
        }

        return $out;
    }

    private function xml(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
