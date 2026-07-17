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
 * Inertia shell for CMS site-wide SEO tools (page-level SEO; robots.txt lives in System Settings).
 */
class SiteSeoToolController extends BaseController
{
    protected $moduleName = 'Cms';

    protected $routeName = 'Page';

    public function __construct(Application $app, Request $request)
    {
        parent::__construct($app, $request);
    }

    public function __invoke(): Response
    {
        $pageTitle = __('Site SEO') . ' - ' . Modularous::pageTitle();
        $headerTitle = __('Site SEO');

        $data = [
            'pageTitle' => $pageTitle,
            'headerTitle' => $headerTitle,
            '_mainConfiguration' => [
                'navigation' => $this->siteSeoNavigationWithBreadcrumbs(),
            ],
        ];

        $this->shareInertiaStoreVariables();

        $systemSettingsUrl = $this->resolveSystemSettingsUrl();

        return Inertia::render('SiteSeo', [
            'systemSettingsUrl' => $systemSettingsUrl,
            'endpoints' => new \stdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }

    protected function resolveSystemSettingsUrl(): ?string
    {
        $candidates = [
            systemRouteNamePrefix() . '.systemsetting.general.index',
            modularousConfig('admin_route_name_prefix', 'admin') . '.system.systemsetting.general.index',
        ];

        foreach ($candidates as $routeName) {
            if (Route::has($routeName)) {
                return route($routeName);
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function siteSeoNavigationWithBreadcrumbs(): array
    {
        $navigation = get_modularous_navigation_config();

        $pageIndexRoute = $this->module->panelRouteNamePrefix() . '.page.index';
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
                'title' => __('Site SEO'),
                'disabled' => true,
            ],
        ];

        return $navigation;
    }
}
