<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Services\MessageStage;
use Unusualify\Modularity\Traits\ManageUtilities;

class RegisterController extends Controller
{
    use ManageUtilities;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('unusual_guest');
    }

    public function showLoginForm()
    {
        return view(unusualBaseKey() . '::auth.register', [
            'formAttributes' => [
                // 'modelValue' => new User(['name', 'surname', 'email', 'password']),
                'title' => [
                    'text' => __('authentication.create-an-account'),
                    'tag' => 'h1',
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
                        'label' => ___('authentication.name'),
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
                        'label' => ___('authentication.surname'),
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
                        'label' => ___('authentication.company'),
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
                        'label' => ___('authentication.password'),
                        'default' => '',
                        'appendInnerIcon' => '$non-visibility',
                        'slotHandlers' => [
                            'appendInner' => 'password',
                        ],
                        'col' => [
                            'cols' => 6,
                            'lg' => 6,
                        ],
                    ],
                    're_password' => [
                        'type' => 'password',
                        'name' => 're-password',
                        'label' => ___('authentication.repeat_password'),
                        'default' => '',
                        'appendInnerIcon' => '$non-visibility',
                        'slotHandlers' => [
                            'appendInner' => 'password',
                        ],
                        'col' => [
                            'cols' => 6,
                            'lg' => 6,
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
                'formClass' => 'mw-lg-24em',
            ],
            'formSlots' => [
                'bottom' => [
                    'tag' => 'v-sheet',
                    'attributes' => [
                        'class' => 'd-flex pb-5 justify-space-around w-100 text-black my-5',
                    ],
                    'elements' => [
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.have-an-account'),
                            'attributes' => [
                                'variant' => 'text',
                                'href' => '',
                                'class' => 'v-col-5',
                            ],
                        ],
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.login'),
                            'attributes' => [
                                'variant' => 'elevated',
                                'href' => '',
                                'class' => 'v-col-5',
                                'type' => 'submit',

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
                                'class' => 'my-5 custom-auth-button',
                            ],
                            'slots' => [
                                'prepend' => [
                                    'tag' => 'ue-svg-icon',
                                    'attributes' => [
                                        'symbol' => 'google',
                                        'width' => '25',
                                        'height' => '25',
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
                                'class' => 'my-5 custom-auth-button',
                            ],
                            'slots' => [
                                'prepend' => [
                                    'tag' => 'ue-svg-icon',
                                    'attributes' => [
                                        'symbol' => 'apple',
                                        'width' => '25',
                                        'height' => '25',
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
        }

        $user = Company::create()->users()->create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);
        // Permission::all()->each(function($p){
        //     $p->update([
        //         'guard_name' => 'unusual_users'
        //     ]);
        // });
        $user->assignRole('client-manager');

        return $request->wantsJson()
        ? new JsonResponse([
            'redirector' => route(Route::hasAdmin('login.form')),
        ], 200)
        : $this->sendLoginResponse($request);
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ];
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
