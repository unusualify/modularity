<?php

namespace Modules\Cms\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Support\CmsParentSegmentRegistryGate;
use Modules\Cms\Support\CmsSluglessFallbackLocale;

/**
 * Resolves a published CMS entity for the public front using {@see UrlRoute} rows.
 * Per–submodule {@see \Modules\Cms\Http\Controllers\Front\CmsController} fixes a {@code $modelClass}; the universal
 * front uses {@see resolveForParentSegmentRegistry()}.
 */
final class CmsPublicModelResolver
{
    public function __construct(
        private CmsVisitorRedirectResolver $visitorPathResolver,
        private CmsLocalizationContract $cmsLocalization,
        private CanonicalUrlResolverInterface $canonicalUrlResolver,
    ) {}

    /**
     * Resolves a published model of any class that appears in {@see UrlRoute} and passes
     * {@see \Modules\Cms\Support\CmsParentSegmentRegistryGate} ({@code ParentSegment} row + {@link HasParentSegment}
     * or {@link \Unusualify\Modularous\Entities\Traits\IsSingular}). Singleton entities still use the concrete class as
     * {@code urlable_type} while rows live on the singletons table. Powers {@see \Modules\Cms\Http\Controllers\Front\CmsPublicFrontController}.
     */
    public function resolveForParentSegmentRegistry(Request $request, ?string $urlRouteKind = null): ?Model
    {
        $urlRouteKind = $urlRouteKind ?? UrlRoute::KIND_PAGE_PUBLIC;
        $row = $this->findPublicUrlRouteRow($request, $urlRouteKind);

        if ($row === null) {
            return null;
        }

        $candidate = $row->urlable;
        if ($candidate === null) {
            return null;
        }

        $modelClass = get_class($candidate);

        if (! CmsParentSegmentRegistryGate::allowsModelClass($modelClass)) {
            return null;
        }

        $locale = (string) $row->locale;

        return $this->loadPublishedModel($modelClass, $candidate->getKey(), $locale);
    }

    /**
     * @param class-string<Model> $modelClass
     */
    public function resolve(Request $request, string $modelClass, string $urlRouteKind): ?Model
    {
        if (! modularousConfig('cms_routing.public_pages_enabled', true)) {
            return null;
        }

        if (! is_a($modelClass, Model::class, true)) {
            return null;
        }

        $row = $this->findPublicUrlRouteRow($request, $urlRouteKind);
        if ($row === null) {
            return null;
        }

        $candidate = $row->urlable;
        if (! is_a($candidate, $modelClass, true)) {
            return null;
        }

        $locale = (string) $row->locale;

        return $this->loadPublishedModel($modelClass, $candidate->getKey(), $locale);
    }

    /**
     * Find the {@link UrlRoute} for the current path/locale; applies locale to the app when implicit.
     */
    private function findPublicUrlRouteRow(Request $request, string $urlRouteKind): ?UrlRoute
    {
        if (! modularousConfig('cms_routing.public_pages_enabled', true)) {
            return null;
        }

        if (! Schema::hasTable((new UrlRoute)->getTable())) {
            return null;
        }

        [$locale, $pathKey, $explicitLocalePrefix] = $this->visitorPathResolver->resolveLocalePathKeyAndExplicitFlag($request);

        $this->cmsLocalization->applyLocaleToApplication($locale);

        $pathVariants = $this->canonicalUrlResolver->normalizedPathRegistryLookupVariants($pathKey);

        $row = UrlRoute::query()
            ->where('locale', $locale)
            ->whereIn('normalized_path', $pathVariants)
            ->where('kind', $urlRouteKind)
            ->first();

        if ($row === null && ! $explicitLocalePrefix) {
            $row = $this->resolveUrlRouteWhenLocaleImplicit($pathKey, $urlRouteKind);
            if ($row !== null) {
                $locale = (string) $row->locale;
                $this->cmsLocalization->applyLocaleToApplication($locale);
            }
        }

        return $row;
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function loadPublishedModel(string $modelClass, int|string $key, string $locale): ?Model
    {
        $query = $modelClass::query()->whereKey($key);
        $this->applyPublishedVisibilityScopes($query, $modelClass);

        if (method_exists($modelClass, 'translations')) {
            $query->with(['translations' => fn ($q) => $q->where('locale', $locale)]);
        }

        return $query->first();
    }

    /**
     * Load a CMS entity by primary key for signed public preview (bypasses {@code published} / {@code visible} scopes).
     *
     * @param class-string<Model> $modelClass
     */
    public function resolveByIdBypassingPublicationScopes(string $modelClass, int|string $id, string $locale): ?Model
    {
        if (! is_a($modelClass, Model::class, true)) {
            return null;
        }

        $this->cmsLocalization->applyLocaleToApplication($locale);

        $query = $modelClass::query()->whereKey($id);

        if (method_exists($modelClass, 'translations')) {
            $query->with(['translations' => fn ($q) => $q->where('locale', $locale)]);
        }

        return $query->first();
    }

    /**
     * @param class-string<Model> $modelClass
     */
    public static function applyPublishedVisibilityScopes(Builder $query, string $modelClass): void
    {
        $scopes = [];
        foreach (['published', 'visible'] as $name) {
            $method = 'scope' . Str::studly($name);
            if (method_exists($modelClass, $method)) {
                $scopes[] = $name;
            }
        }
        if ($scopes !== []) {
            $query->scopes($scopes);
        }
    }

    /**
     * Without an explicit {@code /{locale}/} segment in the URL, only {@see UrlRoute} rows for the implicit editorial
     * locale may match (slugless fallback when enabled — see {@see CmsSluglessFallbackLocale}; otherwise CMS default).
     */
    private function resolveUrlRouteWhenLocaleImplicit(string $pathKey, string $urlRouteKind): ?UrlRoute
    {
        $pathVariants = $this->canonicalUrlResolver->normalizedPathRegistryLookupVariants($pathKey);

        $rows = UrlRoute::query()
            ->whereIn('normalized_path', $pathVariants)
            ->where('kind', $urlRouteKind)
            ->get();

        /**
         * Without an explicit {@code /{locale}/} segment, URLs must only bind to rows for the implicit editorial locale
         * (slugless fallback when enabled — see {@see CmsSluglessFallbackLocale}; else CMS default).
         * Otherwise a TR-only slug like {@code /sayfalar/deneme-2} would resolve under a prefix-less URL intended for EN.
         */
        $implicitLocale = CmsSluglessFallbackLocale::implicitPreferredLocaleOtherwise($this->cmsLocalization->defaultLocale());
        $rows = $rows->filter(
            fn (UrlRoute $r): bool => CmsSluglessFallbackLocale::sameLocale((string) $r->locale, $implicitLocale)
        )->values();

        if ($rows->isEmpty()) {
            return null;
        }

        if ($rows->count() === 1) {
            return $rows->first();
        }

        return $rows->firstWhere(fn (UrlRoute $r): bool => CmsSluglessFallbackLocale::sameLocale((string) $r->locale, app()->getLocale()))
            ?? $rows->first();
    }
}
