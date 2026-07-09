<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\WarmPresentationItemJob;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Support\ModularousCacheLogger;

/**
 * File-primary public CMS presentation HTML ({@see presentationItem} cache type).
 *
 * Primary store is selected by {@see ModularousCache::getPresentationCacheStore()}:
 * `url` → {@see UrlPresentationCacheStoreInterface}, `model` → {@see StaleFileCache}.
 *
 * Redis is not used for public presentationItem reads or writes.
 */
final class CmsPublicPresentationItemCache
{
    public const CACHE_TYPE = 'presentationItem';

    public const CACHE_STATUS_HIT = 'HIT';

    public const CACHE_STATUS_STALE = 'STALE';

    public const CACHE_STATUS_MISS = 'MISS';

    public const CACHE_STATUS_BYPASS = 'BYPASS';

    public const CACHE_STATUS_DISABLED = 'DISABLED';

    public const CACHE_STATUS_URL_HIT = 'URL_HIT';

    public const CACHE_STATUS_URL_STALE = 'URL_STALE';

    /**
     * Resolve public presentation HTML with optional SWR (fresh miss → stale serve + background warm).
     *
     * Render exceptions propagate — no graceful fallback to stale on render failure.
     *
     * @param array<string, mixed> $innerData
     * @return array{html: ?string, status: string}
     */
    public static function resolvePresentationHtml(
        string $moduleName,
        string $moduleRouteName,
        Model $item,
        string $viewName,
        array $innerData,
        ?string $locale = null,
        bool $bypassSwr = false,
        ?string $normalizedPath = null,
        ?string $cacheLookupKey = null,
    ): array {
        $rawLocale = $locale ?? app()->getLocale();
        $locale = self::normalizeCacheLocale($rawLocale);
        $cacheKey = self::cacheKeyForPublicPresentation(
            $moduleName,
            $moduleRouteName,
            $item,
            $viewName,
            $locale,
        );
        $relations = [$item::class => $item->getKey()];

        if (! self::isEnabled($moduleName, $moduleRouteName)) {
            $html = self::renderPresentationHtml($item, $viewName, $innerData, $locale);

            return [
                'html' => $html,
                'status' => self::CACHE_STATUS_DISABLED,
            ];
        }

        if ($bypassSwr) {
            $html = self::renderPresentationHtml($item, $viewName, $innerData, $locale);

            if (
                is_string($html) && $html !== ''
                && self::resolveUrlCacheLookupKey($normalizedPath, $cacheLookupKey) !== null
                && self::usesUrlStore()
            ) {
                self::storeUrlKeyedPresentation(
                    $locale,
                    self::resolveUrlCacheLookupKey($normalizedPath, $cacheLookupKey),
                    $html,
                    $item,
                    $moduleName,
                    $moduleRouteName,
                );
            }

            return [
                'html' => $html,
                'status' => self::CACHE_STATUS_BYPASS,
            ];
        }

        $store = ModularousCache::getPresentationCacheStore();
        $swrEnabled = ModularousCache::isSwrEnabled($moduleName, $moduleRouteName, self::CACHE_TYPE);

        $urlCacheLookupKey = self::resolveUrlCacheLookupKey($normalizedPath, $cacheLookupKey);

        if ($store === 'url' && $urlCacheLookupKey !== null) {
            $urlStore = ModularousCache::getUrlPresentationCacheStore();
            $urlEntry = $urlStore->get($locale, $urlCacheLookupKey);
            if ($urlEntry !== null && StalePublicationGate::isVisible($urlEntry['meta'])) {
                if ($urlEntry['freshness'] === UrlPresentationCacheStoreInterface::FRESHNESS_HIT) {
                    ModularousCacheLogger::info('cache.cms.resolve_presentation.result', [
                        'key' => $cacheKey,
                        'locale' => $locale,
                        'path' => $normalizedPath,
                        'cacheLookupKey' => $urlCacheLookupKey,
                        'result' => self::CACHE_STATUS_URL_HIT,
                    ]);

                    return [
                        'html' => $urlEntry['html'],
                        'status' => self::CACHE_STATUS_URL_HIT,
                    ];
                }

                if ($swrEnabled) {
                    self::dispatchWarmPresentationItemIfNeeded($item, $moduleName, $moduleRouteName);

                    ModularousCacheLogger::info('cache.cms.resolve_presentation.result', [
                        'key' => $cacheKey,
                        'locale' => $locale,
                        'path' => $normalizedPath,
                        'cacheLookupKey' => $urlCacheLookupKey,
                        'result' => self::CACHE_STATUS_URL_STALE,
                    ]);

                    return [
                        'html' => $urlEntry['html'],
                        'status' => self::CACHE_STATUS_URL_STALE,
                    ];
                }
            }
        }

        if ($store === 'model' && $swrEnabled) {
            $stale = ModularousCache::getStale(
                $cacheKey,
                null,
                self::staleRelations($relations, $locale),
                self::CACHE_TYPE,
            );

            if (is_string($stale) && $stale !== '') {
                self::dispatchWarmPresentationItemIfNeeded($item, $moduleName, $moduleRouteName);

                ModularousCacheLogger::info('cache.cms.resolve_presentation.result', [
                    'key' => $cacheKey,
                    'locale' => $locale,
                    'result' => self::CACHE_STATUS_STALE,
                ]);

                return [
                    'html' => $stale,
                    'status' => self::CACHE_STATUS_STALE,
                ];
            }
        }

        $html = self::renderPresentationHtml($item, $viewName, $innerData, $locale);

        if (is_string($html) && $html !== '' && $store !== 'none') {
            self::storePresentationForStore(
                $store,
                $cacheKey,
                $html,
                $moduleName,
                $moduleRouteName,
                $relations,
                $locale,
                $item,
                $normalizedPath,
                $cacheLookupKey,
                $swrEnabled,
            );
        }

        ModularousCacheLogger::info('cache.cms.resolve_presentation.result', [
            'key' => $cacheKey,
            'locale' => $locale,
            'result' => self::CACHE_STATUS_MISS,
            'swrEnabled' => $swrEnabled,
        ]);

        return [
            'html' => $html,
            'status' => self::CACHE_STATUS_MISS,
        ];
    }

