<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\SystemSetting;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\SystemSetting\Entities\General;
use Modules\SystemSetting\Repositories\GeneralRepository;
use Modules\SystemSetting\Services\SystemSettingsService;
use Unusualify\Modularous\Facades\SystemSettings;
use Unusualify\Modularous\Tests\ModelTestCase;

class SystemSettingsServiceTest extends ModelTestCase
{
    use RefreshDatabase;

    protected SystemSettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(SystemSettingsService::class);
        $this->app->alias(SystemSettingsService::class, 'system.settings');

        $this->service = $this->app->make(SystemSettingsService::class);
    }

    public function test_get_returns_default_when_unset(): void
    {
        $this->assertNull(SystemSettings::get('site.email'));
        $this->assertSame('fallback', SystemSettings::get('site.email', 'fallback'));
    }

    public function test_get_reads_nested_values_with_dot_notation(): void
    {
        $general = General::single();
        $general->site = ['email' => 'hello@example.com'];
        $general->save();

        $this->service->forgetCache();

        $this->assertSame('hello@example.com', SystemSettings::get('site.email'));
        $this->assertTrue(SystemSettings::has('site.email'));
    }

    public function test_get_resolves_translated_locale_map(): void
    {
        $general = General::single();
        $general->site = [
            'name' => ['en' => 'English Name', 'tr' => 'Turkish Name'],
        ];
        $general->save();

        $this->service->forgetCache();

        $this->assertSame('Turkish Name', SystemSettings::get('site.name', null, 'tr'));
        $this->assertSame('English Name', SystemSettings::get('site.name', null, 'en'));
    }

    public function test_get_resolves_site_name_using_app_locale(): void
    {
        $general = General::single();
        $general->site = [
            'name' => ['en' => 'English Name', 'tr' => 'Turkish Name'],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('tr');

        $this->assertSame('Turkish Name', SystemSettings::get('site.name'));
    }

    public function test_get_returns_locale_logo_image_array(): void
    {
        $enLogo = [['original' => 'https://cdn.example/en-logo.png', 'url' => 'https://cdn.example/en-logo.png']];
        $trLogo = [['original' => 'https://cdn.example/tr-logo.png', 'url' => 'https://cdn.example/tr-logo.png']];

        $general = General::single();
        $general->site = [
            'logo' => ['en' => $enLogo, 'tr' => $trLogo],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('tr');

        $this->assertSame($trLogo, SystemSettings::get('site.logo'));
        $this->assertSame($trLogo, SystemSettings::value('site.logo'));
        $this->assertIsNotString(SystemSettings::get('site.logo'));
        $this->assertIsNotString(SystemSettings::value('site.logo'));
    }

    public function test_value_does_not_auto_unwrap_logo_to_original_url(): void
    {
        $trLogo = [
            ['original' => 'https://cdn.example/tr-logo.png'],
        ];

        $general = General::single();
        $general->site = [
            'logo' => [
                'en' => [
                    ['original' => 'https://cdn.example/en-logo.png'],
                    ['original' => 'https://cdn.example/en-logo-2.png'],
                ],
                'tr' => $trLogo,
            ],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('tr');

        $this->assertSame($trLogo, SystemSettings::value('site.logo'));
        $this->assertSame('https://cdn.example/tr-logo.png', SystemSettings::value('site.logo.original'));
    }

    public function test_get_falls_back_to_fallback_locale_when_current_locale_missing(): void
    {
        config(['app.fallback_locale' => 'en']);

        $enLogo = [['original' => 'https://cdn.example/en-logo.png']];

        $general = General::single();
        $general->site = [
            'name' => ['en' => 'English Name'],
            'logo' => [
                'en' => $enLogo,
            ],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('nl');

        $this->assertSame('English Name', SystemSettings::get('site.name'));
        $this->assertSame($enLogo, SystemSettings::get('site.logo'));
        $this->assertSame($enLogo, SystemSettings::value('site.logo'));
        $this->assertSame('https://cdn.example/en-logo.png', SystemSettings::value('site.logo.original'));
    }

    public function test_first_returns_first_list_element(): void
    {
        $general = General::single();
        $general->social = [
            ['platform' => 'twitter', 'url' => 'https://x.com/b2press'],
            ['platform' => 'linkedin', 'url' => 'https://linkedin.com/company/b2press'],
        ];
        $general->save();

        $this->service->forgetCache();

        $this->assertSame(
            ['platform' => 'twitter', 'url' => 'https://x.com/b2press'],
            SystemSettings::first('social'),
        );
    }

    public function test_get_walks_through_locale_map_for_deeper_paths(): void
    {
        $general = General::single();
        $general->site = [
            'logo' => [
                'en' => [['original' => 'https://cdn.example/en-logo.png']],
            ],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('en');

        $this->assertSame('https://cdn.example/en-logo.png', SystemSettings::get('site.logo.0.original'));
    }

    public function test_get_expands_leaf_via_locale_index_leaf_pattern(): void
    {
        $general = General::single();
        $general->site = [
            'logo' => [
                'en' => [['frontend' => 'x']],
            ],
        ];
        $general->save();

        $this->service->forgetCache();

        $this->assertSame('x', SystemSettings::get('site.logo.frontend', null, 'en'));
        $this->assertSame('x', SystemSettings::value('site.logo.frontend', null, 'en'));
    }

    public function test_get_expands_leaf_via_locale_leaf_pattern(): void
    {
        $general = General::single();
        $general->site = [
            'logo' => [
                'en' => ['frontend' => 'y'],
            ],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('en');

        $this->assertSame('y', SystemSettings::get('site.logo.frontend'));
        $this->assertSame('y', SystemSettings::value('site.logo.frontend'));
    }

    public function test_get_expands_leaf_via_locale_leaf_index_pattern(): void
    {
        $general = General::single();
        $general->site = [
            'logo' => [
                'en' => ['frontend' => ['z']],
            ],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('en');

        $this->assertSame('z', SystemSettings::get('site.logo.frontend'));
        $this->assertSame('z', SystemSettings::value('site.logo.frontend'));
    }

    public function test_get_expands_original_leaf_through_locale_and_first_index(): void
    {
        $general = General::single();
        $general->site = [
            'logo' => [
                'en' => [['original' => 'https://cdn.example/en-logo.png']],
                'tr' => [['original' => 'https://cdn.example/tr-logo.png']],
            ],
        ];
        $general->save();

        $this->service->forgetCache();

        $this->assertSame('https://cdn.example/en-logo.png', SystemSettings::get('site.logo.original', null, 'en'));
        $this->assertSame('https://cdn.example/en-logo.png', SystemSettings::value('site.logo.original', null, 'en'));
    }

    public function test_get_expands_original_leaf_using_app_locale(): void
    {
        $general = General::single();
        $general->site = [
            'logo' => [
                'en' => [['original' => 'https://cdn.example/en-logo.png']],
                'tr' => [['original' => 'https://cdn.example/tr-logo.png']],
            ],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('tr');

        $this->assertSame('https://cdn.example/tr-logo.png', SystemSettings::get('site.logo.original'));
        $this->assertSame('https://cdn.example/tr-logo.png', SystemSettings::value('site.logo.original'));
    }

    public function test_get_expands_leaf_falls_back_when_locale_missing(): void
    {
        config(['app.fallback_locale' => 'en']);

        $general = General::single();
        $general->site = [
            'logo' => [
                'en' => [[
                    'original' => 'https://cdn.example/en-logo.png',
                    'frontend' => 'en-frontend',
                ]],
            ],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('nl');

        $this->assertSame('https://cdn.example/en-logo.png', SystemSettings::get('site.logo.original'));
        $this->assertSame('en-frontend', SystemSettings::get('site.logo.frontend'));
    }

    public function test_get_site_logo_returns_locale_array_without_jumping_to_original(): void
    {
        $enLogo = [['original' => 'https://cdn.example/en-logo.png']];

        $general = General::single();
        $general->site = [
            'logo' => [
                'en' => $enLogo,
            ],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('en');

        $this->assertSame($enLogo, SystemSettings::get('site.logo'));
        $this->assertSame($enLogo, SystemSettings::value('site.logo'));
        $this->assertIsNotString(SystemSettings::get('site.logo'));
    }

    public function test_get_direct_leaf_path_still_works(): void
    {
        $general = General::single();
        $general->site = [
            'logo' => [
                'original' => 'https://cdn.example/flat-logo.png',
                'frontend' => 'flat-frontend',
            ],
        ];
        $general->save();

        $this->service->forgetCache();

        $this->assertSame('https://cdn.example/flat-logo.png', SystemSettings::get('site.logo.original'));
        $this->assertSame('flat-frontend', SystemSettings::get('site.logo.frontend'));
    }

    public function test_get_leaf_returns_default_when_still_empty(): void
    {
        $general = General::single();
        $general->site = [
            'logo' => [
                'en' => [['url' => '']],
            ],
        ];
        $general->save();

        $this->service->forgetCache();
        app()->setLocale('en');

        $this->assertSame('missing', SystemSettings::get('site.logo.original', 'missing'));
        $this->assertSame('missing', SystemSettings::get('site.logo.frontend', 'missing'));
        $this->assertSame('missing', SystemSettings::value('site.logo.original', 'missing'));
    }

    public function test_set_persists_value_and_invalidates_cache(): void
    {
        Cache::flush();

        SystemSettings::set('seo.robots_txt', "User-agent: *\nDisallow: /private");

        $this->assertSame("User-agent: *\nDisallow: /private", SystemSettings::get('seo.robots_txt'));

        $fresh = General::single()->refresh();
        $this->assertSame("User-agent: *\nDisallow: /private", $fresh->seo['robots_txt'] ?? null);
    }

    public function test_repository_encrypts_smtp_password_and_masks_on_read(): void
    {
        /** @var GeneralRepository $repository */
        $repository = $this->app->make(GeneralRepository::class);

        $general = General::single();
        $repository->update($general->id, [
            'smtp' => [
                'host' => 'smtp.example.com',
                'password' => 'secret-pass',
            ],
        ]);

        $raw = General::withoutGlobalScopes()->find($general->id);
        $content = json_decode((string) $raw->getRawOriginal('content'), true);

        $this->assertNotSame('secret-pass', $content['smtp']['password'] ?? null);

        $this->service->forgetCache();

        $this->assertSame('secret-pass', SystemSettings::get('smtp.password'));
    }

    public function test_repository_preserves_smtp_password_when_blank_on_update(): void
    {
        /** @var GeneralRepository $repository */
        $repository = $this->app->make(GeneralRepository::class);

        $general = General::single();
        $repository->update($general->id, [
            'smtp' => [
                'host' => 'smtp.example.com',
                'password' => 'secret-pass',
            ],
        ]);

        $repository->update($general->id, [
            'smtp' => [
                'host' => 'smtp.example.com',
                'password' => '',
            ],
        ]);

        $this->service->forgetCache();

        $this->assertSame('secret-pass', SystemSettings::get('smtp.password'));
    }
}
