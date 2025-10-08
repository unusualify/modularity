<?php

namespace Unusualify\Modularity\Tests\Http\Controllers\Traits\Utilities;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\SystemUser\Entities\Role;
use Unusualify\Modularity\Brokers\RegisterBroker;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Events\ModularityUserRegistered;
use Unusualify\Modularity\Events\ModularityUserRegistering;
use Unusualify\Modularity\Events\VerifiedEmailRegister;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Tests\Http\Controllers\ControllerUsingCreateVerifiedEmailAccount;
use Unusualify\Modularity\Tests\ModelTestCase;

class CreateVerifiedEmailAccountTest extends ModelTestCase
{
    protected RegisterBroker $broker;

    protected DatabaseTokenRepository $tokens;

    protected array $brokerConfig;

    protected $controller;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ControllerUsingCreateVerifiedEmailAccount;

        config(['auth.passwords.modularity_users' => [
            'provider' => 'modularity_users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ]]);

        config(['auth.passwords.register_verified_users' => [
            'provider' => 'modularity_users',
            'table' => 'um_email_verification_tokens',
            'expire' => 60,
            'throttle' => 60,
        ]]);

        $this->brokerConfig = Config::get('auth.passwords.register_verified_users');

        $this->tokens = new DatabaseTokenRepository(
            $this->app['db']->connection(),
            $this->app['hash'],
            $this->brokerConfig['table'],
            '12345678',
            $this->brokerConfig['expire'],
            $this->brokerConfig['throttle'] ?? 0
        );

        $this->broker = new RegisterBroker(
            $this->tokens,
            $this->app['auth']->createUserProvider('modularity_users'),
            $this->app['db']->connection(),
            $this->brokerConfig,
        );

        Role::updateOrCreate([
            'name' => 'client-manager',
        ], [
            'guard_name' => Modularity::getAuthGuardName(),
        ]);
    }

    public function test_broker()
    {
        $broker = $this->controller->broker();
        $this->assertInstanceOf(RegisterBroker::class, $broker);
    }

    public function test_guard()
    {
        $guard = $this->controller->guard();
        $this->assertInstanceOf(Guard::class, $guard);
    }

    public function test_rules()
    {
        $rules = $this->controller->rules();
        $this->assertArrayHasKey('token', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('surname', $rules);
        $this->assertArrayHasKey('company', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    public function test_credentials()
    {
        $request = new Request;
        $request->merge([
            'email' => 'test@test.com',
            'name' => 'Test',
            'surname' => 'Test',
            'company' => 'Test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => '12345678',
            'not_required_field' => 'not_required_field',
        ]);

        $credentials = $this->controller->credentials($request);
        $this->assertSame([
            'email' => 'test@test.com',
            'name' => 'Test',
            'surname' => 'Test',
            'company' => 'Test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => '12345678',
        ], $credentials);
    }

    public function test_set_user_register()
    {
        $credentials = [
            'name' => 'Name',
            'surname' => 'Surname',
            'email' => 'email@email.com',
            'email_verified_at' => now(),
            'password' => 'password',
        ];

        $user = $this->controller->setUserRegister($credentials);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($credentials['name'], $user->name);
        $this->assertEquals($credentials['surname'], $user->surname);
        $this->assertEquals($credentials['email'], $user->email);
        $this->assertTrue(Hash::check($credentials['password'], $user->password));
    }

    public function test_register_email()
    {
        Event::fake([
            ModularityUserRegistering::class,
            ModularityUserRegistered::class,
            VerifiedEmailRegister::class,
        ]);

        $credentials = [
            'name' => 'Name',
            'surname' => 'Surname',
            'email' => 'email@email.com',
            'email_verified_at' => now(),
            'password' => 'password',
            'company' => 'Test Company', // Add company for spread_payload test
        ];

        $this->controller->registerEmail($credentials);

        $this->assertDatabaseHas(modularityConfig('tables.users', 'um_users'), [
            'name' => $credentials['name'],
            'surname' => $credentials['surname'],
            'email' => $credentials['email'],
            'email_verified_at' => $credentials['email_verified_at'],
        ]);

        // Check password separately
        $user = DB::table(modularityConfig('tables.users', 'um_users'))->where('email', $credentials['email'])->first();
        $this->assertTrue(Hash::check($credentials['password'], $user->password));

        // Test that events were dispatched
        Event::assertDispatched(VerifiedEmailRegister::class);
        Event::assertDispatched(ModularityUserRegistered::class);
        Event::assertDispatched(ModularityUserRegistering::class);
    }

    public function test_register_email_creates_company_with_spread_data()
    {
        // Don't fake events for this test - we want to test the actual observers
        $credentials = [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'company' => 'Test Company',
        ];

        $this->controller->registerEmail($credentials);

        // Verify company was created
        $this->assertDatabaseHas(modularityConfig('tables.companies', 'um_companies'), [
            'name' => 'Test Company',
        ]);

        // Verify the HasSpreadable observer worked - spread data should be in the spreads table
        $company = \Unusualify\Modularity\Entities\Company::where('name', 'Test Company')->first();
        $this->assertNotNull($company, 'Company should be created');

        // Test that the spreadable relationship was created by the observer
        $this->assertNotNull($company->spreadable, 'Spreadable relationship should exist');
        $this->assertArrayHasKey('is_personal', $company->spreadable->content);
        $this->assertFalse($company->spreadable->content['is_personal']);
    }

    public function test_register()
    {
        $plainToken = '1234567890';
        $hashedToken = $this->app['hash']->make($plainToken); // Hash the token first

        DB::table('um_email_verification_tokens')->insert([
            'email' => 'john@example.com',
            'token' => $hashedToken,
            'created_at' => now(),
        ]);

        $user = DB::table('um_email_verification_tokens')->where('email', 'john@example.com')->first();

        $request = new Request;

        $request->merge([
            'email' => $user->email,
            'name' => 'Name',
            'surname' => 'Surname',
            'company' => 'Company',
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => $plainToken,
        ]);

        $response = $this->controller->register($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $this->assertEquals('http://localhost/home', $response->getTargetUrl());

        $this->assertEquals('Registration has been completed successfully.', $response->getSession()->get('status'));
    }
}