    protected static function resolveUrlCacheLookupKey(?string $normalizedPath, ?string $cacheLookupKey): ?string
    {
        if ($cacheLookupKey !== null && $cacheLookupKey !== '') {
            return $cacheLookupKey;
        }

        if ($normalizedPath !== null && $normalizedPath !== '') {
            return $normalizedPath;
        }

        return null;
    }

    public static function cacheHeaderValue(string $status): string
    {
        return self::CACHE_TYPE . '=' . $status;
    }

    /**
     * @param array<string, int|string|array<int|string>> $relations
     */
    protected static function storePresentationForStore(
        string $store,
        string $cacheKey,
        string $html,
        string $moduleName,
        string $moduleRouteName,
        array $relations,
        string $locale,
        Model $item,
        ?string $normalizedPath,
        ?string $cacheLookupKey,
        bool $swrEnabled,
    ): void {
        $urlCacheLookupKey = self::resolveUrlCacheLookupKey($normalizedPath, $cacheLookupKey);

        if ($store === 'url' && $urlCacheLookupKey !== null) {
            self::storeUrlKeyedPresentation(
                $locale,
                $urlCacheLookupKey,
                $html,
                $item,
                $moduleName,
                $moduleRouteName,
            );

            return;
        }

        if ($store === 'model' && $swrEnabled) {
            ModularousCache::putStaleWithRelations(
                $cacheKey,
                $html,
                ModularousCache::getStaleTtl(self::CACHE_TYPE),
                self::staleRelations($relations, $locale),
                self::CACHE_TYPE,
            );
        }
    }

