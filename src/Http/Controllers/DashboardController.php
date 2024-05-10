<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Modules\Webinar\Repositories\SurveyRepository;
use Modules\Webinar\Repositories\VimeoWebinarRepository;
// use Modules\Package\Repositories\PackageContinentRepository;
// use Modules\PressRelease\Entities\PressRelease;
// use Modules\PressRelease\Repositories\PressReleaseRepository;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Facades\UFinder;
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
            $blocks = app()->config->get( unusualBaseKey().'.ui_settings.dashboard.blocks');
            $blocks = $blocks +
            [
                1 => [
                    'component' => 'new-table',
                    'col' => [
                        'cols' => 12,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
                        's' => 12,
                        'class' => 'pl-theme-semi pb-theme-semi'
                    ],
                    'attributes' => [
                        'customTitle' => 'ANNOUNCEMENTS',
                        'tableSubtitle' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam lobortis.',
                        'hide-headers' => true,
                        'fullWidthWrapper' => false,
                        'hideSearchField' => true,
                        'tableType' => 'dashboard',
                        'style' => '',
                        'items' =>  App::make(SurveyRepository::class)->get([], [], [
                            'created_at' => 'desc'
                        ], 2)->items(),
                        'columns' => [
                            [
                                'title' => 'name',
                                'key' => 'created_at',
                                'align' => 'start',
                                'sortable' => true,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                // 'max-width' => 'max-content',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => false,
                                'formatter' => [
                                    'date',
                                    'numeric'
                                ]
                                // 'formatter' => ['date', 'numeric'],
                            ],
                            [
                                'title' => 'Name',
                                'key' => 'name',
                                'align' => 'start',
                                'sortable' => false,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => true,
                                'formatter' => [

                                ],
                            ],
                            [
                                'title' => 'Actions',
                                'key' => 'actions',
                                'sortable' => false,

                            ],
                        ],
                        'tableOptions' => [
                            'page'          => 1,
                            'itemsPerPage'  => 1,
                            'sortBy'        => [],
                            'multiSort'     => false,
                            'mustSort'      => false,
                            'groupBy'       => [],
                        ],
                        'slots' => [
                            'headerBtn' => [
                                'elements' => [
                                    [
                                        'tag' => 'div',
                                        'attributes' => [
                                            'class' => 'text-right pa-8',
                                        ],
                                        'elements' => [
                                            [
                                                'tag' => 'v-btn-tertiary',
                                                'elements' => 'MANAGE RELEASES',
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ],
                2 => [
                    'component' => 'new-table',
                    'col' => [
                        'cols' => 12,
                        'xxl' => 12,
                        'xl' => 12,
                        'lg' => 12,
                        'class' => ''
                    ],
                    'attributes' => [

                        'customTitle' => 'Vimeo Webinars',
                        'tableSubtitle' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam lobortis.',
                        'tableType' => 'dashboard',
                        'hide-headers' => false,
                        'fullWidthWrapper' => true,
                        'hideSearchField' => true,
                        'fillHeight' => true,
                        'style' => '',
                        'items' =>  App::make(VimeoWebinarRepository::class)->get([], [], [
                            'created_at' => 'desc'
                        ], 2)->items(),
                        'columns' => [
                            [
                                'title' => 'Date',
                                'key' => 'start_date',
                                'align' => 'start',
                                'sortable' => true,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                // 'max-width' => 'max-content',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => false,
                                'formatter' => [
                                    'date',
                                    'numeric'
                                ]
                                // 'formatter' => ['date', 'numeric'],
                            ],
                            [
                                'title' => 'Name',
                                'key' => 'name',
                                'align' => 'start',
                                'sortable' => false,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => true,
                                'formatter' => [
                                ],
                            ],
                            [
                                'title' => 'Published',
                                'key' => 'published',
                                'align' => 'start',
                                'sortable' => false,
                                'filterable' => false,
                                'groupable' => false,
                                'divider' => false,
                                'class' => '',
                                'cellClass' => '',
                                'width' => '',
                                'searchable' => true,
                                'isRowEditable' => true,
                                'isColumnEditable' => true,
                                'formatter' => [
                                ],
                            ],
                            [
                                'title' => 'Actions',
                                'key' => 'actions',
                                'sortable' => false,

                            ],
                        ],
                        'tableOptions' => [
                            'page'          => 1,
                            'itemsPerPage'  => 1,
                            'sortBy'        => [],
                            'multiSort'     => false,
                            'mustSort'      => false,
                            'groupBy'       => [],
                        ],
                        'slots' => [
                            'bottom' => [
                                'elements' => [
                                    [
                                        'tag' => 'div',
                                        'attributes' => [
                                            'class' => 'text-right pa-8',
                                        ],
                                        'elements' => [
                                            [
                                                'tag' => 'v-btn-tertiary',
                                                'elements' => 'MANAGE RELEASES',
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ],
            ] ;



            foreach ($blocks as $index => $block) {
                switch ($block['component']) {
                    case 'table':
                        $this->moduleName = 'Webinar';
                        $this->routeName = 'Survey';
                    case 'board-information-plus':
                        $cards = $block['cards'] ?? [];
                        foreach ($cards as $key => $card) {
                            try {
                                $repository = App::make($card['repository']);
                                $data = array_reduce(explode('|', $card['method']),function($s, $a) use($repository){
                                    [$methodName, $args] = array_pad(explode(':',$a,2),2,null);
                                    if($args){
                                        $s = empty($s) ? $repository->{$methodName}(...explode(':',$args))->get() : $s->{$methodName}(...explode(':',$args))->get();
                                    }else{
                                        $s = empty($s) ? $repository->{$methodName}() : $s->$methodName();
                                    }
                                    return $s;
                                });
                            } catch (\Throwable $th) {
                                $data = '-';
                            }
                            $card['data'] = $data;
                            $cards[$key] = $card;
                        }
                        $blocks[$index]['attributes']['cards'] = $cards;
                        break;
                    default:
                        break;
                }
            }


        $options = [
            'endpoints' => $this->getUrls()
        ]+['blocks' => $blocks];
        // dd($data['blocks']);
        $view = "$this->baseKey::layouts.dashboard";

        return View::make($view, $options);
    }
}
