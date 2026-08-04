<?php

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\Translations\PageTranslation;
use Unusualify\Modularous\Entities\Traits\HasTranslation;

/**
 * Head tags for public CMS pages: canonical URL, robots, title/description from translation.
 */
final class CmsPublicSeo
{
    public const ROBOTS_NOINDEX_NOFOLLOW = 'noindex, nofollow';

    public const ROBOTS_INDEX_FOLLOW = 'index, follow';

    public const ROBOTS_TXT_STAGING_DISALLOW_ALL = "User-agent: *\nDisallow: /";

    /**
     * Live request path — uses {@see Request} for host, path, and application locale.
     *
     * @param object|null $translation e.g. {@see PageTranslation}
     * @return array{title: string, description: ?string, canonicalUrl: string, robotsMeta: string}
     */
    public static function build(Request $request, Model $item, CanonicalUrlResolverInterface $canonical): array
    {
        return self::buildSeo(
            app()->getLocale(),
            $item,
            self::resolveCanonicalFromRequest($request, $item, $canonical),
        );
    }

    /**
     * Cache / warmup path — explicit locale and registry path; no {@see Request} or app locale.
     *
     * @return array{title: string, description: ?string, canonicalUrl: string, robotsMeta: string}
     */
    public static function buildForCache(
        string $locale,
        string $registryPath,
        Model $item,
        CanonicalUrlResolverInterface $canonical,
    ): array {
        $browserPath = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath($locale, $registryPath, $canonical);

        return self::buildSeo(
            $locale,
            $item,
            self::resolveCanonicalForCache($locale, $browserPath, $item, $canonical),
        );
    }

    /**
     * @return array{title: string, description: ?string, canonicalUrl: string, robotsMeta: string}
     */
    private static function buildSeo(string $locale, Model $item, string $canonicalUrl): array
    {
        $isTranslatable = @classHasTrait($item, HasTranslation::class);
        $translation = $isTranslatable ? $item->translate($locale) : $item;
        $seoTitle = optional($translation)->seo_title ?? null;
        $title = optional($translation)->title ?? null;

        // #TODO: add default title and description for the page if not set
        if (is_array($seoTitle) && array_key_exists($locale, $seoTitle)) {
            $seoTitle = (string) $seoTitle[$locale] ?? $item->title ?? 'Page';
        }

        if (is_array($title) && array_key_exists($locale, $title)) {
            $title = (string) (optional($translation)->title ?? 'Page');
        }

        if ($title == null) {
            $title = $seoTitle;
        }

        $description = optional($translation)->seo_description;
        $description = $description !== null && $description !== '' ? $description : null;
        if (is_array($description) && array_key_exists($locale, $description)) {
            $description = (string) $description[$locale] ?? null;
        }

        $robotsIndex = $isTranslatable ? optional($translation)->robots_index : $item->robots_index;
        $robotsFollow = $isTranslatable ? optional($translation)->robots_follow : $item->robots_follow;
        if (is_array($robotsIndex) && array_key_exists($locale, $robotsIndex)) {
            $robotsIndex = $robotsIndex[$locale] ?? null;
        }
        if (is_array($robotsFollow) && array_key_exists($locale, $robotsFollow)) {
            $robotsFollow = $robotsFollow[$locale] ?? null;
        }

        // Legacy blog posts store indexability on the model (`seo_index`) rather than
        // HasTranslatableMetadata robots_index — fall back when robots_index is unset.
        if ($robotsIndex === null && array_key_exists('seo_index', $item->getAttributes())) {
            $robotsIndex = (bool) $item->getAttribute('seo_index');
        }

        $robotsMeta = self::resolveRobotsMeta(self::robotsDirective($robotsIndex, $robotsFollow));

        return [
            'title' => $title,
            'seoTitle' => $seoTitle,
            'description' => $description,
            'canonicalUrl' => $canonicalUrl,
            'robotsMeta' => $robotsMeta,
        ];
    }

