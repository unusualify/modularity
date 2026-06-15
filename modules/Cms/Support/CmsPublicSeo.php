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
     * @param object|null $translation e.g. {@see PageTranslation}
     * @return array{title: string, description: ?string, canonicalUrl: string, robotsMeta: string}
     */
    public static function build(Request $request, Model $item, CanonicalUrlResolverInterface $canonical): array
    {
        $isTranslatable = @classHasTrait($item, HasTranslation::class);
        $locale = app()->getLocale();
        $title = (optional($isTranslatable ? $item->translate() : $item)->seo_title ?? null);

        // #TODO: add default title and description for the page if not set
        if (is_array($title) && array_key_exists($locale, $title)) {
            $title = (string) $title[$locale] ?? $item->title ?? 'Page';
        } elseif ($title !== null) {
            $title = (string) (optional($item->translate())->title
                ?? 'Page');
        }

        $description = optional($isTranslatable ? $item->translate() : $item)->seo_description;
        $description = $description !== null && $description !== '' ? $description : null;
        if (is_array($description) && array_key_exists($locale, $description)) {
            $description = (string) $description[$locale] ?? null;
        }

        $canonicalUrl = self::resolveCanonical($request, $item, $canonical);

        $robotsIndex = $isTranslatable ? optional($item->translate())->robots_index : $item->robots_index;
        $robotsFollow = $isTranslatable ? optional($item->translate())->robots_follow : $item->robots_follow;
        if (is_array($robotsIndex) && array_key_exists($locale, $robotsIndex)) {
            $robotsIndex = (string) $robotsIndex[$locale] ?? null;
        }
        if (is_array($robotsFollow) && array_key_exists($locale, $robotsFollow)) {
            $robotsFollow = (string) $robotsFollow[$locale] ?? null;
        }
        $robotsMeta = self::resolveRobotsMeta(self::robotsDirective($robotsIndex, $robotsFollow));

        return [
            'title' => $title,
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

    private static function resolveCanonical(Request $request, Model $item, CanonicalUrlResolverInterface $canonical): string
    {
        $isTranslatable = @classHasTrait($item, HasTranslation::class);
        $locale = app()->getLocale();
        $custom = $isTranslatable ? trim((string) ($item->translate()->canonical_url ?? '')) : '';
        if (is_array($custom) && isset($custom[$locale])) {
            $custom = (string) $custom[$locale];
        } else {
            $custom = (string) $custom;
        }

        if ($custom !== '') {
            if (preg_match('#^https?://#i', $custom)) {
                return $custom;
            }

            return rtrim($request->getSchemeAndHttpHost(), '/') . '/' . ltrim($custom, '/');
        }

        $resolved = $canonical->resolve(
            $request->getHost(),
            $request->getPathInfo() ?: '/',
            app()->getLocale(),
            ['redirect_to_canonical' => false]
        );

        return $resolved['canonical_url'] ?? $request->url();
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
