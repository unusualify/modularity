<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Security;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Notifications\StepUpCodeNotification;
use Unusualify\Modularous\Services\MessageStage;

class StepUpService
{
    private function config(string $key, mixed $default = null): mixed
    {
        return modularousConfig("security.step_up.{$key}", $default);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->config('enabled', false);
    }

    public function otpField(): string
    {
        return (string) $this->config('otp_field', modularousConfig('security.mfa.otp_field', 'verify-code'));
    }

    public function pageKey(): string
    {
        return (string) $this->config('page', 'step_up');
    }

    public function challengeRouteName(): string
    {
        $route = (string) $this->config('challenge_form_route', 'admin.step-up.form');

        return Route::has($route) ? $route : Route::hasAdmin('dashboard');
    }

    public function verifyRouteName(): string
    {
        $route = (string) $this->config('verify_route', 'admin.step-up.verify');

        return Route::has($route) ? $route : Route::hasAdmin('dashboard');
    }

    public function resendRouteName(): string
    {
        $route = (string) $this->config('resend_route', 'admin.step-up.resend');

        return Route::has($route) ? $route : $this->challengeRouteName();
    }

    public function challengePayload(?string $capability = null): array
    {
        return [
            'title' => __('Verification required'),
            'description' => __('We sent a verification code to your email to confirm this sensitive action.'),
            'verifyUrl' => route($this->verifyRouteName()),
            'resendUrl' => route($this->resendRouteName()),
            'otpField' => $this->otpField(),
            'otpLength' => $this->codeLength(),
            'buttonText' => __('Verify'),
            'resendText' => __('Resend code'),
            'capability' => $capability,
        ];
    }

