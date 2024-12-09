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
        // $parentId = $this->getParentId() ?? $parentId;

        // $data = $this->getIndexData($this->isNested ? [
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
        $blocks = app()->config->get(unusualBaseKey() . '.ui_settings.dashboard.blocks');

        // dd('here');

        foreach ($blocks as $index => $block) {
            switch ($block['component']) {
                case 'ue-table':
                    // $controller = App::make($block['controller'])->setTableAttributes(tableOptions: $block['attributes']['tableOptions'],
                    // );
                    // dd($block);
                    $widget = new UWidget;
                    $widget = $widget->makeComponent($block['tag'], $block)->render();

                    // dd(change_connector_event(get_connector_event($block['connector'])));
                    // $data = init_connector($block['connector']);
                    // dd($data);
                    // dd(
                    //     $controller->getIndexData()['initialResource']->resource['data'],
                    //     $data
                    // );
                    // $block['attributes']['items'] = $controller->getIndexData()['initialResource']->resource['data'];
                    // $block['attributes']['items'] = $data['items']->toArray();
                    $block = $widget;
                    // dd($block);
                    // dd($block['elements'][0]['attributes']['items'] = []);
                    // $block['elements'][0]['attributes']['items'] = [];
                    // dd($block);
                    // dd($block['elements']->attributes['endpoints']);
                    // dd($this->transformRoutes($widget['elements'][0]['module']->getRouteUris($widget['elements'][0]['route'])));

                    // dd($block['elements']->attributes['route']);
                    // dd($block['elements'][0]['attributes']);
                    $block['elements'][0]['attributes']['endpoints'] = array_merge($this->transformRoutes($block['elements'][0]['attributes']['module']->getRouteUris($block['elements'][0]['attributes']['route'])), $this->getUrls());
                    $block['elements'][0]['attributes']['tableOptions']['search'] = null;

                    unset($block['elements'][0]['attributes']['endpoints']['index']); //Disable vuetify table store
                    // dd($block);
                    // dd(array_merge($block['attributes']['endpoints'], $this->getUrls()));

                    // $block['attributes']['rowActions'] = $controller->getTableActions();
                    // in order to keep url as default home url
                    // dd($block);
                    $blocks[$index] = $block;

                    // dd($block);
                    break;
                case 'ue-board-information-plus':
                    $cards = $block['cards'] ?? [];
                    // dd($cards);
                    // dd($block);
                    // foreach ($cards as $key => $card) {

                    try {
                        $widget = new UWidget;
                        // dd('here');
                        $widget = $widget->makeComponent($block['tag'], $block)->render();
                        // dd($data);
                        // $repository = App::make($card['repository']);
                        // $data = array_reduce(explode('|', $card['method']), function ($s, $a) use ($repository) {
                        //     [$methodName, $args] = array_pad(explode(':', $a, 2), 2, null);
                        //     if ($args) {
                        //         $s = empty($s) ? $repository->{$methodName}(...explode(':', $args))->get() : $s->{$methodName}(...explode(':', $args))->get();
                        //     } else {
                        //         $s = empty($s) ? $repository->{$methodName}() : $s->$methodName();
                        //     }

                        //     return $s;
                        // });
                        // dd($data);
                        // dd($data);
                        // dd($widget);
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
