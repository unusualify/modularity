<?php

namespace Modules\Cms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\PublicUrlRegistryContract;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Providers\CmsServiceProvider;
use Modules\Cms\Support\CmsParentSegmentRegistryGate;
use Modules\Cms\Support\CmsPublicPathHierarchy;
use Modules\Cms\Support\CmsPublicUrlRegistryCacheManager;
use Unusualify\Modularous\Entities\Traits\HasSlug;
use Unusualify\Modularous\Entities\Traits\IsSingular;

/**
 * Syncs {@see UrlRoute} rows from page-like models (slugs + parent segments) and redirect sources.
 * Uses each model instance's morph class / class name — no hard-coded entity types.
 * {@see IsSingular} models share the singletons table but still sync routes from
 * {@see desiredPublicPathsByLocale()} via {@see ParentSegment} bindings per locale (no per-model slug table).
 * Public requests are gated by {@see CmsParentSegmentRegistryGate} together with
 * {@see CmsPublicModelResolver::resolveForParentSegmentRegistry()}.
 *
 * Application binding: {@see PublicUrlRegistryContract} → this class (see {@see CmsServiceProvider}).
 */
final class CmsUrlRouteRegistry implements PublicUrlRegistryContract
{
    public function __construct(
        private CanonicalUrlResolverInterface $canonicalUrlResolver,
        private CmsParentSegmentResolver $parentSegmentResolver,
    ) {}

    public function tableReady(): bool
    {
        return Schema::hasTable((new UrlRoute)->getTable());
    }

