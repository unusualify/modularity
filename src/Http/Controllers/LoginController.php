<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Factory as ViewFactory;
use Laravel\Socialite\Facades\Socialite;
use Modules\SystemUser\Repositories\UserRepository;
use PragmaRX\Google2FA\Google2FA;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Http\Requests\OauthRequest;
use Unusualify\Modularity\Services\MessageStage;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers, ManageUtilities;

    /**
     * @var AuthManager
     */
    protected $authManager;

    /**
     * @var Encrypter
     */
    protected $encrypter;

    /**
     * @var Redirector
     */
    protected $redirector;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ViewFactory
     */
    protected $viewFactory;

    /**
     * The path the user should be redirected to.
     *
     * @var string
     */
    protected $redirectTo;

    public function __construct(
        Config $config,
        AuthManager $authManager,
        Encrypter $encrypter,
        Redirector $redirector,
        ViewFactory $viewFactory
    ) {
        parent::__construct();

        $this->authManager = $authManager;
        $this->encrypter = $encrypter;
        $this->redirector = $redirector;
        $this->viewFactory = $viewFactory;
        $this->config = $config;

        $this->middleware('modularity.guest', ['except' => 'logout']);
        $this->redirectTo = modularityConfig('auth_login_redirect_path', '/');
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard
     */
    protected function guard()
    {
        return $this->authManager->guard(Modularity::getAuthGuardName());
    }

    /**
     * @return \Illuminate\View\View
     */
    public function showForm()
    {
        return $this->viewFactory->make(modularityBaseKey() . '::auth.login', [
            'attributes' => [
                'bannerDescription' => ___('authentication.banner-description'),
                'bannerSubDescription' => Lang::has('authentication.banner-sub-description') ? ___('authentication.banner-sub-description') : null,
                'redirectButtonText' => ___('authentication.redirect-button-text'),
                'redirectUrl' => Route::has(modularityConfig('auth_guest_route'))
                    ? route(modularityConfig('auth_guest_route'))
                    : null,
            ],
            'formAttributes' => [
                // 'hasSubmit' => true,

                // 'modelValue' => new User(['name', 'surname', 'email', 'password']),
                'title' => [
                    'text' => __('authentication.login-title'),
                    'tag' => 'h1',
                    'color' => 'primary',
                    'type' => 'h5',
                    'weight' => 'bold',
                    'transform' => '',
                    'align' => 'center',
                    'justify' => 'center',
                ],
                'schema' => ($schema = $this->createFormSchema(getFormDraft('login_form'))),
                'actionUrl' => route(Route::hasAdmin('login')),
                'buttonText' => __('authentication.sign-in'),
                'formClass' => 'py-6',
                'no-default-form-padding' => true,
                'hasSubmit' => true,
            ],
            'formSlots' => [
                'options' => [
                    'tag' => 'v-btn',
                    'elements' => __('authentication.forgot-password'),
                    'attributes' => [
                        'variant' => 'plain',
                        'href' => route('admin.password.reset.link'),
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
                                'href' => route('admin.login.provider', ['provider' => 'google']),
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
                        //         'href' => route('admin.login.provider', ['provider' => 'github']),
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
                        [
                            'tag' => 'v-btn',
                            'elements' => ___('authentication.create-an-account'),
                            'attributes' => [
                                'variant' => 'outlined',
                                'href' => route('admin.register.form'),
                                'class' => 'my-2 custom-auth-button',
                                'color' => 'grey-lighten-1',
                                'density' => 'default',

                            ],

                        ],

                    ],
                ],
            ],
        ]);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function showLogin2FaForm()
    {
        return $this->viewFactory->make(modularityBaseKey() . '::auth.2fa');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $this->redirector->to(route(Route::hasAdmin('login.form')));
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        return $this->afterAuthentication($request, $user);
    }

    private function afterAuthentication(Request $request, $user)
    {
        // dd('here',$user->google_2fa_secret && $user->google_2fa_enabled);

        if ($user->google_2fa_secret && $user->google_2fa_enabled) {
            $this->guard()->logout();

            $request->session()->put('2fa:user:id', $user->id);

            return $request->wantsJson()
                ? new JsonResponse([
                    'redirector' => $this->redirector->to(route(Route::hasAdmin('admin.login-2fa.form')))->getTargetUrl(),
                ])
                : $this->redirector->to(route(Route::hasAdmin('admin.login-2fa.form')));
        }

        $previousRouteName = previous_route_name();

        $body = [
            'variant' => MessageStage::SUCCESS,
            'timeout' => 1500,
            'message' => __('authentication.login-success-message'),
        ];

        if (in_array($previousRouteName, ['admin.login.form', 'admin.login.oauth.showPasswordForm'])) {
            // 'redirector' => $this->redirector->intended($this->redirectPath())->getTargetUrl() . '?status=success',
            $body['redirector'] = redirect()->intended($this->redirectTo)->getTargetUrl();
        }

        if ($request->has('_timezone')) {
            session()->put('timezone', $request->get('_timezone'));
        }

        return $request->wantsJson()
            ? new JsonResponse($body, 200)
            : $this->redirector->intended($this->redirectPath());

    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function login2Fa(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');

        $user = User::findOrFail($userId);

        $valid = (new Google2FA)->verifyKey(
            $user->google_2fa_secret,
            $request->input('verify-code')
        );

        if ($valid) {
            $this->authManager->guard(Modularity::getAuthGuardName())->loginUsingId($userId);

            $request->session()->pull('2fa:user:id');

            return $this->redirector->intended($this->redirectTo);
        }

        return $this->redirector->to(route(Route::hasAdmin('admin.login-2fa.form')))->withErrors([
            'error' => 'Your one time password is invalid.',
        ]);
    }

    /**
     * @param string $provider Socialite provider
     * @return \Illuminate\Http\RedirectResponse
     */
    // redirectToProvider($provider, OauthRequest $request)
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)
            // ->scopes($this->config->get(modularityBaseKey() . '.oauth.' . $provider . '.scopes', []))
            // ->with($this->config->get(modularityBaseKey() . '.oauth.' . $provider . '.with', []))
            ->redirect();
    }

    /**
     * @param string $provider Socialite provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($provider, OauthRequest $request)
    {

        $oauthUser = Socialite::driver($provider)->user();
        $repository = App::make(UserRepository::class);

        // If the user with that email exists
        if ($user = $repository->oauthUser($oauthUser)) {

            // If that provider has been linked
            if ($repository->oauthIsUserLinked($oauthUser, $provider)) {
                $user = $repository->oauthUpdateProvider($oauthUser, $provider);

                // Login and redirect
                $this->authManager->guard(Modularity::getAuthGuardName())->login($user);

                return $this->afterAuthentication($request, $user);
            } else {
                if ($user->password) {
                    // If the user has a password then redirect to a form to ask for it
                    // before linking a provider to that email
                    $request->session()->put('oauth:user_id', $user->id);
                    $request->session()->put('oauth:user', $oauthUser);
                    $request->session()->put('oauth:provider', $provider);

                    return $this->redirector->to(route(Route::hasAdmin('admin.login.oauth.showPasswordForm')));
                } else {
                    $user->linkProvider($oauthUser, $provider);

                    // Login and redirect
                    $this->authManager->guard(Modularity::getAuthGuardName())->login($user);

                    return $this->afterAuthentication($request, $user);
                }
            }
        } else {
            // If the user doesn't exist, create it
            $user = $repository->oauthCreateUser($oauthUser);
            $user->linkProvider($oauthUser, $provider);

            // Login and redirect
            $this->authManager->guard(Modularity::getAuthGuardName())->login($user);

            return $this->redirector->intended($this->redirectTo);
        }
    }

    /**
     * @return \Illuminate\View\View
     */
    public function showPasswordForm(Request $request)
    {
        $userId = $request->session()->get('oauth:user_id');
        $user = User::findOrFail($userId);

        return $this->viewFactory->make(modularityBaseKey() . '::auth.login', [
            'formAttributes' => [
                // 'hasSubmit' => true,
                'title' => [
                    'text' => __('authentication.confirm-provider', ['provider' => $request->session()->get('oauth:provider')]),
                    'tag' => 'h1',
                    'color' => 'primary',
                    'type' => 'h5',
                    'weight' => 'bold',
                    'transform' => '',
                    'align' => 'center',
                    'justify' => 'center',
                ],
                'schema' => ($schema = $this->createFormSchema([
                    'email' => [
                        'type' => 'text',
                        'name' => 'email',
                        'label' => ___('authentication.email'),
                        'hint' => 'enter @example.com',
                        'default' => '',
                        'col' => [
                            'lg' => 12,
                        ],
                        'rules' => [
                            ['email'],
                        ],
                        'readonly' => true,
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
                            'lg' => 12,
                        ],
                        'rules' => [
                            // ['password'],
                        ],
                    ],
                ])),

                'modelValue' => [
                    'email' => $user->email,
                    'password' => '',
                ],

                'actionUrl' => route(Route::hasAdmin('login.oauth.linkProvider')),
                'buttonText' => __('authentication.sign-in'),
                'formClass' => 'py-6',
                'no-default-form-padding' => true,
            ],
            'attributes' => [
                'noDivider' => true,
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
                            'elements' => __('authentication.sign-in'),
                            'attributes' => [
                                'variant' => 'elevated',
                                'class' => 'v-col-5 mx-auto',
                                'type' => 'submit',
                                'density' => 'default',
                                'block' => true,
                            ],

                        ],
                    ],
                ],
            ],
            // 'provider' => $request->session()->get('oauth:provider'),
        ]);
    }

    /**
     * @param string $provider Socialite provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function linkProvider(Request $request)
    {
        // If provided credentials are correct
        if ($this->attemptLogin($request)) {
            // Load the user
            $userId = $request->session()->get('oauth:user_id');
            $user = User::findOrFail($userId);

            // Link the provider and login
            $user->linkProvider($request->session()->get('oauth:user'), $request->session()->get('oauth:provider'));
            $this->authManager->guard(Modularity::getAuthGuardName())->login($user);

            // Remove session variables
            $request->session()->forget('oauth:user_id');
            $request->session()->forget('oauth:user');
            $request->session()->forget('oauth:provider');

            // Login and redirect
            return $this->afterAuthentication($request, $user);
        } else {
            throw ValidationException::withMessages([
                'password' => [trans('auth.failed')],
            ]);
        }
    }

    public function redirectTo()
    {
        return route(Route::hasAdmin('dashboard'));
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                $this->username() => [trans('auth.failed')],
                'message' => __('auth.failed'),
                'variant' => MessageStage::WARNING,
            ], 200);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
            'message' => __('auth.failed'),
            'variant' => MessageStage::WARNING,
        ]);
    }

    /**
     * complete registration after email sent
     */
    public function completeRegisterForm()
    {
        $this->inputTypes = modularityConfig('input_types', []);

        return $this->viewFactory->make(modularityBaseKey() . '::auth.complete-registration', [
            'formAttributes' => [
                'hasSubmit' => true,

                // 'modelValue' => new User(['name', 'surname', 'email', 'password']),
                'title' => [
                    'text' => __('authentication.complete-registration-title'),
                    'tag' => 'h1',
                    'color' => 'primary',
                    'type' => 'h5',
                    'weight' => 'bold',
                    'transform' => '',
                    'align' => 'center',
                    'justify' => 'center',
                ],
                'schema' => $this->createFormSchema(
                    getFormDraft(
                        'register_password',
                    )
                ),

                'actionUrl' => route(Route::hasAdmin('register')),
                'buttonText' => __('authentication.complete-registration'),
                'formClass' => 'py-6',
                'no-default-form-padding' => true,

            ],
            'attributes' => [
                'noDivider' => true,
            ],

            'formSlots' => [
                'bottom' => [
                    'tag' => 'v-sheet',
                    'attributes' => [
                        'class' => 'd-flex pb-5 justify-space-between w-100 text-black my-5',
                    ],
                    'elements' => [
                        'tag' => 'v-btn',
                        'elements' => __('authentication.complete-registration'),
                        'attributes' => [
                            'variant' => 'elevated',
                            'class' => 'v-col-5',
                            'type' => 'submit',
                            'density' => 'default',
                        ],

                    ],
                ],

            ],
        ]);
    }

    public function completeRegister(Request $request)
    {
        dd($request->all());

        $request->validate([
            'password' => 'required|min:8|confirmed', // Backend validation for confirmation
        ]);

        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            // Log::debug('Has many too attempts');
            return $this->sendLockoutResponse($request);
        }

        $previousRouteName = previous_route_name();

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            $request->session()->regenerate();

            $this->clearLoginAttempts($request);

            if ($response = $this->authenticated($request, $this->guard()->user())) {
                return $response;
            }

            $body = [
                'variant' => MessageStage::SUCCESS,
                'timeout' => 1500,
                'message' => __('authentication.login-success-message'),
            ];

            if ($previousRouteName === 'admin.login.form') {
                $body['redirector'] = redirect()->intended($this->redirectTo)->getTargetUrl();
            }

            return $request->wantsJson()
                ? new JsonResponse($body, 200)
                : $this->sendLoginResponse($request);

            // return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $request->wantsJson()
            ? new JsonResponse([
                $this->username() => [trans('auth.failed')],
                'message' => __('auth.failed'),
                'variant' => MessageStage::WARNING,
            ])
            : $this->sendFailedLoginResponse($request);
    }
}
