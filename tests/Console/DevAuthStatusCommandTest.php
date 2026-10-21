<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Console;

use Mockery;
use Unusualify\Modularous\Services\DevAuth\DevAutoLoginService;
use Unusualify\Modularous\Tests\TestCase;

class DevAuthStatusCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_succeeds_when_dev_auto_login_resolves_a_user(): void
    {
        $this->bindStatus([
            'enabled' => true,
            'flag' => true,
            'environment' => 'local',
            'environment_allowed' => true,
            'configured_email' => 'dev@example.test',
            'user' => ['id' => 1, 'email' => 'dev@example.test'],
            'config_cached' => false,
            'routes_cached' => false,
            'vite_hot' => true,
        ]);

        $this->artisan('modularous:dev-auth:status')
            ->expectsOutputToContain('Dev auto-login active: panel requests run as dev@example.test.')
            ->assertExitCode(0);
    }

    public function test_fails_with_hints_when_flag_is_off(): void
    {
        $this->bindStatus([
            'enabled' => false,
            'flag' => false,
            'environment' => 'local',
            'environment_allowed' => true,
            'configured_email' => null,
            'user' => null,
            'config_cached' => true,
            'routes_cached' => true,
            'vite_hot' => false,
        ]);

        $this->artisan('modularous:dev-auth:status')
            ->expectsOutputToContain('Set MODULAROUS_DEV_AUTO_LOGIN=true')
            ->expectsOutputToContain('php artisan config:clear')
            ->expectsOutputToContain('php artisan route:clear')
            ->expectsOutputToContain('Vite dev server is not running')
            ->assertExitCode(1);
    }

    public function test_warns_about_email_fallback(): void
    {
        $this->bindStatus([
            'enabled' => true,
            'flag' => true,
            'environment' => 'local',
            'environment_allowed' => true,
            'configured_email' => 'missing@example.test',
            'user' => ['id' => 1, 'email' => 'admin@example.test'],
            'config_cached' => false,
            'routes_cached' => false,
            'vite_hot' => true,
        ]);

        $this->artisan('modularous:dev-auth:status')
            ->expectsOutputToContain('missing@example.test matches no user')
            ->assertExitCode(0);
    }

    public function test_json_option_prints_status(): void
    {
        $this->bindStatus([
            'enabled' => true,
            'flag' => true,
            'environment' => 'local',
            'environment_allowed' => true,
            'configured_email' => null,
            'user' => ['id' => 1, 'email' => 'admin@example.test'],
            'config_cached' => false,
            'routes_cached' => false,
            'vite_hot' => true,
        ]);

        $this->artisan('modularous:dev-auth:status', ['--json' => true])
            ->expectsOutputToContain('"email": "admin@example.test"')
            ->assertExitCode(0);
    }

    /**
     * @param array<string, mixed> $status
     */
    private function bindStatus(array $status): void
    {
        $service = Mockery::mock(DevAutoLoginService::class);
        $service->shouldReceive('status')->andReturn($status);

        $this->app->instance(DevAutoLoginService::class, $service);
    }
}
