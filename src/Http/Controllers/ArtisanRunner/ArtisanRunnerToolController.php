<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\ArtisanRunner;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Services\ArtisanRunner\Contracts\ArtisanRunnerInterface;

/**
 * Inertia shell for the panel Artisan Runner tool.
 */
class ArtisanRunnerToolController extends BaseController
{
    protected $moduleName = 'Dashboard';

    protected $routeName = 'Dashboard';

    protected $setDefaultPermissions = false;

    public function __construct(
        Application $app,
        Request $request,
        private readonly ArtisanRunnerInterface $artisanRunner,
    ) {
        parent::__construct($app, $request);
    }

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $enabled = modularousConfig('artisan_runner.enabled', false);
        $canAccess = $enabled && $this->artisanRunner->userCanAccess($user);

        $pageTitle = __('Artisan Runner') . ' - ' . Modularous::pageTitle();
        $headerTitle = __('Artisan Runner');

        $data = [
            'pageTitle' => $pageTitle,
            'headerTitle' => $headerTitle,
            '_mainConfiguration' => [
                'navigation' => $this->navigationWithBreadcrumbs(),
            ],
        ];

        $this->shareInertiaStoreVariables();

        return Inertia::render('ArtisanRunner', [
            'runnerDisabled' => ! $canAccess,
            'runnerEndpoints' => $canAccess
                ? $this->artisanRunner->panelEndpoints()
                : $this->artisanRunner->emptyPanelEndpoints(),
            'isSuperadmin' => (bool) ($user->is_superadmin ?? false),
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
                'title' => __('Artisan Runner'),
                'disabled' => true,
            ],
        ];

        return $navigation;
    }
}
