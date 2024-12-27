<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Config\Repository as Config;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\Factory as ViewFactory;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Services\MessageStage;
use Unusualify\Modularity\Traits\ManageUtilities;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
     */

    use ManageUtilities, ResetsPasswords;

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

        $this->redirectTo = $this->config->get(unusualBaseKey() . '.auth_login_redirect_path', '/');
        $this->middleware('unusual_guest');
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard
     */
    protected function guard()
    {
        return Auth::guard('unusual_users');
    }

    /**
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker('users');
    }

    /**
     * Reset the given user's password.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
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

        // $request->validate($this->rules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
                    ? $this->sendResetResponse($request, $response)
                    : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * @param string|null $token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        $user = $this->getUserFromToken($token);

        // call exists on the Password repository to check for token expiration (default 1 hour)
        // otherwise redirect to the ask reset link form with error message
        if ($user && Password::broker('users')->getRepository()->exists($user, $token)) {
            return $this->viewFactory->make(unusualBaseKey() . '::auth.passwords.reset')->with([
                'token' => $token,
                'email' => $user->email,

                'formAttributes' => [
                    'hasSubmit' => true,

                    // 'modelValue' => new User(['name', 'surname', 'email', 'password']),
                    'modelValue' => [
                        'email' => $user->email,
                        'token' => $token,
                        'password' => '',
                        'password_confirmation' => '',
                    ],
                    'schema' => ($schema = $this->createFormSchema([
                        'email' => [
                            'type' => 'text',
                            'name' => 'email',
                            'label' => ___('authentication.email'),
                            'default' => '',
                            'col' => [
                                'cols' => 12,
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
                                'cols' => 12,
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
                                'cols' => 12,
                            ],
                        ],
                        'token' => [
                            'type' => 'hidden',
                            // "ext" => "hidden",
                            'name' => 'token',
                        ],
                    ])),

                    'actionUrl' => route(Route::hasAdmin('password.reset.update')),
                    'buttonText' => 'authentication.reset-password',
                    'formClass' => 'px-5',
                ],
            ]);
        }

        return $this->redirector->to(route('password.reset.link'))->withErrors([
            'token' => 'Your password reset token has expired or could not be found, please retry.',
        ]);
    }

    /**
     * @param string|null $token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showWelcomeForm(Request $request, $token = null)
    {
        $user = $this->getUserFromToken($token);

        // we don't call exists on the Password repository here because we don't want to expire the token for welcome emails
        if ($user) {
            return $this->viewFactory->make(unusualBaseKey() . '::auth.passwords.reset')->with([
                'token' => $token,
                'email' => $user->email,
                'welcome' => true,
            ]);
        }

        return $this->redirector->to(route('admin.password.reset'))->withErrors([
            'token' => 'Your password reset token has expired or could not be found, please retry.',
        ]);
    }

    /**
     * Attempts to find a user with the given token.
     *
     * Since Laravel 5.4, reset tokens are encrypted, but we support both cases here
     * https://github.com/laravel/framework/pull/16850
     *
     * @param string $token
     * @return \Unusualify\Modularity\Models\User|null
     */
    private function getUserFromToken($token)
    {
        $clearToken = DB::table($this->config->get('auth.passwords.unusual_users.table', 'password_resets'))->where('token', $token)->first();

        if ($clearToken) {
            return User::where('email', $clearToken->email)->first();
        }

        foreach (DB::table($this->config->get('auth.passwords.users.table', 'password_resets'))->get() as $passwordReset) {
            if (Hash::check($token, $passwordReset->token)) {
                return User::where('email', $passwordReset->email)->first();
            }
        }

        return null;
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param string $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
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
    protected function sendResetFailedResponse(Request $request, $response)
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

    public function success()
    {
        return view(unusualBaseKey() . '::auth.success', [
           'taskState' => [
                'status' => 'success',
                'title' => __('authentication.password-sent'),
                'description' => __('authentication.succcess-reset-email'),
                'button_text' => __('authentication.go-to-sign-in'),
                'button_url' => 'Test'
           ]
        ]);
    }
}
