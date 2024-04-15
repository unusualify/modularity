<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Entities\User;
use Modules\SystemUser\Repositories\CompanyRepository;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularity\Services\View\UComponent;
use Unusualify\Modularity\Services\View\UWrapper;

class ProfileController extends BaseController
{

    protected $namespace = 'Modules\SystemUser';

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

        $user = auth()->user();

        $userSchema = $this->createFormSchema( getFormDraft('user') );
        $userFields = $this->userRepository->getFormFields($user, $userSchema);

        $userPasswordSchema = $this->createFormSchema( getFormDraft('user_password') );
        $userPasswordFields = $this->userRepository->getFormFields($user, $userPasswordSchema, true);

        $sectionFields = [
            [
                UComponent::makeUeForm()
                    ->setAttributes([
                        'class' => 'mb-theme',
                        'title' => ___('Personal Information'),
                        'buttonText' => 'update',
                        'hasSubmit' => true,
                        'stickyButton' => false,

                        'modelValue' => $userFields,
                        'schema' => $userSchema,
                        'defaultItem' => collect($userSchema)->mapWithKeys(function($item, $key){
                            return [ $item['name'] => $item['default'] ?? ''];
                            $carry[$key] = $item->default ?? '';
                        })->toArray(),

                        'actionUrl' => $this->getModuleRoute(id: $userFields['id'], action: 'update', singleton: true),
                    ]),
                UComponent::makeUeForm()
                    ->setAttributes([
                        'title' => ___('Update Password'),
                        'buttonText' => 'update',
                        'hasSubmit' => true,
                        'stickyButton' => false,

                        'schema' => $userPasswordSchema,
                        'modelValue' => $userPasswordFields,
                        'defaultItem' => collect($userPasswordSchema)->mapWithKeys(function($item, $key){
                            return [ $item['name'] => $item['default'] ?? ''];
                            $carry[$key] = $item->default ?? '';
                        })->toArray(),
                        'actionUrl' => $this->getModuleRoute(id: $userPasswordFields['id'], action: 'update',  singleton: true),
                    ])
            ]
        ];

        if($user->company){
            $companySchema = $this->createFormSchema([
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
            $companySchema = $this->createFormSchema( getFormDraft('company') );
            $companyFields = $this->companyRepository->getFormFields(auth()->user()->company, $companySchema);

            $companyFields['country_id'] = 1;
            $companyFields['city_id'] = 1;
            $companyFields['district_id'] = 2;

            $sectionFields[] = [
                'parent_attributes' => ['class' => 'd-flex'],
                'content' => [
                    UComponent::makeUeForm()
                        ->setAttributes([
                            'title' => ___('Company Information'),
                            // 'editable' => true,
                            'buttonText' => 'update',
                            'hasSubmit' => true,
                            'stickyButton' => false,

                            'modelValue' => $companyFields,
                            'schema' => $companySchema,
                            'defaultItem' => collect($companySchema)->mapWithKeys(function($item, $key){
                                return [ $item['name'] => $item['default'] ?? ''];
                                $carry[$key] = $item->default ?? '';
                            })->toArray(),
                            'actionUrl' => route(Route::hasAdmin('profile.company')),
                        ])
                ]
            ];
        }
        $data = [];

        // dd($sectionFields);

        $data['elements'] = [
            UWrapper::makeGridSection($sectionFields)
        ];

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

        $formRequest = $this->validateFormRequest(
            getFormDraft('user') + getFormDraft('user_password')
        );

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
        // dd($user);
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
