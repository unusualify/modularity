<?php

use Modules\Cms\Entities\Sitemap;
use Modules\Cms\Http\Controllers\SitemapToolController;
use Modules\Cms\Services\CmsSitemapBuildService;
use Modules\Cms\Services\CmsSitemapCacheService;

return [
    /**
     * GET /sitemap.xml serves committed XML from cache ({@see CmsSitemapCacheService}).
     */
    'route_enabled' => env('MODULAROUS_CMS_SITEMAP_ROUTE_ENABLED', true),

    /**
     * Cache key (prefixed with Laravel's cache store prefix) for the committed sitemap body.
     */
    'cache_key' => env('MODULAROUS_CMS_SITEMAP_CACHE_KEY', 'modularous_cms_sitemap.committed_v1'),

    /**
     * When no committed build exists yet, optionally run a build synchronously on the first public request
     * (avoids an empty index). Default false: serve an empty but valid <urlset> until a rebuild/commit.
     */
    'build_on_cache_miss' => env('MODULAROUS_CMS_SITEMAP_BUILD_ON_MISS', false),

    /**
     * Default {@see Sitemap} row (migration seeds id = 1, slug = default).
     */
    'default_sitemap_id' => (int) env('MODULAROUS_CMS_SITEMAP_DEFAULT_ID', 1),

    /**
     * Default XML sitemap `changefreq` / `priority` when no per-model row in `cms_sitemapables`.
     * {@see CmsSitemapBuildService}
     */
    'defaults' => [
        'changefreq' => env('MODULAROUS_CMS_SITEMAP_DEFAULT_CHANGEFREQ', 'weekly'),
        'priority' => (float) env('MODULAROUS_CMS_SITEMAP_DEFAULT_PRIORITY', 0.5),
    ],

    /**
     * `lastmod` source: the owning model’s `updated_at` (per-locale sitemap line still uses the same stamp).
     * Translation-only updates should touch the base model in your save pipeline when you need that reflected.
     */
    'lastmod' => [
        'source' => 'model',
    ],

    /**
     * Panel tool ({@see SitemapToolController}): step-up ability for **commit** (dry-run is read-only).
     */
    'panel' => [
        'step_up_ability' => [
            'commit' => env('MODULAROUS_CMS_SITEMAP_PANEL_STEP_UP_COMMIT', 'sitemap.commit'),
        ],
    ],
];
