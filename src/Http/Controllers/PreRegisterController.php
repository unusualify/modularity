<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Facades\Register;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Http\Controllers\Traits\Utilities\SendsEmailVerificationRegister;



class PreRegisterController extends Controller
{
    use ManageUtilities, SendsEmailVerificationRegister;

    protected $registerBrokerManager;

    public function __construct(Application $app)
    {
        parent::__construct();
        $this->middleware('modularity.guest');
    }

    public function broker()
    {
        return Register::broker();
    }

    public function showEmailForm()
    {
        return view(modularityBaseKey() . '::auth.register', [
            'formAttributes' => [
                'title' => [
                    'text' => __('authentication.create-an-account'),
                    'tag' => 'h1',
                    'color' => 'primary',
                    'type' => 'h5',
                    'weight' => 'bold',
                    'transform' => '',
                    'align' => 'center',
                    'justify' => 'center',
                ],
                'schema' => ($schema = $this->createFormSchema([
                    'email' => [
                        'type' => 'text',
                        'name' => 'email',
                        'label' => ___('authentication.email'),
                        'default' => '',
                        'rules' => [
                            ['email'],
                        ],
                    ],
                    'tos' => [
                        'type' => 'checkbox',
                        'name' => 'tos',
                        'label' => __('authentication.tos'),
                        'default' => '',
                        'col' => [
                            'cols' => 12,
                            'lg' => 12,
                        ],
                    ],
                ])),

                'actionUrl' => route(Route::hasAdmin('pre-register')),
                'buttonText' => 'authentication.register',
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
                                'variant' => 'text',
                                'href' => route(Route::hasAdmin('login.form')),
                                'class' => 'v-col-5 justify-content-start',
                                'color' => 'grey-lighten-1',
                                'density' => 'default',

                            ],
                        ],
                        [
                            'tag' => 'v-btn',
                            'elements' => __('authentication.register'),
                            'attributes' => [
                                'variant' => 'elevated',
                                'href' => '',
                                'class' => 'v-col-5',
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

                    ],

                ],
            ],
        ]);
    }
}

