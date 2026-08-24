<?php

declare(strict_types=1);

namespace Modules\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Contracts\CmsVisitorRequestContextResolverInterface;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Modules\Cms\Support\CmsSluglessFallbackLocale;
use Modules\Cms\Support\StalePublicationGate;
use Symfony\Component\HttpFoundation\Response;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Support\ModularousCacheLogger;

/**
 * Serves URL-keyed stale HTML before the controller when store=url and serve_first is enabled.
 */
final class ServeUrlKeyedStaleMiddleware
{
    public function __construct(
        private CmsVisitorRequestContextResolverInterface $resolver,
        private CmsLocalizationContract $cmsLocalization,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldAttemptServeFirst($request)) {
            return $next($request);
        }

        [$locale, $pathKey, $explicitLocale] = $this->resolver->resolveLocalePathKeyAndExplicitFlag($request);

        $entry = $this->resolveVisibleEntry($request, $locale, $pathKey);
        if ($entry === null && ! $explicitLocale) {
            $implicitLocale = CmsSluglessFallbackLocale::implicitPreferredLocaleOtherwise(
                $this->cmsLocalization->defaultLocale(),
            );
            if ($implicitLocale !== $locale) {
                $entry = $this->resolveVisibleEntry($request, $implicitLocale, $pathKey);
            }
        }

        if ($entry === null) {
            ModularousCacheLogger::info('cache.cms.url_stale.miss', [
                'locale' => $locale,
                'path' => $pathKey,
                'explicitLocale' => $explicitLocale,
            ]);

            return $next($request);
        }

        $status = $entry['freshness'] === UrlPresentationCacheStoreInterface::FRESHNESS_HIT
            ? CmsPublicPresentationItemCache::CACHE_STATUS_URL_HIT
            : CmsPublicPresentationItemCache::CACHE_STATUS_URL_STALE;

        ModularousCacheLogger::info('cache.cms.url_stale.hit', [
            'locale' => $entry['meta']['locale'] ?? $locale,
            'path' => $entry['meta']['normalized_path'] ?? $pathKey,
            'query' => $entry['meta']['normalized_query'] ?? null,
            'freshness' => $entry['freshness'],
            'module' => $entry['meta']['module'] ?? null,
            'route' => $entry['meta']['route'] ?? null,
        ]);

        return response(
            $entry['html'],
            200,
            [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Modularous-Cache' => CmsPublicPresentationItemCache::cacheHeaderValue($status),
            ],
        );
    }

    protected function shouldAttemptServeFirst(Request $request): bool
    {
        if (! Modularous::isFrontUrl($request->fullUrl())) {
            return false;
        }

        if (! ModularousCache::isUrlStaleServeFirst()) {
            return false;
        }

        if ($request->getMethod() !== 'GET' && $request->getMethod() !== 'HEAD') {
            return false;
        }

        return ! $this->resolver->shouldExcludeRequest($request);
    }

    /**
     * @return array{html: string, meta: array<string, mixed>, freshness: string}|null
     */
    protected function resolveVisibleEntry(Request $request, string $locale, string $pathKey): ?array
    {
        $cacheLookupKey = ModularousCache::getPresentationUrlCacheKeyResolver()->resolve($request, $pathKey);
        if ($cacheLookupKey === null) {
            ModularousCacheLogger::info('cache.cms.url_stale.bypass_query', [
                'locale' => $locale,
                'path' => $pathKey,
            ]);

            return null;
        }

        $cache = ModularousCache::getUrlPresentationCacheStore();
        $entry = $cache->get($locale, $cacheLookupKey);
        if ($entry === null) {
            return null;
        }

        if (! StalePublicationGate::isVisible($entry['meta'])) {
            ModularousCacheLogger::info('cache.cms.url_stale.not_visible', [
                'locale' => $locale,
                'path' => $pathKey,
                'cacheLookupKey' => $cacheLookupKey,
                'meta' => $entry['meta'],
            ]);

            return null;
        }

        return $entry;
    }
}
