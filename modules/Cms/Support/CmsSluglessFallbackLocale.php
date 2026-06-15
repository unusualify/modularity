<?php

namespace Modules\Cms\Support;

use Modules\Cms\Http\Middleware\FallbackLocaleSluglessCanonicalMiddleware;

/**
 * Optional “slugless” public URLs for a single editorial fallback locale (e.g. {@code /pages/test}
 * mirrors {@code cms_routing.translatable_fallback_locale_then_default_locale} content; {@code /en/pages/test} redirects away).
 *
 * @see FallbackLocaleSluglessCanonicalMiddleware
 */
final class CmsSluglessFallbackLocale
{
    /**
     * When true, URLs without {@code /{locale}/} use the slugless fallback locale for registry resolution hints,
     * {@code /{fallback}/...} redirects to locale-stripped duplicates, and public browser paths omit the segment.
     */
    public static function enabled(): bool
    {
        return (bool) modularousConfig('cms_routing.fallback_locale_optional_path_segment', false);
    }

    /**
     * Locale code used as the single “prefix-optional” content locale (explicit override, else translatable fallback, else CMS default).
     */
    public static function resolvedCode(): string
    {
        $override = modularousConfig('cms_routing.fallback_locale_optional_path_segment_locale');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $trans = config('translatable.fallback_locale');
        if (is_string($trans) && $trans !== '') {
            return $trans;
        }

        return (string) modularousConfig('cms_routing.default_locale', config('app.locale'));
    }

    public static function sameLocale(string $localeA, string $localeB): bool
    {
        return mb_strtolower(trim($localeA)) === mb_strtolower(trim($localeB));
    }

    /** Preferred locale hint when resolving catch-all URLs that carry no locale segment — matches {@see resolvedCode()} only when slugless URLs are enabled. */
    public static function implicitPreferredLocaleOtherwise(string $cmsLocalizationDefault): string
    {
        if (self::enabled()) {
            return self::resolvedCode();
        }

        return $cmsLocalizationDefault;
    }

    /** When building browsable URLs for permalinks, omit {@code /{locale}/} for the slugless fallback locale only. */
    public static function shouldOmitLocaleSegmentFromPublicUrlsFor(string $locale): bool
    {
        return self::enabled() && self::sameLocale((string) $locale, self::resolvedCode());
    }

    /** HTTP redirect when stripping {@code /{slugless}/...}; defaults to permanent (301). */
    public static function explicitSegmentRedirectStatus(): int
    {
        $code = (int) modularousConfig('cms_routing.fallback_locale_explicit_segment_redirect_status', 301);
        if ($code < 300 || $code > 399) {
            return 301;
        }

        return $code;
    }
}
