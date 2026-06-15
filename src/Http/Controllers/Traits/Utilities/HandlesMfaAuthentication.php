<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Traits\Utilities;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Unusualify\Modularous\Brokers\RegisterBroker;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\Register;
use Unusualify\Modularous\Notifications\LoginMfaCodeNotification;
use Unusualify\Modularous\Services\MessageStage;

trait HandlesMfaAuthentication
{
    protected function shouldUseMfaLoginFlow(): bool
    {
        return $this->isMfaEnabled()
            && (bool) modularousConfig('security.mfa.remove_password_login', true);
    }

    protected function isMfaEnabled(): bool
    {
        return (bool) modularousConfig('security.mfa.enabled', false);
    }

    protected function mfaProvider(): string
    {
        return (string) modularousConfig('security.mfa.provider', 'email_otp');
    }

    protected function mfaSessionKey(): string
    {
        return (string) modularousConfig('security.mfa.session_key', '2fa:user:id');
    }

    protected function mfaFlowSessionKey(): string
    {
        return (string) modularousConfig('security.mfa.flow_session_key', '2fa:flow:key');
    }

    protected function mfaOtpField(): string
    {
        return (string) modularousConfig('security.mfa.otp_field', 'verify-code');
    }

    protected function mfaChallengePageKey(): string
    {
        return (string) modularousConfig('security.mfa.challenge_page', 'login_2fa');
    }

    protected function mfaLoginPageKey(): string
    {
        return (string) modularousConfig('security.mfa.login_page', 'login_mfa');
    }

    protected function mfaChallengeFormRoute(): string
    {
        return (string) modularousConfig('security.mfa.challenge_form_route', Route::hasAdmin('login-2fa.form'));
    }

    protected function mfaRegistrationSuccessRoute(): string
    {
        return (string) modularousConfig('security.mfa.registration_success_route', Route::hasAdmin('register.verification.success'));
    }

    protected function mfaCodeLength(): int
    {
        return (int) modularousConfig('security.mfa.email_otp.length', 6);
    }

    protected function mfaCodeExpiryMinutes(): int
    {
        return (int) modularousConfig('security.mfa.email_otp.expire_minutes', 10);
    }

    protected function mfaCodeMaxAttempts(): int
    {
        return (int) modularousConfig('security.mfa.email_otp.max_attempts', 5);
    }

    protected function mfaCachePrefix(): string
    {
        return (string) modularousConfig('security.mfa.email_otp.cache_prefix', 'mfa:email-otp');
    }

    protected function mfaAllowsRegistrationFromLogin(): bool
    {
        return (bool) modularousConfig('security.mfa.register_first_time', true);
    }

    protected function usesEmailOtpMfaProvider(): bool
    {
        return $this->mfaProvider() === 'email_otp';
    }

    protected function userHasMfaEnabled($user): bool
    {
        return ! empty($user?->google_2fa_secret) && (bool) ($user?->google_2fa_enabled ?? false);
    }

    protected function resolveChallengeRouteName(): string
    {
        $challengeRoute = $this->mfaChallengeFormRoute();

        if (! Route::has($challengeRoute)) {
            return Route::hasAdmin('login.form');
        }

        return $challengeRoute;
    }

    protected function resolveRegistrationSuccessRouteName(): string
    {
        $routeName = $this->mfaRegistrationSuccessRoute();

        if (! Route::has($routeName)) {
            return Route::hasAdmin('register.verification.success');
        }

        return $routeName;
    }

    protected function generateMfaCode(): string
    {
        $length = max(4, min(10, $this->mfaCodeLength()));
        $max = (10 ** $length) - 1;

        return mb_str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    protected function createEmailOtpChallenge(Request $request, User $user): string
    {
        $flowKey = $this->mfaCachePrefix() . ':' . (string) Str::uuid();
        $code = $this->generateMfaCode();
        $expiresAt = now()->addMinutes($this->mfaCodeExpiryMinutes());

        Cache::put($flowKey, [
            'user_id' => $user->id,
            'email' => $user->email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => $expiresAt->toDateTimeString(),
        ], $expiresAt);

        $request->session()->put($this->mfaFlowSessionKey(), $flowKey);
        $request->session()->put($this->mfaSessionKey(), $user->id);

        $user->notify(new LoginMfaCodeNotification(
            code: $code,
            expiresAt: $expiresAt,
        ));

        return $flowKey;
    }

    protected function registrationFromMfaLoginResponse(Request $request, string $email): JsonResponse|RedirectResponse
    {
        if (! $this->mfaAllowsRegistrationFromLogin()) {
            return $this->mfaLoginFailedResponse($request, __('auth.failed'));
        }

        $response = Register::broker('register_verified_users')->sendVerificationLink(
            ['email' => $email],
            function ($notifiable, $token) {
                $notifiable->sendRegisterNotification($token);
            }
        );

        if ($response !== RegisterBroker::VERIFICATION_LINK_SENT) {
            return $this->mfaLoginFailedResponse($request, __($response));
        }

        $redirectRoute = $this->resolveRegistrationSuccessRouteName();

        if ($request->wantsJson()) {
            return new JsonResponse([
                'message' => __('authentication.pre-register-description'),
                'variant' => MessageStage::SUCCESS,
                'redirector' => route($redirectRoute),
            ], 200);
        }

        return redirect()->route($redirectRoute)->with('status', __('authentication.pre-register-description'));
    }

    protected function handleMfaLoginRequest(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->string('email')->toString())->first();

        if (! $user) {
            return $this->registrationFromMfaLoginResponse($request, $request->string('email')->toString());
        }

        return $this->startMfaChallenge($request, $user)
            ?? $this->mfaLoginFailedResponse($request, __('auth.failed'));
    }

