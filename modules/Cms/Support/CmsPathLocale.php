<?php

namespace Modules\Cms\Support;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Cms\Contracts\CmsLocalizationContract;

/**
 * Locales that may appear as the first URL segment for public CMS paths ({@code /{locale}/...}).
 * Delegates to {@see CmsLocalizationContract} (mcamara / translatable adapters).
 */
final class CmsPathLocale
{
    /**
     * @return list<string>
     */
    public static function pathSegmentLocales(): array
    {
        if (! app()->bound(CmsLocalizationContract::class)) {
            return self::legacyPathSegmentLocales();
        }

        return app(CmsLocalizationContract::class)->pathSegmentLocales();
    }

    /**
     * When CMS localization is not registered (feature disabled / early boot).
     *
     * @return list<string>
     */
    private static function legacyPathSegmentLocales(): array
    {
        $configured = modularousConfig('cms_routing.path_segment_locales');
        if (is_array($configured) && $configured !== []) {
            return self::sortedLongestFirst(array_values(array_unique(array_filter(array_map('strval', $configured)))));
        }

        $mcamaraKeys = null;

        if (class_exists(LaravelLocalization::class)) {
            try {
                $mcamaraKeys = LaravelLocalization::getSupportedLanguagesKeys();
                if ($mcamaraKeys === []) {
                    $mcamaraKeys = null;
                }
            } catch (\Throwable) {
                $mcamaraKeys = null;
            }
        }

        return self::mergeMcamaraKeysWithSiteLocales($mcamaraKeys);
    }

    /**
     * Locales mcamara exposes as route keys merged with CMS/translatable locales ({@see getLocales()}).
     * Prevents {@code /tr/...} from being parsed under the default locale when Turkish exists only outside mcamara.
     *
     * @param list<string>|null $mcamaraKeys
     * @return list<string>
     */
    public static function mergeMcamaraKeysWithSiteLocales(?array $mcamaraKeys): array
    {
        $keys = [];

        if (is_array($mcamaraKeys)) {
            foreach ($mcamaraKeys as $key) {
                $key = trim((string) $key);
                if ($key !== '') {
                    $keys[] = $key;
                }
            }
        }

        foreach (getLocales() as $locale) {
            $keys[] = (string) $locale;
        }

        return self::sortedLongestFirst(array_values(array_unique(array_filter(array_map('strval', $keys)))));
    }

    /**
     * @param list<string> $locales
     * @return list<string>
     */
    private static function sortedLongestFirst(array $locales): array
    {
        usort($locales, fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        return $locales;
    }
}
