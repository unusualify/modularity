<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits\Utilities;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Mockery;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\HandlesMfaAuthentication;
use Unusualify\Modularous\Tests\TestCase;

class HandlesMfaAuthenticationCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeController(): object
    {
        $redirector = Mockery::mock(Redirector::class);
        $redirector->shouldReceive('to')->andReturnUsing(function ($url) {
            return new class($url)
            {
                public function __construct(private string $url) {}

                public function getTargetUrl(): string
                {
                    return $this->url;
                }

                public function withErrors($errors)
                {
                    return redirect()->to($this->url)->withErrors($errors);
                }
            };
        });
        $redirector->shouldReceive('intended')->andReturnUsing(fn ($url) => redirect()->to($url));

        $authManager = Mockery::mock();
        $guard = Mockery::mock();
        $guard->shouldReceive('loginUsingId')->andReturnNull();
        $guard->shouldReceive('logout')->andReturnNull();
        $authManager->shouldReceive('guard')->andReturn($guard);

        return new class($redirector, $authManager)
        {
            use HandlesMfaAuthentication;

            public $redirector;

            public $authManager;

            public string $redirectTo = '/admin';

            public function __construct($redirector, $authManager)
            {
                $this->redirector = $redirector;
                $this->authManager = $authManager;
            }

            protected function guard()
            {
                return $this->authManager->guard('modularous');
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };
    }

    /** @test */
    public function config_helpers_and_challenge_route_resolution(): void
    {
        config([
            'modularous.security.mfa.enabled' => true,
            'modularous.security.mfa.remove_password_login' => true,
            'modularous.security.mfa.provider' => 'email_otp',
            'modularous.security.mfa.session_key' => '2fa:user:id',
            'modularous.security.mfa.flow_session_key' => '2fa:flow:key',
            'modularous.security.mfa.otp_field' => 'verify-code',
            'modularous.security.mfa.challenge_page' => 'login_2fa',
            'modularous.security.mfa.login_page' => 'login_mfa',
            'modularous.security.mfa.challenge_form_route' => 'login-2fa.form',
            'modularous.security.mfa.registration_success_route' => 'register.verification.success',
            'modularous.security.mfa.email_otp.length' => 6,
            'modularous.security.mfa.email_otp.expire_minutes' => 10,
            'modularous.security.mfa.email_otp.max_attempts' => 5,
            'modularous.security.mfa.email_otp.cache_prefix' => 'mfa:email-otp',
            'modularous.security.mfa.register_first_time' => true,
        ]);

        Route::shouldReceive('hasAdmin')->andReturnUsing(fn ($name) => $name);
        Route::shouldReceive('has')->andReturn(false);

        $controller = $this->makeController();

        $this->assertTrue($controller->call('isMfaEnabled'));
        $this->assertTrue($controller->call('shouldUseMfaLoginFlow'));
        $this->assertSame('email_otp', $controller->call('mfaProvider'));
        $this->assertTrue($controller->call('usesEmailOtpMfaProvider'));
        $this->assertSame('2fa:user:id', $controller->call('mfaSessionKey'));
        $this->assertSame('2fa:flow:key', $controller->call('mfaFlowSessionKey'));
        $this->assertSame('verify-code', $controller->call('mfaOtpField'));
        $this->assertSame('login_2fa', $controller->call('mfaChallengePageKey'));
        $this->assertSame('login_mfa', $controller->call('mfaLoginPageKey'));
        $this->assertSame(6, $controller->call('mfaCodeLength'));
        $this->assertSame(10, $controller->call('mfaCodeExpiryMinutes'));
        $this->assertSame(5, $controller->call('mfaCodeMaxAttempts'));
        $this->assertSame('mfa:email-otp', $controller->call('mfaCachePrefix'));
        $this->assertTrue($controller->call('mfaAllowsRegistrationFromLogin'));
        $this->assertSame('login.form', $controller->call('resolveChallengeRouteName'));
        $this->assertSame('register.verification.success', $controller->call('resolveRegistrationSuccessRouteName'));

        $code = $controller->call('generateMfaCode');
        $this->assertSame(6, strlen($code));

        $user = (object) ['google_2fa_secret' => 'ABC', 'google_2fa_enabled' => true];
        $this->assertTrue($controller->call('userHasMfaEnabled', $user));
        $this->assertFalse($controller->call('userHasMfaEnabled', (object) []));
    }

    /** @test */
    public function email_otp_validation_session_and_failure_responses(): void
    {
        config([
            'modularous.security.mfa.enabled' => true,
            'modularous.security.mfa.provider' => 'email_otp',
            'modularous.security.mfa.session_key' => '2fa:user:id',
            'modularous.security.mfa.flow_session_key' => '2fa:flow:key',
            'modularous.security.mfa.otp_field' => 'verify-code',
            'modularous.security.mfa.challenge_form_route' => 'login-2fa.form',
            'modularous.security.mfa.email_otp.max_attempts' => 2,
            'modularous.security.mfa.email_otp.expire_minutes' => 10,
            'modularous.security.mfa.email_otp.cache_prefix' => 'mfa:email-otp',
        ]);

        Route::shouldReceive('hasAdmin')->andReturnUsing(fn ($name) => $name);
        Route::shouldReceive('has')->andReturn(true);
        Modularous::shouldReceive('getAuthGuardName')->andReturn('modularous');

        $controller = $this->makeController();
        $request = Request::create('/', 'POST', ['verify-code' => '123456']);
        $request->setLaravelSession($this->app['session']->driver());

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 15;
        $user->email = 'a@example.com';

        $this->assertNull($controller->call('resolveMfaUserFromSession', $request));
        $this->assertFalse($controller->call('validateMfaOtp', $user, $request));

        $flowKey = 'mfa:email-otp:test-flow';
        $request->session()->put('2fa:flow:key', $flowKey);
        $request->session()->put('2fa:user:id', 15);
        Cache::put($flowKey, [
            'user_id' => 15,
            'email' => 'a@example.com',
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10)->toDateTimeString(),
        ], now()->addMinutes(10));

        $this->assertTrue($controller->call('validateMfaOtp', $user, $request));

        Cache::put($flowKey, [
            'user_id' => 15,
            'code_hash' => Hash::make('000000'),
            'attempts' => 0,
        ], now()->addMinutes(10));
        $bad = Request::create('/', 'POST', ['verify-code' => '111111']);
        $bad->setLaravelSession($request->session());
        $this->assertFalse($controller->call('validateMfaOtp', $user, $bad));

        Cache::put($flowKey, [
            'user_id' => 15,
            'code_hash' => Hash::make('000000'),
            'attempts' => 2,
        ], now()->addMinutes(10));
        $this->assertFalse($controller->call('validateMfaOtp', $user, $bad));

        $json = Request::create('/', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $json->headers->set('Accept', 'application/json');
        $failed = $controller->call('mfaLoginFailedResponse', $json, 'nope');
        $this->assertInstanceOf(JsonResponse::class, $failed);
        $this->assertSame(422, $failed->getStatusCode());

        $failMfa = $controller->call('mfaFailureResponse', $json, 'bad otp');
        $this->assertSame(422, $failMfa->getStatusCode());

        $controller->call('clearMfaSession', $request);
        $this->assertNull($request->session()->get('2fa:user:id'));
    }

    /** @test */
    public function start_mfa_challenge_and_complete_login_json_paths(): void
    {
        config([
            'modularous.security.mfa.enabled' => false,
            'modularous.security.mfa.provider' => 'email_otp',
            'modularous.security.mfa.session_key' => '2fa:user:id',
            'modularous.security.mfa.flow_session_key' => '2fa:flow:key',
            'modularous.security.mfa.challenge_form_route' => 'login-2fa.form',
            'modularous.security.mfa.email_otp.cache_prefix' => 'mfa:email-otp',
            'modularous.security.mfa.email_otp.length' => 6,
            'modularous.security.mfa.email_otp.expire_minutes' => 10,
        ]);

        \Illuminate\Support\Facades\Route::get('/login-2fa', fn () => 'ok')->name('login-2fa.form');
        $modularous = Mockery::mock(\Unusualify\Modularous\Modularous::class)->makePartial();
        $modularous->shouldReceive('getAuthGuardName')->andReturn('modularous');
        $modularous->shouldReceive('getAdminRouteNamePrefix')->andReturn('admin');
        Modularous::swap($modularous);

        $controller = $this->makeController();
        $request = Request::create('/', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->headers->set('Accept', 'application/json');
        $request->setLaravelSession($this->app['session']->driver());

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 21;
        $user->email = 'b@example.com';
        $user->shouldReceive('notify')->andReturnNull();

        $this->assertNull($controller->call('startMfaChallenge', $request, $user));

        config(['modularous.security.mfa.enabled' => true]);
        // createEmailOtpChallenge needs Notifiable; cover google2fa branch instead.
        config(['modularous.security.mfa.provider' => 'google2fa']);
        $user->google_2fa_secret = 'SECRET';
        $user->google_2fa_enabled = true;

        $challenge = $controller->call('startMfaChallenge', $request, $user);
        $this->assertInstanceOf(JsonResponse::class, $challenge);
        $this->assertArrayHasKey('redirector', $challenge->getData(true));

        $complete = $controller->call('completeMfaLogin', $request, $user);
        $this->assertInstanceOf(JsonResponse::class, $complete);
        $this->assertSame(200, $complete->getStatusCode());
    }

    /** @test */
    public function create_email_otp_challenge_and_registration_success_route_resolution(): void
    {
        config([
            'modularous.security.mfa.enabled' => true,
            'modularous.security.mfa.provider' => 'email_otp',
            'modularous.security.mfa.session_key' => '2fa:user:id',
            'modularous.security.mfa.flow_session_key' => '2fa:flow:key',
            'modularous.security.mfa.email_otp.cache_prefix' => 'mfa:email-otp',
            'modularous.security.mfa.email_otp.length' => 6,
            'modularous.security.mfa.email_otp.expire_minutes' => 10,
            'modularous.security.mfa.registration_success_route' => 'missing.registration.success',
        ]);

        Route::get('/register/success', static fn () => 'ok')->name('admin.register.verification.success');
        Route::getRoutes()->refreshNameLookups();

        $controller = $this->makeController();
        $request = Request::create('/', 'POST');
        $request->setLaravelSession($this->app['session']->driver());

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 33;
        $user->email = 'otp@example.com';
        $user->shouldReceive('notify')->once()->andReturnNull();

        $flowKey = $controller->call('createEmailOtpChallenge', $request, $user);
        $this->assertIsString($flowKey);
        $this->assertNotNull(Cache::get($flowKey));
        $this->assertSame(33, $request->session()->get('2fa:user:id'));

        $resolved = $controller->call('resolveRegistrationSuccessRouteName');
        $this->assertSame('admin.register.verification.success', $resolved);
    }
}
