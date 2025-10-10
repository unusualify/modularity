<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Utilities;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Unusualify\Modularity\Brokers\RegisterBroker;
use Unusualify\Modularity\Facades\Register;
use Unusualify\Modularity\Services\MessageStage;

trait SendsEmailVerificationRegister
{
    protected static $notificationLinkParameters = [];

    public static function addNotificationLinkParameters(callable $callback)
    {
        if (! isset(static::$notificationLinkParameters[static::class])) {
            static::$notificationLinkParameters[static::class] = [];
        }

        static::$notificationLinkParameters[static::class][] = $callback;
    }

    public function getNotificationLinkParameters(Request $request)
    {
        $parameters = [];

        foreach (static::$notificationLinkParameters[static::class] ?? [] as $callback) {
            $parameters = array_merge($parameters, $callback($request));
        }

        return $parameters;
    }

    public function broker()
    {
        return Register::broker('register_verified_users');
    }

    public function sendVerificationLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        $parameters = $this->getNotificationLinkParameters($request);

        $response = $this->broker()->sendVerificationLink($this->credentials($request), function ($user, $token) use ($parameters) {
            $user->sendRegisterNotification($token, $parameters);
        });

        return $response == RegisterBroker::VERIFICATION_LINK_SENT
            ? $this->sendVerificationLinkResponse($request, $response)
            : $this->sendVerificationLinkFailedResponse($request, $response);
    }

    public function validateEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    }

    protected function sendVerificationLinkResponse(Request $request, $response)
    {
        return $request->wantsJson()
            ? new JsonResponse([
                'message' => __($response),
                'variant' => MessageStage::SUCCESS,
                'redirector' => route('admin.register.verification.success'),
            ], 200)
            : back()->with('status', ___($response));
    }

    protected function sendVerificationLinkFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                'email' => [__($response)],
                'message' => __($response),
                'variant' => MessageStage::WARNING,
            ], 422);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($response)]);
    }

    public function credentials(Request $request)
    {
        return $request->only('email');
    }

    public function showSuccessForm()
    {
        return view(modularityBaseKey() . '::auth.success', [
            'taskState' => [
                'status' => 'success',
                'title' => __('authentication.pre-register-title'),
                'description' => __('authentication.pre-register-description'),
                'button_text' => __('authentication.pre-register-button-text'),
                'button_url' => route('admin.login'),
            ],
        ]);
    }
}
