<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\ModuleRouteInspect;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspector;

/**
 * Inertia shell for the Module Route Inspect admin panel.
 */
class ModuleRouteInspectToolController extends BaseController
{
    protected $moduleName = 'Dashboard';

    protected $routeName = 'Dashboard';

    protected $setDefaultPermissions = false;

    public function __construct(
        Application $app,
        Request $request,
        private readonly ModuleRouteInspector $inspector,
    ) {
        parent::__construct($app, $request);
    }

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $enabled = modularousConfig('module_route_inspect.enabled', false);
        $canAccess = $enabled && $this->inspector->userCanAccess($user);

        $pageTitle = __('Module Route Inspect') . ' - ' . Modularous::pageTitle();
        $headerTitle = __('Module Route Inspect');

        $data = [
            'pageTitle' => $pageTitle,
            'headerTitle' => $headerTitle,
            '_mainConfiguration' => [
                'navigation' => $this->navigationWithBreadcrumbs(),
            ],
        ];

        $this->shareInertiaStoreVariables();

        return Inertia::render('ModuleRouteInspect', [
            'inspectDisabled' => ! $canAccess,
            'inspectEndpoints' => $canAccess
                ? $this->inspector->panelEndpoints()
                : $this->inspector->emptyPanelEndpoints(),
            'allowStatusToggle' => $canAccess && $this->inspector->canToggleStatus(),
            'allowHeal' => $canAccess && $this->inspector->canHeal(),
            'highlightFeatureKeys' => $this->inspector->highlightFeatureKeys(),
            'featureKeys' => $this->inspector->featureKeys(),
            'isSuperadmin' => $this->inspector->isSuperadmin($user),
            'endpoints' => new \stdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function navigationWithBreadcrumbs(): array
    {
        $navigation = get_modularous_navigation_config();

        $dashboardRoute = Modularous::getAdminRouteNamePrefix() . '.dashboard';
        $dashboardCrumb = [
            'title' => __('Dashboard'),
            'disabled' => true,
        ];

        if (Route::has($dashboardRoute)) {
            $dashboardCrumb['href'] = route($dashboardRoute);
            $dashboardCrumb['disabled'] = false;
        }

        $navigation['breadcrumbs'] = [
            $dashboardCrumb,
            [
                'title' => __('Module Route Inspect'),
                'disabled' => true,
            ],
        ];

        return $navigation;
    }
}
