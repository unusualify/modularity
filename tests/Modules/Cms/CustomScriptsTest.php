<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\Cms;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cms\Blueprint\SiteSetting\Form\SiteSettingFormInputs;
use Modules\Cms\Entities\SiteSetting;
use Modules\Cms\Services\CmsSettingsService;
use Modules\Cms\Services\SiteSettingsService;
use Modules\Cms\Support\CustomScripts;
use Modules\SystemSetting\Blueprint\General\Form\GeneralFormInputs;
use Modules\SystemSetting\Entities\General;
use Modules\SystemSetting\Services\SystemSettingsService;
use Unusualify\Modularous\Facades\CmsSettings;
use Unusualify\Modularous\Facades\SystemSettings;
use Unusualify\Modularous\Tests\ModelTestCase;

class CustomScriptsTest extends ModelTestCase
{
    use RefreshDatabase;

    protected CustomScripts $scripts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(SystemSettingsService::class);
        $this->app->alias(SystemSettingsService::class, 'system.settings');
        $this->app->singleton(CmsSettingsService::class);
        $this->app->alias(CmsSettingsService::class, 'cms.settings');
        $this->app->singleton(SiteSettingsService::class);
        $this->app->alias(SiteSettingsService::class, 'site.settings');
        $this->app->singleton(CustomScripts::class);

        $this->scripts = $this->app->make(CustomScripts::class);
    }

    public function test_empty_settings_emit_empty_html(): void
    {
        $this->assertSame('', $this->scripts->headHtml());
        $this->assertSame('', $this->scripts->bodyHtml());
    }

    public function test_system_scripts_are_emitted_when_cms_is_empty(): void
    {
        SystemSettings::set('scripts.head', '<script>window.__sysHead=1</script>');
        SystemSettings::set('scripts.body', '<script>window.__sysBody=1</script>');

        $this->assertSame('<script>window.__sysHead=1</script>', $this->scripts->headHtml());
        $this->assertSame('<script>window.__sysBody=1</script>', $this->scripts->bodyHtml());
    }

    public function test_filled_cms_scripts_win_over_system(): void
    {
        SystemSettings::set('scripts.head', '<script>window.__sysHead=1</script>');
        SystemSettings::set('scripts.body', '<script>window.__sysBody=1</script>');
        CmsSettings::set('scripts.head', '<meta name="verify" content="cms">');
        CmsSettings::set('scripts.body', '<script src="https://cms.example/pixel.js"></script>');

        $this->assertSame('<meta name="verify" content="cms">', $this->scripts->headHtml());
        $this->assertSame('<script src="https://cms.example/pixel.js"></script>', $this->scripts->bodyHtml());
    }

    public function test_non_string_and_whitespace_values_are_normalized(): void
    {
        SystemSettings::set('scripts.head', '  <script>ok</script>  ');
        SystemSettings::set('scripts.body', ['not' => 'html']);

        $this->assertSame('<script>ok</script>', $this->scripts->headHtml());
        $this->assertSame('', $this->scripts->bodyHtml());
    }

    public function test_scripts_section_is_on_cms_and_system_models(): void
    {
        $this->assertContains('scripts', SiteSetting::settingsSections());
        $this->assertContains('scripts', General::settingsSections());
    }

    public function test_admin_forms_include_head_and_body_script_fields(): void
    {
        foreach ([
            SiteSettingFormInputs::class,
            GeneralFormInputs::class,
        ] as $class) {
            $source = (string) file_get_contents((new \ReflectionClass($class))->getFileName());
            $this->assertStringContainsString("'name' => 'scripts'", $source);
            $this->assertStringContainsString("'label' => 'Head scripts'", $source);
            $this->assertStringContainsString("'label' => 'Body scripts'", $source);
        }
    }
}
