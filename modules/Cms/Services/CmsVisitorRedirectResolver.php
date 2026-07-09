<?php

namespace Modules\Cms\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Entities\Redirect;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Routing\CmsFrontRouteLocalizationBinding;
use Modules\Cms\Support\CmsFrontPath;
use Modules\Cms\Support\CmsSluglessFallbackLocale;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;

/**
 * Resolves {@see Redirect} rules for public HTTP requests (locale + normalized path).
 * Prefers {@see UrlRoute} registry when the table exists; falls back to scanning {@see Redirect} rows.
 */
final class CmsVisitorRedirectResolver implements \Modules\Cms\Contracts\CmsVisitorRequestContextResolverInterface
{
    public function __construct(
        private CanonicalUrlResolverInterface $canonicalUrlResolver,
        private CmsLocalizationContract $cmsLocalization,
    ) {}

    /**
     * Returns an HTTP redirect response when the request path matches an active site redirect rule.
     */
    public function resolveRedirectResponse(Request $request): ?RedirectResponse
    {
        if (! modularousConfig('cms_routing.visitor_redirects_enabled', true)) {
            return null;
        }

        if ($this->shouldExcludeRequest($request)) {
            return null;
        }

        if ($this->shouldSkipRedirectLookupsForUrlStaleResilience()) {
            return null;
        }

        [$locale, $pathKey, $explicitLocalePrefix] = $this->resolveLocalePathKeyAndExplicitFlag($request);

        if ($this->isActivePagePath($locale, $pathKey, $explicitLocalePrefix)) {
            return null;
        }

        $redirect = $this->findMatchingRedirect($locale, $pathKey);
        if ($redirect === null) {
            return null;
        }

        return $this->toHttpRedirect($redirect);
    }

    public function shouldExcludeRequest(Request $request): bool
    {
        $normalized = $this->canonicalUrlResolver->normalizePath($request->path());
        $previewPrefix = '/' . trim((string) modularousConfig('cms_routing.signed_preview.path_prefix', 'cms/preview'), '/');
        if (modularousConfig('cms_routing.signed_preview.enabled', true)
            && $previewPrefix !== '/'
            && ($normalized === $previewPrefix || str_starts_with($normalized, $previewPrefix . '/'))) {
            return true;
        }

        $stylesheetPrefix = '/' . trim((string) modularousConfig('cms_stylesheets.public_route.path_prefix', 'cms/stylesheets'), '/');
        if ((bool) modularousConfig('cms_stylesheets.public_route.enabled', true)
            && $stylesheetPrefix !== '/'
            && ($normalized === $stylesheetPrefix || str_starts_with($normalized, $stylesheetPrefix . '/'))) {
            return true;
        }

        $first = explode('/', trim($normalized, '/'))[0] ?? '';

        $extra = (array) modularousConfig('cms_routing.visitor_redirect_exclude_prefixes', ['api', 'sanctum', 'livewire']);
        if ($first !== '' && in_array($first, $extra, true)) {
            return true;
        }

        $adminPrefix = Modularous::getAdminUrlPrefix();
        if ($adminPrefix !== false && $adminPrefix !== '') {
            $p = '/' . ltrim((string) $adminPrefix, '/');
            if ($normalized === $p || str_starts_with($normalized, $p . '/')) {
                return true;
            }
        }

        $system = (string) modularousConfig('system_prefix', 'system-settings');
        $system = '/' . trim(str_replace('_', '-', $system), '/');
        if ($normalized === $system || str_starts_with($normalized, $system . '/')) {
            return true;
        }

        return false;
    }

    /**
     * Resolves locale + registry path for public CMS URLs.
     *
     * When the matched route uses a dedicated locale route parameter plus a wildcard path (see
     * {@see CmsFrontRouteLocalizationBinding}), the path parameter is the full remainder after
     * the locale — including ParentSegment prefixes and the slug, aligned with mcamara-style translated URL segments
     * but driven by {@see UrlRoute} and {@see CmsParentSegmentResolver}
     * instead of static lang route files.
     *
     * @return array{0: string, 1: string, 2: bool} [locale, normalized inner path for UrlRoute / from_path match, had explicit locale segment]
     */
    public function resolveLocalePathKeyAndExplicitFlag(Request $request): array
    {
        $route = $request->route();

        if ($route !== null && $route->hasParameter('locale') && $route->hasParameter('path')) {
            $locale = (string) $route->parameter('locale');
            $locales = $this->cmsLocalization->pathSegmentLocales();
            if (in_array($locale, $locales, true)) {
                $tail = (string) $route->parameter('path');
                $pathKey = $this->canonicalUrlResolver->normalizePath('/' . ltrim($tail, '/'));

                return [$locale, $pathKey, true];
            }
        }

        $inner = CmsFrontPath::innerNormalizedPath($request, $this->canonicalUrlResolver);

        return $this->resolveLocaleAndInnerPath($inner);
    }

