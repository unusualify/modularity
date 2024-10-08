<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Factory as ViewFactory;
use Unusualify\Modularity\Services\MessageStage;
use Unusualify\Modularity\Traits\ManageUtilities;

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

    /**
     * @var PasswordBrokerManager
     */
    protected $passwordBrokerManager;

    public function __construct(PasswordBrokerManager $passwordBrokerManager)
    {
        parent::__construct();

        $this->passwordBrokerManager = $passwordBrokerManager;
        $this->middleware('unusual_guest');
    }

    /**
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return $this->passwordBrokerManager->broker('users');
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function showLinkRequestForm(ViewFactory $viewFactory)
    {
        // return $viewFactory->make(unusualBaseKey().'::auth.passwords.email');
        return $viewFactory->make(unusualBaseKey() . '::auth.passwords.email', [
            'formAttributes' => [
                // 'modelValue' => new User(['name', 'surname', 'email', 'password']),
                'schema' => ($schema = $this->createFormSchema([
                    'email' => [
                        'type' => 'text',
                        'name' => 'email',
                        'label' => ___('authentication.email'),
                        'default' => '',
                        'col' => [
                            'cols' => 12,
                        ],
                        'rules' => [
                            ['email'],
                        ],
                    ],
                ])),

                'actionUrl' => route(Route::hasAdmin('password.reset.email')),
                'buttonText' => 'authentication.reset-send',

                'formClass' => 'px-5',

            ],
            'formSlots' => [
                'bottom' => [
                    'tag' => 'v-sheet',
                    'attributes' => [
                        'class' => 'd-flex pb-5 justify-space-around w-100 text-black my-5',
                    ],
                    'elements' => [
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.login'),
                            'attributes' => [
                                'variant' => 'text',
                                'href' => '',
                                'class' => 'v-col-5',
                            ],
                        ],
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.reset-password'),
                            'attributes' => [
                                'variant' => 'elevated',
                                'href' => '',
                                'class' => 'v-col-5',
                                'type' => 'submit',

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
                                'class' => 'my-5 custom-auth-button',
                            ],
                            'slots' => [
                                'prepend' => [
                                    'tag' => 'ue-svg-icon',
                                    'attributes' => [
                                        'symbol' => 'google',
                                        'width' => '25',
                                        'height' => '25',
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
                                'class' => 'my-5 custom-auth-button',
                            ],
                            'slots' => [
                                'prepend' => [
                                    'tag' => 'ue-svg-icon',
                                    'attributes' => [
                                        'symbol' => 'apple',
                                        'width' => '25',
                                        'height' => '25',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'tag' => 'v-btn',
                            'elements' => ___('authentication.create-an-account'),
                            'attributes' => [
                                'variant' => 'outlined',
                                'href' => route(Route::hasAdmin('login.form')),
                                'class' => 'my-5 custom-auth-button',
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
