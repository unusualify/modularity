<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Config\Repository as Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Redirector;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Facades\Modularity;
use Illuminate\Http\Request;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Http\JsonResponse;
use Unusualify\Modularity\Services\MessageStage;
use Illuminate\Support\Facades\Validator;
use Unusualify\Modularity\Facades\Register;
use Unusualify\Modularity\Http\Controllers\Traits\Utilities\CreateVerifiedEmailAccount;

class CompleteRegisterController extends Controller{

    use ManageUtilities, CreateVerifiedEmailAccount;

    /**
     * @var Redirector
     */
    protected $redirector;

    /**
     * @var Config
     */
    protected $config;

    /**
     * The path the user should be redirected to.
     *
     * @var string
     */
    protected $redirectTo;

    /**
     * @var ViewFactory
     */
    protected $viewFactory;

    public function __construct(Config $config, Redirector $redirector, ViewFactory $viewFactory)
    {
        parent::__construct();

        $this->redirector = $redirector;
        $this->viewFactory = $viewFactory;
        $this->config = $config;

        $this->redirectTo = $this->config->get(modularityBaseKey() . '.auth_login_redirect_path', '/');
        $this->middleware('modularity.guest');

    }

    protected function guard(): \Illuminate\Contracts\Auth\StatefulGuard
    {
        return Auth::guard(Modularity::getAuthGuardName());
    }

    public function broker()
    {
        return Register::broker();
    }

    public function showCompleteRegisterForm(Request $request, $token = null)
    {
        $token = $request->route()->parameter('token');
        $email = $request->email;

        if($email && $token && Register::broker('register_verified_users')->emailTokenExists(email: $email, token: $token)){
            return $this->viewFactory->make(modularityBaseKey() . '::auth.register')->with ([
                'formAttributes' => [
                    'title' => [
                        'text' => __('authentication.complete-registration'),
                        'tag' => 'h1',
                        'color' => 'primary',
                        'type' => 'h5',
                        'weight' => 'bold',
                        'align' => 'center',
                        'justify' => 'center',
                    ],
                    'modelValue' => [
                        'email' => $email,
                        'token' => $token,
                    ],
                    'schema' => ($schema = $this->createFormSchema([
                        'email' => [
                            'type' => 'text',
                            'name' => 'email',
                            'label' => 'Email',
                            'default' => $email,
                            'col' => [
                                'cols' => 6,
                                'lg' => 6,
                            ],
                            'rules' => [
                                ['email'],
                            ],
                            'readonly' => true,
                            'clearable' => false,
                        ],
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
                                ['min', 8],
                            ],
                        ],
                        'password_confirmation' => [
                                'type' => 'password',
                                'name' => 'password_confirmation',
                                'label' => ___('authentication.password-confirmation'),
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
                        'token' => [
                            'type' => 'hidden',
                            'name' => 'token',
                        ],

                    ])),

                    'actionUrl' => route(Route::hasAdmin('complete.register')),
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
                ]
            ]);
        }

        return $this->redirector->to(route(Route::hasAdmin('pre-register.email-form')))->withErrors([
            'token' => 'Your email verification token has expired or could not be found, please retry.',
        ]);
    }

    public function completeRegister(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules(), $this->validationErrorMessages());

        if ($validator->fails()) {
            return $request->wantsJson()
            ? new JsonResponse([
                'errors' => $validator->errors(),
                'message' => $validator->messages()->first(),
                'variant' => MessageStage::WARNING,
            ], 200)
            : $request->validate($this->rules(), $this->validationErrorMessages());
        }


        $response = $this->broker()->register(
            $this->credentials($request), function (array $credentials) {
                $this->registerEmail($credentials);
            }
        );


        return $response == Register::VERIFIED_EMAIL_REGISTER
                    ? $this->sendRegisterResponse($request, $response)
                    : $this->sendRegisterFailedResponse($request, $response);

    }

    protected function sendRegisterResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                'message' => trans($response),
                'variant' => MessageStage::SUCCESS,
                'redirector' => $this->redirectPath(),
            ], 200);
        }

        return redirect($this->redirectPath())
            ->with('status', trans($response));
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param string $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendRegisterFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                'email' => [trans($response)],
                'message' => trans($response),
                'variant' => MessageStage::WARNING,
            ], 200);
            // throw ValidationException::withMessages([
            //     'email' => [trans($response)],
            // ]);
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)]);
    }
}


