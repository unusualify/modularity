<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Cms\Repositories\PageRepository;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousRoutes;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Http\Controllers\Traits\ManageInertia;

/**
 * Inertia shell for CMS promotion dry-run / execute (POST targets session web routes, not api/v1).
 * Extends {@see BaseController} so {@see ManageInertia}
 * supplies the same mainConfiguration / headLayoutData as index screens (sidebar, navigation).
 */
class PromotionToolController extends BaseController
{
    protected $moduleName = 'Cms';

    /**
     * Reuse the Page route binding so {@see PageRepository} resolves and
     * BaseController preview/revision middleware checks do not run against a null repository.
     */
    protected $routeName = 'Page';

    public function __construct(Application $app, Request $request)
    {
        parent::__construct($app, $request);
    }

    public function __invoke(Request $request): Response
    {
        $enabled = modularousConfig('cms_promotion.enabled', false);

        $pageTitle = __('CMS promotion') . ' - ' . Modularous::pageTitle();
        $headerTitle = __('CMS promotion');

        $data = [
            'pageTitle' => $pageTitle,
            'headerTitle' => $headerTitle,
            '_mainConfiguration' => [
                'navigation' => $this->promotionNavigationWithBreadcrumbs(),
            ],
        ];

        $this->shareInertiaStoreVariables();

        return Inertia::render('Promotion', [
            'promotionDisabled' => ! $enabled,
            'promotionEndpoints' => $enabled ? $this->promotionSessionEndpoints() : ['dryRun' => '', 'execute' => ''],
            'defaultScope' => (array) modularousConfig('cms_promotion.scope', []),
            'endpoints' => new \stdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }

    /**
     * URLs for POST actions that run under {@see ModularousRoutes::webPanelMiddlewares()}
     * so the panel session authenticates the request (see modules/Cms/Routes/web.php).
     *
     * @return array{dryRun: string, execute: string}
     */
    protected function promotionSessionEndpoints(): array
    {
        $prefix = $this->module->panelRouteNamePrefix();

        return [
            'dryRun' => route($prefix . 'promotion.dryRun.web'),
            'execute' => route($prefix . 'promotion.execute.web'),
        ];
    }

    /**
     * Full navigation config with breadcrumbs for {@see get_modularous_inertia_main_configuration()}.
     *
     * @return array<string, mixed>
     */
    protected function promotionNavigationWithBreadcrumbs(): array
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
                'title' => __('CMS promotion'),
                'disabled' => true,
            ],
        ];

        return $navigation;
    }
}
