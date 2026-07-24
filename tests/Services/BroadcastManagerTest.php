<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Unusualify\Modularous\Services\BroadcastManager;
use Unusualify\Modularous\Support\BroadcastAvailability;
use Unusualify\Modularous\Tests\TestCase;

class BroadcastManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        BroadcastAvailability::clearFake();

        parent::tearDown();
    }

    public function test_get_broadcast_configuration_groups_events_by_channel(): void
    {
        $model = (object) ['id' => 7];

        $config = (new BroadcastManager($model, [
            BroadcastCreatedEvent::class,
            BroadcastUpdatedEvent::class,
        ]))->getBroadcastConfiguration();

        $byName = collect($config)->keyBy('name');

        $this->assertSame('public', $byName['models.7']['type']);
        $this->assertCount(2, $byName['models.7']['events']);
        $this->assertSame('modularous.broadcast.created', $byName['models.7']['events'][0]['event']);

        $this->assertSame('public', $byName['model']['type']);
        $this->assertCount(2, $byName['model']['events']);
    }

    public function test_for_model_static_helper_returns_configuration(): void
    {
        $model = (object) ['id' => 3];

        $config = BroadcastManager::forModel($model, [BroadcastCreatedEvent::class]);

        $this->assertCount(2, $config);
        $this->assertContains('models.3', array_column($config, 'name'));
        $this->assertContains('model', array_column($config, 'name'));
    }

    public function test_skips_missing_event_classes(): void
    {
        $model = (object) ['id' => 1];

        $config = BroadcastManager::forModel($model, [
            BroadcastCreatedEvent::class,
            'Missing\\Event\\Class',
        ]);

        $this->assertCount(2, $config);
    }

    public function test_resolves_channel_objects_and_private_channels(): void
    {
        $model = (object) ['id' => 9];

        $config = BroadcastManager::forModel($model, [BroadcastPrivateChannelEvent::class]);
        $byName = collect($config)->keyBy('name');

        $this->assertSame('private', $byName['private-models.9']['type']);
        $this->assertSame('public', $byName['presence-room']['type']);
        $this->assertSame('modularous.broadcast.private', $byName['private-models.9']['events'][0]['event']);
    }

    public function test_panel_config_includes_user_channel_and_domain_channels(): void
    {
        BroadcastAvailability::fakePusherSdkAvailable(true);
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'null',
        ]);

        $user = (object) ['id' => 42];

        $config = BroadcastManager::panelConfig($user);

        $this->assertTrue($config['enabled']);
        $this->assertSame(42, $config['userId']);
        $this->assertSame('users.42', $config['userChannel']);
        $this->assertContains('assignable', $config['domainChannels']);
        $this->assertContains('payment', $config['domainChannels']);
        $this->assertContains('model', $config['domainChannels']);
    }

    public function test_panel_config_enabled_false_when_broadcasting_unavailable(): void
    {
        config([
            'modularous.broadcasting.enabled' => false,
            'broadcasting.default' => 'null',
        ]);

        $config = BroadcastManager::panelConfig((object) ['id' => 1]);

        $this->assertFalse($config['enabled']);
    }

    public function test_encode_and_decode_model_type(): void
    {
        $class = 'Modules\\Blog\\Entities\\Post';

        $encoded = BroadcastManager::encodeModelType($class);

        $this->assertSame('Modules-Blog-Entities-Post', $encoded);
        $this->assertSame($class, BroadcastManager::decodeModelType($encoded));
    }
}

class BroadcastCreatedEvent
{
    public function __construct(public object $model) {}

    public function broadcastOn(): array
    {
        return [
            'models.' . $this->model->id,
            'model',
        ];
    }

    public function broadcastAs(): string
    {
        return 'modularous.broadcast.created';
    }
}

class BroadcastUpdatedEvent
{
    public function __construct(public object $model) {}

    public function broadcastOn(): array
    {
        return [
            'models.' . $this->model->id,
            'model',
        ];
    }

    public function broadcastAs(): string
    {
        return 'modularous.broadcast.updated';
    }
}

class BroadcastPrivateChannelEvent
{
    public function __construct(public object $model) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('models.' . $this->model->id),
            new Channel('presence-room'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'modularous.broadcast.private';
    }
}
