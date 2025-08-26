<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Entities\Chat;
use Unusualify\Modularity\Entities\ChatMessage;
use Unusualify\Modularity\Entities\Filepond;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class ChatTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_chat()
    {
        $chat = new Chat;
        $this->assertEquals(modularityConfig('tables.chats', 'um_chats'), $chat->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'chatable_id',
            'chatable_type',
        ];

        $chat = new Chat;
        $this->assertEquals($expectedFillable, $chat->getFillable());
    }

    public function test_appended_attributes()
    {
        $expectedAppends = ['attachments'];

        $chat = new Chat;
        $this->assertEquals($expectedAppends, $chat->getAppends());
    }

    public function test_create_chat()
    {
        $user = User::factory()->create();

        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $this->assertEquals($user->id, $chat->chatable_id);
        $this->assertEquals(get_class($user), $chat->chatable_type);
    }

    public function test_update_chat()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $chat = Chat::create([
            'chatable_id' => $user1->id,
            'chatable_type' => get_class($user1),
        ]);

        $chat->update([
            'chatable_id' => $user2->id,
            'chatable_type' => get_class($user2),
        ]);

        $this->assertEquals($user2->id, $chat->chatable_id);
        $this->assertEquals(get_class($user2), $chat->chatable_type);
    }

    public function test_delete_chat()
    {
        $user = User::factory()->create();

        $chat1 = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chat2 = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $this->assertCount(2, Chat::all());

        $chat2->delete();

        $this->assertFalse(Chat::all()->contains('id', $chat2->id));
        $this->assertTrue(Chat::all()->contains('id', $chat1->id));
        $this->assertCount(1, Chat::all());
    }

    public function test_chatable_relationship()
    {
        $user = User::factory()->create();

        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $relation = $chat->chatable();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $chat->chatable);
        $this->assertEquals($user->id, $chat->chatable->id);
    }

    public function test_messages_relationship()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $message1 = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'First message',
        ]);

        $message2 = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Second message',
        ]);

        $relation = $chat->messages();
        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('chat_id', $relation->getForeignKeyName());
        $this->assertInstanceOf(ChatMessage::class, $relation->getRelated());

        $this->assertCount(2, $chat->messages);
        $this->assertTrue($chat->messages->contains('id', $message1->id));
        $this->assertTrue($chat->messages->contains('id', $message2->id));
    }

    public function test_fileponds_relationship()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $relation = $chat->fileponds();
        $this->assertInstanceOf(HasManyThrough::class, $relation);
        $this->assertInstanceOf(Filepond::class, $relation->getRelated());
    }

    public function test_attachments_accessor()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $attachments = $chat->attachments;

        $this->assertInstanceOf(Collection::class, $attachments);
    }

    public function test_pinned_message_accessor()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        // Create messages, one pinned
        $regularMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Regular message',
            'is_pinned' => false,
        ]);

        $pinnedMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Pinned message',
            'is_pinned' => true,
        ]);

        $retrievedPinnedMessage = $chat->pinnedMessage;
        $this->assertInstanceOf(ChatMessage::class, $retrievedPinnedMessage);
        $this->assertEquals($pinnedMessage->id, $retrievedPinnedMessage->id);
        $this->assertTrue($retrievedPinnedMessage->is_pinned);
    }

    public function test_extends_model()
    {
        $chat = new Chat;
        $this->assertInstanceOf(\Unusualify\Modularity\Entities\Model::class, $chat);
    }

    public function test_has_timestamps()
    {
        $user = User::factory()->create();

        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $this->assertTrue($chat->timestamps);
        $this->assertNotNull($chat->created_at);
        $this->assertNotNull($chat->updated_at);
    }

    public function test_soft_deletes()
    {
        $user = User::factory()->create();

        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $chat->delete();

        $this->assertSoftDeleted(modularityConfig('tables.chats', 'um_chats'), ['id' => $chat->id]);
        $this->assertCount(0, Chat::all());
        $this->assertCount(1, Chat::withTrashed()->get());
    }

    public function test_chat_with_multiple_messages()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        // Create multiple messages for the chat
        $messages = collect();
        for ($i = 1; $i <= 5; $i++) {
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'content' => "Message {$i}",
                'is_read' => $i % 2 === 0, // Every second message is read
            ]);
            $messages->push($message);
        }

        $this->assertCount(5, $chat->messages);

        // Test that we can filter messages
        $readMessages = $chat->messages()->where('is_read', true)->get();
        $unreadMessages = $chat->messages()->where('is_read', false)->get();

        $this->assertCount(2, $readMessages);
        $this->assertCount(3, $unreadMessages);
    }

    public function test_chat_workflow_like_controller()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        // Create message like controller does
        $chatMessage = $chat->messages()->create([
            'content' => 'Controller workflow message',
        ]);

        $this->assertEquals($chat->id, $chatMessage->chat_id);
        $this->assertEquals('Controller workflow message', $chatMessage->content);

        // Test getting attachments like controller
        $attachments = $chat->attachments;
        $this->assertInstanceOf(Collection::class, $attachments);

        // Test getting pinned message like controller
        $pinnedMessage = $chat->pinnedMessage;
        $this->assertNull($pinnedMessage); // No pinned message yet

        // Pin the message
        $chatMessage->update(['is_pinned' => true]);

        // Refresh chat and check pinned message
        $chat->refresh();
        $pinnedMessage = $chat->pinnedMessage;
        $this->assertInstanceOf(ChatMessage::class, $pinnedMessage);
        $this->assertEquals($chatMessage->id, $pinnedMessage->id);
    }

    public function test_create_chat_with_minimum_fields()
    {
        $user = User::factory()->create();

        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $this->assertNotNull($chat->id);
        $this->assertEquals($user->id, $chat->chatable_id);
        $this->assertEquals(get_class($user), $chat->chatable_type);
    }

    public function test_chat_messages_ordering()
    {
        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        // Create messages with specific timestamps
        $firstMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'First message',
        ]);

        sleep(1); // Ensure different timestamps

        $secondMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Second message',
        ]);

        // Test default ordering (should be by creation time)
        $messagesAsc = $chat->messages()->orderBy('created_at')->get();
        $this->assertEquals($firstMessage->id, $messagesAsc->first()->id);
        $this->assertEquals($secondMessage->id, $messagesAsc->last()->id);

        // Test descending order (like in controller)
        $messagesDesc = $chat->messages()->orderBy('created_at', 'desc')->get();
        $this->assertEquals($secondMessage->id, $messagesDesc->first()->id);
        $this->assertEquals($firstMessage->id, $messagesDesc->last()->id);
    }
}
