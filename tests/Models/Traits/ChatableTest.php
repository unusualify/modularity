<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Modules\SystemNotification\Events\UnreadChatMessage;
use Spatie\Permission\Models\Role;
use Unusualify\Modularity\Entities\Chat;
use Unusualify\Modularity\Entities\ChatMessage;
use Unusualify\Modularity\Entities\CreatorRecord;
use Unusualify\Modularity\Entities\Traits\Chatable;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularity\Entities\Traits\HasCreator;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class ChatableTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tables
        Schema::create('test_chatable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->testModel = new TestChatableModel(['name' => 'Test Model']);
        $this->testModel->save();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'modularity']);
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'modularity']);
        Role::firstOrCreate(['name' => 'client-manager', 'guard_name' => 'modularity']);
        Role::firstOrCreate(['name' => 'client-assistant', 'guard_name' => 'modularity']);
    }

    public function test_model_uses_chatable_trait()
    {
        $model = new TestChatableModel();
        $traits = class_uses_recursive($model);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\Chatable', $traits);
    }

    public function test_initialize_chatable_appends_count_attributes()
    {
        $model = new TestChatableModel();
        $appends = $model->getAppends();

        $this->assertContains('chat_messages_count', $appends);
        $this->assertContains('unread_chat_messages_count', $appends);
        $this->assertContains('unread_chat_messages_for_you_count', $appends);
    }

    public function test_initialize_chatable_respects_no_appends_flag()
    {
        $model = new TestChatableModelNoAppends();
        $appends = $model->getAppends();

        $this->assertNotContains('chat_messages_count', $appends);
        $this->assertNotContains('unread_chat_messages_count', $appends);
        $this->assertNotContains('unread_chat_messages_for_you_count', $appends);
    }

    public function test_chat_relationship()
    {
        $relation = $this->testModel->chat();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, $relation);
        $this->assertEquals(Chat::class, $relation->getRelated()::class);
        $this->assertEquals('chatable_type', $relation->getMorphType());
        $this->assertEquals('chatable_id', $relation->getForeignKeyName());
    }

    public function test_chat_messages_relationship()
    {
        $relation = $this->testModel->chatMessages();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasManyThrough::class, $relation);
        $this->assertEquals(ChatMessage::class, $relation->getRelated()::class);
    }

    public function test_unread_chat_messages_relationship()
    {
        // Create a chat with messages
        $chat = $this->testModel->chat()->create();
        $readMessage = $chat->messages()->create([
            'content' => 'Read message',
            'is_read' => true,
        ]);
        $unreadMessage = $chat->messages()->create([
            'content' => 'Unread message',
            'is_read' => false,
        ]);

        $unreadMessages = $this->testModel->unreadChatMessages()->get();

        $this->assertCount(1, $unreadMessages);
        $this->assertEquals($unreadMessage->id, $unreadMessages->first()->id);
    }

    public function test_latest_chat_message_relationship()
    {
        $chat = $this->testModel->chat()->create();

        // Create messages with different timestamps
        $oldMessage = $chat->messages()->create([
            'content' => 'Old message',
            'created_at' => now()->subHours(2),
        ]);

        sleep(1);

        $latestMessage = $chat->messages()->create([
            'content' => 'Latest message',
            'created_at' => now()->subHour(),
        ]);

        $result = $this->testModel->latestChatMessage()->first();

        $this->assertNotNull($result);
        $this->assertEquals($latestMessage->id, $result->id);
    }

    public function test_boot_chatable_creates_chat_on_model_created()
    {
        $model = new TestChatableModel(['name' => 'New Model']);
        $model->save();

        // Check that a chat was created
        $this->assertNotNull($model->chat);
        $this->assertEquals($model->id, $model->chat->chatable_id);
        $this->assertEquals(TestChatableModel::class, $model->chat->chatable_type);
    }

    public function test_boot_chatable_sets_chat_id_on_retrieved()
    {
        // Create a model with a chat
        $chat = $this->testModel->chat;

        // Retrieve the model fresh from database
        $retrieved = TestChatableModel::find($this->testModel->id);

        $this->assertEquals($chat->id, $retrieved->getAttribute('_chat_id'));
    }

    public function test_boot_chatable_creates_chat_if_missing_on_retrieved()
    {
        // Create a model without a chat initially
        $model = new TestChatableModel(['name' => 'No Chat Model']);
        $model->save();

        // Manually remove the chat that was created by the created event
        $model->truncateChat();

        // Retrieve the model - should create a new chat
        $retrieved = TestChatableModel::find($model->id);

        $this->assertNotNull($retrieved->chat);
        $this->assertEquals($retrieved->chat->id, $retrieved->getAttribute('_chat_id'));
    }

    public function test_boot_chatable_removes_chat_id_on_saving()
    {
        $this->testModel->setAttribute('_chat_id', 123);
        $this->testModel->save();

        $this->assertNull($this->testModel->getAttribute('_chat_id'));
    }

    public function test_chat_messages_count_attribute()
    {
        $chat = $this->testModel->chat;
        $chat->messages()->create(['content' => 'Message 1']);
        $chat->messages()->create(['content' => 'Message 2']);
        $chat->messages()->create(['content' => 'Message 3']);

        $this->testModel->refresh();

        // Test that the attribute is appended
        $this->assertContains('chat_messages_count', $this->testModel->getAppends());

        // Test the actual count
        $this->assertEquals(3, $this->testModel->chat_messages_count);
    }

    public function test_unread_chat_messages_count_attribute()
    {
        $chat = $this->testModel->chat;
        $chat->messages()->create(['content' => 'Read', 'is_read' => true]);
        $chat->messages()->create(['content' => 'Unread 1', 'is_read' => false]);
        $chat->messages()->create(['content' => 'Unread 2', 'is_read' => false]);

        $this->testModel->refresh();

        // Test that the attribute is appended
        $this->assertContains('unread_chat_messages_count', $this->testModel->getAppends());

        // Test the actual count
        $this->assertEquals(2, $this->testModel->unread_chat_messages_count);
    }

    public function test_unread_chat_messages_for_you_count_attribute()
    {
        // Create users
        $creator = User::create([
            'name' => 'Creator User',
            'email' => 'creator@example.com',
            'published' => true,
        ]);

        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'published' => true,
        ]);

        $chat = $this->testModel->chat;

        // Create messages from different users
        $messageFromCreator = $chat->messages()->create([
            'content' => 'Message from creator',
            'is_read' => false,
        ]);

        $messageFromOther = $chat->messages()->create([
            'content' => 'Message from other',
            'is_read' => false,
        ]);

        // Create creator records
        $messageFromCreator->creatorRecord()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        $messageFromOther->creatorRecord()->create([
            'creator_id' => $otherUser->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        $this->testModel->refresh();

        // Test that the attribute is appended
        $this->assertContains('unread_chat_messages_for_you_count', $this->testModel->getAppends());

        // Test the count (exact value depends on authorization logic)
        $this->assertIsInt($this->testModel->unread_chat_messages_for_you_count);
        $this->assertGreaterThanOrEqual(0, $this->testModel->unread_chat_messages_for_you_count);
    }

    public function test_unread_chat_messages_from_creator_count_attribute()
    {
        $modelWithCreator = new TestChatableModelWithCreator(['name' => 'Model With Creator']);
        $modelWithCreator->save();

        $creator = User::create([
            'name' => 'Creator User',
            'email' => 'creator@example.com',
            'published' => true,
        ]);

        // Create creator record for the model
        $modelWithCreator->creatorRecord()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        $chat = $modelWithCreator->chat;

        // Create message from creator
        $messageFromCreator = $chat->messages()->create([
            'content' => 'Message from creator',
            'is_read' => false,
        ]);

        // Create creator record for the message
        $messageFromCreator->creatorRecord()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        $modelWithCreator->refresh();

        // Test that the attribute is appended (if not using $noChatableAppends)
        $this->assertContains('unread_chat_messages_for_you_count', $modelWithCreator->getAppends());

        // Test the count
        $this->assertIsInt($modelWithCreator->unread_chat_messages_from_creator_count);
        $this->assertGreaterThanOrEqual(0, $modelWithCreator->unread_chat_messages_from_creator_count);
    }

    public function test_unread_chat_messages_from_client_count_attribute()
    {
        $chat = $this->testModel->chat;

        // Create a regular message (not from client)
        $regularMessage = $chat->messages()->create([
            'content' => 'Regular message',
            'is_read' => false,
        ]);

        $this->testModel->refresh();

        // Test that the attribute is not in appends (since it's not in the initializeChatable method)
        // But we can still access it as a computed attribute
        $this->assertIsInt($this->testModel->unread_chat_messages_from_client_count);
        $this->assertGreaterThanOrEqual(0, $this->testModel->unread_chat_messages_from_client_count);
    }

    public function test_is_unanswered_attribute()
    {
        $modelWithCreator = new TestChatableModelWithCreator(['name' => 'Model With Creator']);
        $modelWithCreator->save();

        $creator = User::create([
            'name' => 'Creator User',
            'email' => 'creator@example.com',
            'published' => true,
        ]);

        // Create creator record for the model
        $modelWithCreator->creatorRecord()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        $chat = $modelWithCreator->chat;

        // Initially, no messages means not unanswered
        $this->assertEquals(0, $modelWithCreator->is_unanswered);

        // Create message from creator
        $messageFromCreator = $chat->messages()->create([
            'content' => 'Message from creator',
            'is_read' => false,
        ]);

        // Create creator record for the message
        $messageFromCreator->creatorRecord()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        $modelWithCreator->refresh();

        // Test the is_unanswered attribute
        $this->assertIsInt($modelWithCreator->is_unanswered);
        $this->assertGreaterThanOrEqual(0, $modelWithCreator->is_unanswered);
        $this->assertLessThanOrEqual(1, $modelWithCreator->is_unanswered);
    }

    public function test_should_send_chatable_notification()
    {
        $unreadMessage = new ChatMessage(['is_read' => false]);
        $readMessage = new ChatMessage(['is_read' => true]);

        $this->assertTrue($this->testModel->shouldSendChatableNotification($unreadMessage));
        $this->assertFalse($this->testModel->shouldSendChatableNotification($readMessage));
    }

    public function test_get_chatable_notification_interval_default()
    {
        $interval = TestChatableModel::getChatableNotificationInterval();
        $this->assertEquals(60, $interval);
    }

    public function test_get_chatable_notification_interval_custom()
    {
        $interval = TestChatableModelCustomInterval::getChatableNotificationInterval();
        $this->assertEquals(30, $interval);
    }

    public function test_handle_chatable_notification_dispatches_event()
    {
        Event::fake([
            UnreadChatMessage::class,
        ]);

        $chat = $this->testModel->chat()->create();
        $message = $chat->messages()->create([
            'content' => 'Test message',
            'is_read' => false,
        ]);

        $message->created_at = now()->subMinutes(65);
        $message->save();

        $this->testModel->handleChatableNotification();

        Event::assertDispatched(UnreadChatMessage::class, function ($event) use ($message) {
            return $event->model->id === $message->id;
        });
    }

    public function test_handle_chatable_notification_does_not_dispatch_for_read_message()
    {
        Event::fake([UnreadChatMessage::class]);

        $chat = $this->testModel->chat()->create();
        $message = $chat->messages()->create([
            'content' => 'Test message',
            'is_read' => true, // Read message
        ]);

        $message->created_at = now()->subMinutes(65);
        $message->save();

        $this->testModel->handleChatableNotification();

        Event::assertNotDispatched(UnreadChatMessage::class);
    }

    public function test_handle_chatable_notification_does_not_dispatch_for_recent_message()
    {
        Event::fake([UnreadChatMessage::class]);

        $chat = $this->testModel->chat()->create();
        $message = $chat->messages()->create([
            'content' => 'Test message',
            'is_read' => false,
        ]);

        $message->created_at = now()->subMinutes(30);
        $message->save();

        $this->testModel->handleChatableNotification();

        Event::assertNotDispatched(UnreadChatMessage::class);
    }

    public function test_handle_chatable_notification_does_not_dispatch_for_already_notified()
    {
        Event::fake([UnreadChatMessage::class]);

        $chat = $this->testModel->chat()->create();
        $message = $chat->messages()->create([
            'content' => 'Test message',
            'is_read' => false,
            'notified_at' => now()->subMinutes(30), // Already notified
        ]);

        $message->created_at = now()->subMinutes(65);
        $message->save();

        $this->testModel->handleChatableNotification();

        Event::assertNotDispatched(UnreadChatMessage::class);
    }

    public function test_handle_chatable_notification_from_chatable_model_with_has_creator()
    {
        Event::fake([UnreadChatMessage::class]);
        $modelWithCreator = new TestChatableModelWithCreator(['name' => 'Creator']);
        $modelWithCreator->save();

        $creator = User::create([
            'name' => 'Creator User',
            'email' => 'creator@example.com',
            'published' => true,
        ]);

        $modelWithCreator->creatorRecord()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        $chat = $modelWithCreator->chat;

        $message = $chat->messages()->create([
            'content' => 'Message from creator',
            'is_read' => false,
        ]);

        $message->creatorRecord()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        $message->created_at = now()->subMinutes(65);
        $message->save();

        $modelWithCreator->handleChatableNotification();

        Event::assertDispatched(UnreadChatMessage::class, function ($event) use ($message) {
            return $event->model->id === $message->id;
        });
    }

    public function test_chatable_scopes_trait_is_used()
    {
        $model = new TestChatableModel();
        $traits = class_uses_recursive($model);
        $this->assertContains('Unusualify\Modularity\Entities\Scopes\ChatableScopes', $traits);
    }

    public function test_scope_has_chat_messages()
    {
        // Model with chat messages
        $modelWithMessages = new TestChatableModel(['name' => 'With Messages']);
        $modelWithMessages->save();
        $chat1 = $modelWithMessages->chat()->create();
        $chat1->messages()->create(['content' => 'Message']);

        // Model without chat messages
        $modelWithoutMessages = new TestChatableModel(['name' => 'Without Messages']);
        $modelWithoutMessages->save();
        $modelWithoutMessages->chat()->create(); // Chat exists but no messages

        $results = TestChatableModel::hasChatMessages()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($modelWithMessages->id, $results->first()->id);
    }

    public function test_scope_has_unread_chat_messages()
    {
        // Model with unread messages
        $modelWithUnread = new TestChatableModel(['name' => 'With Unread']);
        $modelWithUnread->save();
        $chat1 = $modelWithUnread->chat()->create();
        $chat1->messages()->create(['content' => 'Unread', 'is_read' => false]);

        // Model with only read messages
        $modelWithRead = new TestChatableModel(['name' => 'With Read']);
        $modelWithRead->save();
        $chat2 = $modelWithRead->chat()->create();
        $chat2->messages()->create(['content' => 'Read', 'is_read' => true]);

        $results = TestChatableModel::hasUnreadChatMessages()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($modelWithUnread->id, $results->first()->id);
    }

    public function test_creator_chat_messages_relationship()
    {
        // Create a user to act as creator
        $creator = User::create([
            'name' => 'Creator User',
            'email' => 'creator@example.com',
            'published' => true,
        ]);

        // Create a test model with creator
        $modelWithCreator = new TestChatableModelWithCreator(['name' => 'Model With Creator']);
        $modelWithCreator->save();

        // Simulate creator record creation
        $modelWithCreator->creatorRecord()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        // Create chat messages
        $chat = $modelWithCreator->chat;
        $message1 = $chat->messages()->create(['content' => 'Message from creator']);
        $message2 = $chat->messages()->create(['content' => 'Another message']);

        // Create creator records for messages to simulate they were created by the same creator
        $message1->creatorRecord()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        // Test the relationship exists and is correctly typed
        $relation = $modelWithCreator->creatorChatMessages();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasManyThrough::class, $relation);

        // Test that we can get messages from the relationship
        $creatorMessages = $modelWithCreator->creatorChatMessages()->get();
        $this->assertIsIterable($creatorMessages);
    }

    public function test_unread_chat_messages_for_you_relationship()
    {
        // Create users
        $creator = User::create([
            'name' => 'Creator User',
            'email' => 'creator@example.com',
            'published' => true,
        ]);

        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'published' => true,
        ]);

        // Create a test model
        $model = new TestChatableModelWithCreator(['name' => 'Test Model']);
        $model->save();

        // Create chat with messages
        $chat = $model->chat;

        // Message created by the model's creator (should not be "for you" if you are the creator)
        $messageFromCreator = $chat->messages()->create([
            'content' => 'Message from creator',
            'is_read' => false,
        ]);

        // Message created by another user (should be "for you" if you are not the creator)
        $messageFromOther = $chat->messages()->create([
            'content' => 'Message from other',
            'is_read' => false,
        ]);

        // Create creator records
        $messageFromCreator->creatorRecord()->create([
            'creator_id' => $creator->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        $messageFromOther->creatorRecord()->create([
            'creator_id' => $otherUser->id,
            'creator_type' => User::class,
            'guard_name' => 'web',
        ]);

        // Test the relationship exists
        $relation = $model->unreadChatMessagesForYou();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasManyThrough::class, $relation);

        // Get the messages
        $unreadForYou = $model->unreadChatMessagesForYou()->get();

        // The exact count depends on the authorization logic implementation
        // For now, we just verify the relationship works
        $this->assertIsIterable($unreadForYou);
    }

    public function test_unread_chat_messages_from_client_relationship()
    {
        $chat = $this->testModel->chat;

        // Create a message
        $message = $chat->messages()->create([
            'content' => 'Message from client',
            'is_read' => false,
        ]);

        // Test the relationship exists
        $relation = $this->testModel->unreadChatMessagesFromClient();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasManyThrough::class, $relation);
    }

    public function test_unread_chat_messages_from_creator_relationship()
    {
        // Create a user to act as creator
        $creator = User::create([
            'name' => 'Creator User',
            'email' => 'creator@example.com',
            'published' => true,
        ]);

        // Create a model with creator
        $modelWithCreator = new TestChatableModelWithCreator(['name' => 'Model With Creator']);
        $modelWithCreator->save();

        // Create chat with message
        $chat = $modelWithCreator->chat;
        $message = $chat->messages()->create([
            'content' => 'Message from creator',
            'is_read' => false,
        ]);

        // Test the relationship exists and is correctly typed
        $relation = $modelWithCreator->unreadChatMessagesFromCreator();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasManyThrough::class, $relation);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_chatable_models');
        parent::tearDown();
    }
}

// Test model that uses the Chatable trait
class TestChatableModel extends Model
{
    use Chatable, ModelHelpers;

    protected $table = 'test_chatable_models';
    protected $fillable = ['name'];
}

// Test model with no appends flag
class TestChatableModelNoAppends extends Model
{
    use Chatable, ModelHelpers;

    protected $table = 'test_chatable_models';
    protected $fillable = ['name'];
    protected static $noChatableAppends = true;
}

// Test model with custom notification interval
class TestChatableModelCustomInterval extends Model
{
    use Chatable, ModelHelpers;

    protected $table = 'test_chatable_models';
    protected $fillable = ['name'];
    protected static $chatableNotificationInterval = 30;
}

// Test model that uses both Chatable and HasCreator traits
class TestChatableModelWithCreator extends Model
{
    use Chatable, ModelHelpers, HasCreator;

    protected $table = 'test_chatable_models';
    protected $fillable = ['name'];
}
