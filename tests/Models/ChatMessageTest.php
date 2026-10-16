<?php

namespace Unusualify\Modularous\Tests\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularous\Entities\Chat;
use Unusualify\Modularous\Entities\ChatMessage;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Scopes\ChatMessageScopes;
use Unusualify\Modularous\Entities\Traits\HasCreator;
use Unusualify\Modularous\Entities\Traits\HasFileponds;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Tests\ModelTestCase;

class ChatMessageTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_chat_message()
    {
        $chatMessage = new ChatMessage;
        $this->assertEquals(modularousConfig('tables.chat_messages', 'um_chat_messages'), $chatMessage->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'chat_id',
            'content',
            'is_read',
            'is_pinned',
            'is_sent',
            'is_received',
            'edited_at',
            'notified_at',
            'pinned_until',

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
            'is_pinned' => 'boolean',
            'is_sent' => 'boolean',
            'is_received' => 'boolean',
            'edited_at' => 'datetime',
            'pinned_at' => 'datetime',
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
        $expectedAppends = ['user_profile', 'attachments', 'is_editable', 'editable_until', 'is_deletable', 'is_starred'];

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
            'is_pinned' => false,
            'is_sent' => true,
            'is_received' => false,
        ]);

        $this->assertEquals($chat->id, $chatMessage->chat_id);
        $this->assertEquals('Hello, this is a test message!', $chatMessage->content);
        $this->assertFalse($chatMessage->is_read);
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
        ]);

        $chatMessage->update([
            'content' => 'Updated message',
            'is_read' => true,
            'edited_at' => now(),
        ]);

        $this->assertEquals('Updated message', $chatMessage->content);
        $this->assertTrue($chatMessage->is_read);
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
            HasCreator::class,
            class_uses_recursive(new ChatMessage)
        ));
    }

    public function test_has_fileponds_trait()
    {
        $this->assertTrue(in_array(
            HasFileponds::class,
            class_uses_recursive(new ChatMessage)
        ));
    }

    public function test_chat_message_scopes_trait()
    {
        $this->assertTrue(in_array(
            ChatMessageScopes::class,
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
        $chatMessage->refresh();

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
        $this->assertInstanceOf(Model::class, $chatMessage);
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

        $this->assertSoftDeleted(modularousConfig('tables.chat_messages', 'um_chat_messages'), ['id' => $chatMessage->id]);
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
        // nobody starred it
        $this->assertFalse($chatMessage->is_starred);
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
            'is_pinned' => '1',
            'is_sent' => '0',
            'is_received' => true,
        ]);

        $this->assertTrue($chatMessage->is_read);
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

        // several messages can be pinned at the same time
        $this->assertTrue($message1->fresh()->is_pinned);
    }

    public function test_pinning_sets_and_unpinning_clears_pinned_at()
    {
        $chat = $this->createChat();

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Message',
        ]);

        $this->assertNull($message->pinned_at);

        $message->update(['is_pinned' => true]);
        $this->assertNotNull($message->fresh()->pinned_at);

        $message->update(['is_pinned' => false]);
        $this->assertNull($message->fresh()->pinned_at);
    }

    public function test_pinning_over_the_limit_unpins_the_oldest_pin()
    {
        config([modularousBaseKey('chat.max_pinned_messages') => 3]);

        $chat = $this->createChat();

        $messages = collect(range(1, 4))->map(fn ($i) => ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => "Message {$i}",
        ]));

        foreach ($messages as $i => $message) {
            $this->travel($i)->minutes();
            $message->update(['is_pinned' => true]);
        }

        $this->assertFalse($messages[0]->fresh()->is_pinned);
        $this->assertNull($messages[0]->fresh()->pinned_at);

        foreach ($messages->slice(1) as $message) {
            $this->assertTrue($message->fresh()->is_pinned);
        }

        $this->assertSame(3, $chat->messages()->where('is_pinned', true)->count());
    }

    public function test_pins_of_other_chats_are_not_affected()
    {
        config([modularousBaseKey('chat.max_pinned_messages') => 1]);

        $chat = $this->createChat();
        $otherChat = $this->createChat();

        $otherMessage = ChatMessage::create(['chat_id' => $otherChat->id, 'content' => 'Other']);
        $otherMessage->update(['is_pinned' => true]);

        $message = ChatMessage::create(['chat_id' => $chat->id, 'content' => 'Message']);
        $message->update(['is_pinned' => true]);

        $this->assertTrue($otherMessage->fresh()->is_pinned);
        $this->assertTrue($message->fresh()->is_pinned);
    }

    public function test_unpinning_clears_pinned_until()
    {
        $chat = $this->createChat();

        $message = ChatMessage::create(['chat_id' => $chat->id, 'content' => 'Message']);
        $message->update(['is_pinned' => true, 'pinned_until' => now()->addDay()]);

        $this->assertNotNull($message->fresh()->pinned_until);

        $message->update(['is_pinned' => false]);
        $this->assertNull($message->fresh()->pinned_until);
    }

    public function test_expired_pins_do_not_count_towards_the_limit()
    {
        config([modularousBaseKey('chat.max_pinned_messages') => 1]);

        $chat = $this->createChat();

        $expired = ChatMessage::create(['chat_id' => $chat->id, 'content' => 'Expired']);
        $expired->update(['is_pinned' => true, 'pinned_until' => now()->addHour()]);

        $this->travel(2)->hours();

        $message = ChatMessage::create(['chat_id' => $chat->id, 'content' => 'Message']);
        $message->update(['is_pinned' => true]);

        $this->assertTrue($message->fresh()->is_pinned);
        // left to the expired pins cleanup, not counted as an overflowing pin
        $this->assertNotNull($expired->fresh()->pinned_until);
    }

    public function test_get_pin_durations_reads_the_config()
    {
        config([modularousBaseKey('chat.pin_durations') => [8, '48', 0, 8]]);

        $this->assertSame([8, 48], ChatMessage::getPinDurations());
    }

    protected function createChat(): Chat
    {
        $user = User::factory()->create();

        return Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);
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
        ]);

        $this->assertTrue($chatMessage->is_read);
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
        $chatMessage->refresh();

        $this->assertEquals($creator->id, $chatMessage->creator->id);
        $this->assertEquals(get_class($creator), $chatMessage->creatorRecord->creator_type);
    }

    protected function createMessageFor(User $creator, array $attributes = []): ChatMessage
    {
        $chat = Chat::create([
            'chatable_id' => $creator->id,
            'chatable_type' => get_class($creator),
        ]);

        $chatMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Editable message',
            'custom_creator_id' => $creator->id,
            'custom_creator_type' => get_class($creator),
            ...$attributes,
        ]);

        return $chatMessage->refresh();
    }

    public function test_fresh_unread_message_can_be_edited()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $this->assertTrue($chatMessage->canBeEdited());
        $this->assertTrue($chatMessage->is_editable);
        $this->assertNotNull($chatMessage->editable_until);
    }

    public function test_message_cannot_be_edited_after_edit_window()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $this->travel(ChatMessage::getEditWindow() + 1)->minutes();

        $this->assertFalse($chatMessage->canBeEdited());
        $this->assertNull($chatMessage->editable_until);
    }

    public function test_edit_window_is_config_driven()
    {
        config(['modularous.chat.edit_window' => 5]);

        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $this->travel(6)->minutes();

        $this->assertSame(5, ChatMessage::getEditWindow());
        $this->assertFalse($chatMessage->canBeEdited());
    }

    public function test_read_message_cannot_be_edited()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator, ['is_read' => true]);

        $this->assertFalse($chatMessage->canBeEdited());
    }

    public function test_message_can_be_edited_only_once()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator, ['edited_at' => now()]);

        $this->assertTrue($chatMessage->isEdited());
        $this->assertFalse($chatMessage->canBeEdited());
    }

    public function test_only_creator_can_edit_message()
    {
        $creator = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $this->assertTrue($chatMessage->canBeEditedBy($creator));
        $this->assertFalse($chatMessage->canBeEditedBy($other));
        $this->assertFalse($chatMessage->canBeEditedBy(null));
    }

    public function test_deleting_follows_editing_rules()
    {
        $creator = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $this->assertTrue($chatMessage->canBeDeleted());
        $this->assertTrue($chatMessage->is_deletable);
        $this->assertTrue($chatMessage->canBeDeletedBy($creator));
        $this->assertFalse($chatMessage->canBeDeletedBy($other));
        $this->assertFalse($chatMessage->canBeDeletedBy(null));

        $chatMessage->update(['is_read' => true]);
        $this->assertFalse($chatMessage->canBeDeleted());

        $edited = $this->createMessageFor($creator, ['edited_at' => now()]);
        $this->assertFalse($edited->canBeDeleted());

        $expired = $this->createMessageFor($creator);
        $this->travel(ChatMessage::getEditWindow() + 1)->minutes();
        $this->assertFalse($expired->canBeDeleted());
    }
}
