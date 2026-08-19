<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Auth;

use Illuminate\Config\Repository as Config;
use Illuminate\Routing\Redirector;
use Illuminate\View\Factory as ViewFactory;
use Unusualify\Modularous\Http\Controllers\Auth\Controller;
use Unusualify\Modularous\Tests\TestCase;

/**
 * Test controller that exposes protected AuthFormBuilder methods for testing.
 */
class TestAuthController extends Controller
{
    public function buildAuthViewData(string $pageKey, array $overrides = []): array
    {
        return parent::buildAuthViewData($pageKey, $overrides);
    }

    public function authFormTitle(string $text, array $overrides = []): array
    {
        return parent::authFormTitle($text, $overrides);
    }

    public function restartOptionSlot(): array
    {
        return parent::restartOptionSlot();
    }

    public function resendOptionSlot(): array
    {
        return parent::resendOptionSlot();
    }

    public function haveAccountOptionSlot(): array
    {
        return parent::haveAccountOptionSlot();
    }
}

class AuthFormBuilderTest extends TestCase
{
    protected TestAuthController $controller;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('modularous.enabled.users-management', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new TestAuthController(
            app(Config::class),
            app(Redirector::class),
            app(ViewFactory::class)
        );

        $this->setupAuthConfig();
    }

    protected function setupAuthConfig(): void
    {
        config([
            'modularous.auth_pages' => array_merge(config('modularous.auth_pages', []), [
                'layout' => [
                    'logoSymbol' => 'main-logo-dark',
                    'logoLightSymbol' => 'main-logo-light',
                ],
                'layoutPresets' => [
                    'banner' => ['noSecondSection' => false],
                    'minimal' => ['noSecondSection' => true],
                ],
                'pages' => [
                    'login' => [
                        'pageTitle' => 'authentication.login',
                        'layoutPreset' => 'banner',
                        'formDraft' => 'login_form',
                        'actionRoute' => 'admin.login',
                        'formTitle' => 'authentication.login-title',
                        'buttonText' => 'authentication.sign-in',
                        'formSlotsPreset' => 'login_options',
                        'slotsPreset' => 'login_bottom',
                    ],
                    'forgot_password' => [
                        'pageTitle' => 'authentication.forgot-password',
                        'layoutPreset' => 'minimal',
                        'formDraft' => 'forgot_password_form',
                        'actionRoute' => 'admin.password.reset.email',
                        'formSlotsPreset' => 'forgot_password_form',
                        'slotsPreset' => 'forgot_password_bottom',
                    ],
                ],
            ]),
            'modularous.form_drafts.login_form' => [
                ['name' => 'email', 'type' => 'text', 'label' => 'Email'],
                ['name' => 'password', 'type' => 'password', 'label' => 'Password'],
            ],
            'modularous.form_drafts.forgot_password_form' => [
                ['name' => 'email', 'type' => 'text', 'label' => 'Email'],
            ],
        ]);
    }

    /** @test */
    public function it_builds_auth_view_data_for_login_page(): void
    {
        $data = $this->controller->buildAuthViewData('login');

        $this->assertArrayHasKey('attributes', $data);
        $this->assertArrayHasKey('formAttributes', $data);
        $this->assertArrayHasKey('formSlots', $data);
        $this->assertArrayHasKey('slots', $data);
        $this->assertArrayHasKey('pageTitle', $data);

        $this->assertArrayHasKey('noSecondSection', $data['attributes']);
        $this->assertArrayHasKey('logoLightSymbol', $data['attributes']);
        $this->assertArrayHasKey('logoSymbol', $data['attributes']);
    }

    /** @test */
    public function it_merges_layout_preset_into_attributes(): void
    {
        $data = $this->controller->buildAuthViewData('login');

        $this->assertFalse($data['attributes']['noSecondSection']);
    }

    /** @test */
    public function it_merges_minimal_preset_for_forgot_password(): void
    {
        $data = $this->controller->buildAuthViewData('forgot_password');

        $this->assertTrue($data['attributes']['noSecondSection']);
    }

