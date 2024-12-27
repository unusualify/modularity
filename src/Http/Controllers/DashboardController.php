<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
// use Modules\Package\Repositories\PackageContinentRepository;
// use Modules\PressRelease\Entities\PressRelease;
// use Modules\PressRelease\Repositories\PressReleaseRepository;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Services\View\UWidget;
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
        $blocks = app()->config->get(unusualBaseKey() . '.ui_settings.dashboard.blocks');

        // dd('here');

        foreach ($blocks as $index => $block) {
            switch ($block['component']) {
                case 'ue-table':

                    $widget = new UWidget;
                    $widget = $widget->makeComponent($block['tag'], $block)->render();


                    $block = $widget;

                    $block['elements'][0]['attributes']['endpoints'] = array_merge($this->transformRoutes($block['elements'][0]['attributes']['module']->getRouteUris($block['elements'][0]['attributes']['route'])), $this->getUrls());
                    // $block['elements'][0]['attributes']['endpoints']['index'] = 'test';

                    $block['elements'][0]['attributes']['tableOptions']['search'] = null;

                    // unset($block['elements'][0]['attributes']['endpoints']['index']); //Enable vuetify table store

                    // dd($block);
                    $blocks[$index] = $block;

                    // dd($block);
                    break;
                case 'ue-board-information-plus':
                    $cards = $block['cards'] ?? [];

                    try {
                        $widget = new UWidget;
                        $widget = $widget->makeComponent($block['tag'], $block)->render();

                    } catch (\Throwable $th) {
                        $widget = [];
                        dd($th);
                    }

                    $cards = $widget;
                    // }
                    $blocks[$index] = $cards;

                    // dd($blocks);
                    break;
                default:
                    break;
            }

        }
        // dd($blocks);
        $options = ['blocks' => $blocks];
        // dd($blocks);
        // dd($data['blocks']);
        $view = "$this->baseKey::layouts.dashboard";

        $options['endpoints'] = $this->getUrls();

        return View::make($view, $options);
    }

    protected function transformRoutes($routes)
    {
        $result = [];
        foreach ($routes as $key => $value) {
            // Skip if value is an array
            if (is_array($value)) {
                continue;
            }

            // Transform the key - remove everything before last dot
            $newKey = preg_replace('/^.*\.([^.]+)$/', '$1', $key);

            // Transform the value - replace {something} with :id
            $newValue = preg_replace('/\{[^}]+\}/', ':id', $value);

            // Add base url to the path using Laravel's url helper
            $newValue = url($newValue);

            $result[$newKey] = $newValue;
        }

        return $result;
    }

    // Usage example:
}
