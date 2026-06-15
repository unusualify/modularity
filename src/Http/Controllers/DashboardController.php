<?php

namespace Unusualify\Modularous\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularous\Traits\Allowable;
use Unusualify\Modularous\View\Component;

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

    public function __construct(Application $app, Request $request)
    {
        parent::__construct(
            $app,
            $request
        );

        $this->middleware('can:dashboard', ['only' => ['index']]);
    }

    public function index($parentId = null)
    {
        $blockItems = $this->app->config->get(modularousBaseKey() . '.ui_settings.dashboard.blocks');

        foreach ($blockItems as $index => $blockItem) {
            if ($this->isAllowedItem($blockItem, 'allowedRoles')) {
                $blockItems[$index] = Component::create($blockItem);
            }
        }

        $endpoints = $this->getUrls();
        $pageTitle = __('Dashboard') . ' - ' . Modularous::pageTitle();
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

        return Inertia::render('Dashboard', [
            'blockItems' => $data['blockItems'] ?? [],
            'endpoints' => $data['endpoints'] ?? new \StdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }
}
