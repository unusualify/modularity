<?php

namespace Unusualify\Modularous\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use Modules\SystemUser\Http\Requests\CompanyRequest;
use Modules\SystemUser\Repositories\CompanyRepository;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularous\Services\View\UComponent;
use Unusualify\Modularous\Services\View\UWrapper;

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
        Application $app,
        Request $request,
        protected UserRepository $userRepository,
        protected CompanyRepository $companyRepository,
    ) {

        parent::__construct(
            $app,
            $request
        );

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

        if ($emailVerified) {
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
            'refreshOnSaved' => true,
            'forceRefresh' => true,
            'schema' => $userSchema,
            'defaultItem' => collect($userSchema)->mapWithKeys(function ($item, $key) {
                return [$item['name'] => $item['default'] ?? ''];
                $carry[$key] = $item->default ?? '';
            })->toArray(),

            'actionUrl' => $this->getModuleRoute(id: $userFields['id'], action: 'update', singleton: true),
        ]);

        if (! $emailVerified) {
            $verifyButtonAttributes['href'] = route(Route::hasAdmin('admin.verification.send'));
            $verifyButtonAttributes['readonly'] = false;
            $verifyButtonAttributes['color'] = 'warning';
            $verifyButtonAttributes['variant'] = 'elevated';
            $verifyButtonAttributes['appendIcon'] = 'mdi-email-outline';

            $verifyEmailButton = UComponent::makeVBtnPrimary()
                ->setAttributes($verifyButtonAttributes)
                ->setElements(! $emailVerified ? __('Verify Email') : __('Verified'))
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
                        'clearOnSaved' => true,

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

            $lockCompanyEdit = config('modularous.lock_company_edit') && $user->validCompany;
            if ($lockCompanyEdit) {
                $companySchema = array_map(function ($item) {
                    $item['noSubmit'] = false;
                    $item['clearable'] = false;
                    $item['readonly'] = true;

                    return $item;
                }, $companySchema);
            }

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
                            'hasSubmit' => ! $lockCompanyEdit,
                            'stickyButton' => false,

                            'modelValue' => $companyFields,
                            'schema' => $companySchema,
                            'defaultItem' => collect($companySchema)->mapWithKeys(function ($item, $key) {
                                return [$item['name'] => $item['default'] ?? ''];
                                $carry[$key] = $item->default ?? '';
                            })->toArray(),
                            'refreshOnSaved' => true,
                            'forceRefresh' => true,
                            'actionUrl' => $lockCompanyEdit ? null : route(Route::hasAdmin('profile.company')),
                        ]),
                ],
            ];
        }
        $data = [];

        // dd($sectionFields);

        $elements = [
            UWrapper::makeGridSection($sectionFields, rowAttributes: ['class' => 'h-100'], colAttributes: ['class' => 'd-flex flex-column ga-6']),
        ];
        // dd($data);
        $endpoints = $this->getUrls();

        $pageTitle = __('Profile Settings') . ' - ' . Modularous::pageTitle();
        $headerTitle = __('My Profile');

        if ($this->shouldUseInertia()) {
            return $this->renderInertiaProfile(compact('elements', 'endpoints', 'pageTitle', 'headerTitle'));
        }

        $view = "$this->baseKey::layouts.profile";

        return View::make($view, compact('elements', 'endpoints', 'pageTitle', 'headerTitle'));
    }

    protected function renderInertiaProfile(array $data)
    {
        $this->shareInertiaStoreVariables();

        return Inertia::render('Profile', [
            'elements' => $data['elements'] ?? [],
            'endpoints' => $data['endpoints'] ?? new \StdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return JsonResponse
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

        $response = [];

        activity()->performedOn($item)->log('updated');

        return $this->respondWithSuccess(__('messages.profile-update-success'), $response);

    }

    public function display()
    {
        $user = auth()->user();

        $data = get_user_profile($user);

        if ($this->request->ajax()) {
            return response()->json($data);
        }

        return view('modularous::layouts.profile', $data);
    }

    /**
     * @param int $id
     * @param int|null $submoduleId
     * @return JsonResponse
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
