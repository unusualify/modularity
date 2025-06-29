<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Utilities;


use Unusualify\Modularity\Facades\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Events\VerifiedEmailRegister;
use Illuminate\Support\Facades\Hash;
use Unusualify\Modularity\Entities\Traits\Auth\RedirectsUsers;


trait CreateVerifiedEmailAccount
{
    use RedirectsUsers;
    public function broker()
    {
        return Register::broker();
    }

    public function guard()
    {
        return Auth::guard();
    }

    public  function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'name' => 'required',
            'surname' => 'required',
            'company' => 'required',
            'password' => ['required', 'confirmed'],
        ];
    }

    public function validationErrorMessages()
    {
        return [];
    }

    public function credentials(Request $request)
    {
        return $request->only(
            'email', 'name','surname','company','password', 'password_confirmation', 'token'
        );
    }

        /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendRegisterResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['message' => trans($response)], 200);
        }

        return redirect($this->redirectPath())
                            ->with('status', trans($response));
    }

    protected function sendRegisterFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [trans($response)],
            ]);
        }

        return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => trans($response)]);
    }

    // !! This method is not used in the project but it is here for reference like ResetPasswordController::reset method
    // equivalent to completeRegister in CompleteRegisterController
    //!!Main related method is completeRegister in CompleteRegisterController

    public function register(Request $request)
    {
        // dd("Register method in CreateVerifiedEmailAccount trait");
        $request->validate($this->rules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->register(
            $this->credentials($request), function (array $credentials) {
                $this->registerEmail($credentials);
            }
        );


        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Register::VERIFIED_EMAIL_REGISTER
                    ? $this->sendRegisterResponse($request, $response)
                    : $this->sendRegisterFailedResponse($request, $response);
    }

    public function registerEmail(array $credentials)
    {
        $user = $this->setUserRegister($credentials);

        $user->setRememberToken(Str::random(60));

        $user->save();
        $freshUser = User::find($user->id);
        $freshUser->assignRole('client-manager');
        $freshUser->company()->create();

        event(new VerifiedEmailRegister($user));

        $this->guard()->login($user);
    }

    public function setUserRegister(array $credentials)
    {
        $user = new User();
        $user->name = $credentials['name'];
        $user->surname = $credentials['surname'];
        $user->email = $credentials['email'];
        $user->email_verified_at = now();
        $user->password = Hash::make($credentials['password']);

        return $user;
    }
}

