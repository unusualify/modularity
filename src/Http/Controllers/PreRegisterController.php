<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Lang;
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
            'attributes' => [
                'bannerDescription' => ___('authentication.banner-description'),
                'bannerSubDescription' => Lang::has('authentication.banner-sub-description') ? ___('authentication.banner-sub-description') : null,
                'redirectButtonText' => ___('authentication.redirect-button-text'),
                'redirectUrl' => Route::has(modularityConfig('auth_guest_route'))
                    ? route(modularityConfig('auth_guest_route'))
                    : null,
            ],
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
                    'class' => 'justify-md-center',
                ],
                'schema' => $this->createFormSchema(getFormDraft('pre_register_form')),
                'actionUrl' => route(Route::hasAdmin('register.verification')),
                'buttonText' => 'authentication.register',
                'formClass' => 'py-6',
                'no-default-form-padding' => true,
                'hasSubmit' => true,
                'noSchemaUpdatingProgressBar' => true,
            ],
            'formSlots' => [
                'options' => [
                    'tag' => 'v-btn',
                    'elements' => __('authentication.have-an-account'),
                    'attributes' => [
                        'variant' => 'text',
                        'href' => route(Route::hasAdmin('login.form')),
                        'class' => 'd-flex flex-1-0 flex-md-grow-0',
                        'color' => 'grey-lighten-1',
                        'density' => 'default',
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
                            'elements' => ___('authentication.sign-up-oauth', ['provider' => 'Google']),
                            'attributes' => [
                                'variant' => 'outlined',
                                'href' => route('admin.login.provider', ['provider' => 'google']),
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
                    ],
                ],
            ],
        ]);
    }
}