    /**
     * Normalized paths already claimed as {@see UrlRoute::KIND_PAGE_PUBLIC} for this locale.
     * When the registry table is missing, returns an empty list (no entity-specific fallbacks).
     *
     * @return list<string>
     */
    public function activePublicPagePathsForLocale(string $locale): array
    {
        $locale = (string) $locale;

        if (! $this->tableReady()) {
            return [];
        }

        return UrlRoute::query()
            ->where('locale', $locale)
            ->where('kind', UrlRoute::KIND_PAGE_PUBLIC)
            ->pluck('normalized_path')
            ->map(fn ($p) => $this->canonicalUrlResolver->normalizePath((string) $p))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Resolved public URL paths per locale from slug rows (after sync, matches {@see desiredPublicPathsByLocale}).
     *
     * @return array<string, string> locale => normalized path
     */
    public function publicPagePathsByLocale(Model $model): array
    {
        if (classHasTrait($model::class, HasSlug::class)) {
            $model->unsetRelation('slugs');
            $model->load('slugs');
        }

        return $this->desiredPublicPathsByLocale($model);
    }

    /**
     * @return list<string>
     */
    public function nestedPathPrefixWarnings(
        string $locale,
        string $normalizedPath,
        string $routeKind,
        string $pathOwnerMorphClass,
        ?int $exceptUrlableId = null,
    ): array {
        if (! $this->tableReady()) {
            return [];
        }

        $normalizedPath = $this->canonicalUrlResolver->normalizePath($normalizedPath);
        $warnings = [];

        $rows = UrlRoute::query()
            ->where('locale', $locale)
            ->where('kind', $routeKind)
            ->where('urlable_type', $pathOwnerMorphClass)
            ->when($exceptUrlableId !== null, fn ($q) => $q->where('urlable_id', '<>', $exceptUrlableId))
            ->get(['normalized_path', 'urlable_id']);

        foreach ($rows as $row) {
            $other = (string) $row->normalized_path;
            if ($other === $normalizedPath) {
                continue;
            }
            if (CmsPublicPathHierarchy::segmentsOverlapAsPrefix($normalizedPath, $other)) {
                $warnings[] = sprintf(
                    'Path segment overlap: "%s" shares a prefix relationship with existing path "%s" (%s id %s).',
                    $normalizedPath,
                    $other,
                    class_basename($pathOwnerMorphClass),
                    $row->urlable_id
                );
            }
        }

        return array_values(array_unique($warnings));
    }

    public function removePublicPageRoutesForModel(Model $model): void
    {
        if (! $this->tableReady()) {
            return;
        }

        UrlRoute::query()
            ->where('urlable_type', $model->getMorphClass())
            ->where('urlable_id', $model->getKey())
            ->where('kind', UrlRoute::KIND_PAGE_PUBLIC)
            ->delete();

        app(CmsPublicUrlRegistryCacheManager::class)->touchUrlRouteRegistry();
    }

    public function syncPublicPageRoutesForModel(Model $model, bool $touchRegistryCache = true): void
    {
        if (! $this->tableReady()) {
            return;
        }

        if (classHasTrait($model::class, HasSlug::class)) {
            $model->unsetRelation('slugs');
            $model->load('slugs');
        }

        $desiredPathsByLocale = $this->desiredPublicPathsByLocale($model);
        $morphClass = $model->getMorphClass();

        $existingByLocale = UrlRoute::query()
            ->where('urlable_type', $morphClass)
            ->where('urlable_id', $model->getKey())
            ->where('kind', UrlRoute::KIND_PAGE_PUBLIC)
            ->get()
            ->keyBy('locale');

        foreach ($desiredPathsByLocale as $locale => $path) {
            $path = $this->canonicalUrlResolver->normalizePath((string) $path);
            $row = $existingByLocale->get($locale);
            if ($row === null) {
                if ($this->pathTakenByAnother($locale, $path)) {
                    continue;
                }

                UrlRoute::query()->create([
                    'locale' => $locale,
                    'normalized_path' => $path,
                    'urlable_type' => $morphClass,
                    'urlable_id' => $model->getKey(),
                    'kind' => UrlRoute::KIND_PAGE_PUBLIC,
                ]);

                continue;
            }

            if ($this->canonicalUrlResolver->normalizePath((string) $row->normalized_path) === $path) {
                continue;
            }

            if ($this->pathTakenByAnother($locale, $path, $row->id)) {
                continue;
            }

            $row->update(['normalized_path' => $path]);
        }

        foreach ($existingByLocale as $locale => $row) {
            if (! array_key_exists($locale, $desiredPathsByLocale)) {
                $row->delete();
            }
        }

        if ($touchRegistryCache) {
            app(CmsPublicUrlRegistryCacheManager::class)->touchUrlRouteRegistry();
        }
    }

    /**
     * Rebuilds {@see UrlRoute::KIND_PAGE_PUBLIC} rows for every model of {@code $modelClass} that participates in the
     * registry ({@see HasSlug} or {@see IsSingular}). Invoked when {@see \Modules\Cms\Entities\ParentSegment} rows
     * change so URL prefixes stay aligned without per-model saves.
     *
     * @param class-string<Model> $modelClass
     */
    public function syncPublicPageRoutesForAllModelsOfClass(string $modelClass): void
    {
        if (! $this->tableReady() || ! class_exists($modelClass) || ! is_a($modelClass, Model::class, true)) {
            return;
        }

        if (! classHasTrait($modelClass, HasSlug::class) && ! classHasTrait($modelClass, IsSingular::class)) {
            return;
        }

        $chunkSize = max(1, (int) modularousConfig('cms_routing.parent_segment_change_resync_chunk_size', 100));

        /** @var Model $prototype */
        $prototype = new $modelClass;
        $keyName = $prototype->getKeyName();

        $modelClass::query()->chunkById($chunkSize, function ($models): void {
            foreach ($models as $model) {
                if ($model instanceof Model) {
                    $this->syncPublicPageRoutesForModel($model, touchRegistryCache: false);
                }
            }
        }, $keyName);

        app(CmsPublicUrlRegistryCacheManager::class)->touchUrlRouteRegistry();
    }

    public function removeRedirectSourceRoute(Model $redirect): void
    {
        if (! $this->tableReady()) {
            return;
        }

        UrlRoute::query()
            ->where('urlable_type', $redirect->getMorphClass())
            ->where('urlable_id', $redirect->getKey())
            ->where('kind', UrlRoute::KIND_REDIRECT_SOURCE)
            ->delete();
    }

    public function syncRedirectSourceRoute(Model $redirect): void
    {
        if (! $this->tableReady()) {
            return;
        }

        if (! $redirect->is_active) {
            $this->removeRedirectSourceRoute($redirect);

            return;
        }

        $locale = (string) $redirect->locale;
        $path = $this->canonicalUrlResolver->normalizePath((string) $redirect->from_path);
        $morphClass = $redirect->getMorphClass();

        $existing = UrlRoute::query()
            ->where('urlable_type', $morphClass)
            ->where('urlable_id', $redirect->getKey())
            ->where('kind', UrlRoute::KIND_REDIRECT_SOURCE)
            ->first();

        if ($existing === null) {
            if ($this->pathTakenByAnother($locale, $path)) {
                return;
            }

            UrlRoute::query()->create([
                'locale' => $locale,
                'normalized_path' => $path,
                'urlable_type' => $morphClass,
                'urlable_id' => $redirect->getKey(),
                'kind' => UrlRoute::KIND_REDIRECT_SOURCE,
            ]);

            return;
        }

        if ($existing->normalized_path === $path && $existing->locale === $locale) {
            return;
        }

        if ($this->pathTakenByAnother($locale, $path, $existing->id)) {
            return;
        }

        $existing->update([
            'normalized_path' => $path,
            'locale' => $locale,
        ]);
    }

    /**
     * One path per configured locale ({@see getLocales()}) plus any extra locales that only appear on slug rows:
     * each locale uses its own **active** slug segment when present; otherwise the segment falls back to the first
     * available active slug chosen in order: `cms_routing.default_locale`, `translatable.fallback_locale`,
     * {@see getLocales()}, then remaining locales that have active slugs. Parent prefix comes from
     * {@see CmsParentSegmentResolver} per target locale (e.g. TR uses `sayfalar/…` while the leaf may match EN).
     * If no locale has a non-empty active slug, no paths are returned (sync removes page routes).
     *
     * @return array<string, string>
     */
    protected function desiredPublicPathsByLocale(Model $model): array
    {
        if (classHasTrait($model::class, IsSingular::class)) {
            return $model->parentSegments()->mapWithKeys(function ($parentSegment) {
                $trimmed = trim((string) ($parentSegment->normalized_prefix ?? ''));

                $normalized = $trimmed === ''
                    ? '/'
                    : $this->canonicalUrlResolver->normalizePath('/' . ltrim($trimmed, '/'));

                return [(string) ($parentSegment->locale ?? '') => $normalized];
            })->toArray();
        }

        if ($model->slugs->isEmpty()) {
            return [];
        }

        $out = [];
        $targetClass = $model::class;
        $slugGroups = $model->slugs->groupBy('locale');

        /** @var array<string, string> $activeLeafByLocale */
        $activeLeafByLocale = [];
        foreach ($slugGroups as $locale => $rows) {
            $locale = (string) $locale;
            $rows = $rows->values();
            $picked = $rows->first(fn ($s) => (bool) ($s->active ?? false));
            if ($picked === null) {
                continue;
            }
            $segment = trim((string) ($picked->slug ?? ''));

            $activeLeafByLocale[$locale] = $segment;
        }

        $canonicalLeaf = $this->canonicalPublicSlugLeafForFallback($activeLeafByLocale);
        if ($canonicalLeaf === null) {
            return [];
        }

        $seenLocale = [];
        $localesForPublic = [];

        foreach (getLocales() as $loc) {
            $loc = (string) $loc;
            if (isset($seenLocale[$loc])) {
                continue;
            }
            $seenLocale[$loc] = true;
            $localesForPublic[] = $loc;
        }

        foreach ($slugGroups->keys()->all() as $loc) {
            $loc = (string) $loc;
            if (isset($seenLocale[$loc])) {
                continue;
            }
            $seenLocale[$loc] = true;
            $localesForPublic[] = $loc;
        }

        foreach ($localesForPublic as $locale) {
            $leaf = $activeLeafByLocale[$locale] ?? $canonicalLeaf;
            $leaf = trim((string) $leaf);

            $out[$locale] = $this->parentSegmentResolver->joinPublicLeafPath($targetClass, $locale, $leaf);
        }

        return $out;
    }

    /**
     * Pick one active slug string per locale fallback order — prefers non-empty leaves, otherwise allows an explicit empty
     * homepage slug when every active binding uses a blank slug segment for that locale bucket.
     *
     * @param array<string, string> $activeLeafByLocale
     */
    protected function canonicalPublicSlugLeafForFallback(array $activeLeafByLocale): ?string
    {
        if ($activeLeafByLocale === []) {
            return null;
        }

        /** @var list<string> */
        $orderedLocales = [];
        $push = static function (?string $loc) use (&$orderedLocales): void {
            if ($loc === null || $loc === '') {
                return;
            }
            $loc = trim($loc);
            if ($loc === '') {
                return;
            }
            $orderedLocales[] = $loc;
        };

        $push(modularousConfig('cms_routing.default_locale', config('app.locale')));
        $transFallback = config('translatable.fallback_locale');
        if (is_string($transFallback) && $transFallback !== '') {
            $push($transFallback);
        }
        foreach (getLocales() as $loc) {
            $push((string) $loc);
        }

        foreach (array_keys($activeLeafByLocale) as $locale) {
            $push((string) $locale);
        }

        foreach (array_values(array_unique($orderedLocales)) as $locale) {
            if (! isset($activeLeafByLocale[$locale])) {
                continue;
            }
            if ((string) $activeLeafByLocale[$locale] !== '') {
                return (string) $activeLeafByLocale[$locale];
            }
        }

        foreach (array_values(array_unique($orderedLocales)) as $locale) {
            if (! isset($activeLeafByLocale[$locale])) {
                continue;
            }

            return (string) $activeLeafByLocale[$locale];
        }

        foreach ($activeLeafByLocale as $slug) {
            if ((string) $slug !== '') {
                return (string) $slug;
            }
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function isPathClaimedByOther(
        string $locale,
        string $normalizedPath,
        ?string $excludeUrlableType = null,
        ?int $excludeUrlableId = null,
    ): bool {
        if (! $this->tableReady()) {
            return false;
        }

        $path = $this->canonicalUrlResolver->normalizePath($normalizedPath);

        $query = UrlRoute::query()
            ->where('locale', $locale)
            ->whereIn(
                'normalized_path',
                $this->canonicalUrlResolver->normalizedPathRegistryLookupVariants($path)
            );

        if ($excludeUrlableType !== null && $excludeUrlableId !== null) {
            $query->where(function ($q) use ($excludeUrlableType, $excludeUrlableId): void {
                $q->where('urlable_type', '!=', $excludeUrlableType)
                    ->orWhere('urlable_id', '<>', $excludeUrlableId);
            });
        }

        return $query->exists();
    }

    /**
     * @param int|null $exceptId When set, ignore this row (for in-place updates).
     */
    protected function pathTakenByAnother(string $locale, string $path, ?int $exceptId = null): bool
    {
        $canonical = $this->canonicalUrlResolver->normalizePath($path);

        $query = UrlRoute::query()
            ->where('locale', $locale)
            ->whereIn(
                'normalized_path',
                $this->canonicalUrlResolver->normalizedPathRegistryLookupVariants($canonical)
            );

        if ($exceptId !== null) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->exists();
    }
}
