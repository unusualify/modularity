<?php

namespace OoBook\CRM\Base\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Modules\PressRelease\Entities\PressRelease;
use Modules\PressRelease\Repositories\PressReleaseRepository;
use OoBook\CRM\Base\Entities\Enums\Permission;
use OoBook\CRM\Base\Entities\User;
use OoBook\CRM\Base\Repositories\CompanyRepository;
use OoBook\CRM\Base\Repositories\UserRepository;

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

    public function __construct(
        \Illuminate\Foundation\Application $app,
        Request $request,
        protected UserRepository $userRepository,
        protected CompanyRepository $companyRepository,
    )
    {

        parent::__construct(
            $app,
            $request
        );

        // dd(
        //     "can:{$this->permissionPrefix(Permission::VIEW->value)}",
        //     "can:{$this->permissionPrefix(Permission::EDIT->value)}",
        //     $this->middleware,
        //     get_class_methods($this),
        //     $this
        // );
        $this->removeMiddleware("can:{$this->permissionPrefix(Permission::VIEW->value)}");
        $this->removeMiddleware("can:{$this->permissionPrefix(Permission::EDIT->value)}");
        // dd(
        //     $this->middleware
        // );
        // $this->removeMiddleware("can:{$this->permissionPrefix()}_". Permission::VIEW->value);
        // $this->middleware('can:dashboard', ['only' => ['index']]);

    }

    public function edit($id = null, $submoduleId = null)
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

        $user = auth()->user();
        $userSchema = $this->getFormSchema([
            'name' => [
                "type" => "text",
                "name" => "name",
                "label" => "Name",
                "default" => "",
                'col' => [
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    'md' => 6,
                    'sm' => 6,
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
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    'md' => 6,
                    'sm' => 6,
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
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    'md' => 6,
                    'sm' => 6,
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
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    'md' => 6,
                    'sm' => 6,
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
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    'md' => 6,
                    'sm' => 6,
                ],
                'rules' => [
                    // ['email']
                ],
                'clearable' => false

            ],
            // 'xphone' => [
            //     "type" => "custom-input-phone",
            //     "name" => "xphone",
            //     "label" => "Phone Number",
            //     "default" => "",
            //     'col' => [
            //         'cols' => 12,
            //         'xxl' => 6,
            //         'xl' => 6,
            //         'lg' => 6,
            //         'md' => 6,
            //         'sm' => 6,
            //     ],
            //     'rules' => [
            //         // ['email']
            //     ]
            // ],
            'country' => [
                "type" => "text",
                "name" => "country",
                "label" => "Country",
                "default" => "",
                'col' => [
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    'md' => 6,
                    'sm' => 6,
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
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    'md' => 6,
                    'sm' => 6,
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
                "type" => "combobox",
                "name" => "timezone",
                "label" => "Timezone",
                "default" => 0,
                "returnObject" => false,
                'col' => [
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    'md' => 6,
                    'sm' => 6,
                ],
                'itemTitle' => 'label',
                'itemValue' => 'value',
                'items' => collect((new \Camroncade\Timezone\Timezone())->timezoneList)->map(function($value,$key){
                    return [
                        'label' => $key,
                        'value' => $value
                    ];
                })->values()->toArray(),
                'items_' => array_map(function($locale) {
                    return [
                        'value' => $locale,
                        'label' => getLabelFromLocale($locale, true)
                    ];
                }, unusualConfig('available_user_locales', ['en', 'tr']))
            ],
        ]);

        $userFields = $this->userRepository->getFormFields($user, $userSchema);

        $userPasswordSchema = $this->getFormSchema([
            'password' => [
                "type" => "password",
                // "ext" => "password",
                "name" => "password",
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
                    'cols' => 12,
                    'sm' => 6
                ],
                'rules' => [
                    ['min', 6],
                    ['confirmation', 'new_password'],
                ]
            ],
        ]);

        $userPasswordFields = $this->userRepository->getFormFields($user, $userPasswordSchema, true);

        $data = [
            'elements' => [
                [
                    "tag" => "v-row",
                    "attributes" => ['class' => ''],
                    "elements" => [
                        [
                            "tag" => "v-col",
                            // "attributes" => [
                            //     'cols' => 12,
                            //     'xxl' => 6,
                            //     'xl' => 6,
                            //     'lg' => 6,
                            // ],
                            "elements" => [
                                [
                                    "tag" => "v-row",
                                    "attributes" => [],
                                    "elements" => [
                                        [
                                            "tag" => "v-col",
                                            "attributes" => ['cols' => 12, 'class' => 'pb-theme-semi pr-theme-semi'],
                                            "elements" => [
                                                [
                                                    "tag" => "v-sheet",
                                                    "attributes" => [],
                                                    "elements" => [
                                                        [
                                                            "tag" => "ue-form",
                                                            "attributes" => [
                                                                'modelValue' => $userFields,

                                                                'hasSubmit' => true,
                                                                'stickyButton' => false,

                                                                'title' => ___('Personal Information'),
                                                                'buttonText' => 'update',
                                                                'schema' => $userSchema,
                                                                'defaultItem' => collect($userSchema)->mapWithKeys(function($item, $key){
                                                                    return [ $item['name'] => $item['default'] ?? ''];
                                                                    $carry[$key] = $item->default ?? '';
                                                                })->toArray(),
                                                                'actionUrl' => $this->getModuleRoute($userFields['id'], 'update', singleton: true),
                                                            ]
                                                        ]
                                                    ]
                                                ],
                                            ]
                                        ],
                                        [
                                            "tag" => "v-col",
                                            "attributes" => ['cols' => 12, 'class' => 'pt-theme-semi pr-theme-semi'],
                                            "elements" => [
                                                [
                                                    "tag" => "v-sheet",
                                                    "attributes" => [],
                                                    "elements" => [
                                                        [
                                                            "tag" => "ue-form",
                                                            "attributes" => [
                                                                'modelValue' => $userPasswordFields,
                                                                'hasSubmit' => true,
                                                                'stickyButton' => false,
                                                                'title' => ___('Update Password'),
                                                                'schema' => $userPasswordSchema,
                                                                'buttonText' => 'update',
                                                                'defaultItem' => collect($userPasswordSchema)->mapWithKeys(function($item, $key){
                                                                    return [ $item['name'] => $item['default'] ?? ''];
                                                                    $carry[$key] = $item->default ?? '';
                                                                })->toArray(),
                                                                'actionUrl' => $this->getModuleRoute($userPasswordFields['id'], 'update',  singleton: true),
                                                            ]
                                                        ]
                                                    ]
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
        ];

        if($user->company){
            $companySchema = $this->getFormSchema([
                'name' => [
                    "type" => "text",
                    "name" => "name",
                    "label" => "Company",
                    "default" => "",
                    'col' => [
                        'cols' => 6,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
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
                        'cols' => 12,
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
                        'cols' => 6,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
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
                        'cols' => 6,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
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
                        'cols' => 6,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
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
                        'cols' => 6,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
                    ],
                    'rules' => [
                        ['min', 3]
                    ]
                ],
                'phone' => [
                    "type" => "custom-input-phone",
                    "name" => "phone",
                    "label" => "Phone Number",
                    "default" => "",
                    'col' => [
                        'cols' => 6,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
                    ],
                    'rules' => [
                        // ['email']
                    ],
                    'clearable' => false
                ],
                'vat_number' => [
                    "type" => "text",
                    "name" => "vat_number",
                    "label" => "VAT Number",
                    "default" => "",
                    'col' => [
                        'cols' => 6,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
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
                        'cols' => 6,
                        'xxl' => 6,
                        'xl' => 6,
                        'lg' => 6,
                    ],
                    'rules' => [
                        ['min', 3]
                    ]
                ],
                // 'country_id' => [
                //     "type" => "select",
                //     "name" => "country_id",
                //     "label" => "Country",
                //     "default" => "",
                //     "cascade" => "city_id",
                //     'col' => [
                //         'cols' => 6,
                //         'xxl' => 6,
                //         'xl' => 6,
                //         'lg' => 6,
                //     ],
                //     'itemValue' => 'value',
                //     'itemTitle' => 'text',
                //     'items' => [
                //         [
                //             'value' => 1,
                //             'text' => 'Germany',
                //             'items' => [
                //                 [
                //                     "value" => 1,
                //                     "text" => 'Berlin',
                //                     'items' => [
                //                         [
                //                             'value' => 1,
                //                             'text' => 'Mitte',
                //                         ],
                //                         [
                //                             'value' => 2,
                //                             'text' => 'Pankow',
                //                         ],
                //                     ]
                //                 ],
                //                 [
                //                     "value" => 2,
                //                     "text" => 'Munchen',
                //                     'items' => [
                //                         [
                //                             'value' => 3,
                //                             'text' => 'Altstadt',
                //                         ],
                //                         [
                //                             'value' => 4,
                //                             'text' => 'Bogenhausen',
                //                         ],
                //                     ]
                //                 ],
                //             ]
                //         ],
                //         [
                //             'value' => 2,
                //             'text' => 'France',
                //             'items' => [
                //                 [
                //                     "value" => 3,
                //                     "text" => 'Nantes',
                //                     'items' => [
                //                         [
                //                             'value' => 5,
                //                             'text' => 'Malakoff',
                //                         ],
                //                         [
                //                             'value' => 6,
                //                             'text' => 'Bouffay',
                //                         ],
                //                     ]
                //                 ],
                //                 [
                //                     "value" => 4,
                //                     "text" => 'Bordeaux',
                //                     'items' => [
                //                         [
                //                             'value' => 7,
                //                             'text' => 'Tere',
                //                         ],
                //                         [
                //                             'value' => 8,
                //                             'text' => 'Bhausen',
                //                         ],
                //                     ]
                //                 ],
                //             ]
                //         ],
                //         [
                //             'value' => 3,
                //             'text' => 'Italy',
                //             'items' => [
                //                 [
                //                     "value" => 5,
                //                     "text" => 'Milano'
                //                 ],
                //                 [
                //                     "value" => 6,
                //                     "text" => 'Venice'
                //                 ]
                //             ]
                //         ]
                //     ],
                //     'rules' => [
                //         // ['email']
                //     ]
                // ],
                // 'city_id' => [
                //     "type" => "select",
                //     "name" => "city_id",
                //     "parent" => "country_id",
                //     "cascade" => "district_id",
                //     "label" => "City",
                //     "default" => "",
                //     'col' => [
                //         'cols' => 6,
                //         'xxl' => 6,
                //         'xl' => 6,
                //         'lg' => 6,
                //     ],
                //     'itemValue' => 'value',
                //     'itemTitle' => 'text',
                //     // 'items' => 'country_id.items',
                //     'items' => [],
                //     'rules' => [
                //         // ['email']
                //     ]
                // ],
                // 'district_id' => [
                //     "type" => "select",
                //     "name" => "district_id",
                //     "label" => "District",
                //     "default" => "",
                //     'col' => [
                //         'cols' => 6,
                //         'xxl' => 6,
                //         'xl' => 6,
                //         'lg' => 6,
                //     ],
                //     'itemValue' => 'value',
                //     'itemTitle' => 'text',
                //     // 'items' => 'country_id.items',
                //     'items' => [],
                //     'rules' => [
                //         // ['email']
                //     ]
                // ],
            ]);

            $companyFields = $this->companyRepository->getFormFields(auth()->user()->company, $companySchema);
            $companyFields['country_id'] = 1;
            $companyFields['city_id'] = 1;
            $companyFields['district_id'] = 2;

            $data['elements'][0]['elements'][] = [
                "tag" => "v-col",
                "attributes" => [
                    'cols' => 12,
                    'xxl' => 6,
                    'xl' => 6,
                    'lg' => 6,
                    "class" => "d-flex pl-theme-semi"
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
                                    'modelValue' => $companyFields,
                                    'hasSubmit' => true,
                                    'stickyButton' => false,
                                    'title' => ___('Company Information'),
                                    // 'editable' => true,
                                    'buttonText' => 'update',
                                    // 'item' => $user,
                                    'schema' => $companySchema,
                                    'defaultItem' => collect($companySchema)->mapWithKeys(function($item, $key){
                                        return [ $item['name'] => $item['default'] ?? ''];
                                        $carry[$key] = $item->default ?? '';
                                    })->toArray(),
                                    'actionUrl' => route('profile.company'),
                                ]
                            ]
                        ]
                    ],
                ]
            ];
        }

        // dd($userFields);

        // dd(
        //     $user,

        //     array_map(function($locale) {
        //         return [
        //             'value' => $locale,
        //             'label' => getLabelFromLocale($locale, true)
        //         ];
        //     }, unusualConfig('available_user_locales', ['en', 'tr']))
        // );

        // dd(
        //     ___('update')
        // );


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
    public function update($id = null, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $id = last($params) ?: $this->request->get('id');

        $item = $this->repository->getById($id);
        // $item = auth()->user();
        // dd($item);
        // $input = $this->request->all();

        $formRequest = $this->validateFormRequest();

        // dd($formRequest);

        $this->repository->update($id, $formRequest->all());

        // $item->update($formRequest->all());

        activity()->performedOn($item)->log('updated');

        return $this->respondWithSuccess(___("save-success"));

    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCompany()
    {
        $id = auth()->user()->company_id;

        $item = $this->companyRepository->getById($id);

        // $item = auth()->user();
        // dd($item);

        $input = $this->request->all();


        $formRequest = $this->validateFormRequest();

        // dd($formRequest);

        $this->companyRepository->update($id, $formRequest->all());

        // $item->update($formRequest->all());

        activity()->performedOn($item)->log('updated');

        return $this->respondWithSuccess(___("save-success"));

    }


}
