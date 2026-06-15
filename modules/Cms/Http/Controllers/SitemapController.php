<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Requests\SitemapItemUpsertRequest;
use Modules\Cms\Http\Requests\SitemapRequest;
use Modules\Cms\Repositories\SitemapRepository;
use Unusualify\Modularous\Http\Controllers\BaseController;

/**
 * Modül Inertia index; özel arayüz {@see Sitemap/Index.vue}. JSON: {@see CmsSitemapPanelController} (dry-run, commit, item upsert).
 */
class SitemapController extends BaseController
{
    protected $moduleName = 'Cms';

    protected $routeName = 'Sitemap';

    /**
     * @param Request $request
     */
    public function indexData($request): array
    {
        $repo = $this->getRepository();
        if (! $repo instanceof SitemapRepository) {
            return [];
        }
        $prefix = $this->module->panelRouteNamePrefix() . '.';

        $breadcrumbs = [
            [
                'title' => __('CMS'),
            ],
            [
                'title' => __('Sitemap'),
            ],
        ];

        return [
            'tableAttributes' => [
                'sitemapPanel' => [
                    'rows' => $repo->getSitemapItemRowsForPanel(),
                    'publicSitemapUrl' => Route::has('cms.sitemap') ? route('cms.sitemap') : null,
                ],
                'breadcrumbs' => $breadcrumbs,
            ],
            'endpoints' => array_filter([
                'sitemapDryRun' => route($prefix . 'sitemap.dryRun.web'),
                'sitemapCommit' => route($prefix . 'sitemap.commit.web'),
                'sitemapItemUpsert' => route($prefix . 'sitemap.item.upsert.web'),
            ]),
        ];
    }

    public function dryRun(SitemapRequest $request): JsonResponse
    {
        $repo = $this->resolveSitemapRepository();

        return response()->json($repo->getPanelDryRunPayload());
    }

    public function commit(SitemapRequest $request): JsonResponse
    {
        $repo = $this->resolveSitemapRepository();

        return response()->json($repo->commitSitemapToLiveCache());
    }

    public function upsertItem(SitemapItemUpsertRequest $request): JsonResponse
    {
        $repo = $this->resolveSitemapRepository();
        $v = $request->validated();

        return response()->json($repo->upsertSitemapableItem(
            (string) $v['sitemapable_type'],
            (int) $v['sitemapable_id'],
            (string) $v['changefreq'],
            (string) $v['priority'],
        ));
    }

    private function resolveSitemapRepository(): SitemapRepository
    {
        $repo = $this->getRepository();
        if (! $repo instanceof SitemapRepository) {
            abort(500, 'SitemapRepository is not bound for the Sitemap route.');
        }

        return $repo;
    }
}