    /** @test */
    public function it_applies_overrides_to_attributes(): void
    {
        $data = $this->controller->buildAuthViewData('login', [
            'attributes' => ['noSecondSection' => true],
        ]);

        $this->assertTrue($data['attributes']['noSecondSection']);
    }

    /** @test */
    public function it_resolves_form_slots_preset_login_options(): void
    {
        $data = $this->controller->buildAuthViewData('login');

        $this->assertArrayHasKey('options', $data['formSlots']);
        $this->assertIsArray($data['formSlots']['options']);
        $this->assertArrayHasKey('tag', $data['formSlots']['options']);
        $this->assertEquals('v-btn', $data['formSlots']['options']['tag']);
    }

    /** @test */
    public function it_resolves_restart_option_slot(): void
    {
        $slot = $this->controller->restartOptionSlot();

        $this->assertArrayHasKey('options', $slot);
        $this->assertArrayHasKey('tag', $slot['options']);
        $this->assertEquals('v-btn', $slot['options']['tag']);
        $this->assertArrayHasKey('attributes', $slot['options']);
        $this->assertArrayHasKey('href', $slot['options']['attributes']);
    }

    /** @test */
    public function it_resolves_resend_option_slot(): void
    {
        $slot = $this->controller->resendOptionSlot();

        $this->assertArrayHasKey('options', $slot);
        $this->assertEquals('v-btn', $slot['options']['tag']);
    }

    /** @test */
    public function it_resolves_have_account_option_slot(): void
    {
        $slot = $this->controller->haveAccountOptionSlot();

        $this->assertArrayHasKey('options', $slot);
        $this->assertArrayHasKey('attributes', $slot['options']);
    }

    /** @test */
    public function it_builds_auth_form_title(): void
    {
        $title = $this->controller->authFormTitle('Test Title');

        $this->assertEquals('Test Title', $title['text']);
        $this->assertEquals('h1', $title['tag']);
        $this->assertEquals('primary', $title['color']);
    }

    /** @test */
    public function it_builds_auth_form_title_with_overrides(): void
    {
        $title = $this->controller->authFormTitle('Test', ['tag' => 'h2']);

        $this->assertEquals('h2', $title['tag']);
    }

    /** @test */
    public function it_applies_form_title_overrides_from_page_config(): void
    {
        $pages = config('modularous.auth_pages.pages');
        $pages['login']['formTitleOverrides'] = ['margin' => 'b-8'];
        config(['modularous.auth_pages.pages' => $pages]);

        $data = $this->controller->buildAuthViewData('login');

        $this->assertEquals('b-8', $data['formAttributes']['title']['margin']);
        $this->assertEquals('h1', $data['formAttributes']['title']['tag']);
    }

    /** @test */
    public function it_resolves_form_slots_preset_login_forgot_below(): void
    {
        $pages = config('modularous.auth_pages.pages');
        $pages['login']['formSlotsPreset'] = 'login_forgot_below';
        config(['modularous.auth_pages.pages' => $pages]);

        $data = $this->controller->buildAuthViewData('login');

        $this->assertArrayNotHasKey('options', $data['formSlots']);
        $this->assertArrayHasKey('bottom', $data['formSlots']);
        $this->assertEquals('v-btn', $data['formSlots']['bottom']['tag']);
        $this->assertTrue($data['formSlots']['bottom']['attributes']['block']);
        $this->assertEquals('primary', $data['formSlots']['bottom']['attributes']['color']);
    }

    /** @test */
    public function it_resolves_slots_preset_login_bottom_v2(): void
    {
        $pages = config('modularous.auth_pages.pages');
        $pages['login']['slotsPreset'] = 'login_bottom_v2';
        config(['modularous.auth_pages.pages' => $pages]);

        $data = $this->controller->buildAuthViewData('login');

        $this->assertArrayHasKey('bottom', $data['slots']);
        $elements = $data['slots']['bottom']['elements'];
        $this->assertCount(2, $elements);
        $this->assertEquals('text', $elements[1]['attributes']['variant']);
        $this->assertEquals('primary', $elements[1]['attributes']['color']);
    }

