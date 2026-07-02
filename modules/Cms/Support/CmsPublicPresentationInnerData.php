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
     * @return array{item: Model, seoTitle: string, seoDescription: ?string, canonicalUrl: string, robotsMeta: string}
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
     * @return array{item: Model, seoTitle: string, seoDescription: ?string, canonicalUrl: string, robotsMeta: string}
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
     * @param array{title: string, description: ?string, canonicalUrl: string, robotsMeta: string} $seo
     * @return array{item: Model, seoTitle: string, seoDescription: ?string, canonicalUrl: string, robotsMeta: string}
     */
    private static function fromSeo(Model $item, array $seo): array
    {
        return [
            'item' => $item,
            'seoTitle' => $seo['title'],
            'seoDescription' => $seo['description'],
            'canonicalUrl' => $seo['canonicalUrl'],
            'robotsMeta' => $seo['robotsMeta'],
        ];
    }
}
