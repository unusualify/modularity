<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Events\ModularityUserRegistered;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Services\MessageStage;

class RegisterController extends Controller
{
    use ManageUtilities;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('modularity.guest');
    }

    public function showForm()
    {
        return view(modularityBaseKey() . '::auth.register', [
            'attributes' => [
                'bannerDescription' => ___('authentication.banner-description'),
                'bannerSubDescription' => Lang::has('authentication.banner-sub-description') ? ___('authentication.banner-sub-description') : null,
                'redirectButtonText' => ___('authentication.redirect-button-text'),
                'redirectUrl' => Route::has(modularityConfig('auth_guest_route'))
                    ? route(modularityConfig('auth_guest_route'))
                    : null,
            ],
            'formAttributes' => [
                // 'modelValue' => new User(['name', 'surname', 'email', 'password']),
                'title' => [
                    'text' => __('authentication.create-an-account'),
                    'tag' => 'h1',
                    'color' => 'primary',
                    'type' => 'h5',
                    'weight' => 'bold',
                    'transform' => '',
                    'align' => 'center',
                    'justify' => 'center',
                ],
                'schema' => $this->createFormSchema(getFormDraft('register_form')),
                'actionUrl' => route(Route::hasAdmin('register')),
                'buttonText' => 'authentication.register',
                'formClass' => 'py-6',
                'no-default-form-padding' => true,
                'hasSubmit' => true,
            ],
            'formSlots' => [
                'options' => [
                    'tag' => 'v-btn',
                    'elements' => __('authentication.have-an-account'),
                    'attributes' => [
                        'variant' => 'text',
                        'href' => route(Route::hasAdmin('login.form')),
                        'class' => '',
                        'color' => 'grey-lighten-1',
                        'density' => 'default',
                    ],
                ],
            ],
            'slots' => [
                'bottom' => [
                    'tag' => 'v-sheet',
                    'attributes' => [
                        'class' => 'd-flex pb-5 justify-end flex-column w-100 text-black',
                    ],
                    'elements' => [
                        [
                            'tag' => 'v-btn',
                            'elements' => ___('authentication.sign-in-google'),
                            'attributes' => [
                                'variant' => 'outlined',
                                'href' => route(Route::hasAdmin('login.form')),
                                'class' => 'mt-5 mb-2 custom-auth-button',
                                'color' => 'grey-lighten-1',
                                'density' => 'default',

                            ],
                            'slots' => [
                                'prepend' => [
                                    'tag' => 'ue-svg-icon',
                                    'attributes' => [
                                        'symbol' => 'google',
                                        'width' => '16',
                                        'height' => '16',
                                    ],
                                ],
                            ],
                        ],
                        // [
                        //     'tag' => 'v-btn',
                        //     'elements' => ___('authentication.sign-in-apple'),
                        //     'attributes' => [
                        //         'variant' => 'outlined',
                        //         'href' => route(Route::hasAdmin('login.form')),
                        //         'class' => 'my-2 custom-auth-button',
                        //         'color' => 'grey-lighten-1',
                        //         'density' => 'default',

                        //     ],
                        //     'slots' => [
                        //         'prepend' => [
                        //             'tag' => 'ue-svg-icon',
                        //             'attributes' => [
                        //                 'symbol' => 'apple',
                        //                 'width' => '16',
                        //                 'height' => '16',

                        //             ],
                        //         ],
                        //     ],
                        // ],

                    ],
                ],
            ],
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, $this->rules());
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\Models\User
     */
    protected function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return $request->wantsJson()
                ? new JsonResponse([
                    'errors' => $validator->errors(),
                    'message' => $validator->messages()->first(),
                    'variant' => MessageStage::WARNING,
                ], 200)
                : $request->validate($this->rules());

            return $res;
        }

        $user = Company::create([
            'name' => $request['company'] ?? '',
            'spread_payload' => [
                'is_personal' => $request['company'] ? false : true,
            ],
        ])->users()->create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);

        event(new ModularityUserRegistered($user));

        $user->assignRole('client-manager');

        return $request->wantsJson()
            ? new JsonResponse([
                'redirector' => route(Route::hasAdmin('register.success')),
            ], 200)
            : $this->sendLoginResponse($request);
    }

    public function rules()
    {
        $usersTable = modularityConfig('tables.users', 'um_users');

        return [
            'name' => ['required', 'string', 'max:255'],
            // Surname is not mandatory.
            'surname' => ['required', 'string', 'max:255'],
            // 'company' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . $usersTable . ',email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'tos' => ['required', 'boolean'],
        ];
    }

    public function success()
    {
        return view(modularityBaseKey() . '::auth.success', [
            'taskState' => [
                'status' => 'success',
                'title' => __('authentication.register-title'),
                'description' => __('authentication.register-description'),
                'button_text' => __('authentication.register-button_text'),
                'button_url' => route('admin.login'),
            ],
        ]);
    }
}
