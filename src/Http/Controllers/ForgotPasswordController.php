<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Factory as ViewFactory;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Services\MessageStage;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
     */

    use ManageUtilities, SendsPasswordResetEmails;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('modularity.guest');
    }

    /**
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker(Modularity::getAuthProviderName());
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function showLinkRequestForm(ViewFactory $viewFactory)
    {
        // return $viewFactory->make(modularityBaseKey().'::auth.passwords.email');
        return $viewFactory->make(modularityBaseKey() . '::auth.passwords.email', [
            'attributes' => [
                'noSecondSection' => true,
            ],
            'formAttributes' => [
                // 'modelValue' => new User(['name', 'surname', 'email', 'password']),
                'title' => [
                    'text' => __('authentication.forgot-password'),
                    'tag' => 'h1',
                    'color' => 'primary',
                    'type' => 'h5',
                    'weight' => 'bold',
                    'transform' => '',
                    'align' => 'center',
                    'justify' => 'center',
                ],
                'schema' => $this->createFormSchema(getFormDraft('forgot_password_form')),

                'actionUrl' => route(Route::hasAdmin('password.reset.email')),
                'buttonText' => 'authentication.reset-send',
                'formClass' => 'py-6',
                'no-default-form-padding' => true,

            ],
            'formSlots' => [
                'bottom' => [
                    'tag' => 'v-sheet',
                    'attributes' => [
                        'class' => 'd-flex pb-5 justify-space-between w-100 text-black my-5',
                    ],
                    'elements' => [
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.sign-in'),
                            'attributes' => [
                                'variant' => 'elevated',
                                'href' => route(Route::hasAdmin('login.form')),
                                'class' => '',
                                'color' => 'success',
                                'density' => 'default',
                            ],
                        ],
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.reset-password'),
                            'attributes' => [
                                'variant' => 'elevated',
                                'href' => '',
                                'class' => '',
                                'type' => 'submit',
                                'density' => 'default',
                            ],
                        ],
                    ],
                ],
            ],
            'slots' => [
                'bottom' => [
                    'tag' => 'v-sheet',
                    'attributes' => [
                        'class' => 'd-flex pb-5 justify-end flex-column w-100 text-black',
                    ],
                    'elements' => [
                        [
                            'tag' => 'v-btn',
                            'elements' => ___('authentication.sign-in-google'),
                            'attributes' => [
                                'variant' => 'outlined',
                                'href' => route(Route::hasAdmin('login.form')),
                                'class' => 'mt-5 mb-2 custom-auth-button',
                                'color' => 'grey-lighten-1',
                                'density' => 'default',

                            ],
                            'slots' => [
                                'prepend' => [
                                    'tag' => 'ue-svg-icon',
                                    'attributes' => [
                                        'symbol' => 'google',
                                        'width' => '16',
                                        'height' => '16',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'tag' => 'v-btn',
                            'elements' => ___('authentication.sign-in-apple'),
                            'attributes' => [
                                'variant' => 'outlined',
                                'href' => route(Route::hasAdmin('login.form')),
                                'class' => 'my-2 custom-auth-button',
                                'color' => 'grey-lighten-1',
                                'density' => 'default',
                            ],
                            'slots' => [
                                'prepend' => [
                                    'tag' => 'ue-svg-icon',
                                    'attributes' => [
                                        'symbol' => 'apple',
                                        'width' => '16',
                                        'height' => '16',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'tag' => 'v-btn',
                            'elements' => ___('authentication.create-an-account'),
                            'attributes' => [
                                'variant' => 'outlined',
                                'href' => route(Route::hasAdmin('register.form')),
                                'class' => 'my-2 custom-auth-button',
                                'color' => 'grey-lighten-1',
                                'density' => 'default',

                            ],

                        ],

                    ],

                ],
            ],
        ]);

    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param string $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return $request->wantsJson()
                    ? new JsonResponse([
                        'message' => ___($response),
                        'variant' => MessageStage::SUCCESS,
                    ], 200)
                    : back()->with('status', ___($response));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param string $response
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            // dd('safa');
            return new JsonResponse([
                'email' => [___($response)],
                'message' => ___($response),
                'variant' => MessageStage::WARNING,
            ]);
            // throw ValidationException::withMessages([
            //     'email' => [trans($response)],
            // ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => ___($response)]);
    }
}
