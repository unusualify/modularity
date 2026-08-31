<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Tests\TestCase;

class SourcesHelpersTest extends TestCase
{
    /** @test */
    public function test_get_locales_with_config()
    {
        Config::set('translatable.locales', ['en', 'fr', 'es']);

        $locales = getLocales();

        $this->assertEquals(['en', 'fr', 'es'], $locales);
    }

    /** @test */
    public function test_get_locales_with_country_codes()
    {
        Config::set('translatable.locales', [
            'en' => ['US', 'GB'],
            'fr' => ['FR', 'BE'],
        ]);

        $locales = getLocales();

        $this->assertEquals(['en-US', 'en-GB', 'fr-FR', 'fr-BE'], $locales);
    }

    /** @test */
    public function test_get_locales_fallback_to_app_locale()
    {
        Config::set('translatable.locales', []);
        Config::set('app.locale', 'tr');

        $locales = getLocales();

        $this->assertEquals(['tr'], $locales);
    }

    /** @test */
    public function test_get_timezone_list()
    {
        Cache::shouldReceive('rememberForever')
            ->once()
            ->with('timezones_list_collection', \Closure::class)
            ->andReturn(collect([
                'UTC' => 'UTC (UTC +00:00)',
                'Europe/London' => 'Europe/London (UTC +01:00)',
                'America/New_York' => 'America/New_York (UTC -04:00)',
            ]));

        $timezones = getTimeZoneList();

        $this->assertTrue($timezones->has('UTC'));
        $this->assertTrue($timezones->has('Europe/London'));
        $this->assertTrue($timezones->has('America/New_York'));
    }

    /** @test */
    public function test_get_form_draft_basic()
    {
        Config::set('modularous.form_drafts.test_form', [
            'field1' => [
                'type' => 'text',
                'label' => 'Field 1',
                'default' => 'value1',
            ],
            'field2' => [
                'type' => 'text',
                'label' => 'Field 2',
                'default' => 'value2',
            ],
        ]);

        $draft = getFormDraft('test_form');

        $this->assertEquals([
            'field1' => [
                'type' => 'text',
                'label' => 'Field 1',
                'default' => 'value1',
            ],
            'field2' => [
                'type' => 'text',
                'label' => 'Field 2',
                'default' => 'value2',
            ],
        ], $draft);
    }

    /** @test */
    public function test_get_form_draft_with_overwrites()
    {
        Config::set('modularous.form_drafts.test_form', [
            'field1' => [
                'type' => 'text',
                'label' => 'Field 1',
                'default' => 'value1',
            ],
            'field2' => [
                'type' => 'text',
                'label' => 'Field 2',
                'default' => 'value2',
            ],
        ]);

        $draft = getFormDraft('test_form', [
            'field2' => [
                'type' => 'text',
                'label' => 'Field 2',
                'default' => 'new_value',
            ],
            'field3' => [
                'type' => 'text',
                'label' => 'Field 3',
                'default' => 'value3',
            ],
        ]);

        $this->assertEquals([
            'field1' => [
                'type' => 'text',
                'label' => 'Field 1',
                'default' => 'value1',
            ],
            'field2' => [
                'type' => 'text',
                'label' => 'Field 2',
                'default' => 'new_value',
            ],
            'field3' => [
                'type' => 'text',
                'label' => 'Field 3',
                'default' => 'value3',
            ],
        ], $draft);
    }

    /** @test */
    public function test_get_form_draft_with_excludes()
    {
        Config::set('modularous.form_drafts.test_form', [
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3',
        ]);

        $draft = getFormDraft('test_form', [], ['field2']);

        $this->assertEquals([
            'field1' => 'value1',
            'field3' => 'value3',
        ], $draft);
    }

    /** @test */
    public function test_admin_route_name_prefix()
    {
        Config::set('modularous.admin_route_name_prefix', 'admin');
        $this->assertEquals('admin', adminRouteNamePrefix());

        Config::set('modularous.admin_route_name_prefix', '.admin.');
        $this->assertEquals('admin', adminRouteNamePrefix());
    }

    /** @test */
    public function test_admin_url_prefix()
    {
        Config::set('modularous.admin_app_url', '');
        Config::set('modularous.admin_app_path', 'admin');
        $this->assertEquals('admin', adminUrlPrefix());

        Config::set('modularous.admin_app_url', 'https://admin.example.com');
        $this->assertFalse(adminUrlPrefix());
    }

    /** @test */
    public function test_system_url_prefix()
    {
        Config::set('modularous.system_prefix', 'system-settings');
        $this->assertEquals('system-settings', systemUrlPrefix());
    }

    /** @test */
    public function test_system_route_name_prefix()
    {
        Config::set('modularous.system_prefix', 'system-settings');
        $this->assertEquals('system_settings', systemRouteNamePrefix());
    }

