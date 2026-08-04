<?php

declare(strict_types=1);

namespace Modules\ErrorPage\Support;

use Modules\Cms\Support\CmsPathLocale;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Modules\Cms\Support\CmsPublicPresentationWarmupContext;
use Modules\ErrorPage\Entities\ErrorPage;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Support\ModularousCacheLogger;

/**
 * Model-scoped presentationItem HTML for published {@see ErrorPage} rows.
 *
 * Uses {@see \Unusualify\Modularous\Services\Cache\StaleFileCache} keyed by model id + locale
 * (not visitor URL). Independent of {@code presentationItem.store=url} — ErrorPage has no UrlRoute.
 */
final class ErrorPagePresentationCache
{
    public const MODULE = 'ErrorPage';

    public const ROUTE = 'ErrorPage';

    public static function enabled(): bool
    {
        if (! (bool) modularousConfig('cms_features.error_pages_cache_enabled', true)) {
            return false;
        }

        if (! (bool) config('modularous.cache.enabled', true)) {
            return false;
        }

        if (! ModularousCache::isPresentationCacheEnabled()) {
            return false;
        }

        return ModularousCache::isCacheTypeConfigured(self::MODULE, self::ROUTE, CmsPublicPresentationItemCache::CACHE_TYPE);
    }

    /**
     * @param  callable(): (?string)  $render
     */
    public static function remember(ErrorPage $item, string $locale, callable $render): ?string
    {
        if (! self::enabled() || $item->getKey() === null) {
            return $render();
        }

        $locale = CmsPublicPresentationItemCache::normalizeCacheLocale($locale);
        $cacheKey = self::cacheKey($item, $locale);
        $relations = CmsPublicPresentationItemCache::staleRelations(
            [ErrorPage::class => $item->getKey()],
            $locale,
        );

        $stale = ModularousCache::getStaleFileCache();
        $cached = $stale->get($cacheKey, null, $relations);
        if (is_string($cached) && $cached !== '') {
            ModularousCacheLogger::info('cache.error_page.presentation.hit', [
                'key' => $cacheKey,
                'locale' => $locale,
                'id' => $item->getKey(),
                'error_code' => $item->error_code,
            ]);

            return $cached;
        }

        $html = $render();
        if (! is_string($html) || $html === '') {
            return $html;
        }

        $written = $stale->put(
            $cacheKey,
            $html,
            ModularousCache::getStaleTtl(CmsPublicPresentationItemCache::CACHE_TYPE),
            $relations,
        );

        ModularousCacheLogger::info('cache.error_page.presentation.miss', [
            'key' => $cacheKey,
            'locale' => $locale,
            'id' => $item->getKey(),
            'error_code' => $item->error_code,
            'written' => $written,
        ]);

        return $html;
    }

    /**
     * Rebuild model-scoped presentation HTML for admin / job warm (no UrlRoute).
     *
     * Unpublished or missing publish state skips gracefully. When {@code $locale} is null,
     * warms CMS path-segment locales (fallback: app locale).
     */
    public static function warm(ErrorPage $item, ?string $locale = null): bool
    {
        if (! self::enabled() || $item->getKey() === null) {
            return false;
        }

        if (! (bool) $item->published) {
            ModularousCacheLogger::info('cache.error_page.presentation.warm.skip', [
                'id' => $item->getKey(),
                'error_code' => $item->error_code ?? null,
                'reason' => 'unpublished',
            ]);

            return false;
        }

        $locales = self::resolveWarmLocales($locale);
        if ($locales === []) {
            ModularousCacheLogger::info('cache.error_page.presentation.warm.skip', [
                'id' => $item->getKey(),
                'reason' => 'no_locales',
            ]);

            return false;
        }

        ModularousCacheLogger::info('cache.error_page.presentation.warm.start', [
            'id' => $item->getKey(),
            'error_code' => $item->error_code,
            'locales' => $locales,
        ]);

        $renderer = app(ErrorPageRenderer::class);
        $warmedAny = false;

        foreach ($locales as $warmLocale) {
            $html = CmsPublicPresentationWarmupContext::run($warmLocale, function () use ($item, $renderer, $warmLocale): ?string {
                return self::remember(
                    $item,
                    $warmLocale,
                    static fn (): ?string => $renderer->renderPublishedHtml($item),
                );
            });

            if (is_string($html) && $html !== '') {
                $warmedAny = true;
                ModularousCacheLogger::info('cache.error_page.presentation.warm.locale', [
                    'id' => $item->getKey(),
                    'locale' => $warmLocale,
                    'error_code' => $item->error_code,
                    'result' => 'written_or_hit',
                ]);
            }
        }

        ModularousCacheLogger::info('cache.error_page.presentation.warm.complete', [
            'id' => $item->getKey(),
            'warmedAny' => $warmedAny,
        ]);

        return $warmedAny;
    }

    public static function invalidate(ErrorPage $item): void
    {
        $id = $item->getKey();
        if ($id === null) {
            return;
        }

        ModularousCache::getStaleFileCache()->forgetByRelation(ErrorPage::class, $id);
        ModularousCache::getStaleFileCache()->forgetByModuleRouteId(self::MODULE, self::ROUTE, $id);
        ModularousCache::invalidatePresentationItemCache(
            self::MODULE,
            self::ROUTE,
            $id,
            ErrorPage::class,
        );

        ModularousCacheLogger::info('cache.error_page.presentation.invalidate', [
            'id' => $id,
            'error_code' => $item->error_code ?? null,
        ]);
    }

    public static function cacheKey(ErrorPage $item, string $locale): string
    {
        return CmsPublicPresentationItemCache::cacheKey(
            self::MODULE,
            self::ROUTE,
            $item->getKey(),
            $locale,
            ['error_code' => (string) $item->error_code],
        );
    }

    /**
     * @return list<string>
     */
    private static function resolveWarmLocales(?string $locale): array
    {
        if ($locale !== null && $locale !== '') {
            return [CmsPublicPresentationItemCache::normalizeCacheLocale($locale)];
        }

        $locales = [];

        if (class_exists(CmsPathLocale::class)) {
            try {
                $locales = CmsPathLocale::pathSegmentLocales();
            } catch (\Throwable) {
                $locales = [];
            }
        }

        if ($locales === [] && function_exists('getLocales')) {
            foreach (getLocales() as $loc) {
                $locales[] = (string) $loc;
            }
        }

        if ($locales === []) {
            $appLocale = (string) config('app.locale', 'en');
            if ($appLocale !== '') {
                $locales = [$appLocale];
            }
        }

        $normalized = [];
        foreach ($locales as $loc) {
            $loc = trim((string) $loc);
            if ($loc === '') {
                continue;
            }
            $normalized[] = CmsPublicPresentationItemCache::normalizeCacheLocale($loc);
        }

        return array_values(array_unique($normalized));
    }
}
