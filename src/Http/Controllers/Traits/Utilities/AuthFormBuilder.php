<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\Traits\Utilities;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

/**
 * Provides reusable methods for building auth form view data.
 * Reduces duplication across Login, Register, ForgotPassword, and ResetPassword controllers.
 *
 * View data structure is config-driven via config('modularous.auth_pages').
 * Override auth_pages in your app config to customize UI without touching controllers.
 */
trait AuthFormBuilder
{
    /**
     * Returns form title structure for auth pages.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function authFormTitle(string $text, array $overrides = []): array
    {
        return array_merge([
            'text' => $text,
            'tag' => 'h1',
            'color' => 'primary',
            'type' => 'headline-small',
            'weight' => 'bold',
            'transform' => 'uppercase',
            'align' => 'center',
            'justify' => 'center',
            'class' => 'justify-md-center',
        ], $overrides);
    }

    /**
     * Returns base form attributes for auth forms.
     *
     * @param string|array $formDraft Form draft name or array schema
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function authFormBaseAttributes(
        string|array $formDraft,
        string $actionUrl,
        string $buttonText,
        array $overrides = []
    ): array {
        $schema = is_array($formDraft)
            ? $this->createFormSchema($formDraft)
            : $this->createFormSchema(getFormDraft($formDraft));

        return array_merge([
            'schema' => $schema,
            'actionUrl' => $actionUrl,
            'buttonText' => $buttonText,
            'formClass' => 'py-6',
            'no-default-form-padding' => true,
            'hasSubmit' => true,
            'noSchemaUpdatingProgressBar' => true,
        ], $overrides);
    }

    /**
     * Returns OAuth Google button slot element.
     */
    protected function oauthGoogleButtonSlot(string $type = 'sign-in'): array
    {
        $translationKey = $type === 'sign-up'
            ? 'authentication.sign-up-oauth'
            : 'authentication.sign-in-oauth';

        return [
            'tag' => 'v-btn',
            'elements' => ___($translationKey, ['provider' => 'Google']),
            'attributes' => [
                'variant' => 'outlined',
                'href' => route('admin.login.provider', ['provider' => 'google']),
                'class' => 'mt-5 mb-2 custom-auth-button',
                'color' => 'grey-lighten-1',
                'density' => 'default',
                'block' => true,
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
        ];
    }

    /**
     * Returns create account button slot element.
     */
    protected function createAccountButtonSlot(): array
    {
        $registerRoute = modularousConfig('email_verified_register')
            ? Route::hasAdmin('register.email_form')
            : Route::hasAdmin('register.form');

        return [
            'tag' => 'v-btn',
            'elements' => ___('authentication.create-an-account'),
            'attributes' => [
                'variant' => 'outlined',
                'href' => route($registerRoute),
                'class' => 'my-2 custom-auth-button',
                'color' => 'grey-lighten-1',
                'density' => 'default',
                'block' => true,
            ],
        ];
    }

    /**
     * Returns create account as a primary text link (V2 auth layout).
     */
    protected function createAccountLinkSlot(): array
    {
        $registerRoute = modularousConfig('email_verified_register')
            ? Route::hasAdmin('register.email_form')
            : Route::hasAdmin('register.form');

        return [
            'tag' => 'v-btn',
            'elements' => ___('authentication.create-an-account'),
            'attributes' => [
                'variant' => 'text',
                'href' => route($registerRoute),
                'class' => 'my-2 text-none auth-v2-create-account',
                'color' => 'primary',
                'density' => 'default',
                'block' => true,
            ],
        ];
    }

    /**
     * Returns form option slot (e.g. forgot password, have account link).
     *
     * @param array<string, mixed> $attributes
     */
    protected function authFormOptionSlot(string $text, string $href, array $attributes = []): array
    {
        return [
            'tag' => 'v-btn',
            'elements' => $text,
            'attributes' => array_merge([
                'variant' => 'plain',
                'href' => $href,
                'class' => '',
                'color' => 'grey-lighten-1',
                'density' => 'default',
            ], $attributes),
        ];
    }

    /**
     * Returns bottom slots wrapper with given elements.
     *
     * @param array<int, array> $elements
     * @param array<string, mixed> $sheetAttributes
     */
    protected function authBottomSlots(array $elements, array $sheetAttributes = []): array
    {
        return [
            'tag' => 'v-sheet',
            'attributes' => array_merge([
                'border' => false,
                'class' => 'd-flex pb-5 justify-end flex-column w-100 text-black',
            ], $sheetAttributes),
            'elements' => $elements,
        ];
    }

    /**
     * Returns form slots wrapper for bottom buttons (e.g. sign-in, reset-password).
     *
     * @param array<int, array> $elements
     */
    protected function authFormBottomSlots(array $elements): array
    {
        return [
            'bottom' => [
                'tag' => 'v-sheet',
                'attributes' => [
                    'class' => 'd-flex pb-5 justify-space-between w-100 text-black my-5',
                ],
                'elements' => $elements,
            ],
        ];
    }

    /**
     * Returns "have an account" link slot for register forms.
     */
    protected function haveAccountOptionSlot(): array
    {
        return [
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
        ];
    }

    /**
     * Returns "restart" link slot for complete register form.
     */
    protected function restartOptionSlot(): array
    {
        return [
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
        ];
    }

    /**
     * Returns "resend" link slot for password reset form.
     */
    protected function resendOptionSlot(): array
    {
        return [
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
    }

    /**
     * Build auth view data from config-driven page definition.
     *
     * @param string $pageKey Key from auth_pages.pages (login, register, forgot_password, etc.)
     * @param array<string, mixed> $overrides Override attributes, formAttributes, formSlots, slots, modelValue
     * @return array{attributes: array, formAttributes: array, formSlots: array, slots: array, pageTitle: string}
     */
    protected function buildAuthViewData(string $pageKey, array $overrides = []): array
    {
        $config = modularousConfig('auth_pages', []);
        $pageConfig = $config['pages'][$pageKey] ?? [];
        $layoutConfig = $config['layout'] ?? [];
        $layoutPresets = $config['layoutPresets'] ?? [];

        $layoutPresetName = $pageConfig['layoutPreset'] ?? 'minimal';
        $layoutPreset = $layoutPresets[$layoutPresetName] ?? [];

        $attributes = array_merge(
            $layoutConfig,
            $layoutPreset,
            modularousConfig('auth_pages.attributes', []),
            $pageConfig['attributes'] ?? [],
            $overrides['attributes'] ?? []
        );

        $attributes['logoSymbol'] ??= $layoutConfig['logoSymbol'] ?? 'main-logo-dark';
        $attributes['logoLightSymbol'] ??= $layoutConfig['logoLightSymbol'] ?? 'main-logo-light';

        if (! isset($attributes['redirectUrl']) && Route::has(modularousConfig('auth_guest_route'))) {
            $attributes['redirectUrl'] = route(modularousConfig('auth_guest_route'));
        }

        $formDraft = $pageConfig['formDraft'] ?? null;
        $actionRoute = $pageConfig['actionRoute'] ?? '';
        $formTitle = $pageConfig['formTitle'] ?? '';
        $buttonText = $pageConfig['buttonText'] ?? '';

        $formAttributes = $overrides['formAttributes'] ?? [];
        $formAttributes['buttonDensity'] ??= 'default';

        if ($formDraft !== null) {
            $actionUrl = Route::has($actionRoute) ? route($actionRoute) : $actionRoute;
            $buttonTextResolved = is_string($buttonText) && str_starts_with($buttonText, 'authentication.')
                ? $buttonText
                : __($buttonText);

            $baseForm = $this->authFormBaseAttributes($formDraft, $actionUrl, $buttonTextResolved);
            $formOverrides = $pageConfig['formOverrides'] ?? [];
            $formAttributes = array_merge($baseForm, $formOverrides, $formAttributes);
        }

        if ($formTitle && ! isset($formAttributes['title'])) {
            $formTitleOverrides = array_merge(
                $pageKey === 'register' ? ['transform' => ''] : [],
                $pageConfig['formTitleOverrides'] ?? []
            );
            $formAttributes['title'] = $this->authFormTitle(
                is_string($formTitle) ? __($formTitle) : $formTitle,
                $formTitleOverrides
            );
        }

        $formSlots = $this->resolveFormSlotsPreset($pageConfig['formSlotsPreset'] ?? null);
        $formSlots = array_merge($formSlots, $overrides['formSlots'] ?? []);

        $slots = $this->resolveSlotsPreset($pageConfig['slotsPreset'] ?? null);
        $slots = array_merge($slots, $overrides['slots'] ?? []);

        $pageTitle = ___($pageConfig['pageTitle'] ?? 'authentication.login');

        return array_merge([
            'attributes' => $attributes,
            'formAttributes' => array_merge_recursive_preserve($formAttributes, $overrides['formAttributes'] ?? []),
            'formSlots' => $formSlots,
            'slots' => $slots,
            'pageTitle' => $pageTitle,
            'endpoints' => $overrides['endpoints'] ?? new \stdClass,
            'formStore' => $overrides['formStore'] ?? new \stdClass,
        ], Arr::except($overrides, ['formAttributes']));
    }

    /**
     * Resolve formSlots from preset name.
     *
     * @return array<string, mixed>
     */
    protected function resolveFormSlotsPreset(?string $preset): array
    {
        return match ($preset) {
            'login_options' => [
                'options' => $this->authFormOptionSlot(
                    __('authentication.forgot-password'),
                    route('admin.password.reset.link')
                ),
            ],
            'login_forgot_below' => [
                'bottom' => $this->authFormOptionSlot(
                    __('authentication.forgot-password'),
                    route('admin.password.reset.link'),
                    [
                        'variant' => 'text',
                        'color' => 'primary',
                        'block' => true,
                        'class' => 'w-100 mt-2 text-none auth-v2-forgot',
                    ]
                ),
            ],
            'sign_in_below' => [
                'bottom' => $this->authFormOptionSlot(
                    __('authentication.sign-in'),
                    route(Route::hasAdmin('login.form')),
                    [
                        'variant' => 'text',
                        'color' => 'primary',
                        'block' => true,
                        'class' => 'w-100 mt-2 text-none auth-v2-sign-in',
                    ]
                ),
            ],
            'login_mfa_options' => [
                'options' => $this->authFormOptionSlot(
                    __('authentication.create-an-account'),
                    route(Route::hasAdmin('register.email_form'))
                ),
            ],
            'login_2fa_options' => [
                'options' => $this->authFormOptionSlot(
                    __('authentication.back-to-login'),
                    route(Route::hasAdmin('login.form'))
                ),
            ],
            'step_up_options' => [
                'options' => $this->authFormOptionSlot(
                    __('Resend verification code'),
                    route(Route::hasAdmin('step-up.resend'))
                ),
            ],
            'have_account' => $this->haveAccountOptionSlot(),
            'register_have_account_below' => [
                'bottom' => $this->authFormOptionSlot(
                    __('authentication.have-an-account'),
                    route(Route::hasAdmin('login.form')),
                    [
                        'variant' => 'text',
                        'color' => 'primary',
                        'block' => true,
                        'class' => 'w-100 mt-2 text-none auth-v2-have-account',
                    ]
                ),
            ],
            'restart' => $this->restartOptionSlot(),
            'complete_register_restart_below' => [
                'bottom' => $this->authFormOptionSlot(
                    __('Restart'),
                    route(Route::hasAdmin('register.email_form')),
                    [
                        'variant' => 'text',
                        'color' => 'primary',
                        'block' => true,
                        'class' => 'w-100 mt-2 text-none auth-v2-restart',
                    ]
                ),
            ],
            'resend' => $this->resendOptionSlot(),
            'oauth_submit' => $this->authFormBottomSlots([
                [
                    'tag' => 'v-btn',
                    'elements' => __('authentication.sign-in'),
                    'attributes' => [
                        'variant' => 'elevated',
                        'class' => 'v-col-5 mx-auto',
                        'type' => 'submit',
                        'density' => 'default',
                        'block' => true,
                    ],
                ],
            ]),
            'forgot_password_form' => [
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
            default => [],
        };
    }

    /**
     * Resolve slots (bottom) from preset name.
     *
     * @return array<string, mixed>
     */
    protected function resolveSlotsPreset(?string $preset): array
    {
        return match ($preset) {
            'login_bottom' => [
                'bottom' => $this->authBottomSlots([
                    $this->oauthGoogleButtonSlot('sign-in'),
                    $this->createAccountButtonSlot(),
                ]),
            ],
            'login_bottom_v2' => [
                'bottom' => $this->authBottomSlots([
                    $this->oauthGoogleButtonSlot('sign-in'),
                    $this->createAccountLinkSlot(),
                ]),
            ],
            'login_mfa_bottom' => [
                'bottom' => $this->authBottomSlots([
                    $this->oauthGoogleButtonSlot('sign-in'),
                ]),
            ],
            'register_bottom' => [
                'bottom' => $this->authBottomSlots([
                    $this->oauthGoogleButtonSlot('sign-up'),
                ]),
            ],
            'forgot_password_bottom' => [
                'bottom' => $this->authBottomSlots([
                    $this->oauthGoogleButtonSlot('sign-in'),
                    $this->createAccountButtonSlot(),
                ]),
            ],
            'forgot_password_bottom_v2' => [
                'bottom' => $this->authBottomSlots([
                    $this->oauthGoogleButtonSlot('sign-in'),
                    $this->createAccountLinkSlot(),
                ]),
            ],
            default => [],
        };
    }
}
