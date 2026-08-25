<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;

/**
 * Shared view data for public CMS presentation (live requests and {@see presentationItem} warmup).
 */
final class CmsPublicPresentationInnerData
{
    /**
     * @return array{
     *   item: Model,
     *   seoTitle: string,
     *   seoDescription: ?string,
     *   canonicalUrl: string,
     *   robotsMeta: string,
     *   ogImage: ?string,
     *   twitterImage: ?string,
     *   hreflangAlternates: list<array{hreflang: string, href: string}>,
     *   pageSlug: string,
     *   pageFallbackSlug: string,
     *   pageSlugs: array<string, string>
     * }
     */
    public static function build(
        Request $request,
        Model $item,
        CanonicalUrlResolverInterface $canonical,
        bool $forcePreviewRobotsNoIndex = false,
    ): array {
        $seo = CmsPublicSeo::build($request, $item, $canonical);
        $seo['robotsMeta'] = CmsPublicSeo::resolveRobotsMeta($seo['robotsMeta'], $forcePreviewRobotsNoIndex);

        return self::fromSeo($item, $seo);
    }

    /**
     * Cache / warmup entry point — explicit locale and UrlRoute registry path; no {@see Request}.
     *
     * @return array{
     *   item: Model,
     *   seoTitle: string,
     *   seoDescription: ?string,
     *   canonicalUrl: string,
     *   robotsMeta: string,
     *   ogImage: ?string,
     *   twitterImage: ?string,
     *   hreflangAlternates: list<array{hreflang: string, href: string}>,
     *   pageSlug: string,
     *   pageFallbackSlug: string,
     *   pageSlugs: array<string, string>
     * }
     */
    public static function buildForCache(
        string $locale,
        string $registryPath,
        Model $item,
        CanonicalUrlResolverInterface $canonical,
    ): array {
        $seo = CmsPublicSeo::buildForCache($locale, $registryPath, $item, $canonical);

        return self::fromSeo($item, $seo);
    }

    /**
     * @param array{title: string, seoTitle?: mixed, description: ?string, canonicalUrl: string, robotsMeta: string, ogImage?: ?string, twitterImage?: ?string} $seo
     * @return array{
     *   item: Model,
     *   seoTitle: string,
     *   seoDescription: ?string,
     *   canonicalUrl: string,
     *   robotsMeta: string,
     *   ogImage: ?string,
     *   twitterImage: ?string,
     *   hreflangAlternates: list<array{hreflang: string, href: string}>,
     *   pageSlug: string,
     *   pageFallbackSlug: string,
     *   pageSlugs: array<string, string>
     * }
     */
    private static function fromSeo(Model $item, array $seo): array
    {
        $slugLeaves = CmsPublicPageSlugLeaves::forItem($item);

        return [
            'item' => $item,
            'title' => $seo['title'],
            'seoTitle' => $seo['seoTitle'],
            'seoDescription' => $seo['description'],
            'canonicalUrl' => $seo['canonicalUrl'],
            'robotsMeta' => $seo['robotsMeta'],
            'ogImage' => $seo['ogImage'] ?? null,
            'twitterImage' => $seo['twitterImage'] ?? $seo['ogImage'] ?? null,
            'hreflangAlternates' => self::hreflangAlternates($item),
            'pageSlug' => $slugLeaves['pageSlug'],
            'pageFallbackSlug' => $slugLeaves['pageFallbackSlug'],
            'pageSlugs' => $slugLeaves['pageSlugs'],
        ];
    }

    /**
     * Head {@code link rel="alternate" hreflang} rows (x-default + available locales).
     *
     * @return list<array{hreflang: string, href: string}>
     */
    public static function hreflangAlternates(Model $item): array
    {
        if (! method_exists($item, 'localizedUrlAlternates')) {
            return [];
        }

        /** @var list<array{locale?: string, url?: string, available?: bool, is_fallback?: bool}> $rows */
        $rows = $item->localizedUrlAlternates();
        if (! is_array($rows) || $rows === []) {
            return [];
        }

        $alternates = [];
        $xDefaultHref = null;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            if (($row['is_fallback'] ?? false) === true) {
                continue;
            }

            if (($row['available'] ?? true) !== true) {
                continue;
            }

            $locale = trim((string) ($row['locale'] ?? ''));
            $href = trim((string) ($row['url'] ?? ''));
            if ($locale === '' || $href === '' || $href === '#') {
                continue;
            }

            $alternates[] = [
                'hreflang' => $locale,
                'href' => $href,
            ];

            if ($locale === 'en' || $xDefaultHref === null) {
                $xDefaultHref = $href;
            }
        }

        if ($alternates === [] || $xDefaultHref === null) {
            return [];
        }

        array_unshift($alternates, [
            'hreflang' => 'x-default',
            'href' => $xDefaultHref,
        ]);

        return $alternates;
    }
}
