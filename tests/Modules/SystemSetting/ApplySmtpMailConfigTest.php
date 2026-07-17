<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\SystemSetting;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\SystemSetting\Services\SystemSettingsService;
use Modules\SystemSetting\Support\ApplySmtpMailConfig;
use Unusualify\Modularous\Facades\SystemSettings;
use Unusualify\Modularous\Tests\ModelTestCase;

class ApplySmtpMailConfigTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(SystemSettingsService::class);
        $this->app->alias(SystemSettingsService::class, 'system.settings');
    }

    public function test_does_nothing_when_override_disabled(): void
    {
        config([
            'modularous.system_settings.mail_override_enabled' => false,
            'mail.default' => 'log',
            'mail.mailers.smtp.host' => '127.0.0.1',
        ]);

        SystemSettings::set('smtp.host', 'smtp.example.com');

        $this->app->make(ApplySmtpMailConfig::class)->apply();

        $this->assertSame('log', config('mail.default'));
        $this->assertSame('127.0.0.1', config('mail.mailers.smtp.host'));
    }

    public function test_does_nothing_when_host_empty(): void
    {
        config([
            'modularous.system_settings.mail_override_enabled' => true,
            'mail.default' => 'log',
            'mail.mailers.smtp.host' => '127.0.0.1',
        ]);

        SystemSettings::set('smtp.host', '');

        $this->app->make(ApplySmtpMailConfig::class)->apply();

        $this->assertSame('log', config('mail.default'));
        $this->assertSame('127.0.0.1', config('mail.mailers.smtp.host'));
    }

    public function test_overrides_mail_config_when_enabled_and_host_set(): void
    {
        config([
            'modularous.system_settings.mail_override_enabled' => true,
            'mail.default' => 'log',
            'mail.from.address' => 'old@example.com',
            'mail.from.name' => 'Old',
        ]);

        SystemSettings::set('smtp.host', 'smtp.example.com');
        SystemSettings::set('smtp.port', 587);
        SystemSettings::set('smtp.username', 'user@example.com');
        SystemSettings::set('smtp.password', 'secret');
        SystemSettings::set('smtp.encryption', 'tls');
        SystemSettings::set('smtp.from_address', 'noreply@example.com');
        SystemSettings::set('smtp.from_name', 'Example');

        $this->app->make(ApplySmtpMailConfig::class)->apply();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->assertSame('user@example.com', config('mail.mailers.smtp.username'));
        $this->assertSame('secret', config('mail.mailers.smtp.password'));
        $this->assertSame('noreply@example.com', config('mail.from.address'));
        $this->assertSame('Example', config('mail.from.name'));
    }

    public function test_maps_ssl_encryption_to_smtps_scheme(): void
    {
        config([
            'modularous.system_settings.mail_override_enabled' => true,
            'mail.default' => 'log',
        ]);

        SystemSettings::set('smtp.host', 'smtp.example.com');
        SystemSettings::set('smtp.port', 465);
        SystemSettings::set('smtp.encryption', 'ssl');

        $this->app->make(ApplySmtpMailConfig::class)->apply();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
    }
}
