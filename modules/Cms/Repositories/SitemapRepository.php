<?php

namespace Modules\Cms\Repositories;

use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Entities\CmsSitemapableItem;
use Modules\Cms\Entities\Sitemap;
use Modules\Cms\Services\CmsSitemapBuildService;
use Modules\Cms\Services\CmsSitemapCacheService;
use Modules\Cms\Services\CmsSitemapXsltService;
use Unusualify\Modularous\Repositories\Repository;

/**
 * CMS sitemap: panel dry-run / commit, backed by {@see CmsSitemapBuildService} + {@see CmsSitemapCacheService}.
 * {@see Sitemap} row(s) in DB hold optional per-urlable override rows ({@see CmsSitemapableItem}).
 */
class SitemapRepository extends Repository
{
    public function __construct(
        Sitemap $model,
        protected CmsSitemapBuildService $sitemapBuild,
        protected CmsSitemapCacheService $sitemapCache,
        protected CmsSitemapXsltService $sitemapXslt,
    ) {
        $this->model = $model;
    }

    /**
     * @return array{ok: true, xml: string, html: string|null, xsl: string|null, urlCount: int, bytes: int}
     */
    public function getPanelDryRunPayload(): array
    {
        $xml = $this->sitemapBuild->buildXml();
        $dtos = $this->sitemapBuild->buildEntryDtos();
        $urlCount = is_countable($dtos) ? count($dtos) : 0;

        return [
            'ok' => true,
            'xml' => $xml,
            'html' => $this->sitemapXslt->transformXmlToHtml($xml),
            // Browser-side XSLT fallback when PHP ext-xsl is unavailable (common on some FPM builds).
            'xsl' => $this->sitemapXslt->stylesheetContents(),
            'urlCount' => $urlCount,
            'bytes' => mb_strlen($xml),
        ];
    }

    /**
     * @return array{ok: true, message: string, xml: string, urlCount: int, bytes: int}
     */
    public function commitSitemapToLiveCache(): array
    {
        $xml = $this->sitemapBuild->buildXml();
        $this->sitemapCache->commit($xml);
        $dtos = $this->sitemapBuild->buildEntryDtos();
        $urlCount = is_countable($dtos) ? count($dtos) : 0;

        return [
            'ok' => true,
            'message' => __('Sitemap cache updated.'),
            'xml' => $xml,
            'urlCount' => $urlCount,
            'bytes' => mb_strlen($xml),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSitemapItemRowsForPanel(): array
    {
        return $this->sitemapBuild->getPanelItemRows();
    }

    /**
     * Create or update a per–urlable override for the default sitemap bucket.
     *
     * @return array{ok: true, item: array{id: int, changefreq: string, priority: string}}
     */
    public function upsertSitemapableItem(string $sitemapableType, int $sitemapableId, string $changefreq, string $priority): array
    {
        if (! is_a($sitemapableType, Model::class, true)) {
            abort(422, 'Invalid sitemapable type.');
        }

        $sitemapId = (int) modularousConfig('cms_sitemap.default_sitemap_id', 1);

        $row = CmsSitemapableItem::query()->updateOrCreate(
            [
                'sitemap_id' => $sitemapId,
                'sitemapable_type' => $sitemapableType,
                'sitemapable_id' => $sitemapableId,
            ],
            [
                'changefreq' => $changefreq,
                'priority' => (float) $priority,
            ],
        );

        $p = $row->priority !== null ? number_format((float) $row->priority, 1, '.', '') : (string) $priority;

        return [
            'ok' => true,
            'item' => [
                'id' => (int) $row->getKey(),
                'changefreq' => (string) $row->changefreq,
                'priority' => $p,
            ],
        ];
    }
}
