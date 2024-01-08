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

class DashboardController extends BaseController
{
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
        $data = [
            'blocks' => [
                [
                    'component' => 'custom-board-information',
                    'col' => [
                        'cols' => 12,
                        'xxl' => 8,
                        'xl' => 8,
                        'lg' => 8,
                        'class' => 'pr-theme-semi pb-theme-semi'
                    ],
                    'attributes' => [

                    ],
                ],
                // [
                //     'component' => 'table',
                //     'col' => [
                //         'cols' => 12,
                //         'xxl' => 3,
                //         'xl' => 4,
                //         'lg' => 4,
                //         'class' => 'pl-theme-semi pb-theme-semi'
                //     ],
                //     'attributes' => [
                //         'name' => 'pressRelease',
                //         'title-key' => '',
                //         'custom-header' => 'Recent Revisions',
                //         'hide-headers' => true,
                //         'fullWidthWrapper' => true,
                //         'style' => 'min-height: 100%',
                //         'items' =>  App::make(PackageContinentRepository::class)->get([], [], [
                //             'created_at' => 'desc'
                //         ], 2)->items(),
                //         'columns' => [
                //             [
                //                 'title' => 'name',
                //                 'key' => 'name',
                //                 'align' => 'start',
                //                 'sortable' => false,
                //                 'filterable' => false,
                //                 'groupable' => false,
                //                 'divider' => false,
                //                 'class' => '',
                //                 'cellClass' => '',
                //                 'width' => '',
                //                 'searchable' => true,
                //                 'isRowEditable' => true,
                //                 'isColumnEditable' => false,
                //                 // 'formatter' => ['date', 'numeric'],
                //             ],
                //             [
                //                 'title' => 'Headline',
                //                 'key' => 'headline',
                //                 'align' => 'start',
                //                 'sortable' => false,
                //                 'filterable' => false,
                //                 'groupable' => false,
                //                 'divider' => false,
                //                 'class' => '',
                //                 'cellClass' => '',
                //                 'width' => '',
                //                 'searchable' => true,
                //                 'isRowEditable' => true,
                //                 'isColumnEditable' => false,
                //                 'formatter' => [],
                //             ]
                //         ],
                //         'table-options' => [
                //             'page'          => 1,
                //             'itemsPerPage'  => 2,
                //             'sortBy'        => [],
                //             'multiSort'     => false,
                //             'mustSort'      => false,
                //             'groupBy'       => [],
                //         ],
                //         'slots' => [
                //             'bottom' => [
                //                 'elements' => [
                //                     [
                //                         'tag' => 'div',
                //                         'attributes' => [
                //                             'class' => 'text-right pa-8',
                //                         ],
                //                         'elements' => [
                //                             [
                //                                 'tag' => 'v-btn-tertiary',
                //                                 'elements' => 'MANAGE RELEASES'
                //                             ]
                //                         ]
                //                     ]
                //                 ]
                //             ]
                //         ]
                //     ],
                // ],
                // [
                //     'component' => 'table',
                //     'col' => [
                //         'cols' => 12,
                //         'xxl' => 6,
                //         'xl' => 8,
                //         'lg' => 8,
                //         'class' => 'pr-theme-semi pt-theme-semi'

                //     ],
                //     'attributes' => [
                //         'name' => 'pressRelease',
                //         'title-key' => '',
                //         'custom-header' => 'Recently Published',
                //         'fullWidthWrapper' => true,
                //         'style' => 'min-height: 100%',
                //         'items' =>  App::make(PackageContinentRepository::class)->get([], [], [
                //             'created_at' => 'desc'
                //         ], 8)->items(),
                //         'columns' => [
                //             [
                //                 'title' => 'Name',
                //                 'key' => 'name',
                //                 'align' => 'start',
                //                 // 'formatter' => [
                //                 //     'edit'
                //                 // ]
                //             ],
                //             [
                //                 'title' => 'PR Headline',
                //                 'key' => 'headline',
                //                 'align' => 'start',
                //             ]
                //         ],
                //         'table-options' => [
                //             'page'          => 1,
                //             'itemsPerPage'  => 8,
                //             'sortBy'        => [],
                //             'multiSort'     => false,
                //             'mustSort'      => false,
                //             'groupBy'       => [],
                //         ],
                //         'slots' => [
                //             'bottom' => [
                //                 'elements' => [
                //                     [
                //                         'tag' => 'div',
                //                         'attributes' => [
                //                             'class' => 'text-right pa-8',
                //                         ],
                //                         'elements' => [
                //                             [
                //                                 'tag' => 'v-btn-secondary',
                //                                 'attributes' => [
                //                                     'class' => 'mr-5'
                //                                 ],
                //                                 'elements' => 'CONTINUE IN PROGRESS'
                //                             ],
                //                             [
                //                                 'tag' => 'v-btn',
                //                                 'elements' => 'CREATE PRESS RELEASE'
                //                             ]
                //                         ]
                //                     ]
                //                 ]
                //             ]
                //         ]
                //     ],
                // ],
            ]
        ];
        // dd($data['blocks']);
        $view = "$this->baseKey::layouts.dashboard";

        return View::make($view, $data);
    }
}