    protected function mfaLoginFailedResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                'errors' => [
                    'email' => [$message],
                ],
                'message' => $message,
                'variant' => MessageStage::WARNING,
            ], 422);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $message]);
    }

    protected function startMfaChallenge(Request $request, $user): JsonResponse|RedirectResponse|null
    {
        if (! $this->isMfaEnabled()) {
            return null;
        }

        if ($this->usesEmailOtpMfaProvider()) {
            $this->createEmailOtpChallenge($request, $user);
        } elseif ($this->userHasMfaEnabled($user)) {
            $this->guard()->logout();
            $request->session()->put($this->mfaSessionKey(), $user->id);
        } else {
            return null;
        }

        $challengeRoute = $this->resolveChallengeRouteName();

        $redirectUrl = $this->redirector->to(route($challengeRoute))->getTargetUrl();

        return $request->wantsJson()
            ? new JsonResponse(['redirector' => $redirectUrl], 200)
            : $this->redirector->to($redirectUrl);
    }

    protected function resolveMfaUserFromSession(Request $request): ?User
    {
        $userId = $request->session()->get($this->mfaSessionKey());

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    protected function clearMfaSession(Request $request): void
    {
        $flowKey = (string) $request->session()->get($this->mfaFlowSessionKey(), '');
        if ($flowKey !== '') {
            Cache::forget($flowKey);
        }

        $request->session()->forget($this->mfaSessionKey());
        $request->session()->forget($this->mfaFlowSessionKey());
    }

    protected function validateMfaOtp(User $user, Request $request): bool
    {
        if ($this->usesEmailOtpMfaProvider()) {
            $flowKey = (string) $request->session()->get($this->mfaFlowSessionKey(), '');
            $challenge = $flowKey !== '' ? Cache::get($flowKey) : null;

            if (! is_array($challenge)) {
                return false;
            }

            if ((int) ($challenge['user_id'] ?? 0) !== (int) $user->id) {
                return false;
            }

            if ((int) ($challenge['attempts'] ?? 0) >= $this->mfaCodeMaxAttempts()) {
                Cache::forget($flowKey);

                return false;
            }

            $otp = (string) $request->input($this->mfaOtpField(), '');
            $valid = Hash::check($otp, (string) ($challenge['code_hash'] ?? ''));

            if (! $valid) {
                $challenge['attempts'] = (int) ($challenge['attempts'] ?? 0) + 1;
                $expiresAt = now()->addMinutes($this->mfaCodeExpiryMinutes());
                Cache::put($flowKey, $challenge, $expiresAt);
            }

            return $valid;
        }

        $otp = (string) $request->input($this->mfaOtpField(), '');

        return (new Google2FA)->verifyKey((string) $user->google_2fa_secret, $otp);
    }

    protected function mfaFailureResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        $challengeRoute = $this->resolveChallengeRouteName();

        if ($request->wantsJson()) {
            return new JsonResponse([
                'message' => $message,
                'variant' => MessageStage::WARNING,
            ], 422);
        }

        return $this->redirector->to(route($challengeRoute))->withErrors([
            'error' => $message,
        ]);
    }

    protected function completeMfaLogin(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $this->authManager->guard(Modularous::getAuthGuardName())->loginUsingId($user->id);
        $this->clearMfaSession($request);

        $redirectUrl = redirect()->intended($this->redirectTo)->getTargetUrl();

        if ($request->wantsJson()) {
            return new JsonResponse([
                'message' => __('authentication.login-success-message'),
                'variant' => MessageStage::SUCCESS,
                'redirector' => $redirectUrl,
            ], 200);
        }

        return $this->redirector->intended($this->redirectTo);
    }
}
