<?php

namespace Unusualify\Modularity\Tests\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Tests\TestCase;

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
            'fr' => ['FR', 'BE']
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
                'America/New_York' => 'America/New_York (UTC -04:00)'
            ]));

        $timezones = getTimeZoneList();

        $this->assertTrue($timezones->has('UTC'));
        $this->assertTrue($timezones->has('Europe/London'));
        $this->assertTrue($timezones->has('America/New_York'));
    }

    /** @test */
    public function test_get_form_draft_basic()
    {
        Config::set('modularity.form_drafts.test_form', [
            'field1' => [
                'type' => 'text',
                'label' => 'Field 1',
                'default' => 'value1'
            ],
            'field2' => [
                'type' => 'text',
                'label' => 'Field 2',
                'default' => 'value2'
            ]
        ]);

        $draft = getFormDraft('test_form');

        $this->assertEquals([
            'field1' => [
                'type' => 'text',
                'label' => 'Field 1',
                'default' => 'value1'
            ],
            'field2' => [
                'type' => 'text',
                'label' => 'Field 2',
                'default' => 'value2'
            ]
        ], $draft);
    }

    /** @test */
    public function test_get_form_draft_with_overwrites()
    {
        Config::set('modularity.form_drafts.test_form', [
            'field1' => [
                'type' => 'text',
                'label' => 'Field 1',
                'default' => 'value1'
            ],
            'field2' => [
                'type' => 'text',
                'label' => 'Field 2',
                'default' => 'value2'
            ]
        ]);

        $draft = getFormDraft('test_form', [
            'field2' => [
                'type' => 'text',
                'label' => 'Field 2',
                'default' => 'new_value'
            ],
            'field3' => [
                'type' => 'text',
                'label' => 'Field 3',
                'default' => 'value3'
            ]
        ]);

        $this->assertEquals([
            'field1' => [
                'type' => 'text',
                'label' => 'Field 1',
                'default' => 'value1'
            ],
            'field2' => [
                'type' => 'text',
                'label' => 'Field 2',
                'default' => 'new_value'
            ],
            'field3' => [
                'type' => 'text',
                'label' => 'Field 3',
                'default' => 'value3'
            ]
        ], $draft);
    }

    /** @test */
    public function test_get_form_draft_with_excludes()
    {
        Config::set('modularity.form_drafts.test_form', [
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3'
        ]);

        $draft = getFormDraft('test_form', [], ['field2']);

        $this->assertEquals([
            'field1' => 'value1',
            'field3' => 'value3'
        ], $draft);
    }

    /** @test */
    public function test_admin_route_name_prefix()
    {
        Config::set('modularity.admin_route_name_prefix', 'admin');
        $this->assertEquals('admin', adminRouteNamePrefix());

        Config::set('modularity.admin_route_name_prefix', '.admin.');
        $this->assertEquals('admin', adminRouteNamePrefix());
    }

    /** @test */
    public function test_admin_url_prefix()
    {
        Config::set('modularity.admin_app_url', null);
        Config::set('modularity.admin_app_path', 'admin');
        $this->assertEquals('admin', adminUrlPrefix());

        Config::set('modularity.admin_app_url', 'https://admin.example.com');
        $this->assertFalse(adminUrlPrefix());
    }

    /** @test */
    public function test_system_url_prefix()
    {
        Config::set('modularity.system_prefix', 'system-settings');
        $this->assertEquals('system-settings', systemUrlPrefix());
    }

    /** @test */
    public function test_system_route_name_prefix()
    {
        Config::set('modularity.system_prefix', 'system-settings');
        $this->assertEquals('system_settings', systemRouteNamePrefix());
    }

    /** @test */
    public function test_built_in_modularity_themes()
    {
        $mockThemes = [
            '/path/to/vendor/modularity/vue/src/sass/themes/default',
            '/path/to/vendor/modularity/vue/src/sass/themes/dark'
        ];

        File::shouldReceive('isDirectory')->andReturn(true);
        File::shouldReceive('glob')->with(Modularity::getVendorPath('vue/src/sass/themes') . '/*', GLOB_ONLYDIR)
            ->andReturn($mockThemes);

        $themes = builtInModularityThemes();

        $this->assertEquals([
            'default' => 'Default',
            'dark' => 'Dark'
        ], $themes->toArray());
    }

    /** @test */
    public function test_custom_modularity_themes()
    {
        $mockThemes = [
            resource_path('vendor/modularity/themes/custom1'),
            resource_path('vendor/modularity/themes/custom2')
        ];


        File::shouldReceive('isDirectory')->andReturn(true);
        File::shouldReceive('glob')->with(resource_path('vendor/modularity/themes/*'), GLOB_ONLYDIR)
            ->andReturn($mockThemes);

        // File::shouldReceive('isDirectory')
        //     ->andReturnUsing(function ($path) use ($mockThemes) {
        //         return in_array($path, $mockThemes);
        //     });

        $themes = customModularityThemes();

        $this->assertEquals([
            'custom1' => 'Custom1',
            'custom2' => 'Custom2'
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
            ->with('modularity-languages')
            ->andReturn(false);

        Cache::shouldReceive('set')
            ->with('modularity-languages', json_encode($translations), 600)
            ->once();

        $result = get_translations();

        $this->assertEquals($translations, $result);
    }

    /** @test */
    public function test_clear_translations()
    {
        Cache::shouldReceive('forget')
            ->with('modularity-languages')
            ->once();

        clear_translations();

        $this->assertTrue(true, 'Cache::forget() was called once with correct key');
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
