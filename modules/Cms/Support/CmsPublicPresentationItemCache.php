<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Support\ModularousCacheLogger;

/**
 * Redis-backed public CMS presentation HTML ({@see presentationItem} cache type).
 *
 * Key shape: {@code {prefix}:{Module}:{Route}:presentationItem:{id}:{locale_hash}}
 *
 * Page-layout shell routes store the full HTML document from {@see CmsPageLayoutPresentationWrapper::documentOrNull()}.
 * Non-shell routes use {@see rememberFullViewHtml()} with a {@code full} key suffix.
 */
final class CmsPublicPresentationItemCache
{
    public const CACHE_TYPE = 'presentationItem';

    /**
     * Full HTML document (inner body + page-layout shell) for public routes wrapped by {@see CmsPageLayoutPresentationWrapper}.
     *
     * @param array<string, mixed> $innerData
     */
    public static function rememberWrappedDocumentHtml(
        string $moduleName,
        string $moduleRouteName,
        Model $item,
        string $viewName,
        array $innerData,
        ?string $locale = null,
    ): ?string {
        $rawLocale = $locale ?? app()->getLocale();
        $locale = self::normalizeCacheLocale($rawLocale);
        $id = $item->getKey();
        $key = self::cacheKey($moduleName, $moduleRouteName, $id, $locale);
        $ttl = ModularousCache::getTtl(self::CACHE_TYPE, $moduleName, $moduleRouteName);
        $relations = [$item::class => $id];

        if (! self::isEnabled($moduleName, $moduleRouteName)) {
            return CmsPageLayoutPresentationWrapper::documentOrNull($item, $viewName, $innerData);
        }

        $cached = ModularousCache::getWithRelations($key, null, $moduleName, $moduleRouteName, $relations, self::CACHE_TYPE);
        if (is_string($cached) && $cached !== '') {
            ModularousCacheLogger::info('cache.cms.remember_wrapped_document.hit', [
                'key' => $key,
                'rawLocale' => $rawLocale,
                'locale' => $locale,
                'localeHashInput' => ['locale' => $locale],
                'result' => 'hit',
            ]);

            return $cached;
        }

        $document = CmsPageLayoutPresentationWrapper::documentOrNull($item, $viewName, $innerData);
        if (! is_string($document) || $document === '') {
            ModularousCacheLogger::info('cache.cms.remember_wrapped_document.result', [
                'key' => $key,
                'rawLocale' => $rawLocale,
                'locale' => $locale,
                'localeHashInput' => ['locale' => $locale],
                'result' => 'empty',
            ]);

            return null;
        }

        ModularousCache::putWithRelations($key, $document, $ttl, $moduleName, $moduleRouteName, $relations, self::CACHE_TYPE);

        ModularousCacheLogger::info('cache.cms.remember_wrapped_document.result', [
            'key' => $key,
            'rawLocale' => $rawLocale,
            'locale' => $locale,
            'localeHashInput' => ['locale' => $locale],
            'result' => 'written',
        ]);

        return $document;
    }

    /**
     * Inner body fragment only — prefer {@see rememberWrappedDocumentHtml()} for page-layout shell public routes.
     *
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
        $rawLocale = $locale ?? app()->getLocale();
        $locale = self::normalizeCacheLocale($rawLocale);
        $id = $item->getKey();
        $key = self::cacheKey($moduleName, $moduleRouteName, $id, $locale);
        $ttl = ModularousCache::getTtl(self::CACHE_TYPE, $moduleName, $moduleRouteName);
        $relations = [$item::class => $id];

        return ModularousCache::rememberWithRelations(
            $key,
            $ttl,
            static fn (): string => CmsPageLayoutPresentationWrapper::renderInnerPresentationHtml($item, $viewName, $innerData),
            $moduleName,
            $moduleRouteName,
            $relations,
            self::CACHE_TYPE,
        );
    }

    /**
     * @param array<string, mixed> $innerData
     */
    /**
     * Same cache branch as {@see CmsController::renderPublicCmsPresentation()} (wrapped document vs full view).
     *
     * @param array<string, mixed> $innerData
     */
    public static function rememberPublicPresentation(
        string $moduleName,
        string $moduleRouteName,
        Model $item,
        string $viewName,
        array $innerData,
        ?string $locale = null,
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

        $relations = [$item::class => $item->getKey()];
        $cached = self::isEnabled($moduleName, $moduleRouteName)
            ? ModularousCache::getWithRelations(
                $cacheKey,
                null,
                $moduleName,
                $moduleRouteName,
                $relations,
                self::CACHE_TYPE,
            )
            : null;

        if (is_string($cached) && $cached !== '') {
            ModularousCacheLogger::info('cache.cms.remember_public_presentation.result', [
                'key' => $cacheKey,
                'locale' => $locale,
                'variant' => $wrapped ? 'wrapped' : 'full',
                'result' => 'hit',
            ]);

            return $cached;
        }

        $html = $wrapped
            ? self::rememberWrappedDocumentHtml(
                $moduleName,
                $moduleRouteName,
                $item,
                $viewName,
                $innerData,
                $locale,
            )
            : self::rememberFullViewHtml(
                $moduleName,
                $moduleRouteName,
                $item,
                $viewName,
                $innerData,
                $locale,
            );

        $result = 'empty';
        if (is_string($html) && $html !== '') {
            $result = 'written';
        }

        ModularousCacheLogger::info('cache.cms.remember_public_presentation.result', [
            'key' => $cacheKey,
            'locale' => $locale,
            'variant' => $wrapped ? 'wrapped' : 'full',
            'result' => $result,
        ]);

        return $html;
    }

    /**
     * Cache key for {@see rememberPublicPresentation()} — matches wrapped vs {@code full} suffix logic.
     */
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

    public static function rememberFullViewHtml(
        string $moduleName,
        string $moduleRouteName,
        Model $item,
        string $viewName,
        array $innerData,
        ?string $locale = null,
    ): string {
        $rawLocale = $locale ?? app()->getLocale();
        $locale = self::normalizeCacheLocale($rawLocale);
        $id = $item->getKey();
        $key = self::cacheKey($moduleName, $moduleRouteName, $id, $locale, ['full' => true]);
        $ttl = ModularousCache::getTtl(self::CACHE_TYPE, $moduleName, $moduleRouteName);
        $relations = [$item::class => $id];

        $existing = ModularousCache::getWithRelations($key, null, $moduleName, $moduleRouteName, $relations, self::CACHE_TYPE);
        if (is_string($existing) && $existing !== '') {
            ModularousCacheLogger::info('cache.cms.remember_full_view.hit', [
                'key' => $key,
                'locale' => $locale,
                'result' => 'hit',
            ]);

            return $existing;
        }

        $html = (string) ModularousCache::rememberWithRelations(
            $key,
            $ttl,
            static fn (): string => View::make($viewName, $innerData)->render(),
            $moduleName,
            $moduleRouteName,
            $relations,
            self::CACHE_TYPE,
        );

        ModularousCacheLogger::info('cache.cms.remember_full_view.result', [
            'key' => $key,
            'locale' => $locale,
            'result' => $html !== '' ? 'written' : 'empty',
        ]);

        return $html;
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

    /**
     * Canonical locale for presentationItem cache keys — warmup UrlRoute locales and live
     * {@see app()->getLocale()} (e.g. {@code en_US}) must hash identically.
     */
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
        return ModularousCache::isEnabled($moduleName, $moduleRouteName, self::CACHE_TYPE);
    }
}
