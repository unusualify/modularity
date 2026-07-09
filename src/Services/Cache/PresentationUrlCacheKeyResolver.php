<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Cache;

use Illuminate\Http\Request;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Services\ModularousCacheService;

/**
 * Resolves locale + path + optional query into a URL stale cache lookup key.
 *
 * Returns null when the request should bypass URL-keyed cache (unknown query params).
 */
final class PresentationUrlCacheKeyResolver
{
    public function __construct(
        private readonly ModularousCacheService $cache,
        private readonly UrlPresentationCacheStoreInterface $urlPresentationCacheStore,
    ) {}

    /**
     * @return string|null Full cache lookup key (path or path?query), or null to bypass cache.
     */
    public function resolve(
        Request $request,
        string $normalizedPath,
        ?string $moduleName = null,
        ?string $moduleRouteName = null,
    ): ?string {
        [$strategy, $allowlist] = $this->resolveStrategyAndAllowlist($normalizedPath, $moduleName, $moduleRouteName);

        if (PresentationUrlCacheKey::shouldBypassRequest($request, $allowlist, $strategy)) {
            return null;
        }

        $querySuffix = PresentationUrlCacheKey::normalizeQuerySuffix(
            $request->query->all(),
            $allowlist,
            $strategy,
        );

        return $this->urlPresentationCacheStore->composeLookupKey($normalizedPath, $querySuffix);
    }

    /**
     * @return array{0: string, 1: list<string>}
     */
    public function resolveStrategyAndAllowlist(
        string $normalizedPath,
        ?string $moduleName,
        ?string $moduleRouteName,
    ): array {
        if ($moduleName !== null && $moduleRouteName !== null) {
            return [
                $this->cache->getPresentationCacheKeyStrategy($moduleName, $moduleRouteName),
                $this->cache->getPresentationCacheQueryAllowlist($moduleName, $moduleRouteName),
            ];
        }

        $pathConfigs = (array) config('modularous.cache.presentationItem.url.path_query', []);
        $path = $this->urlPresentationCacheStore->normalizePath($normalizedPath);

        if (isset($pathConfigs[$path])) {
            return [
                PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST,
                array_values(array_map('strval', (array) $pathConfigs[$path])),
            ];
        }

        return [PresentationUrlCacheKey::STRATEGY_PATH_ONLY, []];
    }
}