    public static function storeUrlKeyedPresentation(
        string $locale,
        string $cacheLookupKey,
        string $html,
        Model $item,
        string $moduleName,
        string $moduleRouteName,
    ): bool {
        if (! self::usesUrlStore()) {
            return false;
        }

        $freshTtl = ModularousCache::getTtl(self::CACHE_TYPE, $moduleName, $moduleRouteName);
        [$normalizedPath] = ModularousCache::getUrlPresentationCacheStore()->splitLookupKey($cacheLookupKey);
        $meta = StalePublicationMeta::fromModel(
            $item,
            $locale,
            $normalizedPath,
            $moduleName,
            $moduleRouteName,
            $freshTtl,
        );

        $written = ModularousCache::getUrlPresentationCacheStore()->put(
            $locale,
            $cacheLookupKey,
            $html,
            $meta,
            $freshTtl,
            ModularousCache::getUrlStaleTtl(),
        );

        if (! $written) {
            ModularousCacheLogger::warning('cache.cms.url_stale.put_failed', [
                'locale' => $locale,
                'path' => $normalizedPath,
                'cacheLookupKey' => $cacheLookupKey,
                'module' => $moduleName,
                'route' => $moduleRouteName,
                'model' => $item::class,
                'id' => $item->getKey(),
            ]);
        }

        return $written;
    }

    /**
     * @param array<string, mixed> $innerData
     */
    protected static function renderPresentationHtml(
        Model $item,
        string $viewName,
        array $innerData,
        ?string $locale = null,
    ): ?string {
        $render = static function () use ($item, $viewName, $innerData): ?string {
            if (CmsPageLayoutPresentationWrapper::resolvesWithPageLayoutShell($item, $viewName)) {
                return CmsPageLayoutPresentationWrapper::documentOrNull($item, $viewName, $innerData);
            }

            return (string) View::make($viewName, $innerData)->render();
        };

        if ($locale !== null && $locale !== '') {
            return CmsPublicPresentationWarmupContext::run($locale, $render);
        }

        return CmsPublicSiteUrl::runWithForcedPublicRootUrl($render);
    }

    /**
     * @param array<string, mixed> $innerData
     */
    public static function rememberWrappedDocumentHtml(
        string $moduleName,
        string $moduleRouteName,
        Model $item,
        string $viewName,
        array $innerData,
        ?string $locale = null,
        ?string $normalizedPath = null,
    ): ?string {
        $resolved = self::resolvePresentationHtml(
            $moduleName,
            $moduleRouteName,
            $item,
            $viewName,
            $innerData,
            $locale,
            normalizedPath: $normalizedPath,
        );

        return $resolved['html'];
    }

    /**
     * @param array<string, mixed> $innerData
     */
    public static function rememberInnerHtml(
        string $moduleName,
        string $moduleRouteName,
        Model $item,
        string $viewName,
        array $innerData,
        ?string $locale = null,
    ): ?string {
        if (! self::isEnabled($moduleName, $moduleRouteName)) {
            return CmsPageLayoutPresentationWrapper::renderInnerPresentationHtml($item, $viewName, $innerData);
        }

        return CmsPageLayoutPresentationWrapper::renderInnerPresentationHtml($item, $viewName, $innerData);
    }

    /**
     * @param array<string, mixed> $innerData
     */
    public static function rememberPublicPresentation(
        string $moduleName,
        string $moduleRouteName,
        Model $item,
        string $viewName,
        array $innerData,
        ?string $locale = null,
        ?string $normalizedPath = null,
    ): ?string {
        $rawLocale = $locale ?? app()->getLocale();
        $locale = self::normalizeCacheLocale($rawLocale);
        $wrapped = CmsPageLayoutPresentationWrapper::resolvesWithPageLayoutShell($item, $viewName);
        $cacheKey = self::cacheKeyForPublicPresentation(
            $moduleName,
            $moduleRouteName,
            $item,
            $viewName,
            $locale,
        );

        ModularousCacheLogger::info('cache.cms.remember_public_presentation', [
            'key' => $cacheKey,
            'rawLocale' => $rawLocale,
            'locale' => $locale,
            'localeHashInput' => ['locale' => $locale],
            'variant' => $wrapped ? 'wrapped' : 'full',
            'module' => $moduleName,
            'route' => $moduleRouteName,
            'model' => $item::class,
            'id' => $item->getKey(),
        ]);

        $resolved = self::resolvePresentationHtml(
            $moduleName,
            $moduleRouteName,
            $item,
            $viewName,
            $innerData,
            $locale,
            normalizedPath: $normalizedPath,
        );

        ModularousCacheLogger::info('cache.cms.remember_public_presentation.result', [
            'key' => $cacheKey,
            'locale' => $locale,
            'variant' => $wrapped ? 'wrapped' : 'full',
            'result' => strtolower($resolved['status']),
        ]);

        return $resolved['html'];
    }

