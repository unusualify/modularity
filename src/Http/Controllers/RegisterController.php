<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Unusualify\Modularity\Events\ModularityUserRegistered;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Entities\User;
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
                'schema' => ($schema = $this->createFormSchema([
                    'name' => [
                        'type' => 'text',
                        'name' => 'name',
                        'label' => 'Name',
                        'default' => '',
                        'col' => [
                            'cols' => 6,
                            'lg' => 6,
                        ],
                        'rules' => [
                            ['min', 3],
                        ],
                    ],
                    'surname' => [
                        'type' => 'text',
                        'name' => 'surname',
                        'label' => 'Surname',
                        'default' => '',
                        'col' => [
                            'cols' => 6,
                            'lg' => 6,
                        ],
                        'rules' => [
                            ['min', 2],
                        ],
                    ],
                    'company' => [
                        'type' => 'text',
                        'name' => 'company',
                        'label' => 'Company',
                        'default' => '',
                        'col' => [
                            'cols' => 6,
                            'lg' => 6,
                        ],
                        'rules' => [
                            ['min', 2],
                        ],
                    ],
                    'email' => [
                        'type' => 'text',
                        'name' => 'email',
                        'label' => ___('authentication.email'),
                        'default' => '',
                        'col' => [
                            'cols' => 6,
                            'lg' => 6,
                        ],
                        'rules' => [
                            ['email'],
                        ],
                    ],
                    'password' => [
                        'type' => 'password',
                        'name' => 'password',
                        'label' => 'Password',
                        'default' => '',
                        'appendInnerIcon' => '$non-visibility',
                        'slotHandlers' => [
                            'appendInner' => 'password',
                        ],
                        'col' => [
                            'cols' => 6,
                            'lg' => 6,
                        ],
                        'rules' => [
                            ['required', 'classic', null, null, 'Password is required'],
                            ['min', 8, 'Password must be at least 8 characters'],
                        ],

                    ],
                    're_password' => [
                        'type' => 'password',
                        'name' => 're-password',
                        'label' => 'Repeat Password',
                        'default' => '',
                        'appendInnerIcon' => '$non-visibility',
                        'slotHandlers' => [
                            'appendInner' => 'password',
                        ],
                        'col' => [
                            'cols' => 6,
                            'lg' => 6,
                        ],
                        'rules' => [
                            ['required', 'classic',null, null, 'Confirm Password'],
                        ],
                    ],
                    'tos' => [
                        'type' => 'checkbox',
                        'name' => 'tos',
                        'label' => __('authentication.tos'),
                        'default' => '',
                        'col' => [
                            'cols' => 12,
                            'lg' => 12,
                        ],
                    ],
                ])),

                'actionUrl' => route(Route::hasAdmin('register')),
                'buttonText' => 'authentication.register',
                'formClass' => 'py-6',
                'no-default-form-padding' => true,
            ],
            'formSlots' => [
                'bottom' => [
                    'tag' => 'v-sheet',
                    'attributes' => [
                        'class' => 'd-flex pb-5 justify-space-between w-100 text-black my-5',
                    ],
                    'elements' => [
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.have-an-account'),
                            'attributes' => [
                                'variant' => 'text',
                                'href' => route(Route::hasAdmin('login.form')),
                                'class' => 'v-col-5 justify-content-start',
                                'color' => 'grey-lighten-1',
                                'density' => 'default',

                            ],
                        ],
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.register'),
                            'attributes' => [
                                'variant' => 'elevated',
                                'href' => '',
                                'class' => 'v-col-5',
                                'type' => 'submit',
                                'density' => 'default',

                            ],
                        ],
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
                        [
                            'tag' => 'v-btn',
                            'elements' => ___('authentication.sign-in-apple'),
                            'attributes' => [
                                'variant' => 'outlined',
                                'href' => route(Route::hasAdmin('login.form')),
                                'class' => 'my-2 custom-auth-button',
                                'color' => 'grey-lighten-1',
                                'density' => 'default',

                            ],
                            'slots' => [
                                'prepend' => [
                                    'tag' => 'ue-svg-icon',
                                    'attributes' => [
                                        'symbol' => 'apple',
                                        'width' => '16',
                                        'height' => '16',

                                    ],
                                ],
                            ],
                        ],

                    ],

                ],
            ],
            // 'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
            //     return [ $item['name'] => $item['default'] ?? ''];
            //     $carry[$key] = $item->default ?? '';
            // })->toArray(),

        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // dd(
        //     trans('validation.unique'),
        //     __('validation.unique'),
        //     ___('validation.unique'),
        // );
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

        $user = Company::create()->users()->create([
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
        return [
            'name' => ['required', 'string', 'max:255'],
            //Surname is not mandatory.
            //'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admin_users,email'],
            'password' => ['required', 'string', 'min:8'],
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

    // public function store(Request $request)
    // {
    //     dd(
    //         $request->all()
    //     );
    //     $this->validate($request, [
    //         // 'name' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required'
    //     ]);

    //     $user = User::create($request->only(['name', 'email', 'password']));

    //     // auth()->login($user);

    //     return redirect()->to('login.form');
    // }
}
