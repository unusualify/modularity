<?php

namespace OoBook\CRM\Base\Http\Controllers;

use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\Factory as ViewFactory;
use OoBook\CRM\Base\Traits\ManageUtilities;

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

    use SendsPasswordResetEmails, ManageUtilities;

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
     * @param ViewFactory $viewFactory
     * @return \Illuminate\Contracts\View\View
     */
    public function showLinkRequestForm(ViewFactory $viewFactory)
    {
        // return $viewFactory->make('unusual::auth.passwords.email');
        return $viewFactory->make('unusual::auth.passwords.email', [
            'formAttributes' => [
                'hasSubmit' => true,

                // 'modelValue' => new User(['name', 'surname', 'email', 'password']),
                'schema' => ($schema = $this->getFormSchema([
                    'email' => [
                        "type" => "text",
                        "name" => "email",
                        "label" => ___('authentication.email'),
                        "default" => "",
                        'col' => [
                            'cols' => 12,
                        ],
                        'rules' => [
                            ['email']
                        ]
                    ],
                ])),

                'actionUrl' => route('password.reset.email'),
                'buttonText' => 'authentication.reset-send',

                'formClass' => 'px-5',

            ],
            'slots' => [
                'bottom' => [
                    'tag' => 'v-sheet',
                    'attributes' => [
                        'class' => 'd-flex pb-5 mx-8 justify-end',

                    ],
                    'elements' => [
                        [
                            "tag" => "v-btn",
                            'elements' => ___('authentication.back-to-login'),
                            "attributes" => [
                                'variant' => 'plain',
                                'href' => route('login.form'),
                                'class' => ''
                            ],
                        ]
                    ]

                ]
            ]
        ]);

    }

        /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return $request->wantsJson()
                    ? new JsonResponse([
                        'message' => ___($response),
                        'variant' => 'success'
                    ], 200)
                    : back()->with('status', ___($response));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
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
                'variant' => 'warning'
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
