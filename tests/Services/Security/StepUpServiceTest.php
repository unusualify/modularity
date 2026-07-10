<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Notifications\StepUpCodeNotification;
use Unusualify\Modularous\Providers\RouteServiceProvider;
use Unusualify\Modularous\Services\MessageStage;
use Unusualify\Modularous\Services\Security\StepUpService;
use Unusualify\Modularous\Tests\TestCase;

class StepUpServiceTest extends TestCase
{
    private StepUpService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRouteMacros();
        $this->registerStepUpRoutes();
        $this->createUsersTable();

        config()->set('modularous.security.step_up.enabled', true);
        config()->set('modularous.security.step_up.provider', 'email_otp');
        config()->set('modularous.security.step_up.otp_field', 'verify-code');
        config()->set('modularous.security.step_up.email_otp.length', 6);
        config()->set('modularous.security.step_up.email_otp.expire_minutes', 10);
        config()->set('modularous.security.step_up.email_otp.max_attempts', 5);
        config()->set('modularous.security.step_up.email_otp.cache_prefix', 'step-up:email-otp');

        Cache::flush();
        Notification::fake();

        $this->service = new StepUpService;
    }

    public function test_configuration_accessors(): void
    {
        $this->assertTrue($this->service->isEnabled());
        $this->assertSame('verify-code', $this->service->otpField());
        $this->assertSame('step_up', $this->service->pageKey());
        $this->assertSame('admin.step-up.form', $this->service->challengeRouteName());
        $this->assertSame('admin.step-up.verify', $this->service->verifyRouteName());
        $this->assertSame('admin.step-up.resend', $this->service->resendRouteName());
    }

    public function test_challenge_payload_includes_routes_and_otp_metadata(): void
    {
        $payload = $this->service->challengePayload('payments.refund');

        $this->assertSame(route('admin.step-up.verify'), $payload['verifyUrl']);
        $this->assertSame(route('admin.step-up.resend'), $payload['resendUrl']);
        $this->assertSame('verify-code', $payload['otpField']);
        $this->assertSame(6, $payload['otpLength']);
        $this->assertSame('payments.refund', $payload['capability']);
    }

    public function test_interrupt_returns_json_challenge_for_api_requests(): void
    {
        $user = $this->createUser();
        $request = Request::create('/admin/payments/refund', 'POST', ['amount' => 10]);
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session.store']);
        $request->headers->set('Accept', 'application/json');

        $response = $this->service->interrupt($request, 'payments.refund');

        $this->assertSame(428, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertTrue($payload['step_up_required']);
        $this->assertSame(MessageStage::WARNING->value, $payload['variant']);
        $this->assertTrue($this->service->hasActiveChallenge($request));
        Notification::assertSentTo($user, StepUpCodeNotification::class);
    }

    public function test_interrupt_redirects_html_clients_to_challenge_form(): void
    {
        $user = $this->createUser();
        $request = Request::create('/admin/payments/refund', 'POST');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session.store']);

        $response = $this->service->interrupt($request, 'payments.refund');

        $this->assertTrue($response->isRedirect(route('admin.step-up.form')));
    }

    public function test_verify_accepts_valid_email_otp_for_json_requests(): void
    {
        $user = $this->createUser();
        $request = $this->makeSessionRequest($user);
        $code = $this->seedEmailOtpChallenge($request, $user, 'payments.refund');

        $verifyRequest = Request::create('/admin/step-up/verify', 'POST', ['verify-code' => $code]);
        $verifyRequest->setLaravelSession($request->session());
        $verifyRequest->headers->set('Accept', 'application/json');

        $response = $this->service->verify($verifyRequest);

        $this->assertSame(200, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertTrue($payload['step_up_verified']);
        $this->assertSame(MessageStage::SUCCESS->value, $payload['variant']);
        $this->assertNotNull($verifyRequest->session()->get('security_step_up_verified_at'));
    }

    public function test_verify_rejects_invalid_email_otp(): void
    {
        $user = $this->createUser();
        $request = $this->makeSessionRequest($user);
        $this->seedEmailOtpChallenge($request, $user, 'payments.refund');

        $verifyRequest = Request::create('/admin/step-up/verify', 'POST', ['verify-code' => '000000']);
        $verifyRequest->setLaravelSession($request->session());
        $verifyRequest->headers->set('Accept', 'application/json');

        $response = $this->service->verify($verifyRequest);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(MessageStage::WARNING->value, $response->getData(true)['variant']);
    }

    public function test_resend_issues_new_code_when_session_is_active(): void
    {
        $user = $this->createUser();
        $request = $this->makeSessionRequest($user);
        $this->seedEmailOtpChallenge($request, $user, 'payments.refund');

        Notification::fake();

        $resendRequest = Request::create('/admin/step-up/resend', 'POST');
        $resendRequest->setLaravelSession($request->session());
        $resendRequest->headers->set('Accept', 'application/json');

        $response = $this->service->resend($resendRequest);

        $this->assertSame(200, $response->getStatusCode());
        Notification::assertSentTo($user, StepUpCodeNotification::class);
    }

    public function test_resend_fails_when_verification_session_expired(): void
    {
        $request = Request::create('/admin/step-up/resend', 'POST');
        $request->setLaravelSession($this->app['session.store']);
        $request->headers->set('Accept', 'application/json');

        $response = $this->service->resend($request);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_resolve_user_from_session_returns_null_without_session_key(): void
    {
        $request = Request::create('/admin/step-up/verify', 'GET');
        $request->setLaravelSession($this->app['session.store']);

        $this->assertNull($this->service->resolveUserFromSession($request));
    }

    private function bootRouteMacros(): void
    {
        if (Route::hasMacro('hasAdmin')) {
            return;
        }

        $provider = new RouteServiceProvider($this->app);
        $method = new ReflectionMethod($provider, 'bootMacros');
        $method->setAccessible(true);
        $method->invoke($provider);
    }

    private function registerStepUpRoutes(): void
    {
        Route::get('/admin/step-up', fn () => 'form')->name('admin.step-up.form');
        Route::post('/admin/step-up/verify', fn () => 'verify')->name('admin.step-up.verify');
        Route::post('/admin/step-up/resend', fn () => 'resend')->name('admin.step-up.resend');
        Route::get('/admin/dashboard', fn () => 'dashboard')->name('admin.dashboard');
        Route::getRoutes()->refreshNameLookups();
    }

    private function createUsersTable(): void
    {
        $this->createPermissionTables();

        if (! Schema::hasTable('um_companies')) {
            Schema::create('um_companies', function ($table) {
                $table->id();
                $table->string('name')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        $tableName = (new User)->getTable();

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function ($table) {
            $table->id();
            $table->foreignId('company_id')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function createPermissionTables(): void
    {
        if (Schema::hasTable('roles')) {
            return;
        }

        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('model_has_roles', function ($table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });
    }

    private function createUser(): User
    {
        return User::withoutEvents(function () {
            return User::query()->create([
                'email' => 'step-up-' . uniqid('', true) . '@example.com',
                'password' => Hash::make('secret'),
            ]);
        });
    }

    private function makeSessionRequest(User $user): Request
    {
        $request = Request::create('/admin/payments/refund', 'POST', ['amount' => 10]);
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession($this->app['session.store']);

        return $request;
    }

    private function seedEmailOtpChallenge(Request $request, User $user, string $capability): string
    {
        $this->service->interrupt($request, $capability);

        $notification = Notification::sent($user, StepUpCodeNotification::class)->first();
        $this->assertNotNull($notification);

        return $notification->code;
    }
}
