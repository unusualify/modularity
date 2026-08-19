<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Unusualify\Modularous\Events\ModularousUserRegistering;
use Unusualify\Modularous\Facades\Register;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\CreateVerifiedEmailAccount;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\RespondsWithJsonOrRedirect;

class CompleteRegisterController extends Controller
{
    use CreateVerifiedEmailAccount, RespondsWithJsonOrRedirect;

    protected function guard()
    {
        return parent::guard();
    }

    public function broker()
    {
        return Register::broker();
    }

    public function showCompleteRegisterForm(Request $request, $token = null)
    {
        $token = $request->route()->parameter('token');
        $email = $request->email;

        if ($email && $token && Register::broker('register_verified_users')->emailTokenExists(email: $email, token: $token)) {
            event(new ModularousUserRegistering($request));

            $rawSchema = getFormDraft('complete_register_form');
            $keys = array_map(fn ($key) => $key['name'], $rawSchema);
            $defaultValues = $request->only(array_diff($keys, ['password', 'password_confirmation']));
            $defaultValues['token'] = $token;

            $actionUrl = Route::has('admin.complete.register') ? route('admin.complete.register') : '';
            $viewData = $this->buildAuthViewData('complete_register', [
                'formAttributes' => [
                    'schema' => $this->createFormSchema($rawSchema),
                    'modelValue' => $defaultValues,
                    'actionUrl' => $actionUrl,
                    'buttonText' => __('Complete'),
                    'hasSubmit' => true,
                ],
            ]);

            return $this->viewFactory->make(modularousBaseKey() . '::auth.register')->with($viewData);
        }

        return $this->redirector->to(route(Route::hasAdmin('register.email_form')))->withErrors([
            'token' => 'Your email verification token has expired or could not be found, please retry.',
        ]);
    }

    public function completeRegister(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules(), $this->validationErrorMessages());

        if ($validator->fails()) {
            return $this->sendValidationFailedResponse($request, $validator);
        }

        $response = $this->broker()->register(
            $this->credentials($request),
            fn (array $credentials) => $this->registerEmail($credentials)
        );

        return $response == Register::VERIFIED_EMAIL_REGISTER
            ? $this->sendRegisterResponse($request, $response)
            : $this->sendRegisterFailedResponse($request, $response);
    }

    protected function sendRegisterResponse(Request $request, $response): JsonResponse|RedirectResponse
    {
        return $this->sendSuccessResponse($request, trans($response), $this->redirectPath());
    }

    protected function sendRegisterFailedResponse(Request $request, $response): JsonResponse|RedirectResponse
    {
        return $this->sendFailedResponse($request, trans($response), 'email');
    }
}
