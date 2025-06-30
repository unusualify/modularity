<?php

namespace Unusualify\Modularity\Tests\Http\Controllers\Traits\Utilities;

use Unusualify\Modularity\Tests\ModelTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Unusualify\Modularity\Brokers\RegisterBroker;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Unusualify\Modularity\Entities\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Unusualify\Modularity\Tests\Http\Controllers\ControllerUsingCreateVerifiedEmailAccount;
use Illuminate\Support\Facades\Event;
use Modules\SystemUser\Entities\Role;
use Unusualify\Modularity\Events\VerifiedEmailRegister;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\Register;

class CreateVerifiedEmailAccountTest extends ModelTestCase
{
    protected RegisterBroker $broker;

    protected DatabaseTokenRepository $tokens;

    protected array $brokerConfig;

    protected $controller;

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new ControllerUsingCreateVerifiedEmailAccount();


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

        $this->brokerConfig = Config::get("auth.passwords.register_verified_users");

        $this->tokens = new DatabaseTokenRepository(
            $this->app['db']->connection(),
            $this->app['hash'],
            $this->brokerConfig['table'],
            "12345678",
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

    public function testBroker()
    {
        $broker = $this->controller->broker();
        $this->assertInstanceOf(RegisterBroker::class, $broker);
    }

    public function testGuard()
    {
        $guard = $this->controller->guard();
        $this->assertInstanceOf(Guard::class, $guard);
    }

    public function testRules()
    {
        $rules = $this->controller->rules();
        $this->assertArrayHasKey('token', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('surname', $rules);
        $this->assertArrayHasKey('company', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    public function testCredentials()
    {
        $request = new Request();
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
            'token' => '12345678'
        ], $credentials);
    }

    public function testSetUserRegister()
    {
        $credentials = [
            'name' => 'Name ',
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

    public function testRegisterEmail()
    {
        Event::fake();
        $credentials = [
            'name' => 'Name',
            'surname' => 'Surname',
            'email' => 'email@email.com',
            'email_verified_at' => now(),
            'password' => 'password',
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

        Event::assertDispatched(VerifiedEmailRegister::class);

    }

    public function testRegister()
    {
        $plainToken = '1234567890';
        $hashedToken = $this->app['hash']->make($plainToken);// Hash the token first

        DB::table('um_email_verification_tokens')->insert([
            'email' => 'john@example.com',
            'token' => $hashedToken,
            'created_at' => now(),
        ]);

        $user = DB::table('um_email_verification_tokens')->where('email', 'john@example.com')->first();

        $request = new Request();

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
