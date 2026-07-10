<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Horizon\Horizon;
use Mockery;
use ReflectionMethod;
use Spatie\Permission\Models\Permission;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Providers\AuthServiceProvider;
use Unusualify\Modularous\Tests\TestCase;

class AuthServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_register_customizes_verify_email_url_and_mail(): void
    {
        Route::get('/admin/email/verify/{id}/{hash}', static fn () => 'ok')
            ->middleware('signed')
            ->name('admin.verification.verify');

        $provider = new AuthServiceProvider($this->app);
        $provider->register();

        $notifiable = Mockery::mock(MustVerifyEmail::class);
        $notifiable->shouldReceive('getKey')->andReturn(42);
        $notifiable->shouldReceive('getEmailForVerification')->andReturn('user@example.com');

        $notification = new VerifyEmail;
        $urlMethod = new ReflectionMethod($notification, 'verificationUrl');
        $urlMethod->setAccessible(true);
        $verificationUrl = $urlMethod->invoke($notification, $notifiable);

        $this->assertStringContainsString('admin/email/verify/42', $verificationUrl);
        $this->assertStringContainsString('signature=', $verificationUrl);

        $mailMethod = new ReflectionMethod($notification, 'buildMailMessage');
        $mailMethod->setAccessible(true);
        $mail = $mailMethod->invoke($notification, $verificationUrl);

        $this->assertInstanceOf(MailMessage::class, $mail);
    }

    public function test_boot_defines_gates_from_permissions_table(): void
    {
        Permission::create(['name' => 'articles_edit', 'guard_name' => 'web']);

        $provider = new AuthServiceProvider($this->app);
        $provider->boot();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasRole')->with('superadmin')->andReturn(false);
        $user->shouldReceive('hasPermission')->with('dashboard')->andReturn(true);
        $user->shouldReceive('hasPermission')->with('articles_edit')->andReturn(false);
        $user->shouldReceive('checkPermissionTo')->andReturn(false);
        $user->shouldReceive('hasPermissionTo')->andReturn(false);

        $this->assertTrue(Gate::forUser($user)->allows('dashboard'));
        $this->assertFalse(Gate::forUser($user)->allows('articles_edit'));
        $this->assertTrue(Gate::has('impersonate'));
    }

    public function test_boot_superadmin_gate_before_allows_all_abilities(): void
    {
        Permission::create(['name' => 'restricted', 'guard_name' => 'web']);

        $provider = new AuthServiceProvider($this->app);
        $provider->boot();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasRole')->with('superadmin')->andReturn(true);
        $user->shouldReceive('checkPermissionTo')->andReturn(false);

        $this->assertTrue(Gate::forUser($user)->allows('restricted'));
        $this->assertTrue(Gate::forUser($user)->allows('anything'));
    }

    public function test_impersonate_gate_is_registered(): void
    {
        $provider = new AuthServiceProvider($this->app);
        $provider->boot();

        $this->assertTrue(Gate::has('impersonate'));
    }

    public function test_horizon_auth_allows_local_environment(): void
    {
        $this->app['env'] = 'local';

        $provider = new AuthServiceProvider($this->app);
        $provider->boot();

        $callback = Horizon::$authUsing;
        $request = Request::create('/horizon');

        $this->assertNotNull($callback);
        $this->assertTrue($callback($request));
    }

    public function test_horizon_auth_allows_superadmin_or_allowlisted_email(): void
    {
        $this->app['env'] = 'production';

        $provider = new AuthServiceProvider($this->app);
        $provider->boot();

        $callback = Horizon::$authUsing;

        $superadminRequest = Request::create('/horizon');
        $superadminRequest->setUserResolver(static fn () => (object) [
            'is_superadmin' => true,
            'email' => 'other@example.com',
        ]);

        $allowlistedRequest = Request::create('/horizon');
        $allowlistedRequest->setUserResolver(static fn () => (object) [
            'is_superadmin' => false,
            'email' => 'software-dev@unusualgrowth.cm',
        ]);

        $deniedRequest = Request::create('/horizon');
        $deniedRequest->setUserResolver(static fn () => (object) [
            'is_superadmin' => false,
            'email' => 'denied@example.com',
        ]);

        $this->assertTrue($callback($superadminRequest));
        $this->assertTrue($callback($allowlistedRequest));
        $this->assertFalse($callback($deniedRequest));
    }

    public function test_protected_authorize_and_role_helpers(): void
    {
        $provider = new AuthServiceProvider($this->app);

        $authorize = new ReflectionMethod($provider, 'authorize');
        $authorize->setAccessible(true);
        $this->assertTrue($authorize->invoke($provider, (object) [], static fn () => true));

        $userHasRole = new ReflectionMethod($provider, 'userHasRole');
        $userHasRole->setAccessible(true);
        $this->assertTrue($userHasRole->invoke($provider, (object) ['roles' => 'admin'], ['admin', 'editor']));
        $this->assertFalse($userHasRole->invoke($provider, (object) ['roles' => 'viewer'], ['admin']));

        $userHasPermission = new ReflectionMethod($provider, 'userHasPermission');
        $userHasPermission->setAccessible(true);
        $this->assertTrue($userHasPermission->invoke($provider, (object) ['permissions' => 'edit'], ['edit']));
        $this->assertFalse($userHasPermission->invoke($provider, (object) ['permissions' => 'view'], ['edit']));
    }
}
