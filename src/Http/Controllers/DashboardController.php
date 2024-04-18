<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
// use Modules\Package\Repositories\PackageContinentRepository;
// use Modules\PressRelease\Entities\PressRelease;
// use Modules\PressRelease\Repositories\PressReleaseRepository;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Traits\ManageUtilities;

class DashboardController extends BaseController
{
    use ManageUtilities;
    /**
     * @var string
     */
    protected $moduleName = 'Dashboard';

    /**
     * @var string
     */
    protected $routeName = 'Dashboard';

    public function __construct(\Illuminate\Foundation\Application $app,Request $request)
    {
        parent::__construct(
            $app,
            $request
        );

        $this->removeMiddleware("can:{$this->permissionPrefix()}_". Permission::VIEW->value);
        $this->middleware('can:dashboard', ['only' => ['index']]);
    }

    public function index($parentId = null)
    {
        // $parentId = $this->getParentId() ?? $parentId;

        // $data = $this->getIndexData($this->nested ? [
        //     $this->getParentModuleForeignKey() => $parentId,
        // ] : []);

        // App::make($this->getRepositoryClass($this->modelName));

        // dd(
        //     get_class_methods( App::make(PressReleaseRepository::class)->get() ),
        //     App::make(PressReleaseRepository::class)
        //         ->get()
        //         ->toArray(),
        //     App::make(PressReleaseRepository::class)
        //         ->get()
        //         ->items()
        //     // $this->getIndexItems([])

        // );
            $blocks = app()->config->get( unusualBaseKey().'.ui_settings.dashboard');

        $options = [
            'endpoints' => $this->getUrls()
        ];
        // dd($data['blocks']);
        $view = "$this->baseKey::layouts.dashboard";

        return View::make($view, $blocks + $options);
    }
}
