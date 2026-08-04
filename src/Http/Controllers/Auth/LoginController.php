<?php

namespace Unusualify\Modularous\Http\Controllers\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\View;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\EnforcesMfaSetupOnLogin;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\HandlesMfaAuthentication;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\HandlesOAuth;
use Unusualify\Modularous\Services\MessageStage;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        login as protected defaultPasswordLogin;
    }
    use EnforcesMfaSetupOnLogin, HandlesMfaAuthentication, HandlesOAuth;

    /**
     * @var AuthManager
     */
    protected $authManager;

    /**
     * @var Encrypter
     */
    protected $encrypter;

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

        $this->redirectTo = modularousConfig('auth_login_redirect_path', '/');
    }

    protected function guestMiddlewareExcept(): array
    {
        return ['logout'];
    }

    protected function guard()
    {
        return $this->authManager->guard(Modularous::getAuthGuardName());
    }

    public function showForm()
    {
        $pageKey = $this->shouldUseMfaLoginFlow()
            ? $this->mfaLoginPageKey()
            : 'login';

        return $this->viewFactory->make(modularousBaseKey() . '::auth.login', $this->buildAuthViewData($pageKey));
    }

    public function login(Request $request)
    {
        if ($this->shouldUseMfaLoginFlow()) {
            return $this->handleMfaLoginRequest($request);
        }

        return $this->defaultPasswordLogin($request);
    }

    /**
     * @return View
     */
    public function showLogin2FaForm()
    {
        if (! $this->isMfaEnabled()) {
            return $this->viewFactory->make(modularousBaseKey() . '::auth.login', $this->buildAuthViewData('login'));
        }

        return $this->viewFactory->make(
            modularousBaseKey() . '::auth.login',
            $this->buildAuthViewData($this->mfaChallengePageKey())
        );
    }

    /**
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $this->redirector->to(route(Route::hasAdmin('login.form')));
    }

    /**
     * @param User $user
     * @return RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        return $this->afterAuthentication($request, $user);
    }

    protected function afterAuthentication(Request $request, $user)
    {
        if ($mfaResponse = $this->enforceMfaSetupOnLogin($request, $user)) {
            return $mfaResponse;
        }

        if ($mfaChallenge = $this->startMfaChallenge($request, $user)) {
            return $mfaChallenge;
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

        if ($request->has('modularous_timezone')) {
            session()->put('modularous_timezone', $request->get('modularous_timezone'));
            session()->put('timezone', $request->get('modularous_timezone'));
        } elseif ($request->has('_timezone')) {
            session()->put('modularous_timezone', $request->get('_timezone'));
            session()->put('timezone', $request->get('_timezone'));
        }

        return $request->wantsJson()
            ? new JsonResponse($body, 200)
            : $this->redirector->intended($this->redirectPath());

    }

    public function login2Fa(Request $request)
    {
        if (! $this->isMfaEnabled()) {
            return $this->redirector->to(route(Route::hasAdmin('login.form')));
        }

        $user = $this->resolveMfaUserFromSession($request);

        if (! $user) {
            return $this->mfaFailureResponse($request, 'Your MFA session has expired. Please login again.');
        }

        if (! $this->validateMfaOtp($user, $request)) {
            return $this->mfaFailureResponse($request, 'Your one time password is invalid.');
        }

        return $this->completeMfaLogin($request, $user);
    }

    public function redirectTo()
    {
        return route(Route::hasAdmin('dashboard'));
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @return RedirectResponse|JsonResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        if ($request->wantsJson()) {
            $errors = [
                $this->username() => [trans('auth.failed')],
            ];

            return new JsonResponse([
                'errors' => $errors,
                'message' => __('auth.failed'),
                'variant' => MessageStage::WARNING,
            ], 422);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
            'message' => __('auth.failed'),
            'variant' => MessageStage::WARNING,
        ]);
    }
}
