<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Entities\Concerns\HasParentSegment;
use Modules\Cms\Entities\UrlRoute;
use Unusualify\Modularous\Entities\Traits\HasSlug;
use Unusualify\Modularous\Entities\Traits\IsSingular;

/**
 * Editorial URL leaf strings per locale from a resolved public CMS {@see Model}.
 *
 * Used for presentation view data (CSS page bundles keyed by EN/fallback leaf). Does not join
 * parent prefixes or read {@see UrlRoute} rows.
 */
final class CmsPublicPageSlugLeaves
{
    /**
     * @return array{
     *   pageSlugs: array<string, string>,
     *   pageSlug: string,
     *   pageFallbackSlug: string
     * }
     */
    public static function forItem(Model $item): array
    {
        $pageSlugs = self::leavesByLocale($item);
        $currentLocale = (string) app()->getLocale();

        $currentLeaf = $pageSlugs[$currentLocale] ?? '';
        $fallbackLeaf = self::pickFallbackLeaf($pageSlugs, $currentLocale);

        return [
            'pageSlugs' => $pageSlugs,
            'pageSlug' => self::cssLeaf($currentLeaf),
            'pageFallbackSlug' => self::cssLeaf($fallbackLeaf),
        ];
    }

    /**
     * @return array<string, string> locale => raw leaf (empty string allowed for homepage bindings)
     */
    public static function leavesByLocale(Model $item): array
    {
        if (classHasTrait($item, HasSlug::class)) {
            $fromSlugs = self::leavesFromHasSlug($item);
            if ($fromSlugs !== []) {
                return $fromSlugs;
            }
        }

        if (classHasTrait($item, HasParentSegment::class) || classHasTrait($item, IsSingular::class)) {
            return self::leavesFromParentSegments($item);
        }

        return [];
    }

    /**
     * @param array<string, string> $pageSlugs
     */
    public static function pickFallbackLeaf(array $pageSlugs, ?string $currentLocale = null): string
    {
        if ($pageSlugs === []) {
            return '';
        }

        $currentLocale = $currentLocale ?? (string) app()->getLocale();

        foreach (self::fallbackLocaleCandidates($currentLocale) as $locale) {
            if (! array_key_exists($locale, $pageSlugs)) {
                continue;
            }

            $leaf = (string) $pageSlugs[$locale];
            if ($leaf !== '') {
                return $leaf;
            }
        }

        foreach ($pageSlugs as $leaf) {
            if ((string) $leaf !== '') {
                return (string) $leaf;
            }
        }

        // All leaves empty (locale-root homepage) — still a valid CSS key via cssLeaf().
        if (array_key_exists($currentLocale, $pageSlugs)) {
            return (string) $pageSlugs[$currentLocale];
        }

        return (string) reset($pageSlugs);
    }

    /**
     * Homepage empty leaf → {@code index} for b2press page CSS filenames.
     */
    public static function cssLeaf(string $leaf): string
    {
        $leaf = trim($leaf);
        if ($leaf === '') {
            return 'index';
        }

        $leaf = str_replace('\\', '/', $leaf);
        $segments = array_values(array_filter(
            explode('/', $leaf),
            static fn (string $segment): bool => $segment !== ''
        ));

        if ($segments === []) {
            return 'index';
        }

        // Multi-segment prefixes: use the last segment as the CSS page key.
        return (string) end($segments);
    }

    /**
     * @return array<string, string>
     */
    private static function leavesFromHasSlug(Model $item): array
    {
        if (! method_exists($item, 'getSlug') || ! method_exists($item, 'slugs')) {
            return [];
        }

        $item->loadMissing('slugs');
        $out = [];

        foreach ($item->slugs as $slugRow) {
            if (! (bool) ($slugRow->active ?? false)) {
                continue;
            }

            $locale = trim((string) ($slugRow->locale ?? ''));
            if ($locale === '' || array_key_exists($locale, $out)) {
                continue;
            }

            $segment = trim((string) ($slugRow->slug ?? ''));
            // Skip blank HasSlug leaves (not homepage — those use ParentSegment).
            if ($segment === '') {
                continue;
            }

            $out[$locale] = $segment;
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    private static function leavesFromParentSegments(Model $item): array
    {
        if (! method_exists($item, 'parentSegments')) {
            return [];
        }

        $out = [];

        foreach ($item->parentSegments() as $segment) {
            $locale = trim((string) ($segment->locale ?? ''));
            if ($locale === '') {
                // Empty locale binding = all locales; expand via getLocales() when possible.
                foreach (self::configuredLocales() as $configuredLocale) {
                    if (! array_key_exists($configuredLocale, $out)) {
                        $out[$configuredLocale] = trim((string) ($segment->normalized_prefix ?? ''));
                    }
                }

                continue;
            }

            if (! array_key_exists($locale, $out)) {
                $out[$locale] = trim((string) ($segment->normalized_prefix ?? ''));
            }
        }

        return $out;
    }

    /**
     * @return list<string>
     */
    private static function fallbackLocaleCandidates(string $currentLocale): array
    {
        $ordered = [];
        $push = static function (?string $locale) use (&$ordered): void {
            $locale = trim((string) $locale);
            if ($locale === '' || in_array($locale, $ordered, true)) {
                return;
            }
            $ordered[] = $locale;
        };

        $push(modularousConfig('cms_routing.default_locale', config('app.locale')));
        $transFallback = config('translatable.fallback_locale');
        if (is_string($transFallback)) {
            $push($transFallback);
        }
        $push('en');
        $push($currentLocale);
        foreach (self::configuredLocales() as $locale) {
            $push($locale);
        }

        return $ordered;
    }

    /**
     * @return list<string>
     */
    private static function configuredLocales(): array
    {
        if (! function_exists('getLocales')) {
            return ['en', 'nl', 'tr'];
        }

        $locales = [];
        foreach (getLocales() as $locale) {
            $locale = trim((string) $locale);
            if ($locale !== '') {
                $locales[] = $locale;
            }
        }

        return $locales !== [] ? $locales : ['en', 'nl', 'tr'];
    }
}