    public static function shouldForceNoIndex(): bool
    {
        return (bool) modularousConfig('cms_seo.staging.force_noindex', false);
    }

    public static function defaultRobotsMeta(): string
    {
        return self::shouldForceNoIndex()
            ? self::ROBOTS_NOINDEX_NOFOLLOW
            : self::ROBOTS_INDEX_FOLLOW;
    }

    public static function resolveRobotsMeta(string $pageRobotsMeta, bool $forcePreviewRobotsNoIndex = false): string
    {
        if (self::shouldForceNoIndex() || $forcePreviewRobotsNoIndex) {
            return self::ROBOTS_NOINDEX_NOFOLLOW;
        }

        return $pageRobotsMeta;
    }

    /**
     * When staging {@see shouldForceNoIndex()} is on, overrides DB/env robots.txt with {@see ROBOTS_TXT_STAGING_DISALLOW_ALL}.
     */
    public static function resolvedStagingRobotsTxtBody(): ?string
    {
        if (! self::shouldForceNoIndex()) {
            return null;
        }

        return self::ROBOTS_TXT_STAGING_DISALLOW_ALL . "\n";
    }

    private static function resolveCanonicalFromRequest(
        Request $request,
        Model $item,
        CanonicalUrlResolverInterface $canonical,
    ): string {
        return self::resolveCanonical(
            app()->getLocale(),
            $request->getHost(),
            $request->getPathInfo() ?: '/',
            $request->getSchemeAndHttpHost(),
            $request->url(),
            $item,
            $canonical,
        );
    }

    private static function resolveCanonicalForCache(
        string $locale,
        string $pathInfo,
        Model $item,
        CanonicalUrlResolverInterface $canonical,
    ): string {
        $schemeAndHttpHost = self::schemeAndHttpHostFromAppUrl();
        $host = parse_url($schemeAndHttpHost, PHP_URL_HOST) ?: '';

        return self::resolveCanonical(
            $locale,
            $host,
            $pathInfo ?: '/',
            $schemeAndHttpHost,
            rtrim($schemeAndHttpHost, '/') . ($pathInfo ?: '/'),
            $item,
            $canonical,
        );
    }

    private static function resolveCanonical(
        string $locale,
        string $host,
        string $pathInfo,
        string $schemeAndHttpHost,
        string $fallbackUrl,
        Model $item,
        CanonicalUrlResolverInterface $canonical,
    ): string {
        $isTranslatable = @classHasTrait($item, HasTranslation::class);
        $translation = $isTranslatable ? $item->translate($locale) : $item;
        $custom = $isTranslatable ? trim((string) (optional($translation)->canonical_url ?? '')) : '';
        if (is_array($custom) && isset($custom[$locale])) {
            $custom = (string) $custom[$locale];
        } else {
            $custom = (string) $custom;
        }

        if ($custom !== '') {
            if (preg_match('#^https?://#i', $custom)) {
                return $custom;
            }

            return rtrim($schemeAndHttpHost, '/') . '/' . ltrim($custom, '/');
        }

        $scheme = parse_url($schemeAndHttpHost, PHP_URL_SCHEME) ?: 'http';

        $resolved = $canonical->resolve(
            $host,
            $pathInfo ?: '/',
            $locale,
            [
                'redirect_to_canonical' => false,
                'scheme' => $scheme,
            ]
        );

        return $resolved['canonical_url'] ?? $fallbackUrl;
    }

    private static function schemeAndHttpHostFromAppUrl(): string
    {
        $appUrl = (string) config('app.url', 'http://localhost');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
        $host = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';

        return $scheme . '://' . $host;
    }

    /**
     * Laravel validation often omits unchecked bools; treat null as "allow" (index/follow).
     */
    private static function robotsDirective(mixed $index, mixed $follow): string
    {
        $noIndex = $index === false;
        $noFollow = $follow === false;

        $indexPart = $noIndex ? 'noindex' : 'index';
        $followPart = $noFollow ? 'nofollow' : 'follow';

        return $indexPart . ', ' . $followPart;
    }
}
