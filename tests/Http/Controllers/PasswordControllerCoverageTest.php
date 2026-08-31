<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\View;
use Mockery;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\PasswordController;
use Unusualify\Modularous\Tests\ModelTestCase;
use Unusualify\Modularous\Traits\Traitify;

class PasswordControllerCoverageTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function broker_and_guard_use_modularous_auth_names(): void
    {
        Modularous::shouldReceive('getAuthProviderName')->andReturn('users');
        Modularous::shouldReceive('getAuthGuardName')->andReturn('modularous');

        $broker = Mockery::mock(PasswordBroker::class);
        Password::shouldReceive('broker')->with('users')->andReturn($broker);

        $guard = Mockery::mock(StatefulGuard::class);
        Auth::shouldReceive('guard')->with('modularous')->andReturn($guard);

        $controller = new PasswordControllerTestable;

        $this->assertSame($broker, $controller->broker());
        $this->assertSame($guard, $controller->exposeGuard());
    }

    /** @test */
    public function reset_password_sets_token_saves_and_attempts_login(): void
    {
        Modularous::shouldReceive('getAuthGuardName')->andReturn('modularous');

        $user = Mockery::mock(User::class)->makePartial();
        $user->email = 'ada@example.com';
        $user->shouldReceive('setRememberToken')->once();
        $user->shouldReceive('save')->once()->andReturn(true);

        $guard = Mockery::mock(StatefulGuard::class);
        $guard->shouldReceive('attempt')
            ->once()
            ->with(['email' => 'ada@example.com', 'password' => 'Secret123!'], true)
            ->andReturn(true);
        Auth::shouldReceive('guard')->with('modularous')->andReturn($guard);

        $controller = new PasswordControllerTestable;
        $controller->exposeResetPassword($user, 'Secret123!');

        $this->assertTrue(true);
    }

    /** @test */
    public function show_form_returns_reset_view_payload(): void
    {
        Config::set('modularous.form_drafts.reset_password_form', [
            'email' => ['type' => 'text', 'name' => 'email'],
            'password' => ['type' => 'password', 'name' => 'password'],
        ]);

        if (! RouteFacade::has('admin.password.reset.link')) {
            RouteFacade::get('/admin/password/reset-link', fn () => 'ok')->name('admin.password.reset.link');
        }
        if (! RouteFacade::has('admin.register.password.generate')) {
            RouteFacade::post('/admin/password/generate', fn () => 'ok')->name('admin.register.password.generate');
        }

        $request = Request::create('/password/reset/token-1?email=ada@example.com', 'GET', [
            'email' => 'ada@example.com',
        ]);
        $route = new Route(['GET'], '/password/reset/{token}', [
            'as' => 'admin.register.password.generate.form',
        ]);
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);

        $view = Mockery::mock(ViewContract::class);
        View::shouldReceive('make')
            ->once()
            ->withArgs(function ($name, $data) {
                return str_contains((string) $name, 'auth.passwords.reset')
                    && ($data['formAttributes']['modelValue']['token'] ?? null) === 'token-1'
                    && ($data['formAttributes']['modelValue']['email'] ?? null) === 'ada@example.com';
            })
            ->andReturn($view);

        $controller = new PasswordControllerTestable;
        $this->assertSame($view, $controller->showForm($request, 'token-1'));
    }

    /** @test */
    public function save_password_returns_json_validation_errors(): void
    {
        $request = Request::create('/password/save', 'POST', [
            'email' => '',
            'token' => '',
            'password' => '',
        ]);
        $request->headers->set('Accept', 'application/json');

        $controller = new PasswordControllerTestable;
        $response = $controller->savePassword($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertArrayHasKey('errors', $data);
    }

    /** @test */
    public function save_password_handles_broker_failure_json(): void
    {
        Modularous::shouldReceive('getAuthProviderName')->andReturn('users');

        $failBroker = Mockery::mock(PasswordBroker::class);
        $failBroker->shouldReceive('reset')->once()->andReturn(Password::INVALID_TOKEN);
        Password::shouldReceive('broker')->with('users')->andReturn($failBroker);

        $failRequest = Request::create('/password/save', 'POST', [
            'token' => 'bad',
            'email' => 'ada@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);
        $failRequest->headers->set('Accept', 'application/json');

        $controller = new PasswordControllerTestable;
        $failResponse = $controller->savePassword($failRequest);
        $this->assertInstanceOf(JsonResponse::class, $failResponse);
        $this->assertArrayHasKey('message', $failResponse->getData(true));
    }

    /** @test */
    public function save_password_handles_broker_success_json(): void
    {
        Modularous::shouldReceive('getAuthProviderName')->andReturn('users');
        Modularous::shouldReceive('getAdminRouteNamePrefix')->andReturn('admin');

        $okBroker = Mockery::mock(PasswordBroker::class);
        $okBroker->shouldReceive('reset')->once()->andReturn(Password::PASSWORD_RESET);
        Password::shouldReceive('broker')->with('users')->andReturn($okBroker);

        if (! RouteFacade::has('admin.profile')) {
            RouteFacade::get('/admin/profile', fn () => 'ok')->name('admin.profile');
        }

        $user = User::factory()->create([
            'email' => 'ada-password-coverage@example.com',
            'email_verified_at' => null,
        ]);

        $okRequest = Request::create('/password/save', 'POST', [
            'token' => 'good',
            'email' => $user->email,
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);
        $okRequest->headers->set('Accept', 'application/json');

        $controller = new PasswordControllerTestable;
        $okResponse = $controller->savePassword($okRequest);
        $this->assertInstanceOf(JsonResponse::class, $okResponse);
        $data = $okResponse->getData(true);
        $this->assertArrayHasKey('message', $data);
        $this->assertTrue(isset($data['redirector']) || isset($data['variant']));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}

class PasswordControllerTestable extends PasswordController
{
    use Traitify;

    public function __construct()
    {
        // Skip guest middleware registration from parent constructor.
    }

    public function exposeGuard()
    {
        return $this->guard();
    }

    public function exposeResetPassword($user, $password): void
    {
        $this->setUserPassword($user, $password);
        $this->resetPassword($user, $password);
    }

    protected function setUserPassword($user, $password)
    {
        $user->password = Hash::make($password);
    }

    public function createFormSchema($inputs)
    {
        return is_array($inputs) ? $inputs : [];
    }
}
