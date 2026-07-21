<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Modules\SystemNotification\Notifications\FeatureNotification;
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
        };

        $message = $notification->toBroadcast($user);

        $this->assertInstanceOf(BroadcastMessage::class, $message);
        $data = $message->data;

        $this->assertSame('Subject line', $data['subject']);
        $this->assertSame('Message body', $data['message']);
        $this->assertArrayHasKey('notification_type', $data);
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
}
