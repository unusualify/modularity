<?php

namespace Unusualify\Modularity\Tests\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Unusualify\Modularity\Events\ModelEvent;
use Unusualify\Modularity\Tests\TestCase;

class ModelEventTest extends TestCase
{
    use RefreshDatabase;

    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test model instance
        $this->testModel = new TestModel([
            'id' => 1,
            'name' => 'Test Model',
            'email' => 'test@example.com',
        ]);
    }

    public function test_constructor_sets_model_and_model_type()
    {
        $event = new TestModelEvent($this->testModel);

        $this->assertEquals($this->testModel, $event->model);
        $this->assertEquals(TestModel::class, $event->modelType);
        $this->assertNull($event->serializedData);
    }

    public function test_constructor_sets_serialized_data()
    {
        $serializedData = ['key' => 'value', 'data' => 'test'];
        $event = new TestModelEvent($this->testModel, $serializedData);

        $this->assertEquals($this->testModel, $event->model);
        $this->assertEquals(TestModel::class, $event->modelType);
        $this->assertEquals($serializedData, $event->serializedData);
    }

    public function test_constructor_sets_default_broadcast_service()
    {
        $event = new TestModelEvent($this->testModel);

        $this->assertEquals('reverb', $event->broadcastService);
    }

    public function test_constructor_with_broadcasting_trait()
    {
        $event = new TestBroadcastingModelEvent($this->testModel);

        $this->assertEquals($this->testModel, $event->model);
        $this->assertEquals(TestModel::class, $event->modelType);
        // The broadcastVia method should be called automatically
    }

    public function test_broadcast_on_returns_correct_channels()
    {
        $event = new TestModelEvent($this->testModel);

        $channels = $event->broadcastOn();

        $this->assertIsArray($channels);
        $this->assertCount(2, $channels);

        // Check PrivateChannel for specific model
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertEquals('private-models.1', $channels[0]->name);

        // Check general Channel
        $this->assertInstanceOf(Channel::class, $channels[1]);
        $this->assertEquals('model', $channels[1]->name);
    }

    public function test_broadcast_on_with_different_model_id()
    {
        $modelWithDifferentId = new TestModel([
            'id' => 999,
            'name' => 'Different Model',
            'email' => 'different@example.com',
        ]);

        $event = new TestModelEvent($modelWithDifferentId);

        $channels = $event->broadcastOn();

        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertEquals('private-models.999', $channels[0]->name);
    }

    public function test_broadcast_when_returns_true()
    {
        $event = new TestModelEvent($this->testModel);

        $this->assertTrue($event->broadcastWhen());
    }

    public function test_broadcast_as_returns_correct_event_name()
    {
        $event = new TestModelEvent($this->testModel);

        $broadcastAs = $event->broadcastAs();

        // Should convert TestModelEvent to 'modularity.test.model'
        $this->assertEquals('modularity.test.model', $broadcastAs);
    }

    public function test_broadcast_as_with_different_event_class_names()
    {
        // Test with UserCreatedEvent
        $userEvent = new UserCreatedEvent($this->testModel);
        $this->assertEquals('modularity.user.created', $userEvent->broadcastAs());

        // Test with ProductUpdatedEvent
        $productEvent = new ProductUpdatedEvent($this->testModel);
        $this->assertEquals('modularity.product.updated', $productEvent->broadcastAs());

        // Test with OrderDeletedEvent
        $orderEvent = new OrderDeletedEvent($this->testModel);
        $this->assertEquals('modularity.order.deleted', $orderEvent->broadcastAs());
    }

    public function test_broadcast_as_removes_event_suffix()
    {
        $eventWithSuffix = new TestModelEventWithSuffix($this->testModel);

        // Should remove '_event' suffix and convert to snake_case
        $this->assertEquals('modularity.test.model.with.suffix', $eventWithSuffix->broadcastAs());
    }

    public function test_model_type_with_different_model_classes()
    {
        // Test with different model classes
        $userModel = new UserModel(['id' => 1, 'name' => 'User']);
        $productModel = new ProductModel(['id' => 1, 'name' => 'Product']);

        $userEvent = new TestModelEvent($userModel);
        $productEvent = new TestModelEvent($productModel);

        $this->assertEquals(UserModel::class, $userEvent->modelType);
        $this->assertEquals(ProductModel::class, $productEvent->modelType);
    }

    public function test_serialized_data_with_complex_data_structures()
    {
        $complexData = [
            'user' => ['id' => 1, 'name' => 'John'],
            'metadata' => [
                'action' => 'update',
                'timestamp' => now()->toISOString(),
                'changes' => ['name' => ['old' => 'Jane', 'new' => 'John']],
            ],
            'nested' => [
                'deep' => [
                    'value' => 'test',
                    'array' => [1, 2, 3, 4, 5],
                ],
            ],
        ];

        $event = new TestModelEvent($this->testModel, $complexData);

        $this->assertEquals($complexData, $event->serializedData);
        $this->assertEquals('John', $event->serializedData['user']['name']);
        $this->assertEquals('update', $event->serializedData['metadata']['action']);
        $this->assertEquals([1, 2, 3, 4, 5], $event->serializedData['nested']['deep']['array']);
    }

    public function test_event_properties_are_public()
    {
        $event = new TestModelEvent($this->testModel, ['test' => 'data']);

        // Test that properties are public and accessible
        $this->assertEquals($this->testModel, $event->model);
        $this->assertEquals(['test' => 'data'], $event->serializedData);
        $this->assertEquals(TestModel::class, $event->modelType);
        $this->assertEquals('reverb', $event->broadcastService);
    }

    public function test_broadcast_service_can_be_customized()
    {
        $event = new CustomBroadcastServiceEvent($this->testModel);

        $this->assertEquals('pusher', $event->broadcastService);
    }

    public function test_broadcasting_integration()
    {
        // Test that the event can be used with Laravel's broadcasting system
        $event = new TestBroadcastingModelEvent($this->testModel, ['action' => 'created']);

        // Test channels
        $channels = $event->broadcastOn();
        $this->assertCount(2, $channels);

        // Test broadcast condition
        $this->assertTrue($event->broadcastWhen());

        // Test broadcast name
        $this->assertEquals('modularity.test.broadcasting.model', $event->broadcastAs());
    }

    public function test_event_with_null_model_id()
    {
        $modelWithNullId = new TestModel([
            'id' => null,
            'name' => 'Model without ID',
        ]);

        $event = new TestModelEvent($modelWithNullId);

        $channels = $event->broadcastOn();

        // Should handle null ID gracefully
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertEquals('private-models.', $channels[0]->name); // Empty string after null
    }

    public function test_event_with_string_model_id()
    {
        $modelWithStringId = new TestModel;
        $modelWithStringId->id = 'uuid-123-456';
        $modelWithStringId->name = 'Model with UUID';

        $event = new TestModelEvent($modelWithStringId);

        $channels = $event->broadcastOn();

        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertEquals('private-models.uuid-123-456', $channels[0]->name);
    }

    public function test_multiple_event_instances_are_independent()
    {
        $model1 = new TestModel(['id' => 1, 'name' => 'Model 1']);
        $model2 = new TestModel(['id' => 2, 'name' => 'Model 2']);

        $event1 = new TestModelEvent($model1, ['data' => 'event1']);
        $event2 = new TestModelEvent($model2, ['data' => 'event2']);

        // Verify independence
        $this->assertEquals(1, $event1->model->id);
        $this->assertEquals(2, $event2->model->id);
        $this->assertEquals(['data' => 'event1'], $event1->serializedData);
        $this->assertEquals(['data' => 'event2'], $event2->serializedData);
        $this->assertEquals(TestModel::class, $event1->modelType);
        $this->assertEquals(TestModel::class, $event2->modelType);
    }

    public function test_broadcast_as_edge_cases()
    {
        // Test with single word class name
        $singleWordEvent = new TestEvent($this->testModel);
        $this->assertEquals('modularity.test', $singleWordEvent->broadcastAs());

        // Test with class name ending in Event
        $eventEndingEvent = new TestEndingEvent($this->testModel);
        $this->assertEquals('modularity.test.ending', $eventEndingEvent->broadcastAs());
    }
}

// Test model classes
class TestModel extends Model
{
    protected $fillable = ['id', 'name', 'email'];

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';
}

class UserModel extends Model
{
    protected $fillable = ['id', 'name'];

    public $timestamps = false;
}

class ProductModel extends Model
{
    protected $fillable = ['id', 'name'];

    public $timestamps = false;
}

// Test event classes
class TestModelEvent extends ModelEvent
{
    // Basic test event
}

class TestBroadcastingModelEvent extends ModelEvent
{
    use InteractsWithBroadcasting;
}

class CustomBroadcastServiceEvent extends ModelEvent
{
    public $broadcastService = 'pusher';
}

class UserCreatedEvent extends ModelEvent
{
    // Test event for broadcast name testing
}

class ProductUpdatedEvent extends ModelEvent
{
    // Test event for broadcast name testing
}

class OrderDeletedEvent extends ModelEvent
{
    // Test event for broadcast name testing
}

class TestModelEventWithSuffix extends ModelEvent
{
    // Test event with suffix for broadcast name testing
}

class TestEvent extends ModelEvent
{
    // Single word event name
}

class TestEndingEvent extends ModelEvent
{
    // Event name ending with "Event"
}
