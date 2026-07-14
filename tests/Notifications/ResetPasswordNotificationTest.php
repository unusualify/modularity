<?php

namespace Unusualify\Modularous\Tests\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Unusualify\Modularous\Notifications\ResetPasswordNotification;
use Unusualify\Modularous\Tests\TestCase;

class ResetPasswordNotificationTest extends TestCase
{
    /** @test */
    public function test_extends_laravel_reset_password()
    {
        $notification = new ResetPasswordNotification('token');

        $this->assertInstanceOf(ResetPassword::class, $notification);
    }

    /** @test */
    public function test_to_mail_uses_callback_when_set()
    {
        $callbackExecuted = false;
        $callback = function ($notifiable, $token) use (&$callbackExecuted) {
            $callbackExecuted = true;

            return new MailMessage;
        };

        // Set the static callback using reflection
        $reflection = new \ReflectionClass(ResetPasswordNotification::class);
        $property = $reflection->getProperty('toMailCallback');
        $property->setAccessible(true);
        $property->setValue($callback);

        $notification = new ResetPasswordNotification('test-token');
        $notifiable = $this->create_mock_notifiable();

        $result = $notification->toMail($notifiable);

        $this->assertTrue($callbackExecuted);
        $this->assertInstanceOf(MailMessage::class, $result);

        // Clean up
        $property->setValue(null);
    }

    /** @test */
    public function test_to_mail_generates_message_without_callback()
    {
        // Ensure callback is null
        $reflection = new \ReflectionClass(ResetPasswordNotification::class);
        $property = $reflection->getProperty('toMailCallback');
        $property->setAccessible(true);
        $property->setValue(null);

        config([
            'app.name' => 'Test App',
            'app.locale' => 'en',
            'app.fallback_locale' => 'en',
            'auth.defaults.passwords' => 'users',
            'auth.passwords.users.expire' => 60,
        ]);

        Lang::shouldReceive('get')
            ->andReturnUsing(function ($key, $params = []) {
                if (isset($params['appName'])) {
                    return str_replace(':appName', $params['appName'], $key);
                }
                if (isset($params['userName'])) {
                    return str_replace(':userName', $params['userName'], $key);
                }
                if (isset($params['count'])) {
                    return str_replace(':count', $params['count'], $key);
                }

                return $key;
            });

        $notification = new ResetPasswordNotification('reset-token');
        $notifiable = $this->create_mock_notifiable('John Doe', 'john@example.com');

        $mailMessage = $notification->toMail($notifiable);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    /** @test */
    public function test_mail_includes_user_name_in_greeting()
    {
        $reflection = new \ReflectionClass(ResetPasswordNotification::class);
        $property = $reflection->getProperty('toMailCallback');
        $property->setAccessible(true);
        $property->setValue(null);

        config([
            'app.name' => 'Test App',
            'app.locale' => 'en',
            'app.fallback_locale' => 'en',
        ]);

        // Config::shouldReceive('get')
        //     ->withAnyArgs()
        //     ->passthru();

        $greetingCalled = false;
        Lang::shouldReceive('get')
            ->andReturnUsing(function ($key, $params = []) use (&$greetingCalled) {
                if (isset($params['userName'])) {
                    $greetingCalled = true;
                    $this->assertEquals('Jane Smith', $params['userName']);
                }

                return $key;
            });

        $notification = new ResetPasswordNotification('token');
        $notifiable = $this->create_mock_notifiable('Jane Smith', 'jane@example.com');

        $notification->toMail($notifiable);

        $this->assertTrue($greetingCalled);
    }

    /** @test */
    // public function test_mail_includes_app_name_in_subject_and_salutation()
    // {
    //     $reflection = new \ReflectionClass(ResetPasswordNotification::class);
    //     $property = $reflection->getProperty('toMailCallback');
    //     $property->setAccessible(true);
    //     $property->setValue(null);

    //     config([
    //         'app.name'            => 'MyTestApp',
    //         'app.locale'          => 'en',
    //         'app.fallback_locale' => 'en',
    //         'auth.defaults.passwords' => 'users',
    //         'auth.passwords.users.expire' => 60,
    //     ]);
    //     Lang::shouldReceive('get')
    //         ->andReturnUsing(function ($key, $params = []) {
    //             return $key;
    //         });

    //     $notification = new ResetPasswordNotification('token');
    //     $notifiable = $this->create_mock_notifiable('User', 'user@test.com');

    //     $notification->toMail($notifiable);

    //     $this->assertStringContainsString('MyTestApp', $notification->toMail($notifiable)->subject);

    //     // App name should be used at least once
    // }

    protected function create_mock_notifiable($name = 'Test User', $email = 'test@example.com')
    {
        $notifiable = new class($name, $email)
        {
            public $name;

            public $email;

            public function __construct($name, $email)
            {
                $this->name = $name;
                $this->email = $email;
            }

            public function getEmailForPasswordReset()
            {
                return $this->email;
            }
        };

        return $notifiable;
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
