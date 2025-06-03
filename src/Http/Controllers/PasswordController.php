<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\Traits\MakesResponses;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Services\MessageStage;

class PasswordController extends Controller
{
    use ManageUtilities, MakesResponses, ResetsPasswords;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('modularity.guest');
    }

    public function broker()
    {
        return Password::broker(Modularity::getAuthProviderName());
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard(Modularity::getAuthGuardName());
    }

    /**
     * Reset the given user's password.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        // $this->guard()->login($user);

        $this->guard()->attempt(['email' => $user->email, 'password' => $password], true);
    }

    public function showForm(Request $request, $token)
    {
        $routeName = null;
        $buttonText = __('authentication.reset-password');

        $formSlots = [
            'options' => [
                'tag' => 'v-btn',
                'elements' => __('Resend'),
                'attributes' => [
                    'variant' => 'plain',
                    'href' => route('admin.password.reset.link'),
                    'class' => '',
                    'color' => 'grey-lighten-1',
                    'density' => 'default',
                ],
            ],
        ];
        try {
            $routeName = $request->route()->getName();
            if($routeName == 'admin.register.password.generate.form') {
                $buttonText = __('authentication.generate-password');
            }

        } catch (\Throwable $th) {

        }
        $token = $request->route()->parameter('token');
        $email = $request->get('email');

        $resetPasswordSchema = getFormDraft('reset_password_form');

        return view(modularityBaseKey() . '::auth.passwords.reset', [
            'attributes' => [
                'noSecondSection' => true,
            ],
            'formAttributes' => [
                'title' => [
                    'text' => __('Save Your Password'),
                    'tag' => 'h1',
                    'color' => 'primary',
                    'type' => 'h5',
                    'weight' => 'bold',
                    'align' => 'center',
                    'justify' => 'center',
                ],
                'formClass' => 'px-5',
                'hasSubmit' => true,
                'color' => 'primary',
                'actionUrl' => route(Route::hasAdmin('register.password.generate')),
                'buttonText' => $buttonText,
                'modelValue' => [
                    'email' => $email,
                    'password' => '',
                    'password_confirmation' => '',
                    'token' => $token,
                ],

                'schema' => $this->createFormSchema($resetPasswordSchema),
            ],
            'formSlots' => $formSlots,
        ]);
    }

    public function savePassword(Request $request)
    {
        $rules = [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $request->wantsJson()
                ? new JsonResponse([
                    'errors' => $validator->errors(),
                    'message' => $validator->messages()->first(),
                    'variant' => MessageStage::WARNING,
                ], 200)
                : $request->validate($rules);
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        if($response !== Password::PASSWORD_RESET) {
            return $request->wantsJson()
                ? $this->respondWithError(trans($response))
                : redirect()->back()->with('error', trans($response));
        }

        $email = $request->get('email');

        if ($email) {
            $user = User::where('email', $email)->first();
            $user->markEmailAsVerified();
        }

        return $request->wantsJson()
            ? $this->respondWithSuccess(__('messages.password-saved'), ['redirector' => route(Route::hasAdmin('profile'))])
            : redirect()->route(Route::hasAdmin('profile'));
    }
}
