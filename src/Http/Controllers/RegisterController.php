<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Entities\User;
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
        return view('unusual::auth.register', [
            'formAttributes' => [
                'hasSubmit' => true,
                // 'modelValue' => new User(['name', 'surname', 'email', 'password']),
                'schema' => ($schema = $this->getFormSchema([
                    'name' => [
                        "type" => "text",
                        "name" => "name",
                        "label" => ___('authentication.name'),
                        "default" => "",
                        'col' => [
                            'cols' => 12,
                            'lg' => 12
                        ],
                        'rules' => [
                            ['min', 3]
                        ]
                    ],
                    'surname' => [
                        "type" => "text",
                        "name" => "surname",
                        "label" => ___('authentication.surname'),
                        "default" => "",
                        'col' => [
                            'cols' => 12,
                            'lg' => 12
                        ],
                        'rules' => [
                            ['min', 2]
                        ]
                    ],
                    'email' => [
                        "type" => "text",
                        "name" => "email",
                        "label" => ___('authentication.email'),
                        "default" => "",
                        'col' => [
                            'cols' => 12,
                            'lg' => 12
                        ],
                        'rules' => [
                            ['email']
                        ]
                    ],
                    'password' => [
                        "type" => "password",
                        "name" => "password",
                        "label" => ___('authentication.password'),
                        "default" => "",
                        "appendInnerIcon" => '$non-visibility',
                        "slotHandlers" => [
                            'appendInner' => 'password',
                        ],
                        'col' => [
                            'cols' => 12,
                            'lg' => 12
                        ]
                    ],
                ])),

                'actionUrl' => route('register'),
                'buttonText' => 'authentication.register',
                'formClass' => 'px-5',
            ],
            'slots' => [
                'bottom' => [
                    'tag' => 'v-sheet',
                    'attributes' => [
                        'class' => 'd-flex pb-5 mx-8 justify-end',
                    ],
                    'elements' => [
                        [
                            "tag" => "v-btn",
                            'elements' => ___('authentication.back-to-login'),
                            "attributes" => [
                                'variant' => 'plain',
                                'href' => route('login.form'),
                                'class' => ''
                            ],
                        ]
                    ]

                ]
            ]
            // 'defaultItem' => collect($schema)->mapWithKeys(function($item, $key){
            //     return [ $item['name'] => $item['default'] ?? ''];
            //     $carry[$key] = $item->default ?? '';
            // })->toArray(),

        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
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
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if($validator->fails()){
            return $request->wantsJson()
                ? new JsonResponse([
                    'errors' => $validator->errors(),
                    'message' => $validator->messages()->first(),
                    'variant' => 'warning',
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
        ?   new JsonResponse([
                'redirector' => route('login.form')
            ], 200)
        :   $this->sendLoginResponse($request);
    }

    public function rules() {
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













