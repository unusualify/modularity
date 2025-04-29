<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Modules\SystemUser\Http\Requests\CompanyRequest;
use Modules\SystemUser\Repositories\CompanyRepository;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Services\MessageStage;
use Unusualify\Modularity\Services\View\UComponent;
use Unusualify\Modularity\Services\View\UWrapper;

class ProfileController extends BaseController
{
    use ManageUtilities;

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
    ) {

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

        $user = auth()->user();

        $userSchema = $this->createFormSchema(getFormDraft('user'));
        $userFields = $this->userRepository->getFormFields($user, $userSchema);

        $userPasswordSchema = $this->createFormSchema(getFormDraft('user_password'));
        $userPasswordFields = $this->userRepository->getFormFields($user, $userPasswordSchema, noSerialization: false);

        $personalForm = UComponent::makeUeForm();

        $verifyButtonAttributes = [
            'variant' => 'plain',
            'appendIcon' => 'mdi-check-circle-outline',
            'color' => 'success',
        ];

        $user = Auth::user();
        $emailVerified = $user->hasVerifiedEmail();

        if($emailVerified){
            // $userSchema['email']['appendInnerIcon'] = 'mdi-check-circle-outline';
            $userSchema['email']['slots']['append-inner'] = UComponent::makeVIcon()
                ->setAttributes([
                    'icon' => 'mdi-check-circle-outline',
                    'color' => 'success',
                ])
                ->render();
        }

        $personalForm = $personalForm->setAttributes([
            'class' => '',
            // 'style' => 'min-height: 480px',
            // 'fillHeight' => true,
            // 'pushButtonToBottom' => true,
            'formClass' => 'elevation-2 rounded h-100',

            'title' => [
                'text' => __('User Profile'),
                // 'tag' => 'p',
                'type' => 'h6',
                'weight' => 'bold',
                'transform' => '',
                'align' => 'center',
                'justify' => 'start',
                'color' => 'primary',
                // 'margin' => 'b-11',
            ],

            'buttonText' => 'Update',
            'hasSubmit' => true,
            'stickyButton' => false,
            'modelValue' => $userFields,
            'schema' => $userSchema,
            'defaultItem' => collect($userSchema)->mapWithKeys(function ($item, $key) {
                return [$item['name'] => $item['default'] ?? ''];
                $carry[$key] = $item->default ?? '';
            })->toArray(),

            'actionUrl' => $this->getModuleRoute(id: $userFields['id'], action: 'update', singleton: true),
        ]);

        if(!$emailVerified){
            $verifyButtonAttributes['href'] = route(Route::hasAdmin('admin.verification.send'));
            $verifyButtonAttributes['readonly'] = false;
            $verifyButtonAttributes['color'] = 'warning';
            $verifyButtonAttributes['variant'] = 'elevated';
            $verifyButtonAttributes['appendIcon'] = 'mdi-email-outline';

            $verifyEmailButton = UComponent::makeVBtnPrimary()
                ->setAttributes($verifyButtonAttributes)
                ->setElements(!$emailVerified ? __('Verify Email') : __('Verified'))
                ->render();

            $personalForm = $personalForm->addSlot('options', $verifyEmailButton);
        }

        $sectionFields = [
            [
                $personalForm,
                UComponent::makeUeForm()
                    ->setAttributes([
                        'class' => 'h-50',
                        'fillHeight' => true,
                        'pushButtonToBottom' => true,
                        'formClass' => 'elevation-2 rounded',

                        'title' => [
                            'text' => __('Security'),
                            // 'tag' => 'p',
                            'type' => 'h6',
                            'weight' => 'bold',
                            'transform' => '',
                            'align' => 'center',
                            'justify' => 'start',

                            'color' => 'primary',
                            // 'margin' => 'y-6',
                        ],

                        'buttonText' => 'Update',
                        'hasSubmit' => true,
                        'stickyButton' => false,

                        'schema' => $userPasswordSchema,
                        'modelValue' => $userPasswordFields,
                        'defaultItem' => collect($userPasswordSchema)->mapWithKeys(function ($item, $key) {
                            return [$item['name'] => $item['default'] ?? ''];
                            $carry[$key] = $item->default ?? '';
                        })->toArray(),
                        'actionUrl' => $this->getModuleRoute(id: $userPasswordFields['id'], action: 'update', singleton: true),
                    ]),
            ],
        ];

        if ($user->company) {
            $companySchema = $this->createFormSchema(getFormDraft('company'));
            $company = auth()->user()->company;
            $company = $this->companyRepository->getById($company->id);
            $companyFields = $this->companyRepository->getFormFields($company, $companySchema);

            $companyFields['country_id'] = 1;
            $companyFields['city_id'] = 1;
            $companyFields['district_id'] = 2;

            $sectionFields[] = [
                'content' => [
                    UComponent::makeUeForm()
                        ->setAttributes([
                            'class' => 'h-100',
                            'fillHeight' => true,
                            'pushButtonToBottom' => true,

                            'formClass' => 'elevation-2 rounded',
                            'title' => [
                                'text' => __('Billing Profile'),
                                'type' => 'h6',
                                'weight' => 'bold',
                                'transform' => '',
                                'align' => 'center',
                                'justify' => 'start',
                                'color' => 'primary',
                                // 'margin' => 'y-6',
                            ],
                            // 'editable' => true,
                            'buttonText' => 'Update',
                            'hasSubmit' => true,
                            'stickyButton' => false,

                            'modelValue' => $companyFields,
                            'schema' => $companySchema,
                            'defaultItem' => collect($companySchema)->mapWithKeys(function ($item, $key) {
                                return [$item['name'] => $item['default'] ?? ''];
                                $carry[$key] = $item->default ?? '';
                            })->toArray(),
                            'actionUrl' => route(Route::hasAdmin('profile.company')),
                        ]),
                ],
            ];
        }
        $data = [];

        // dd($sectionFields);

        $data['elements'] = [
            UWrapper::makeGridSection($sectionFields, rowAttributes: ['class' => 'h-100'], colAttributes: ['class' => 'd-flex flex-column ga-6']),
        ];
        // dd($data);
        $data['endpoints'] = $this->getUrls();

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

        $formRequest = $this->validateFormRequest(
            getFormDraft('user') + getFormDraft('user_password')
        );

        $schema = null;

        if (array_key_exists('avatar', $formRequest->all())) {
            $schema = getFormDraft('profile_shortcut');
        }

        $this->repository->update($id, $formRequest->all(), $schema);

        // $item->update($formRequest->all());

        activity()->performedOn($item)->log('updated');

        return $this->respondWithSuccess(___('messages.save-success'));

    }

    public function display()
    {
        $user = auth()->user();

        $data = get_user_profile($user);

        if ($this->request->ajax()) {
            return response()->json($data);
        }

        return view('modularity::layouts.profile', $data);
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCompany(CompanyRequest $request)
    {
        // dd($user);
        $id = auth()->user()->company_id;

        $item = $this->companyRepository->getById($id);

        $this->companyRepository->update($id, $request->all());

        activity()->performedOn($item)->log('updated');

        return $this->respondWithSuccess(___('messages.save-success'));

    }
}
