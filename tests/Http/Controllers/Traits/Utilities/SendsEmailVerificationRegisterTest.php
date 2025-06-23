<?php

namespace Unusualify\Modularity\Tests\Http\Controllers\Traits\Utilities;

use Unusualify\Modularity\Tests\ModelTestCase;
use Unusualify\Modularity\Brokers\RegisterBroker;
use Illuminate\Support\Facades\Config;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\Http\Controllers\ControllerUsingSendsEmailVerificationRegister;
class SendsEmailVerificationRegisterTest extends ModelTestCase
{
    protected $registerBrokerManager;

    protected DatabaseTokenRepository $tokens;

    protected RegisterBroker $broker;

    protected array $brokerConfig;

    protected $controller;

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new ControllerUsingSendsEmailVerificationRegister();

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
    }

    public function testBroker()
    {
        $broker = $this->controller->broker();
        $this->assertInstanceOf(RegisterBroker::class, $broker);

        $this->assertArrayHasKey('register_verified_users', Config::get('auth.passwords'));

        $this->assertEquals('um_email_verification_tokens', $this->brokerConfig['table']);
        $this->assertEquals('modularity_users', $this->brokerConfig['provider']);
        $this->assertEquals(60, $this->brokerConfig['expire']);
        $this->assertEquals(60, $this->brokerConfig['throttle']);
    }

    public function testValidateEmail()
    {
        $request = new Request();
        $request->merge(['email' => 'test@test.com']);

        $this->controller->validateEmail($request);
        $this->assertTrue(true);

        $request->merge(['email' => 'test']);
        $this->expectExceptionMessage("The email field must be a valid email address.");
        $this->controller->validateEmail($request);
    }

    public function testCredentials()
    {
        $request = new Request();
        $request->merge(['email' => 'test@test.com','password' => 'password']);
        $credentials = $this->controller->credentials($request);
        $this->assertEquals(['email' => 'test@test.com'], $credentials);
    }

    public function testSendVerificationLinkEmail()
    {
        Mail::fake();

        $request = new Request();
        $request->merge(['email' => 'test@test.com']);

        $response = $this->controller->sendVerificationLinkEmail($request);

        // Test that it's a redirect response
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        // Test the redirect URL
        $this->assertEquals('http://localhost', $response->getTargetUrl());

        // Test the session flash message
        $this->assertEquals('We have emailed your email verification link.', $response->getSession()->get('status'));

        User::create([
            'name' => 'test',
            'email' => 'test@test.com',
        ]);

        $failedRequest = new Request();
        $failedRequest->merge(['email' => 'test@test.com']);
        $failedResponse = $this->controller->sendVerificationLinkEmail($failedRequest);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $failedResponse);

        $this->assertTrue($failedResponse->getSession()->get('errors')->has('email'));

        $failedResponseMessages = $failedResponse->getSession()->get('errors')->get('email');

        $firstFailedResponseMessage = $failedResponseMessages[0];

        $this->assertEquals('This email is already registered.', $firstFailedResponseMessage);

    }
}
