<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Modules\SystemNotification\Notifications\ChatableMessageBroadcastNotification;
use Modules\SystemNotification\Notifications\ChatableUnreadNotification;
use Modules\SystemNotification\Notifications\FeatureNotification;
use Unusualify\Modularous\Entities\Chat;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Support\BroadcastAvailability;
use Unusualify\Modularous\Tests\TestCase;

class FeatureNotificationBroadcastTest extends TestCase
{
    protected function tearDown(): void
    {
        BroadcastAvailability::clearFake();

        parent::tearDown();
    }

    public function test_to_broadcast_returns_broadcast_message_with_feature_fields(): void
    {
        $user = new User;
        $user->id = 1;
        $user->name = 'Test';

        $model = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];
        };
        $model->id = 10;
        $model->name = 'Demo';

        $notification = new class($model) extends FeatureNotification
        {
            public $defaultChannels = ['broadcast'];

            public function getNotificationSubject(object $notifiable, $model): string
            {
                return 'Subject line';
            }

            public function getNotificationMessage(object $notifiable, $model): string
            {
                return 'Message body';
            }

            public function getNotificationMailRedirector(object $notifiable, Model $model)
            {
                return null;
            }
        };

        $message = $notification->toBroadcast($user);

        $this->assertInstanceOf(BroadcastMessage::class, $message);
        $data = $message->data;

        $this->assertSame('Subject line', $data['subject']);
        $this->assertSame('Message body', $data['message']);
        $this->assertArrayHasKey('notification_type', $data);
    }

    public function test_to_broadcast_prefers_notification_record_redirector(): void
    {
        $user = new User;
        $user->id = 1;
        $user->name = 'Test';

        $model = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];
        };
        $model->id = 10;

        $notification = new class($model) extends FeatureNotification
        {
            public $defaultChannels = ['broadcast'];

            public function getNotificationSubject(object $notifiable, $model): string
            {
                return 'Subject';
            }

            public function getNotificationMessage(object $notifiable, $model): string
            {
                return 'Body';
            }

            public function getNotificationRedirector(object $notifiable, Model $model)
            {
                return 'https://example.test/direct-model';
            }

            public function getNotificationMailRedirector(object $notifiable, Model $model)
            {
                return 'https://example.test/notifications/99?redirector=1';
            }
        };

        $data = $notification->toBroadcast($user)->data;

        $this->assertSame('https://example.test/notifications/99?redirector=1', $data['redirector']);
        $this->assertTrue($data['hasRedirector']);
    }

    public function test_to_broadcast_falls_back_to_direct_redirector_when_record_missing(): void
    {
        $user = new User;
        $user->id = 1;
        $user->name = 'Test';

        $model = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];
        };
        $model->id = 10;

        $notification = new class($model) extends FeatureNotification
        {
            public $defaultChannels = ['broadcast'];

            public function getNotificationSubject(object $notifiable, $model): string
            {
                return 'Subject';
            }

            public function getNotificationMessage(object $notifiable, $model): string
            {
                return 'Body';
            }

            public function getNotificationRedirector(object $notifiable, Model $model)
            {
                return 'https://example.test/direct-model';
            }

            public function getNotificationMailRedirector(object $notifiable, Model $model)
            {
                return null;
            }
        };

        $data = $notification->toBroadcast($user)->data;

        $this->assertSame('https://example.test/direct-model', $data['redirector']);
        $this->assertTrue($data['hasRedirector']);
    }

    public function test_via_strips_broadcast_when_unavailable(): void
    {
        config([
            'modularous.broadcasting.enabled' => false,
            'broadcasting.default' => 'null',
        ]);

        $model = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];
        };
        $model->id = 1;

        $notification = new class($model) extends FeatureNotification
        {
            public $defaultChannels = ['database', 'mail', 'broadcast'];
        };

        $user = new User;
        $user->id = 1;

        $this->assertSame(['database', 'mail'], $notification->via($user));
    }

    public function test_via_keeps_broadcast_when_enabled(): void
    {
        BroadcastAvailability::fakePusherSdkAvailable(true);
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'null',
        ]);

        $model = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];
        };
        $model->id = 1;

        $notification = new class($model) extends FeatureNotification
        {
            public $defaultChannels = ['database', 'broadcast'];
        };

        $user = new User;
        $user->id = 1;

        $this->assertSame(['database', 'broadcast'], $notification->via($user));
    }

    public function test_empty_string_channels_config_falls_back_to_default_channels(): void
    {
        BroadcastAvailability::fakePusherSdkAvailable(true);
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'null',
        ]);

        $model = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];
        };
        $model->id = 1;

        $notification = new class($model) extends FeatureNotification
        {
            public $defaultChannels = ['database', 'mail', 'broadcast'];
        };

        // Simulate host .env KEY= (empty) wiping the package env() default.
        config([
            'modularous.notifications.' . $notification::class . '.channels' => '',
        ]);

        $user = new User;
        $user->id = 1;

        $this->assertSame(
            ['database', 'mail', 'broadcast'],
            $notification->via($user)
        );
    }

    public function test_whitespace_only_channels_config_falls_back_to_defaults(): void
    {
        BroadcastAvailability::fakePusherSdkAvailable(true);
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'null',
        ]);

        $model = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];
        };
        $model->id = 1;

        $notification = new class($model) extends FeatureNotification
        {
            public $defaultChannels = ['database', 'broadcast'];
        };

        config([
            'modularous.notifications.' . $notification::class . '.channels' => '   ',
        ]);

        $user = new User;
        $user->id = 1;

        $this->assertSame(['database', 'broadcast'], $notification->via($user));
    }

    public function test_chatable_unread_via_includes_broadcast_when_enabled(): void
    {
        BroadcastAvailability::fakePusherSdkAvailable(true);
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'null',
            // Host apps often ship blank env; must not wipe broadcast.
            'modularous.notifications.' . ChatableUnreadNotification::class . '.channels' => '',
        ]);

        $chatable = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];
        };
        $chatable->id = 1;

        $chat = new Chat;
        $chat->setRelation('chatable', $chatable);

        $notification = new ChatableUnreadNotification($chat);

        $user = new User;
        $user->id = 1;

        $channels = $notification->via($user);

        $this->assertContains('broadcast', $channels);
        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);
    }

    public function test_chatable_unread_broadcast_is_not_retainable(): void
    {
        BroadcastAvailability::fakePusherSdkAvailable(true);
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'null',
        ]);

        $chatable = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];

            public function getModuleName()
            {
                return null;
            }

            public function getRouteName()
            {
                return null;
            }
        };
        $chatable->id = 1;

        $chat = new Chat;
        $chat->setRelation('chatable', $chatable);

        $notification = new class($chat) extends ChatableUnreadNotification
        {
            public function getNotificationMailRedirector(object $notifiable, Model $model)
            {
                return null;
            }
        };

        $user = new User;
        $user->id = 1;

        $this->assertFalse($notification->isRetainable());

        $data = $notification->toBroadcast($user)->data;

        $this->assertFalse($data['retainable']);
        $this->assertNull($data['retainUntil']);
        $this->assertNull($data['retainGroup']);
        $this->assertArrayHasKey('notification_type', $data);
    }

    public function test_chatable_message_broadcast_is_retainable(): void
    {
        BroadcastAvailability::fakePusherSdkAvailable(true);
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'null',
        ]);

        $chatable = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];

            public function getModuleName()
            {
                return null;
            }

            public function getRouteName()
            {
                return null;
            }
        };
        $chatable->id = 1;

        $chat = new Chat;
        $chat->id = 42;
        $chat->setRelation('chatable', $chatable);

        $notification = new class($chat) extends ChatableMessageBroadcastNotification
        {
            public function getNotificationMailRedirector(object $notifiable, Model $model)
            {
                return null;
            }

            public function getNotificationRedirector(object $notifiable, Model $model)
            {
                return 'https://example.test/chatable/1';
            }
        };

        $user = new User;
        $user->id = 1;

        $this->assertTrue($notification->isRetainable());
        $this->assertSame('chat:42', $notification->getRetainGroup());

        $data = $notification->toBroadcast($user)->data;

        $this->assertTrue($data['retainable']);
        $this->assertNotEmpty($data['retainUntil']);
        $this->assertSame('chat:42', $data['retainGroup']);
        $this->assertTrue($data['hasRedirector']);
        $this->assertSame('https://example.test/chatable/1', $data['redirector']);
        $this->assertSame('Look', $data['redirectorText']);
        $this->assertArrayHasKey('notification_type', $data);
        $this->assertNotEmpty($data['token']);
        $this->assertNotSame($data['token'], $data['retainGroup']);
    }

    public function test_default_feature_notification_broadcast_is_not_retainable(): void
    {
        $user = new User;
        $user->id = 1;

        $model = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];
        };
        $model->id = 10;

        $notification = new class($model) extends FeatureNotification
        {
            public $defaultChannels = ['broadcast'];

            public function getNotificationSubject(object $notifiable, $model): string
            {
                return 'Subject';
            }

            public function getNotificationMessage(object $notifiable, $model): string
            {
                return 'Body';
            }

            public function getNotificationMailRedirector(object $notifiable, Model $model)
            {
                return null;
            }
        };

        $this->assertNull($notification->getRetainGroup());

        $data = $notification->toBroadcast($user)->data;

        $this->assertFalse($data['retainable']);
        $this->assertNull($data['retainUntil']);
        $this->assertNull($data['retainGroup']);
    }

    public function test_retainable_feature_notification_includes_retain_group_in_broadcast(): void
    {
        $user = new User;
        $user->id = 1;

        $model = new class extends Model
        {
            protected $table = 'broadcast_notification_test_models';

            public $timestamps = false;

            protected $guarded = [];
        };
        $model->id = 10;

        $notification = new class($model) extends FeatureNotification
        {
            public $defaultChannels = ['broadcast'];

            public function isRetainable(): bool
            {
                return true;
            }

            public function getRetainGroup(): ?string
            {
                return 'entity:'.$this->model->id;
            }

            public function getNotificationSubject(object $notifiable, $model): string
            {
                return 'Subject';
            }

            public function getNotificationMessage(object $notifiable, $model): string
            {
                return 'Body';
            }

            public function getNotificationMailRedirector(object $notifiable, Model $model)
            {
                return null;
            }
        };

        $data = $notification->toBroadcast($user)->data;

        $this->assertTrue($data['retainable']);
        $this->assertSame('entity:10', $data['retainGroup']);
        $this->assertNotEmpty($data['retainUntil']);
    }
}
