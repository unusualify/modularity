<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Utilities;

use Illuminate\Http\Request;
use Unusualify\Modularity\Brokers\RegisterBroker;
use Illuminate\Http\JsonResponse;
use Unusualify\Modularity\Services\MessageStage;
use Unusualify\Modularity\Facades\Register;

trait SendsEmailVerificationRegister
{
    public function sendVerificationLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        $response = $this->broker()->sendVerificationLink(
            $this->credentials($request)
        );


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
                        'message' => ___($response),
                        'variant' => MessageStage::SUCCESS,
                    ], 200)
                    : back()->with('status', ___($response));
    }
    protected function sendVerificationLinkFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                'email' => [___($response)],
                'message' => __($response),
                'variant' => MessageStage::WARNING,
            ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => ___($response)]);
    }

    public function credentials(Request $request)
    {
        return $request->only('email');
    }

    public function broker()
    {
        return Register::broker('register_verified_users');
    }

}