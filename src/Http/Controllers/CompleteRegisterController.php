<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Config\Repository as Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\Factory as ViewFactory;
use Unusualify\Modularity\Events\ModularityUserRegistering;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\Register;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Http\Controllers\Traits\Utilities\CreateVerifiedEmailAccount;
use Unusualify\Modularity\Services\MessageStage;

class CompleteRegisterController extends Controller
{
    use ManageUtilities, CreateVerifiedEmailAccount;

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

        $this->redirectTo = $this->config->get(modularityBaseKey() . '.auth_login_redirect_path', '/');
        $this->middleware('modularity.guest');

    }

    protected function guard(): \Illuminate\Contracts\Auth\StatefulGuard
    {
        return Auth::guard(Modularity::getAuthGuardName());
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
            event(new ModularityUserRegistering($request));

            $rawSchema = getFormDraft('complete_register_form');
            $keys = array_map(fn ($key) => $key['name'], $rawSchema);
            $defaultValues = $request->only(array_diff($keys, ['password', 'password_confirmation']));
            $defaultValues['token'] = $token;

            return $this->viewFactory->make(modularityBaseKey() . '::auth.register')->with([
                'attributes' => [
                    'noSecondSection' => true,
                ],
                'formAttributes' => [
                    'title' => [
                        'text' => __('authentication.complete-registration'),
                        'tag' => 'h1',
                        'color' => 'primary',
                        'type' => 'h5',
                        'weight' => 'bold',
                        'transform' => 'uppercase',
                        'align' => 'center',
                        'justify' => 'center',
                        'class' => 'justify-md-center',
                    ],
                    'modelValue' => $defaultValues,
                    'schema' => $this->createFormSchema(getFormDraft('complete_register_form')),

                    'actionUrl' => route(Route::hasAdmin('complete.register')),
                    'buttonText' => 'Complete',
                    'formClass' => 'py-6',
                    'no-default-form-padding' => true,
                    'hasSubmit' => true,
                    'noSchemaUpdatingProgressBar' => true,
                ],

                'formSlots' => [
                    'options' => [
                        'tag' => 'v-btn',
                        'elements' => __('Restart'),
                        'attributes' => [
                            'variant' => 'text',
                            'href' => route(Route::hasAdmin('register.email_form')),
                            'class' => 'd-flex flex-1-0 flex-md-grow-0',
                            'color' => 'grey-lighten-1',
                            'density' => 'default',
                        ],
                    ],
                ],
            ]);
        }

        return $this->redirector->to(route(Route::hasAdmin('register.email_form')))->withErrors([
            'token' => 'Your email verification token has expired or could not be found, please retry.',
        ]);
    }

    public function completeRegister(Request $request)
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

        $response = $this->broker()->register(
            $this->credentials($request), function (array $credentials) {
                $this->registerEmail($credentials);
            }
        );

        return $response == Register::VERIFIED_EMAIL_REGISTER
            ? $this->sendRegisterResponse($request, $response)
            : $this->sendRegisterFailedResponse($request, $response);

    }

    protected function sendRegisterResponse(Request $request, $response)
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
    protected function sendRegisterFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                'email' => [trans($response)],
                'message' => trans($response),
                'variant' => MessageStage::WARNING,
            ], 200);
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)]);
    }
}
