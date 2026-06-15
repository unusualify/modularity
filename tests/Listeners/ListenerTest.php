<?php

namespace Unusualify\Modularous\Tests\Listeners;

use Illuminate\Support\Facades\Notification;
use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Listeners\Listener;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\TestCase;

class ListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_initializes_with_mail_enabled_from_config()
    {
        config(['modularous.mail.enabled' => true]);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getDirectoryPath')
            ->with('Notifications')
            ->andReturn('/path/to/notifications');

        Modularous::shouldReceive('find')
            ->with('SystemNotification')
            ->andReturn($module);

        $listener = new ConcreteListener;

        $reflection = new \ReflectionClass($listener);
        $property = $reflection->getProperty('mailEnabled');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($listener));
    }

    /**
     * @test
     */
    public function it_initializes_with_mail_disabled_from_config()
    {
        config(['modularous.mail.enabled' => false]);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getDirectoryPath')
            ->with('Notifications')
            ->andReturn('/path/to/notifications');

        Modularous::shouldReceive('find')
            ->with('SystemNotification')
            ->andReturn($module);

        $listener = new ConcreteListener;

        $reflection = new \ReflectionClass($listener);
        $property = $reflection->getProperty('mailEnabled');
        $property->setAccessible(true);

        $this->assertFalse($property->getValue($listener));
    }

    /**
     * @test
     */
    public function it_can_add_notification_path()
    {
        config(['modularous.mail.enabled' => false]);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getDirectoryPath')
            ->with('Notifications')
            ->andReturn('/initial/path');

        Modularous::shouldReceive('find')
            ->with('SystemNotification')
            ->andReturn($module);

        $listener = new ConcreteListener;
        $listener->addNotificationPath('/custom/path');

        $reflection = new \ReflectionClass($listener);
        $property = $reflection->getProperty('notificationPaths');
        $property->setAccessible(true);

        $paths = $property->getValue($listener);
        $this->assertContains('/custom/path', $paths);
    }

    /**
     * @test
     */
    public function it_can_merge_notification_paths()
    {
        config(['modularous.mail.enabled' => false]);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getDirectoryPath')
            ->with('Notifications')
            ->andReturn('/initial/path');

        Modularous::shouldReceive('find')
            ->with('SystemNotification')
            ->andReturn($module);

        $listener = new ConcreteListener;
        $listener->mergeNotificationPaths(['/path/one', '/path/two']);

        $reflection = new \ReflectionClass($listener);
        $property = $reflection->getProperty('notificationPaths');
        $property->setAccessible(true);

        $paths = $property->getValue($listener);
        $this->assertContains('/path/one', $paths);
        $this->assertContains('/path/two', $paths);
    }

    /**
     * @test
     */
    public function it_returns_null_when_notification_class_not_found()
    {
        config(['modularous.mail.enabled' => false]);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getDirectoryPath')
            ->with('Notifications')
            ->andReturn('/non/existent/path');

        Modularous::shouldReceive('find')
            ->with('SystemNotification')
            ->andReturn($module);

        $listener = new ConcreteListener;

        $event = new \stdClass;
        $result = $listener->getNotificationClassPublic($event);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_handles_event_without_sending_email_when_mail_disabled()
    {
        config(['modularous.mail.enabled' => false]);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getDirectoryPath')
            ->with('Notifications')
            ->andReturn('/path/to/notifications');

        Modularous::shouldReceive('find')
            ->with('SystemNotification')
            ->andReturn($module);

        // Notification should NOT be called when mail is disabled
        Notification::shouldReceive('route')->never();

        $listener = new ConcreteListener;
        $event = new \stdClass;
        $event->model = new \stdClass;
        $event->serializedData = [];

        $listener->handle($event);

        // Test passes if no exception thrown and Notification::route not called
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_skips_notification_when_no_matching_class_found()
    {
        config(['modularous.mail.enabled' => true]);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getDirectoryPath')
            ->with('Notifications')
            ->andReturn('/non/existent/path');

        Modularous::shouldReceive('find')
            ->with('SystemNotification')
            ->andReturn($module);

        // Notification should NOT be called when no notification class is found
        Notification::shouldReceive('route')->never();

        $listener = new ConcreteListener;
        $event = new \stdClass;
        $event->model = new \stdClass;
        $event->serializedData = [];

        $listener->handle($event);

        // Test passes if no exception thrown
        $this->assertTrue(true);
    }
}

// Concrete implementation for testing
class ConcreteListener extends Listener
{
    public function getNotificationClassPublic($event): ?string
    {
        return $this->getNotificationClass($event);
    }
}