    /** @test */
    public function it_resolves_form_slots_preset_register_have_account_below(): void
    {
        config([
            'modularous.auth_pages.pages.register' => array_merge(
                config('modularous.auth_pages.pages.register', []),
                [
                    'pageTitle' => 'authentication.register',
                    'layoutPreset' => 'banner',
                    'formDraft' => 'register_form',
                    'actionRoute' => 'admin.register',
                    'formTitle' => 'authentication.create-an-account',
                    'buttonText' => 'authentication.register',
                    'formSlotsPreset' => 'register_have_account_below',
                    'slotsPreset' => 'register_bottom',
                    'formTitleOverrides' => [
                        'margin' => 'b-8',
                        'transform' => 'uppercase',
                    ],
                ]
            ),
            'modularous.form_drafts.register_form' => [
                ['name' => 'email', 'type' => 'text', 'label' => 'Email'],
            ],
        ]);

        $data = $this->controller->buildAuthViewData('register');

        $this->assertArrayNotHasKey('options', $data['formSlots']);
        $this->assertArrayHasKey('bottom', $data['formSlots']);
        $this->assertEquals('v-btn', $data['formSlots']['bottom']['tag']);
        $this->assertEquals('primary', $data['formSlots']['bottom']['attributes']['color']);
        $this->assertEquals('uppercase', $data['formAttributes']['title']['transform']);
    }

    /** @test */
    public function it_resolves_form_slots_preset_complete_register_restart_below(): void
    {
        config([
            'modularous.auth_pages.pages.complete_register' => array_merge(
                config('modularous.auth_pages.pages.complete_register', []),
                [
                    'pageTitle' => 'authentication.complete-registration',
                    'layoutPreset' => 'banner',
                    'formDraft' => 'complete_register_form',
                    'actionRoute' => 'admin.complete.register',
                    'formTitle' => 'authentication.complete-registration-title',
                    'buttonText' => 'Complete',
                    'formSlotsPreset' => 'complete_register_restart_below',
                    'formTitleOverrides' => [
                        'margin' => 'b-8',
                        'transform' => 'uppercase',
                    ],
                ]
            ),
        ]);

        $data = $this->controller->buildAuthViewData('complete_register');

        $this->assertArrayNotHasKey('options', $data['formSlots']);
        $this->assertArrayHasKey('bottom', $data['formSlots']);
        $this->assertEquals('v-btn', $data['formSlots']['bottom']['tag']);
        $this->assertEquals('primary', $data['formSlots']['bottom']['attributes']['color']);
    }

    /** @test */
    public function it_resolves_form_slots_preset_sign_in_below(): void
    {
        $pages = config('modularous.auth_pages.pages');
        $pages['forgot_password']['formSlotsPreset'] = 'sign_in_below';
        config(['modularous.auth_pages.pages' => $pages]);

        $data = $this->controller->buildAuthViewData('forgot_password');

        $this->assertArrayNotHasKey('options', $data['formSlots']);
        $this->assertArrayHasKey('bottom', $data['formSlots']);
        $this->assertEquals('primary', $data['formSlots']['bottom']['attributes']['color']);
    }

    /** @test */
    public function it_resolves_forgot_password_v2_with_submit_enabled(): void
    {
        config([
            'modularous.auth_pages.pages.forgot_password' => array_merge(
                config('modularous.auth_pages.pages.forgot_password', []),
                [
                    'formOverrides' => [
                        'formClass' => 'py-6 auth-v2-form',
                        'hasSubmit' => true,
                    ],
                    'buttonText' => 'authentication.reset-password',
                    'slotsPreset' => 'forgot_password_bottom_v2',
                ]
            ),
        ]);

        $data = $this->controller->buildAuthViewData('forgot_password');

        $this->assertTrue($data['formAttributes']['hasSubmit']);
        $this->assertEquals('authentication.reset-password', $data['formAttributes']['buttonText']);
        $elements = $data['slots']['bottom']['elements'];
        $this->assertEquals('text', $elements[1]['attributes']['variant']);
    }
}