    public static function cacheKeyForPublicPresentation(
        string $moduleName,
        string $moduleRouteName,
        Model $item,
        string $viewName,
        string $locale,
    ): string {
        $extraParams = CmsPageLayoutPresentationWrapper::resolvesWithPageLayoutShell($item, $viewName)
            ? []
            : ['full' => true];

        return self::cacheKey($moduleName, $moduleRouteName, $item->getKey(), $locale, $extraParams);
    }

    /**
     * @param array<string, mixed> $innerData
     */
    public static function rememberFullViewHtml(
        string $moduleName,
        string $moduleRouteName,
        Model $item,
        string $viewName,
        array $innerData,
        ?string $locale = null,
        ?string $normalizedPath = null,
    ): string {
        $resolved = self::resolvePresentationHtml(
            $moduleName,
            $moduleRouteName,
            $item,
            $viewName,
            $innerData,
            $locale,
            normalizedPath: $normalizedPath,
        );

        return (string) ($resolved['html'] ?? '');
    }

    public static function cacheKey(
        string $moduleName,
        string $moduleRouteName,
        int|string $id,
        string $locale,
        array $extraParams = [],
    ): string {
        $locale = self::normalizeCacheLocale($locale);

        return ModularousCache::generateCacheKey(
            $moduleName,
            $moduleRouteName,
            self::CACHE_TYPE . ':' . $id,
            array_merge(['locale' => $locale], $extraParams),
        );
    }

    public static function normalizeCacheLocale(?string $locale = null): string
    {
        $locale = trim(mb_strtolower((string) ($locale ?? app()->getLocale())));

        if ($locale === '') {
            return trim(mb_strtolower((string) config('app.locale', 'en')));
        }

        if (str_contains($locale, '_')) {
            return explode('_', $locale)[0];
        }

        if (str_contains($locale, '-')) {
            return explode('-', $locale)[0];
        }

        return $locale;
    }

    public static function isEnabled(string $moduleName, string $moduleRouteName): bool
    {
        if (! ModularousCache::isPresentationCacheEnabled()) {
            return false;
        }

        if (self::usesUrlStore()) {
            return ModularousCache::isCacheTypeConfigured($moduleName, $moduleRouteName, self::CACHE_TYPE);
        }

        return ModularousCache::isEnabled($moduleName, $moduleRouteName, self::CACHE_TYPE);
    }

    public static function usesUrlStore(): bool
    {
        return ModularousCache::isUrlStaleEnabled();
    }

    /**
     * @deprecated Use usesUrlStore()
     */
    public static function isUrlStaleEnabled(): bool
    {
        return self::usesUrlStore();
    }

    /**
     * @param array<string, int|string|array<int|string>> $relations
     * @return array<string, int|string|array<int|string>>
     */
    public static function staleRelations(array $relations, string $locale): array
    {
        return array_merge($relations, [
            StaleFileCache::LOCALE_RELATION_KEY => self::normalizeCacheLocale($locale),
        ]);
    }

    public static function dispatchWarmPresentationItemIfNeeded(
        Model $item,
        string $moduleName,
        string $moduleRouteName,
    ): void {
        $lockKey = self::warmPresentationOverlapKey($item, $moduleName, $moduleRouteName);
        $cooldown = (int) config(
            'modularous.cache.presentationItem.warm_dispatch_cooldown',
            config('modularous.cache.swr.warm_dispatch_cooldown', 600),
        );

        if (! Cache::add($lockKey, 1, $cooldown)) {
            ModularousCacheLogger::info('cache.cms.warm_presentation_item.skip_duplicate', [
                'model' => $item::class,
                'id' => $item->getKey(),
                'moduleName' => $moduleName,
                'moduleRouteName' => $moduleRouteName,
                'lockKey' => $lockKey,
            ]);

            return;
        }

        WarmPresentationItemJob::dispatch($item, $moduleName, $moduleRouteName);
    }

    public static function warmPresentationOverlapKey(
        Model $item,
        string $moduleName,
        string $moduleRouteName,
    ): string {
        return sprintf(
            'cache:warm-presentation:%s:%s:%s:%s',
            $moduleName,
            $moduleRouteName,
            $item::class,
            (string) $item->getKey(),
        );
    }
}
