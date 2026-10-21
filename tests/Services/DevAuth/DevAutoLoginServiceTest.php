<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\DevAuth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Modules\SystemUser\Repositories\UserRepository;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\DevAuth\DevAutoLoginService;
use Unusualify\Modularous\Tests\ModelTestCase;

class DevAutoLoginServiceTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $users = Mockery::mock(UserRepository::class);
        $users->shouldReceive('getModel')->andReturn(new User);
        $this->app->instance(UserRepository::class, $users);

        config()->set('modularous.dev_auth.allowed_environments', ['local', 'testing']);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_is_disabled_by_default(): void
    {
        config()->set('modularous.dev_auth.enabled', false);

        $this->assertFalse($this->service()->isEnabled());
    }

    public function test_is_enabled_when_flag_is_on_in_allowed_environment(): void
    {
        config()->set('modularous.dev_auth.enabled', true);
        $this->app['env'] = 'local';

        $this->assertTrue($this->service()->isEnabled());
    }

    public function test_is_not_enabled_in_forbidden_environment(): void
    {
        config()->set('modularous.dev_auth.enabled', true);
        $this->app['env'] = 'production';

        $this->assertFalse($this->service()->isEnabled());
    }

    public function test_assert_safe_environment_throws_when_flag_is_on_in_production(): void
    {
        config()->set('modularous.dev_auth.enabled', true);
        $this->app['env'] = 'production';

        $this->expectException(RuntimeException::class);

        $this->service()->assertSafeEnvironment();
    }

    public function test_assert_safe_environment_passes_when_flag_is_off_in_production(): void
    {
        config()->set('modularous.dev_auth.enabled', false);
        $this->app['env'] = 'production';

        $this->service()->assertSafeEnvironment();

        $this->assertTrue(true);
    }

    public function test_resolve_user_id_by_configured_email(): void
    {
        User::factory()->create(['email' => 'other@example.test']);
        $target = User::factory()->create(['email' => 'dev@example.test']);

        config()->set('modularous.dev_auth.email', 'dev@example.test');

        $this->assertSame((int) $target->id, $this->service()->resolveUserId());
    }

    public function test_resolve_user_id_falls_back_to_first_superadmin(): void
    {
        config()->set('modularous.dev_auth.email', null);

        User::factory()->create();
        $superadmin = User::factory()->create();
        Role::create(['name' => DevAutoLoginService::SUPERADMIN_ROLE, 'guard_name' => Modularous::getAuthGuardName()]);
        $superadmin->assignRole(DevAutoLoginService::SUPERADMIN_ROLE);

        $this->assertSame((int) $superadmin->id, $this->service()->resolveUserId());
    }

    public function test_resolve_user_id_falls_back_to_superadmin_when_email_matches_no_user(): void
    {
        config()->set('modularous.dev_auth.email', 'missing@example.test');

        $superadmin = User::factory()->create();
        Role::create(['name' => DevAutoLoginService::SUPERADMIN_ROLE, 'guard_name' => Modularous::getAuthGuardName()]);
        $superadmin->assignRole(DevAutoLoginService::SUPERADMIN_ROLE);

        $this->assertSame((int) $superadmin->id, $this->service()->resolveUserId());
    }

    public function test_resolve_user_id_returns_null_when_no_user_matches(): void
    {
        config()->set('modularous.dev_auth.email', 'missing@example.test');

        $this->assertNull($this->service()->resolveUserId());
    }

    public function test_status_reports_resolved_user_and_environment(): void
    {
        config()->set('modularous.dev_auth.enabled', true);
        config()->set('modularous.dev_auth.email', 'dev@example.test');
        $this->app['env'] = 'testing';
        $user = User::factory()->create(['email' => 'dev@example.test']);

        $status = $this->service()->status();

        $this->assertTrue($status['enabled']);
        $this->assertTrue($status['environment_allowed']);
        $this->assertSame('testing', $status['environment']);
        $this->assertSame(['id' => (int) $user->id, 'email' => 'dev@example.test'], $status['user']);
        $this->assertArrayHasKey('vite_hot', $status);
    }

    public function test_status_skips_user_lookup_when_flag_is_off(): void
    {
        config()->set('modularous.dev_auth.enabled', false);
        User::factory()->create(['email' => 'dev@example.test']);
        config()->set('modularous.dev_auth.email', 'dev@example.test');

        $status = $this->service()->status();

        $this->assertFalse($status['enabled']);
        $this->assertNull($status['user']);
    }

    private function service(): DevAutoLoginService
    {
        return new DevAutoLoginService($this->app);
    }
}