    /**
     * @return array{0: string, 1: string, 2: bool} [locale, normalized inner path for UrlRoute / from_path match, had explicit locale segment]
     */
    public function resolveLocaleAndInnerPath(string $normalizedPath): array
    {
        $locales = $this->cmsLocalization->pathSegmentLocales();

        $default = CmsSluglessFallbackLocale::implicitPreferredLocaleOtherwise($this->cmsLocalization->defaultLocale());

        foreach ($locales as $loc) {
            $needle = '/' . trim($loc, '/');
            if ($normalizedPath === $needle) {
                return [$loc, '/', true];
            }
            if (str_starts_with($normalizedPath, $needle . '/')) {
                $inner = mb_substr($normalizedPath, mb_strlen($needle));
                $innerNorm = $inner === '' || $inner === '/'
                    ? '/'
                    : $this->canonicalUrlResolver->normalizePath($inner);

                return [$loc, $innerNorm, true];
            }
        }

        return [$default, $normalizedPath, false];
    }

    /**
     * @param bool $innerHadExplicitLocale From {@see resolveLocaleAndInnerPath} — {@code false} means no {@code /{locale}/}
     *                                     prefix; then only an {@see UrlRoute} for the implicit editorial locale counts as an active page (consistent
     *                                     with {@see CmsPublicModelResolver}).
     */
    public function isActivePagePath(string $locale, string $pathKey, bool $innerHadExplicitLocale): bool
    {
        if ($this->shouldSkipRedirectLookupsForUrlStaleResilience()) {
            return false;
        }

        if (! database_exists()) {
            return false;
        }

        if (! Schema::hasTable((new UrlRoute)->getTable())) {
            return false;
        }

        $variants = $this->canonicalUrlResolver->normalizedPathRegistryLookupVariants($pathKey);

        $query = UrlRoute::query()
            ->whereIn('normalized_path', $variants)
            ->where('kind', UrlRoute::KIND_PAGE_PUBLIC);

        if ($innerHadExplicitLocale) {
            return $query->where('locale', $locale)->exists();
        }

        $implicitLocale = CmsSluglessFallbackLocale::implicitPreferredLocaleOtherwise($this->cmsLocalization->defaultLocale());
        $needle = mb_strtolower(trim($implicitLocale));

        return $query->whereRaw('LOWER(TRIM(locale)) = ?', [$needle])->exists();
    }

    protected function findMatchingRedirect(string $locale, string $pathKey): ?Redirect
    {
        if ($this->shouldSkipRedirectLookupsForUrlStaleResilience()) {
            return null;
        }

        $variants = $this->canonicalUrlResolver->normalizedPathRegistryLookupVariants($pathKey);

        if (! database_exists()) {
            return null;
        }

        if (Schema::hasTable((new UrlRoute)->getTable())) {
            $row = UrlRoute::query()
                ->where('locale', $locale)
                ->whereIn('normalized_path', $variants)
                ->where('kind', UrlRoute::KIND_REDIRECT_SOURCE)
                ->first();

            if ($row !== null) {
                $urlable = $row->urlable;
                if ($urlable instanceof Redirect && $urlable->is_active) {
                    return $urlable;
                }
            }
        }

        if (! Schema::hasTable((new Redirect)->getTable())) {
            return null;
        }

        return Redirect::query()
            ->where('locale', $locale)
            ->where('is_active', true)
            ->get()
            ->first(function (Redirect $r) use ($pathKey) {
                return $this->canonicalUrlResolver->normalizePath((string) $r->from_path) === $pathKey;
            });
    }

    protected function toHttpRedirect(Redirect $redirect): RedirectResponse
    {
        $code = (int) $redirect->status_code;
        if ($code < 300 || $code > 399) {
            $code = 302;
        }

        $target = trim((string) $redirect->to_path);

        if ($target === '') {
            return redirect()->to('/', $code);
        }

        if (preg_match('#^https?://#i', $target)) {
            return tap(redirect()->away($target), fn (RedirectResponse $r) => $r->setStatusCode($code));
        }

        if (! str_starts_with($target, '/')) {
            $target = '/' . $target;
        }

        return tap(redirect()->to($target), fn (RedirectResponse $r) => $r->setStatusCode($code));
    }

    private function shouldSkipRedirectLookupsForUrlStaleResilience(): bool
    {
        return ModularousCache::isUrlStaleServeFirst() && ! database_exists();
    }
}
