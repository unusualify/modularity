<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Traits\Allowable;
use Unusualify\Modularity\View\Component;

class DashboardController extends BaseController
{
    use ManageUtilities, Allowable;

    /**
     * @var string
     */
    protected $moduleName = 'Dashboard';

    /**
     * @var string
     */
    protected $routeName = 'Dashboard';

    public function __construct(\Illuminate\Foundation\Application $app, Request $request)
    {
        parent::__construct(
            $app,
            $request
        );

        $this->removeMiddleware("can:{$this->permissionPrefix()}_" . Permission::VIEW->value);
        $this->middleware('can:dashboard', ['only' => ['index']]);
    }

    public function index($parentId = null)
    {
        $blockItems = $this->app->config->get(modularityBaseKey() . '.ui_settings.dashboard.blocks');

        foreach ($blockItems as $index => $blockItem) {
            if ($this->isAllowedItem($blockItem, 'allowedRoles')) {
                $blockItems[$index] = Component::create($blockItem);
            }
        }

        $endpoints = $this->getUrls();
        $pageTitle = __('Dashboard') . ' - ' . \Unusualify\Modularity\Facades\Modularity::pageTitle();
        $headerTitle = __('Dashboard');

        if ($this->shouldUseInertia()) {
            return $this->renderInertiaDashboard(compact('blockItems', 'endpoints', 'pageTitle', 'headerTitle'));
        }

        return View::make("$this->baseKey::layouts.dashboard", compact('blockItems', 'endpoints', 'pageTitle', 'headerTitle'));
    }

    /**
     * Render dashboard with Inertia
     */
    protected function renderInertiaDashboard(array $data)
    {
        $this->shareInertiaStoreVariables();

        return \Inertia\Inertia::render('Dashboard', [
            'blockItems' => $data['blockItems'] ?? [],
            'endpoints' => $data['endpoints'] ?? new \StdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }
}
