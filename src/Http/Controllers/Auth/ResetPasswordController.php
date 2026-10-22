<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\RespondsWithJsonOrRedirect;

class ResetPasswordController extends Controller
{
    use ResetsPasswords, RespondsWithJsonOrRedirect;

    public function broker()
    {
        return Password::broker(Modularous::getAuthProviderName());
    }

    /**
     * Reset the given user's password.
     *
     * @return RedirectResponse|JsonResponse
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules(), $this->validationErrorMessages());

        if ($validator->fails()) {
            return $this->sendValidationFailedResponse($request, $validator);
        }

        $response = $this->broker()->reset(
            $this->credentials($request),
            fn ($user, $password) => $this->resetPassword($user, $password)
        );

        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    public function showResetForm(Request $request, $token = null)
    {
        $user = $this->getUserFromToken($token);

        if ($user && Password::broker('users')->getRepository()->exists($user, $token)) {
            $viewData = $this->buildAuthViewData('reset_password', [
                'formAttributes' => [
                    'modelValue' => [
                        'email' => $user->email,
                        'token' => $token,
                        'password' => '',
                        'password_confirmation' => '',
                    ],
                ],
            ]);

            return $this->viewFactory->make(modularousBaseKey() . '::auth.passwords.reset')->with($viewData);
        }

        return $this->redirector->to(route('admin.password.reset.link'))->withErrors([
            'token' => 'Your password reset token has expired or could not be found, please retry.',
        ]);
    }

    /**
     * @param string|null $token
     * @return RedirectResponse|View
     */
    public function showWelcomeForm(Request $request, $token = null)
    {
        $user = $this->getUserFromToken($token);

        // we don't call exists on the Password repository here because we don't want to expire the token for welcome emails
        if ($user) {
            return $this->viewFactory->make(modularousBaseKey() . '::auth.passwords.reset')->with([
                'token' => $token,
                'email' => $user->email,
                'welcome' => true,
            ]);
        }

        return $this->redirector->to(route('admin.password.reset.link'))->withErrors([
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
     * @return User|null
     */
    protected function getUserFromToken($token)
    {
        $clearToken = DB::table($this->config->get('auth.passwords.' . Modularous::getAuthProviderName() . '.table', 'password_resets'))->where('token', $token)->first();

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

    protected function sendResetResponse(Request $request, $response): JsonResponse|RedirectResponse
    {
        return $this->sendSuccessResponse($request, trans($response), $this->redirectPath());
    }

    protected function sendResetFailedResponse(Request $request, $response): JsonResponse|RedirectResponse
    {
        return $this->sendFailedResponse($request, trans($response), 'email');
    }

    public function success()
    {
        return view(modularousBaseKey() . '::auth.success', [
            'taskState' => [
                'status' => 'success',
                'title' => __('authentication.password-sent'),
                'description' => __('authentication.success-reset-email'),
                'button_text' => __('authentication.go-to-sign-in'),
                'button_url' => route('admin.login'),
            ],
        ]);
    }
}
