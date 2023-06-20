<?php

namespace OoBook\CRM\Base\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Modules\PressRelease\Entities\PressRelease;
use Modules\PressRelease\Repositories\PressReleaseRepository;

class ProfileController extends BaseController
{

    protected $namespace = 'OoBook\CRM\Base';

    /**
     * @var string
     */
    protected $moduleName = 'Profile';

    /**
     * @var string
     */
    protected $routeName = 'Profile';

    /**
     * @var string
     */
    protected $modelName = 'User';

    public function __construct(\Illuminate\Foundation\Application $app,Request $request)
    {
        parent::__construct(
            $app,
            $request
        );
    }

    public function index($parentId = null)
    {
        $parentId = $this->getParentId() ?? $parentId;

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
        $user = auth()->user();

        // dd(
        //     $user,

        //     array_map(function($locale) {
        //         return [
        //             'value' => $locale,
        //             'label' => getLabelFromLocale($locale, true)
        //         ];
        //     }, unusualConfig('available_user_locales', ['en', 'tr']))
        // );

        $inputs = [
            'name' => [
                "type" => "text",
                "name" => "name",
                "label" => "Name",
                "default" => "",
                'col' => [
                    'cols' => '12',
                    'xxl' => '6',
                    'xl' => '6',
                    'lg' => '6',
                ],
                'rules' => [
                    ['min', 6]
                ]
            ],
            'email' => [
                "type" => "text",
                "name" => "email",
                "label" => "E-mail",
                "default" => "",
                "appendInnerIcon" =>  '$non-visibility',
                "slotHandlers" => [
                    'appendInner' => 'password'
                ],
                'col' => [
                    'cols' => '12',
                    'xxl' => '6',
                    'xl' => '6',
                    'lg' => '6',
                ],
            ],
            // 'password' => [
            //     "type" => "password",
            //     "name" => "password",
            //     "label" => "User Password",
            //     "hint" => "6 to 12 Chars",
            //     "appendInnerIcon" =>  '$visibility',
            //     "counter" => 12,
            //     "slotHandlers" => [
            //         'appendInner' => 'password'
            //     ],
            //     "default" => "",
            //     'col' => [
            //         'cols' => '12',
            //         'xxl' => '6',
            //         'xl' => '6',
            //         'lg' => '6',
            //     ],
            //     'rules' => [
            //         ['min', 6]
            //     ]
            // ],

        ];
        $schema = $this->getFormSchema(arrayToObject($inputs));
        // dd(
        //     ___('update')
        // );
        $user->country_id = 1;
        $user->city_id = 1;
        $user->district_id = 2;

        $data = [
            'elements' => [
                [
                    "tag" => "v-row",
                    "attributes" => [],
                    "elements" => [
                        [
                            "tag" => "v-col",
                            "attributes" => [
                                'cols' => '12',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            "elements" => [
                                [
                                    "tag" => "v-row",
                                    "attributes" => [],
                                    "elements" => [
                                        [
                                            "tag" => "v-col",
                                            "attributes" => ['cols' => '12',],
                                            "elements" => [
                                                [
                                                    "tag" => "v-sheet",
                                                    "attributes" => [],
                                                    "elements" => [
                                                        [
                                                            "tag" => "ue-form",
                                                            "attributes" => [
                                                                'modelValue' => $user,

                                                                'hasSubmit' => true,
                                                                'stickyButton' => false,

                                                                'formTitle' => ___('Personal Information'),
                                                                'editable' => true,
                                                                'buttonText' => 'update',
                                                                'schema' => ($schema = $this->getFormSchema(arrayToObject([
                                                                    'name' => [
                                                                        "type" => "text",
                                                                        "name" => "name",
                                                                        "label" => "Name",
                                                                        "default" => "",
                                                                        'col' => [
                                                                            'cols' => '6',
                                                                            'xxl' => '6',
                                                                            'xl' => '6',
                                                                            'lg' => '6',
                                                                        ],
                                                                        'rules' => [
                                                                            ['min', 3]
                                                                        ]
                                                                    ],
                                                                    'surname' => [
                                                                        "type" => "text",
                                                                        "name" => "surname",
                                                                        "label" => "Surname",
                                                                        "default" => "",
                                                                        'col' => [
                                                                            'cols' => '6',
                                                                            'xxl' => '6',
                                                                            'xl' => '6',
                                                                            'lg' => '6',
                                                                        ],
                                                                        'rules' => [
                                                                            ['min', 2]
                                                                        ]
                                                                    ],
                                                                    'job_title' => [
                                                                        "type" => "text",
                                                                        "name" => "job_title",
                                                                        "label" => "Job Title",
                                                                        "default" => "",
                                                                        'col' => [
                                                                            'cols' => '6',
                                                                            'xxl' => '6',
                                                                            'xl' => '6',
                                                                            'lg' => '6',
                                                                        ],
                                                                        'rules' => [
                                                                            ['min', 2]
                                                                        ]
                                                                    ],
                                                                    'email' => [
                                                                        "type" => "text",
                                                                        "name" => "email",
                                                                        "label" => "E-mail",
                                                                        "default" => "",
                                                                        'col' => [
                                                                            'cols' => '6',
                                                                            'xxl' => '6',
                                                                            'xl' => '6',
                                                                            'lg' => '6',
                                                                        ],
                                                                        'rules' => [
                                                                            ['email']
                                                                        ]
                                                                    ],
                                                                    'phone' => [
                                                                        "type" => "custom-input-phone",
                                                                        "name" => "phone",
                                                                        "label" => "Phone Number",
                                                                        "default" => "",
                                                                        'col' => [
                                                                            'cols' => '6',
                                                                            'xxl' => '6',
                                                                            'xl' => '6',
                                                                            'lg' => '6',
                                                                        ],
                                                                        'rules' => [
                                                                            // ['email']
                                                                        ]
                                                                    ],
                                                                    'country' => [
                                                                        "type" => "text",
                                                                        "name" => "country",
                                                                        "label" => "Country",
                                                                        "default" => "",
                                                                        'col' => [
                                                                            'cols' => '6',
                                                                            'xxl' => '6',
                                                                            'xl' => '6',
                                                                            'lg' => '6',
                                                                        ],
                                                                        'rules' => [
                                                                            ['min', 3]
                                                                        ]
                                                                    ],
                                                                    'language' => [
                                                                        "type" => "select",
                                                                        "name" => "language",
                                                                        "label" => "Preferred Language",
                                                                        "default" => 0,
                                                                        'col' => [
                                                                            'cols' => '6',
                                                                            'xxl' => '6',
                                                                            'xl' => '6',
                                                                            'lg' => '6',
                                                                        ],
                                                                        'itemTitle' => 'label',
                                                                        // 'itemValue' => 'value',
                                                                        'items_' => [
                                                                            [
                                                                                'text' => 'TR',
                                                                                'value' => 1,
                                                                            ],
                                                                            [
                                                                                'text' => 'EN',
                                                                                'value' => 2
                                                                            ]
                                                                        ],
                                                                        'items' => array_map(function($locale) {
                                                                            return [
                                                                                'value' => $locale,
                                                                                'label' => getLabelFromLocale($locale, true)
                                                                            ];
                                                                        }, unusualConfig('available_user_locales', ['en', 'tr']))
                                                                    ],
                                                                    'timezone' => [
                                                                        "type" => "select",
                                                                        "name" => "timezone",
                                                                        "label" => "Timezone",
                                                                        "default" => 0,
                                                                        'col' => [
                                                                            'cols' => '6',
                                                                            'xxl' => '6',
                                                                            'xl' => '6',
                                                                            'lg' => '6',
                                                                        ],
                                                                        'itemTitle' => 'label',
                                                                        'itemValue' => 'value',
                                                                        'items' => [
                                                                            [
                                                                                'label' => 'Europe/London',
                                                                                'value' => 'Europe/London',
                                                                            ],
                                                                            // [
                                                                            //     'label' => 'Europe/London',
                                                                            //     'value' => 'Europe/London',
                                                                            // ]
                                                                        ],
                                                                        'items_' => array_map(function($locale) {
                                                                            return [
                                                                                'value' => $locale,
                                                                                'label' => getLabelFromLocale($locale, true)
                                                                            ];
                                                                        }, unusualConfig('available_user_locales', ['en', 'tr']))
                                                                    ],
                                                                ]))),
                                                                'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
                                                                    return [ $item['name'] => $item['default'] ?? ''];
                                                                    $carry[$key] = $item->default ?? '';
                                                                })->toArray(),
                                                                'actionUrl' => $this->getModuleRoute($user->id, 'update'),
                                                            ]
                                                        ]
                                                    ]
                                                ],
                                            ]
                                        ],
                                        [
                                            "tag" => "v-col",
                                            "attributes" => ['cols' => '12',],
                                            "elements" => [
                                                [
                                                    "tag" => "v-sheet",
                                                    "attributes" => [],
                                                    "elements" => [
                                                        [
                                                            "tag" => "ue-form",
                                                            "attributes" => [
                                                                'modelValue' => $user,

                                                                'hasSubmit' => true,
                                                                'stickyButton' => false,

                                                                'formTitle' => ___('Update Password'),
                                                                'editable' => true,
                                                                'schema' => ($schema = $this->getFormSchema(arrayToObject([
                                                                    'current-password' => [
                                                                        "type" => "password",
                                                                        // "ext" => "password",
                                                                        "name" => "current_password",
                                                                        "label" => "Current Password",
                                                                        "default" => "",
                                                                        "appendInnerIcon" => '$non-visibility',
                                                                        "slotHandlers" => [
                                                                            'appendInner' => 'password',
                                                                        ],
                                                                        'col' => [
                                                                            'cols' => 12,
                                                                            'sm' => 6
                                                                        ]
                                                                    ],
                                                                    'gap-1' => [
                                                                        'type' => 'v-sheet',
                                                                        'name' => 'gap-1',
                                                                        'class' => 'd-none d-md-block',
                                                                        'col' => [
                                                                            'cols' => 0,
                                                                            'sm' => 6,
                                                                            'class' => 'd-none d-sm-block',
                                                                        ]
                                                                    ],
                                                                    'new-password' => [
                                                                        "type" => "password",
                                                                        // "ext" => "password",
                                                                        "name" => "new_password",
                                                                        "label" => "New Password",
                                                                        "default" => "",
                                                                        "appendInnerIcon" => '$non-visibility',
                                                                        "slotHandlers" => [
                                                                            'appendInner' => 'password',
                                                                        ],
                                                                        'col' => [
                                                                            'cols' => 12,
                                                                            'sm' => 6
                                                                        ],
                                                                        'rules' => [
                                                                            ['min', 6]
                                                                        ]
                                                                    ],
                                                                    'confirm-password' => [
                                                                        "type" => "password",
                                                                        // "ext" => "password",
                                                                        "name" => "confirm_password",
                                                                        "label" => "Confirm Password",
                                                                        "default" => "",
                                                                        "appendInnerIcon" => '$non-visibility',
                                                                        "slotHandlers" => [
                                                                            'appendInner' => 'password',
                                                                        ],
                                                                        'col' => [
                                                                            'cols' => '12',
                                                                            'sm' => 6
                                                                        ],
                                                                        'rules' => [
                                                                            ['min', 6]
                                                                        ]
                                                                    ],
                                                                ]))),
                                                                'buttonText' => 'update',
                                                                'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
                                                                    return [ $item['name'] => $item['default'] ?? ''];
                                                                    $carry[$key] = $item->default ?? '';
                                                                })->toArray(),
                                                                'actionUrl' => $this->getModuleRoute($user->id, 'update'),
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            "tag" => "v-col",
                            "attributes" => [
                                'cols' => '12',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                                "class" => "d-flex"
                            ],
                            "directives" => ["fit-grid"],
                            "elements" => [
                                [
                                    "tag" => "v-sheet",
                                    "attributes" => [],
                                    "elements" => [
                                        [
                                            "tag" => "ue-form",
                                            "attributes" => [
                                                'modelValue' => $user,

                                                'hasSubmit' => true,
                                                'stickyButton' => false,

                                                'formTitle' => ___('Company Information'),
                                                'editable' => true,
                                                'buttonText' => 'update',
                                                'item' => $user,
                                                'schema' => ($schema = $this->getFormSchema(arrayToObject([
                                                    'name' => [
                                                        "type" => "text",
                                                        "name" => "name",
                                                        "label" => "Company",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'rules' => [
                                                            ['min', 3]
                                                        ]
                                                    ],
                                                    'address' => [
                                                        "type" => "text",
                                                        "name" => "address",
                                                        "label" => "Address",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '12',
                                                        ],
                                                        'rules' => [
                                                            // ['email']
                                                        ]
                                                    ],
                                                    'city' => [
                                                        "type" => "text",
                                                        "name" => "city",
                                                        "label" => "City",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'rules' => [
                                                            ['min', 3]
                                                        ]
                                                    ],
                                                    'state' => [
                                                        "type" => "text",
                                                        "name" => "state",
                                                        "label" => "State/Province",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'rules' => [
                                                            ['min', 3]
                                                        ]
                                                    ],
                                                    'country' => [
                                                        "type" => "text",
                                                        "name" => "country",
                                                        "label" => "Country",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'rules' => [
                                                            ['min', 3]
                                                        ]
                                                    ],
                                                    'zip_code' => [
                                                        "type" => "text",
                                                        "name" => "zip_code",
                                                        "label" => "ZIP/Postal Code",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'rules' => [
                                                            ['min', 3]
                                                        ]
                                                    ],
                                                    '_phone' => [
                                                        "type" => "custom-input-phone",
                                                        "name" => "_phone",
                                                        "label" => "Phone Number",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'rules' => [
                                                            // ['email']
                                                        ]
                                                    ],
                                                    'vat_number' => [
                                                        "type" => "text",
                                                        "name" => "vat_number",
                                                        "label" => "VAT Number",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'rules' => [
                                                            ['min', 3]
                                                        ]
                                                    ],
                                                    'tax_id' => [
                                                        "type" => "text",
                                                        "name" => "tax_id",
                                                        "label" => "Tax ID",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'rules' => [
                                                            ['min', 3]
                                                        ]
                                                    ],

                                                    'country_id' => [
                                                        "type" => "select",
                                                        "name" => "country_id",
                                                        "label" => "Country",
                                                        "default" => "",
                                                        "cascade" => "city_id",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'itemValue' => 'value',
                                                        'itemTitle' => 'text',
                                                        'items' => [
                                                            [
                                                                'value' => 1,
                                                                'text' => 'Germany',
                                                                'items' => [
                                                                    [
                                                                        "value" => 1,
                                                                        "text" => 'Berlin',
                                                                        'items' => [
                                                                            [
                                                                                'value' => 1,
                                                                                'text' => 'Mitte',
                                                                            ],
                                                                            [
                                                                                'value' => 2,
                                                                                'text' => 'Pankow',
                                                                            ],
                                                                        ]
                                                                    ],
                                                                    [
                                                                        "value" => 2,
                                                                        "text" => 'Munchen',
                                                                        'items' => [
                                                                            [
                                                                                'value' => 3,
                                                                                'text' => 'Altstadt',
                                                                            ],
                                                                            [
                                                                                'value' => 4,
                                                                                'text' => 'Bogenhausen',
                                                                            ],
                                                                        ]
                                                                    ],
                                                                ]
                                                            ],
                                                            [
                                                                'value' => 2,
                                                                'text' => 'France',
                                                                'items' => [
                                                                    [
                                                                        "value" => 3,
                                                                        "text" => 'Nantes',
                                                                        'items' => [
                                                                            [
                                                                                'value' => 5,
                                                                                'text' => 'Malakoff',
                                                                            ],
                                                                            [
                                                                                'value' => 6,
                                                                                'text' => 'Bouffay',
                                                                            ],
                                                                        ]
                                                                    ],
                                                                    [
                                                                        "value" => 4,
                                                                        "text" => 'Bordeaux',
                                                                        'items' => [
                                                                            [
                                                                                'value' => 7,
                                                                                'text' => 'Tere',
                                                                            ],
                                                                            [
                                                                                'value' => 8,
                                                                                'text' => 'Bhausen',
                                                                            ],
                                                                        ]
                                                                    ],
                                                                ]
                                                            ],
                                                            [
                                                                'value' => 3,
                                                                'text' => 'Italy',
                                                                'items' => [
                                                                    [
                                                                        "value" => 5,
                                                                        "text" => 'Milano'
                                                                    ],
                                                                    [
                                                                        "value" => 6,
                                                                        "text" => 'Venice'
                                                                    ]
                                                                ]
                                                            ]
                                                        ],
                                                        'rules' => [
                                                            // ['email']
                                                        ]
                                                    ],
                                                    'city_id' => [
                                                        "type" => "select",
                                                        "name" => "city_id",
                                                        "parent" => "country_id",
                                                        "cascade" => "district_id",
                                                        "label" => "City",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'itemValue' => 'value',
                                                        'itemTitle' => 'text',
                                                        // 'items' => 'country_id.items',
                                                        'items' => [],
                                                        'rules' => [
                                                            // ['email']
                                                        ]
                                                    ],
                                                    'district_id' => [
                                                        "type" => "select",
                                                        "name" => "district_id",
                                                        "label" => "District",
                                                        "default" => "",
                                                        'col' => [
                                                            'cols' => '6',
                                                            'xxl' => '6',
                                                            'xl' => '6',
                                                            'lg' => '6',
                                                        ],
                                                        'itemValue' => 'value',
                                                        'itemTitle' => 'text',
                                                        // 'items' => 'country_id.items',
                                                        'items' => [],
                                                        'rules' => [
                                                            // ['email']
                                                        ]
                                                    ],

                                                ]))),
                                                'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
                                                    return [ $item['name'] => $item['default'] ?? ''];
                                                    $carry[$key] = $item->default ?? '';
                                                })->toArray(),
                                                'actionUrl' => $this->getModuleRoute($user->id, 'update'),
                                            ]
                                        ]
                                    ]
                                ],
                            ]
                        ],
                    ]
                ]
            ],
            'forms' => [
                [
                    "col" => [
                        'cols' => '12',
                        'xxl' => '6',
                        'xl' => '6',
                        'lg' => '6',
                        'md' => '6'
                    ],
                    'formTitle' => ___('Personal Information'),
                    'editable' => true,
                    'buttonText' => 'update',
                    'item' => $user,
                    'schema' => ($schema = $this->getFormSchema(arrayToObject([
                        'name' => [
                            "type" => "text",
                            "name" => "name",
                            "label" => "Name",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 3]
                            ]
                        ],
                        'surname' => [
                            "type" => "text",
                            "name" => "surname",
                            "label" => "Surname",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 2]
                            ]
                        ],
                        'job_title' => [
                            "type" => "text",
                            "name" => "job_title",
                            "label" => "Job Title",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 2]
                            ]
                        ],
                        'email' => [
                            "type" => "text",
                            "name" => "email",
                            "label" => "E-mail",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['email']
                            ]
                        ],
                        'phone' => [
                            "type" => "custom-input-phone",
                            "name" => "phone",
                            "label" => "Phone Number",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                // ['email']
                            ]
                        ],
                        'country' => [
                            "type" => "text",
                            "name" => "country",
                            "label" => "Country",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 3]
                            ]
                        ],
                        'language' => [
                            "type" => "select",
                            "name" => "language",
                            "label" => "Preferred Language",
                            "default" => 0,
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'itemTitle' => 'label',
                            // 'itemValue' => 'value',
                            'items_' => [
                                [
                                    'text' => 'TR',
                                    'value' => 1,
                                ],
                                [
                                    'text' => 'EN',
                                    'value' => 2
                                ]
                            ],
                            'items' => array_map(function($locale) {
                                return [
                                    'value' => $locale,
                                    'label' => getLabelFromLocale($locale, true)
                                ];
                            }, unusualConfig('available_user_locales', ['en', 'tr']))
                        ],
                        'timezone' => [
                            "type" => "select",
                            "name" => "timezone",
                            "label" => "Timezone",
                            "default" => 0,
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'itemTitle' => 'label',
                            'itemValue' => 'value',
                            'items' => [
                                [
                                    'label' => 'Europe/London',
                                    'value' => 'Europe/London',
                                ],
                                // [
                                //     'label' => 'Europe/London',
                                //     'value' => 'Europe/London',
                                // ]
                            ],
                            'items_' => array_map(function($locale) {
                                return [
                                    'value' => $locale,
                                    'label' => getLabelFromLocale($locale, true)
                                ];
                            }, unusualConfig('available_user_locales', ['en', 'tr']))
                        ],
                    ]))),
                    'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
                        return [ $item['name'] => $item['default'] ?? ''];
                        $carry[$key] = $item->default ?? '';
                    })->toArray(),
                    'actionUrl' => $this->getModuleRoute($user->id, 'update'),
                ],
                [
                    "col" => [
                        'cols' => '12',
                        'xxl' => '6',
                        'xl' => '6',
                        'lg' => '6',
                        'md' => '6'
                    ],
                    'formTitle' => ___('Company Information'),
                    'editable' => true,
                    'buttonText' => 'update',
                    'item' => $user,
                    'schema' => ($schema = $this->getFormSchema(arrayToObject([
                        'name' => [
                            "type" => "text",
                            "name" => "name",
                            "label" => "Company",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 3]
                            ]
                        ],
                        'job_title' => [
                            "type" => "text",
                            "name" => "job_title",
                            "label" => "Job Title",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 2]
                            ]
                        ],
                        'address' => [
                            "type" => "text",
                            "name" => "address",
                            "label" => "Address",
                            "default" => "",
                            'col' => [
                                'cols' => '12',
                            ],
                            'rules' => [
                                // ['email']
                            ]
                        ],
                        'city' => [
                            "type" => "text",
                            "name" => "city",
                            "label" => "City",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 3]
                            ]
                        ],
                        'state' => [
                            "type" => "text",
                            "name" => "state",
                            "label" => "State/Province",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 3]
                            ]
                        ],
                        'country' => [
                            "type" => "text",
                            "name" => "country",
                            "label" => "Country",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 3]
                            ]
                        ],
                        'zip_code' => [
                            "type" => "text",
                            "name" => "zip_code",
                            "label" => "ZIP/Postal Code",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 3]
                            ]
                        ],
                        '_phone' => [
                            "type" => "custom-input-phone",
                            "name" => "_phone",
                            "label" => "Phone Number",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                // ['email']
                            ]
                        ],
                        'vat_number' => [
                            "type" => "text",
                            "name" => "vat_number",
                            "label" => "VAT Number",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 3]
                            ]
                        ],
                        'tax_id' => [
                            "type" => "text",
                            "name" => "tax_id",
                            "label" => "Tax ID",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'rules' => [
                                ['min', 3]
                            ]
                        ],

                        'country_id' => [
                            "type" => "select",
                            "name" => "country_id",
                            "label" => "Country",
                            "default" => "",
                            "cascade" => "city_id",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'itemValue' => 'value',
                            'itemTitle' => 'text',
                            'items' => [
                                [
                                    'value' => 1,
                                    'text' => 'Germany',
                                    'items' => [
                                        [
                                            "value" => 1,
                                            "text" => 'Berlin',
                                            'items' => [
                                                [
                                                    'value' => 1,
                                                    'text' => 'Mitte',
                                                ],
                                                [
                                                    'value' => 2,
                                                    'text' => 'Pankow',
                                                ],
                                            ]
                                        ],
                                        [
                                            "value" => 2,
                                            "text" => 'Munchen',
                                            'items' => [
                                                [
                                                    'value' => 3,
                                                    'text' => 'Altstadt',
                                                ],
                                                [
                                                    'value' => 4,
                                                    'text' => 'Bogenhausen',
                                                ],
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'value' => 2,
                                    'text' => 'France',
                                    'items' => [
                                        [
                                            "value" => 3,
                                            "text" => 'Nantes',
                                            'items' => [
                                                [
                                                    'value' => 5,
                                                    'text' => 'Malakoff',
                                                ],
                                                [
                                                    'value' => 6,
                                                    'text' => 'Bouffay',
                                                ],
                                            ]
                                        ],
                                        [
                                            "value" => 4,
                                            "text" => 'Bordeaux',
                                            'items' => [
                                                [
                                                    'value' => 7,
                                                    'text' => 'Tere',
                                                ],
                                                [
                                                    'value' => 8,
                                                    'text' => 'Bhausen',
                                                ],
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'value' => 3,
                                    'text' => 'Italy',
                                    'items' => [
                                        [
                                            "value" => 5,
                                            "text" => 'Milano'
                                        ],
                                        [
                                            "value" => 6,
                                            "text" => 'Venice'
                                        ]
                                    ]
                                ]
                            ],
                            'rules' => [
                                // ['email']
                            ]
                        ],
                        'city_id' => [
                            "type" => "select",
                            "name" => "city_id",
                            "parent" => "country_id",
                            "cascade" => "district_id",
                            "label" => "City",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'itemValue' => 'value',
                            'itemTitle' => 'text',
                            // 'items' => 'country_id.items',
                            'items' => [],
                            'rules' => [
                                // ['email']
                            ]
                        ],
                        'district_id' => [
                            "type" => "select",
                            "name" => "district_id",
                            "label" => "District",
                            "default" => "",
                            'col' => [
                                'cols' => '6',
                                'xxl' => '6',
                                'xl' => '6',
                                'lg' => '6',
                            ],
                            'itemValue' => 'value',
                            'itemTitle' => 'text',
                            // 'items' => 'country_id.items',
                            'items' => [],
                            'rules' => [
                                // ['email']
                            ]
                        ],

                    ]))),
                    'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
                        return [ $item['name'] => $item['default'] ?? ''];
                        $carry[$key] = $item->default ?? '';
                    })->toArray(),
                    'actionUrl' => $this->getModuleRoute($user->id, 'update'),
                ],
                [
                    "col" => [
                        'cols' => '12',
                        'xxl' => '6',
                        'xl' => '6',
                        'lg' => '6',
                        'md' => '6'
                    ],
                    'formTitle' => ___('Update Password'),
                    'editable' => true,
                    'item' => $user,
                    'schema' => ($schema = $this->getFormSchema(arrayToObject([
                        'current-password' => [
                            "type" => "password",
                            // "ext" => "password",
                            "name" => "current_password",
                            "label" => "Current Password",
                            "default" => "",
                            "appendInnerIcon" => '$non-visibility',
                            "slotHandlers" => [
                                'appendInner' => 'password',
                            ],
                            'col' => [
                                'cols' => 12,
                                'sm' => 6
                            ]
                        ],
                        'gap-1' => [
                            'type' => 'v-sheet',
                            'name' => 'gap-1',
                            'class' => 'd-none d-md-block',
                            'col' => [
                                'cols' => 0,
                                'sm' => 6,
                                'class' => 'd-none d-sm-block',
                            ]
                        ],
                        'new-password' => [
                            "type" => "password",
                            // "ext" => "password",
                            "name" => "new_password",
                            "label" => "New Password",
                            "default" => "",
                            "appendInnerIcon" => '$non-visibility',
                            "slotHandlers" => [
                                'appendInner' => 'password',
                            ],
                            'col' => [
                                'cols' => 12,
                                'sm' => 6
                            ],
                            'rules' => [
                                ['min', 6]
                            ]
                        ],
                        'confirm-password' => [
                            "type" => "password",
                            // "ext" => "password",
                            "name" => "confirm_password",
                            "label" => "Confirm Password",
                            "default" => "",
                            "appendInnerIcon" => '$non-visibility',
                            "slotHandlers" => [
                                'appendInner' => 'password',
                            ],
                            'col' => [
                                'cols' => '12',
                                'sm' => 6
                            ],
                            'rules' => [
                                ['min', 6]
                            ]
                        ],
                    ]))),
                    'buttonText' => 'update',
                    'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
                        return [ $item['name'] => $item['default'] ?? ''];
                        $carry[$key] = $item->default ?? '';
                    })->toArray(),
                    'actionUrl' => $this->getModuleRoute($user->id, 'update'),
                ]
            ],
        ];

        // dd(
        //     collect($schema)->mapWithKeys(function($item, $key){
        //         return [ $item['name'] => $item['default'] ?? ''];
        //         $carry[$key] = $item->default ?? '';
        //     })->toArray()
        // );
        // $data = [
        //     'editable' => !!$user,
        //     'item' => $user,
        //     // 'moduleName' => $this->moduleName,
        //     // 'routeName' => $this->routeName,
        //     // 'titleFormKey' => $this->titleFormKey ?? $this->titleColumnKey,

        //     'formSchema' => $schema,
        //     'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
        //         return [ $item['name'] => $item['default'] ?? ''];
        //         $carry[$key] = $item->default ?? '';
        //     })->toArray(),
        //     'actionUrl' => $this->getModuleRoute($user->id, 'update'),

        // ];

        $view = "$this->baseKey::layouts.profile";

        return View::make($view, $data);
    }

        /**
     * @param int $id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $id = last($params);


        // $item = $this->repository->getById($id);
        $item = auth()->user();

        $input = $this->request->all();

        $formRequest = $this->validateFormRequest();

        dd($formRequest);

        $this->repository->update($id, $formRequest->all());

        activity()->performedOn($item)->log('updated');

        return $this->respondWithSuccess(unusualTrans("$this->baseKey::lang.publisher.save-success"));

    }


}