    public function interrupt(Request $request, string $capability): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof Authenticatable, 403);

        $resolvedUser = User::find($user->getAuthIdentifier());
        abort_unless($resolvedUser instanceof User, 403);

        $this->storePendingRequest($request, $capability);
        $this->createChallenge($request, $resolvedUser, $capability);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Step-up verification required.'),
                'variant' => MessageStage::WARNING,
                'step_up_required' => true,
                'step_up' => $this->challengePayload($capability),
            ], 428);
        }

        return redirect()->to(route($this->challengeRouteName()));
    }

    public function resend(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->resolveUserFromSession($request);

        if (! $user) {
            return $this->failureResponse($request, __('Your verification session has expired. Please try again.'));
        }

        $capability = (string) $request->session()->get($this->capabilitySessionKey(), '');
        $this->createChallenge($request, $user, $capability);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('A new verification code has been sent.'),
                'variant' => MessageStage::SUCCESS,
            ], 200);
        }

        return redirect()->to(route($this->challengeRouteName()))
            ->with('status', __('A new verification code has been sent.'));
    }

    public function verify(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->resolveUserFromSession($request);

        if (! $user) {
            return $this->failureResponse($request, __('Your verification session has expired. Please try again.'));
        }

        if (! $this->validateOtp($request, $user)) {
            return $this->failureResponse($request, __('Your verification code is invalid.'));
        }

        $request->session()->put('security_step_up_verified_at', time());

        if ($request->expectsJson()) {
            $this->clearChallengeState($request, keepPendingRequest: true);

            return response()->json([
                'message' => __('Verification completed. You can continue your action.'),
                'variant' => MessageStage::SUCCESS,
                'step_up_verified' => true,
            ], 200);
        }

        $pending = $this->pullPendingRequest($request);
        $this->clearChallengeState($request, keepPendingRequest: false);

        if (! $pending) {
            $returnUrl = (string) $request->session()->pull($this->returnUrlSessionKey(), route(Route::hasAdmin('dashboard')));

            return redirect()->to($returnUrl);
        }

        if (($pending['method'] ?? 'GET') === 'GET') {
            return redirect()->to((string) ($pending['full_url'] ?? $pending['url'] ?? route(Route::hasAdmin('dashboard'))));
        }

        return response()->view(modularousBaseKey() . '::auth.step-up-replay', [
            'pendingRequest' => $pending,
            'pageTitle' => __('Continuing your action') . ' | ' . Modularous::pageTitle(),
            'otpField' => $this->otpField(),
        ]);
    }

    public function resolveUserFromSession(Request $request): ?User
    {
        $userId = $request->session()->get($this->userSessionKey());

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    public function hasActiveChallenge(Request $request): bool
    {
        return (bool) $request->session()->has($this->userSessionKey());
    }

    private function provider(): string
    {
        return (string) $this->config('provider', modularousConfig('security.mfa.provider', 'email_otp'));
    }

    private function usesEmailOtp(): bool
    {
        return $this->provider() === 'email_otp';
    }

    private function userSessionKey(): string
    {
        return (string) $this->config('user_session_key', 'step-up:user:id');
    }

    private function flowSessionKey(): string
    {
        return (string) $this->config('flow_session_key', 'step-up:flow:key');
    }

    private function capabilitySessionKey(): string
    {
        return (string) $this->config('capability_session_key', 'step-up:capability:key');
    }

    private function pendingRequestSessionKey(): string
    {
        return (string) $this->config('pending_request_session_key', 'step-up:pending:request');
    }

    private function returnUrlSessionKey(): string
    {
        return (string) $this->config('return_url_session_key', 'step-up:return:url');
    }

    private function codeLength(): int
    {
        return (int) $this->config('email_otp.length', 6);
    }

    private function codeExpiryMinutes(): int
    {
        return (int) $this->config('email_otp.expire_minutes', 10);
    }

    private function codeMaxAttempts(): int
    {
        return (int) $this->config('email_otp.max_attempts', 5);
    }

    private function cachePrefix(): string
    {
        return (string) $this->config('email_otp.cache_prefix', 'step-up:email-otp');
    }

    private function generateCode(): string
    {
        $length = max(4, min(10, $this->codeLength()));
        $max = (10 ** $length) - 1;

        return mb_str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    private function createChallenge(Request $request, User $user, ?string $capability = null): void
    {
        if ($this->usesEmailOtp()) {
            $flowKey = $this->cachePrefix() . ':' . (string) Str::uuid();
            $code = $this->generateCode();
            $expiresAt = now()->addMinutes($this->codeExpiryMinutes());

            Cache::put($flowKey, [
                'user_id' => $user->id,
                'email' => $user->email,
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => $expiresAt->toDateTimeString(),
                'capability' => $capability,
            ], $expiresAt);

            $request->session()->put($this->flowSessionKey(), $flowKey);

            $user->notify(new StepUpCodeNotification(
                code: $code,
                expiresAt: $expiresAt,
                capability: $capability,
            ));
        }

        $request->session()->put($this->userSessionKey(), $user->id);
        $request->session()->put($this->capabilitySessionKey(), $capability);
        $request->session()->put($this->returnUrlSessionKey(), url()->previous());
    }

    private function storePendingRequest(Request $request, ?string $capability = null): void
    {
        $request->session()->put($this->pendingRequestSessionKey(), [
            'url' => $request->url(),
            'full_url' => $request->fullUrl(),
            'method' => mb_strtoupper($request->method()),
            'payload' => collect($request->request->all())
                ->except(['_token', '_method'])
                ->toArray(),
            'query' => $request->query(),
            'capability' => $capability,
        ]);
    }

    private function pullPendingRequest(Request $request): ?array
    {
        $pending = $request->session()->pull($this->pendingRequestSessionKey());

        return is_array($pending) ? $pending : null;
    }

    private function clearChallengeState(Request $request, bool $keepPendingRequest = false): void
    {
        $flowKey = (string) $request->session()->get($this->flowSessionKey(), '');
        if ($flowKey !== '') {
            Cache::forget($flowKey);
        }

        $request->session()->forget($this->userSessionKey());
        $request->session()->forget($this->flowSessionKey());
        $request->session()->forget($this->capabilitySessionKey());

        if (! $keepPendingRequest) {
            $request->session()->forget($this->pendingRequestSessionKey());
        }
    }

    private function validateOtp(Request $request, User $user): bool
    {
        if ($this->usesEmailOtp()) {
            $flowKey = (string) $request->session()->get($this->flowSessionKey(), '');
            $challenge = $flowKey !== '' ? Cache::get($flowKey) : null;

            if (! is_array($challenge) || (int) ($challenge['user_id'] ?? 0) !== (int) $user->id) {
                return false;
            }

            if ((int) ($challenge['attempts'] ?? 0) >= $this->codeMaxAttempts()) {
                Cache::forget($flowKey);

                return false;
            }

            $otp = (string) $request->input($this->otpField(), '');
            $valid = Hash::check($otp, (string) ($challenge['code_hash'] ?? ''));

            if (! $valid) {
                $challenge['attempts'] = (int) ($challenge['attempts'] ?? 0) + 1;
                Cache::put($flowKey, $challenge, now()->addMinutes($this->codeExpiryMinutes()));
            }

            return $valid;
        }

        $otp = (string) $request->input($this->otpField(), '');

        return (new Google2FA)->verifyKey((string) $user->google_2fa_secret, $otp);
    }

    private function failureResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'variant' => MessageStage::WARNING,
            ], 422);
        }

        return redirect()->to(route($this->challengeRouteName()))
            ->withErrors(['error' => $message]);
    }
}
