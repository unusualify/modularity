<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Unusualify\Modularity\Entities\Chat;
use Unusualify\Modularity\Entities\ChatMessage;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class ChatMessageTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_chat_message()
    {
        $chatMessage = new ChatMessage;
        $this->assertEquals(modularityConfig('tables.chat_messages', 'um_chat_messages'), $chatMessage->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'chat_id',
            'content',
            'is_read',
            'is_starred',
            'is_pinned',
            'is_sent',
            'is_received',
            'edited_at',

            // Creator record
            'custom_creator_id',
            'custom_creator_type',
            'custom_guard_name',
        ];

        $chatMessage = new ChatMessage;
        $this->assertEquals($expectedFillable, $chatMessage->getFillable());
    }

    public function test_casts()
    {
        $expectedCasts = [
            'is_read' => 'boolean',
            'is_starred' => 'boolean',
            'is_pinned' => 'boolean',
            'is_sent' => 'boolean',
            'is_received' => 'boolean',
            'edited_at' => 'datetime',
        ];

        $chatMessage = new ChatMessage;
        $casts = $chatMessage->getCasts();

        foreach ($expectedCasts as $key => $value) {
            $this->assertArrayHasKey($key, $casts);
            $this->assertEquals($value, $casts[$key]);
        }
    }

    public function test_appended_attributes()
    {
        $expectedAppends = ['user_profile', 'attachments'];

        $chatMessage = new ChatMessage;
        $this->assertEquals($expectedAppends, $chatMessage->getAppends());
    }

    public function test_create_chat_message()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Hello, this is a test message!',
            'is_read' => false,
            'is_starred' => true,
            'is_pinned' => false,
            'is_sent' => true,
            'is_received' => false,
        ]);

        $this->assertEquals($chat->id, $chatMessage->chat_id);
        $this->assertEquals('Hello, this is a test message!', $chatMessage->content);
        $this->assertFalse($chatMessage->is_read);
        $this->assertTrue($chatMessage->is_starred);
        $this->assertFalse($chatMessage->is_pinned);
        $this->assertTrue($chatMessage->is_sent);
        $this->assertFalse($chatMessage->is_received);
    }
    public function test_update_chat_message()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Original message',
            'is_read' => false,
            'is_starred' => false,
        ]);

        $chatMessage->update([
            'content' => 'Updated message',
            'is_read' => true,
            'is_starred' => true,
            'edited_at' => now(),
        ]);

        $this->assertEquals('Updated message', $chatMessage->content);
        $this->assertTrue($chatMessage->is_read);
        $this->assertTrue($chatMessage->is_starred);
        $this->assertNotNull($chatMessage->edited_at);
    }

    public function test_delete_chat_message()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chatMessage1 = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Message 1',
            'is_sent' => true,
        ]);

        $chatMessage2 = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Message 2',
            'is_sent' => true,
        ]);

        $this->assertCount(2, ChatMessage::all());

        $chatMessage2->delete();

        $this->assertFalse(ChatMessage::all()->contains('id', $chatMessage2->id));
        $this->assertTrue(ChatMessage::all()->contains('id', $chatMessage1->id));
        $this->assertCount(1, ChatMessage::all());
    }

    public function test_has_creator_trait()
    {
        $this->assertTrue(in_array(
            \Unusualify\Modularity\Entities\Traits\HasCreator::class,
            class_uses_recursive(new ChatMessage)
        ));
    }

    public function test_has_fileponds_trait()
    {
        $this->assertTrue(in_array(
            \Unusualify\Modularity\Entities\Traits\HasFileponds::class,
            class_uses_recursive(new ChatMessage)
        ));
    }

    public function test_chat_message_scopes_trait()
    {
        $this->assertTrue(in_array(
            \Unusualify\Modularity\Entities\Scopes\ChatMessageScopes::class,
            class_uses_recursive(new ChatMessage)
        ));
    }

    public function test_user_profile_accessor()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Test message with user',
            'custom_creator_id' => $user->id,
            'custom_creator_type' => get_class($user),
        ]);

        $userProfile = $chatMessage->user_profile;
        $this->assertIsArray($userProfile);
    }

    public function test_attachments_accessor()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Test message with attachments',
        ]);

        $attachments = $chatMessage->attachments;
        $this->assertInstanceOf(Collection::class, $attachments);
    }

    public function test_extends_model()
    {
        $chatMessage = new ChatMessage;
        $this->assertInstanceOf(\Unusualify\Modularity\Entities\Model::class, $chatMessage);
    }

    public function test_has_timestamps()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Timestamp test message',
        ]);

        $this->assertTrue($chatMessage->timestamps);
        $this->assertNotNull($chatMessage->created_at);
        $this->assertNotNull($chatMessage->updated_at);
    }

    public function test_soft_deletes()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Soft delete test message',
        ]);

        $chatMessage->delete();

        $this->assertSoftDeleted(modularityConfig('tables.chat_messages', 'um_chat_messages'), ['id' => $chatMessage->id]);
        $this->assertCount(0, ChatMessage::all());
        $this->assertCount(1, ChatMessage::withTrashed()->get());
    }

    public function test_create_chat_message_with_minimum_fields()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Minimal message',
        ]);

        $this->assertNotNull($chatMessage->id);
        $this->assertEquals($chat->id, $chatMessage->chat_id);
        $this->assertEquals('Minimal message', $chatMessage->content);
        $this->assertNull($chatMessage->is_read);
        $this->assertNull($chatMessage->is_starred);
        $this->assertNull($chatMessage->is_pinned);
        $this->assertNull($chatMessage->is_sent);
        $this->assertNull($chatMessage->is_received);
        $this->assertNull($chatMessage->edited_at);
    }

    public function test_boolean_casts_work_correctly()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Boolean test message',
            'is_read' => 1,
            'is_starred' => 0,
            'is_pinned' => '1',
            'is_sent' => '0',
            'is_received' => true,
        ]);

        $this->assertTrue($chatMessage->is_read);
        $this->assertFalse($chatMessage->is_starred);
        $this->assertTrue($chatMessage->is_pinned);
        $this->assertFalse($chatMessage->is_sent);
        $this->assertTrue($chatMessage->is_received);
    }

    public function test_chat_relationship()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Test chat relationship',
        ]);

        $relation = $chatMessage->chat();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Chat::class, $chatMessage->chat);
        $this->assertEquals($chat->id, $chatMessage->chat->id);
    }

    public function test_chat_message_pinning_behavior()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $message1 = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'First message',
            'is_pinned' => true,
        ]);

        $message2 = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Second message',
            'is_pinned' => false,
        ]);

        // Test that we can pin a message
        $this->assertTrue($message1->is_pinned);
        $this->assertFalse($message2->is_pinned);

        // Test updating pin status
        $message2->update(['is_pinned' => true]);
        $this->assertTrue($message2->fresh()->is_pinned);
    }

    public function test_chat_message_workflow_like_controller()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        // Create message like controller does
        $chatMessage = $chat->messages()->create([
            'content' => 'Test message from controller workflow',
        ]);

        $this->assertEquals($chat->id, $chatMessage->chat_id);
        $this->assertEquals('Test message from controller workflow', $chatMessage->content);

        // Test updating message like controller does
        $chatMessage->update([
            'is_read' => true,
            'is_starred' => true,
        ]);

        $this->assertTrue($chatMessage->is_read);
        $this->assertTrue($chatMessage->is_starred);
    }

    public function test_chat_message_with_creator()
    {
        $user = User::factory()->create();
        $creator = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $this->actingAs($creator);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Message with creator',
            'custom_creator_id' => $creator->id,
            'custom_creator_type' => get_class($creator),
        ]);

        $this->assertEquals($creator->id, $chatMessage->creator->id);
        $this->assertEquals(get_class($creator), $chatMessage->creatorRecord->creator_type);
    }
}
