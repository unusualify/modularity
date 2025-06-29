<?php

namespace Unusualify\Modularity\Tests\Brokers;

use Unusualify\Modularity\Brokers\RegisterBrokerManager;
use Unusualify\Modularity\Tests\ModelTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Unusualify\Modularity\Brokers\RegisterBroker;

class RegisterBrokerManagerTest extends ModelTestCase
{
    private $brokerManager;

    private array $brokerConfig;

    private DatabaseTokenRepository $tokens;


    public function setUp(): void
    {
        parent::setUp();

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

        $this->brokerManager = new RegisterBrokerManager($this->app);
    }

    public function testBrokerManagerGetInstance()
    {
        $this->assertInstanceOf(RegisterBrokerManager::class, $this->brokerManager);
    }

    public function  testResolveWithoutConfig()
    {
        $name = 'register_verified_users';

        $this->app['config']->set("auth.passwords.{$name}", null);

        $this->expectExceptionMessage("Email verification broker [$name] is not defined.");
        $this->brokerManager->resolve($name);
    }

    public function testResolve()
    {
        $name = 'register_verified_users';
        $broker = $this->brokerManager->resolve($name);
        $this->assertInstanceOf(RegisterBroker::class, $broker);
    }

    public function testBrokerManagerGetDefaultDriver()
    {
        $defaultDriver = $this->brokerManager->getDefaultDriver();
        $this->assertEquals('register_verified_users', $defaultDriver);
    }
}