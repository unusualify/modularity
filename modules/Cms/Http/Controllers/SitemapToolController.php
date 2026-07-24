<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\BaseController;

/**
 * @deprecated Prefer {@see SitemapController} and {@code modules/Cms/Resources/assets/Pages/Sitemap/Index.vue} (module Inertia index with row table; same panel JSON endpoints).
 * Panel: sitemap dry-run (preview XML) and commit to cache. Legacy standalone Inertia page.
 */
class SitemapToolController extends BaseController
{
    protected $moduleName = 'Cms';

    protected $routeName = 'Sitemap';

    public function __construct(Application $app, Request $request)
    {
        parent::__construct($app, $request);
    }

    public function __invoke(Request $request): Response
    {
        $pageTitle = __('Sitemap') . ' - ' . Modularous::pageTitle();
        $headerTitle = __('Sitemap');

        $data = [
            'pageTitle' => $pageTitle,
            'headerTitle' => $headerTitle,
            '_mainConfiguration' => [
                'navigation' => $this->sitemapNavigationWithBreadcrumbs(),
            ],
        ];

        $this->shareInertiaStoreVariables();

        $prefix = $this->module->panelRouteNamePrefix();

        $publicSitemap = Route::has('cms.sitemap') ? route('cms.sitemap') : null;

        return Inertia::render('Sitemap', [
            'sitemapEndpoints' => [
                'dryRun' => route($prefix . 'sitemap.dryRun.web'),
                'commit' => route($prefix . 'sitemap.commit.web'),
            ],
            'publicSitemapUrl' => $publicSitemap,
            'endpoints' => new \stdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function sitemapNavigationWithBreadcrumbs(): array
    {
        $navigation = get_modularous_navigation_config();

        $pageIndexRoute = $this->module->panelRouteNamePrefix() . 'page.index';
        $cmsCrumb = [
            'title' => __('CMS'),
            'disabled' => true,
        ];
        if (Route::has($pageIndexRoute)) {
            $cmsCrumb['href'] = route($pageIndexRoute);
            $cmsCrumb['disabled'] = false;
        }

        $navigation['breadcrumbs'] = [
            $cmsCrumb,
            [
                'title' => __('Sitemap'),
                'disabled' => true,
            ],
        ];

        return $navigation;
    }
}