    /** @test */
    public function test_built_in_modularous_themes()
    {
        $mockThemes = [
            '/path/to/vendor/modularous/vue/src/sass/themes/default',
            '/path/to/vendor/modularous/vue/src/sass/themes/dark',
        ];

        File::shouldReceive('isDirectory')->andReturn(true);
        File::shouldReceive('glob')->with(Modularous::getVendorPath('vue/src/sass/themes') . '/*', GLOB_ONLYDIR)
            ->andReturn($mockThemes);

        $themes = builtInModularousThemes();

        $this->assertEquals([
            'default' => 'Default',
            'dark' => 'Dark',
        ], $themes->toArray());
    }

    /** @test */
    public function test_custom_modularous_themes()
    {
        $mockThemes = [
            resource_path('vendor/modularous/themes/custom1'),
            resource_path('vendor/modularous/themes/custom2'),
        ];

        File::shouldReceive('isDirectory')->andReturn(true);
        File::shouldReceive('glob')->with(resource_path('vendor/modularous/themes/*'), GLOB_ONLYDIR)
            ->andReturn($mockThemes);

        // File::shouldReceive('isDirectory')
        //     ->andReturnUsing(function ($path) use ($mockThemes) {
        //         return in_array($path, $mockThemes);
        //     });

        $themes = customModularousThemes();

        $this->assertEquals([
            'custom1' => 'Custom1',
            'custom2' => 'Custom2',
        ], $themes->toArray());
    }

    /** @test */
    public function test_get_translations()
    {
        $translations = ['en' => ['messages' => ['hello' => 'Hello']]];

        // Create a mock translator
        $translator = \Mockery::mock('Illuminate\Translation\Translator');
        $translator->shouldReceive('getTranslations')
            ->once()
            ->andReturn($translations);

        // Bind the mock translator to the container
        $this->app->instance('translator', $translator);

        // Mock the cache
        Cache::shouldReceive('store')
            ->with('file')
            ->andReturnSelf();

        Cache::shouldReceive('has')
            ->with('modularous-languages')
            ->andReturn(false);

        Cache::shouldReceive('set')
            ->with('modularous-languages', json_encode($translations), 600)
            ->once();

        $result = get_translations();

        $this->assertEquals($translations, $result);
    }

    /** @test */
    public function test_clear_translations()
    {
        Cache::shouldReceive('forget')
            ->with('modularous-languages')
            ->once();

        clear_translations();

        $this->assertTrue(true, 'Cache::forget() was called once with correct key');
    }

    /** @test */
    public function test_modularous_authorization_localization_and_ui_preferences_for_guest(): void
    {
        $auth = get_modularous_authorization_config();
        $this->assertFalse($auth['isSuperAdmin']);
        $this->assertSame([], $auth['roles']);

        $localization = get_modularous_localization_config();
        $this->assertArrayHasKey('locale', $localization);
        $this->assertArrayHasKey('lang', $localization);

        $prefs = get_modularous_ui_preferences();
        $this->assertArrayHasKey('sidebar', $prefs);
        $this->assertArrayHasKey('topbar', $prefs);

        $head = get_modularous_head_layout_config(['pageTitle' => 'Dash', '_headLayoutData' => ['meta' => 'x']]);
        $this->assertSame('Dash', $head['pageTitle']);
        $this->assertSame('x', $head['meta']);
    }

    /** @test */
    public function test_modularous_navigation_config_for_guest(): void
    {
        Config::set('modularous.navigation.sidebar.guest', []);
        Config::set('modularous.navigation.profileMenu.guest', []);
        Config::set('modularous.navigation.sidebarBottom.guest', []);

        $navigation = get_modularous_navigation_config();
        $this->assertArrayHasKey('sidebar', $navigation);
        $this->assertArrayHasKey('profileMenu', $navigation);
        $this->assertArrayHasKey('sidebarBottom', $navigation);
    }

    /** @test */
    public function test_guest_currency_helpers_and_form_draft_preserve_flag(): void
    {
        Config::set('auth.guards.modularous', [
            'driver' => 'session',
            'provider' => 'users',
        ]);
        Config::set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);

        $this->assertCount(0, get_user_currency_vat_rates());
        $this->assertCount(0, get_user_payment_country_currencies());

        Config::set('modularous.form_drafts.replace_form', [
            'a' => ['type' => 'text', 'name' => 'a'],
            'b' => ['type' => 'text', 'name' => 'b'],
        ]);

        $replaced = getFormDraft('replace_form', [
            'a' => ['type' => 'number', 'name' => 'a'],
        ], [], false);

        $this->assertSame('number', $replaced['a']['type']);
        $this->assertArrayHasKey('b', $replaced);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
