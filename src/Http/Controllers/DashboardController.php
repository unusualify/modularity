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
        $blocks = $this->app->config->get(modularityBaseKey() . '.ui_settings.dashboard.blocks');

        foreach ($blocks as $index => $block) {
            if($this->isAllowedItem($block, 'allowedRoles')){
                $blocks[$index] = Component::create($block);
            }
        }

        $endpoints = $this->getUrls();

        return View::make("$this->baseKey::layouts.dashboard", compact('blocks', 'endpoints'));
    }
}
