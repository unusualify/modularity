<?php

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularous\Entities\Chat;
use Unusualify\Modularous\Entities\ChatMessage;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Services\ChatMessageService;
use Unusualify\Modularous\Tests\ModelTestCase;

class ChatMessageServiceTest extends ModelTestCase
{
    use RefreshDatabase;

    protected ChatMessageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ChatMessageService::class);
    }

    protected function createMessageFor(User $creator, array $attributes = []): ChatMessage
    {
        $chat = Chat::create([
            'chatable_id' => $creator->id,
            'chatable_type' => get_class($creator),
        ]);

        return ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Original content',
            'custom_creator_id' => $creator->id,
            'custom_creator_type' => get_class($creator),
            ...$attributes,
        ])->refresh();
    }

    public function test_creator_can_edit_unread_message_within_window()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $edited = $this->service->edit($chatMessage, $creator, ['content' => 'Edited content']);

        $this->assertSame('Edited content', $edited->content);
        $this->assertNotNull($edited->edited_at);
        $this->assertTrue($edited->isEdited());
        $this->assertFalse($edited->canBeEdited());
        $this->assertFalse($edited->is_read);
    }

    public function test_message_cannot_be_edited_twice()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);
        $this->service->edit($chatMessage, $creator, ['content' => 'First edit']);

        $this->expectException(AuthorizationException::class);

        $this->service->edit($chatMessage, $creator, ['content' => 'Second edit']);
    }

    public function test_read_message_cannot_be_edited()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);
        $chatMessage->update(['is_read' => true]);

        $this->expectException(AuthorizationException::class);

        $this->service->edit($chatMessage, $creator, ['content' => 'Too late']);
    }

    public function test_message_cannot_be_edited_after_window()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $this->travel(ChatMessage::getEditWindow() + 1)->minutes();

        $this->expectException(AuthorizationException::class);

        $this->service->edit($chatMessage, $creator, ['content' => 'Too late']);
    }

    public function test_other_user_cannot_edit_message()
    {
        $creator = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        try {
            $this->service->edit($chatMessage, $other, ['content' => 'Hijacked']);
            $this->fail('Expected AuthorizationException was not thrown.');
        } catch (AuthorizationException) {
            $this->assertSame('Original content', $chatMessage->refresh()->content);
            $this->assertNull($chatMessage->edited_at);
        }
    }

    public function test_unchanged_edit_does_not_mark_message_as_edited()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $result = $this->service->edit($chatMessage, $creator, [
            'content' => 'Original content',
            'attachments' => [],
        ]);

        $this->assertNull($result->edited_at);
        $this->assertFalse($result->isEdited());
        $this->assertNull($chatMessage->refresh()->edited_at);
        // still editable since nothing was consumed
        $this->assertTrue($chatMessage->canBeEdited());
    }

    public function test_whitespace_only_change_is_not_an_edit()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $this->service->edit($chatMessage, $creator, ['content' => "  Original content \n"]);

        $this->assertNull($chatMessage->refresh()->edited_at);
        $this->assertSame('Original content', $chatMessage->content);
    }

    public function test_creator_can_delete_unread_message_within_window()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $deleted = $this->service->delete($chatMessage, $creator);

        $this->assertTrue($deleted->trashed());
        $this->assertNull(ChatMessage::find($chatMessage->id));
        $this->assertNotNull(ChatMessage::withTrashed()->find($chatMessage->id));
    }

    public function test_read_message_cannot_be_deleted()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);
        $chatMessage->update(['is_read' => true]);

        $this->expectException(AuthorizationException::class);

        $this->service->delete($chatMessage, $creator);
    }

    public function test_edited_message_cannot_be_deleted()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);
        $this->service->edit($chatMessage, $creator, ['content' => 'Edited content']);

        $this->expectException(AuthorizationException::class);

        $this->service->delete($chatMessage, $creator);
    }

    public function test_message_cannot_be_deleted_after_window()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $this->travel(ChatMessage::getEditWindow() + 1)->minutes();

        $this->expectException(AuthorizationException::class);

        $this->service->delete($chatMessage, $creator);
    }

    public function test_other_user_cannot_delete_message()
    {
        $creator = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        try {
            $this->service->delete($chatMessage, $other);
            $this->fail('Expected AuthorizationException was not thrown.');
        } catch (AuthorizationException) {
            $this->assertNotNull(ChatMessage::find($chatMessage->id));
        }
    }

    public function test_has_changes_detects_content_and_attachment_differences()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $chatMessage = $this->createMessageFor($creator);

        $this->assertFalse($this->service->hasChanges($chatMessage, ['content' => 'Original content']));
        $this->assertFalse($this->service->hasChanges($chatMessage, ['content' => 'Original content', 'attachments' => []]));
        $this->assertTrue($this->service->hasChanges($chatMessage, ['content' => 'Changed content']));
        $this->assertTrue($this->service->hasChanges($chatMessage, [
            'content' => 'Original content',
            'attachments' => [['uuid' => 'new-upload-uuid']],
        ]));
    }

    public function test_mark_all_as_read_marks_only_messages_of_the_other_party()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user);

        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $create = fn (User $creator) => ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Message',
            'custom_creator_id' => $creator->id,
            'custom_creator_type' => get_class($creator),
        ]);

        $incoming = [$create($other), $create($other)];
        $own = $create($user);

        $count = $this->service->markAllAsRead($chat, $user);

        $this->assertSame(2, $count);

        foreach ($incoming as $message) {
            $this->assertTrue($message->refresh()->is_read);
        }

        $this->assertFalse($own->refresh()->is_read);

        // nothing left to mark
        $this->assertSame(0, $this->service->markAllAsRead($chat, $user));
    }

    public function test_mark_all_as_read_without_user_does_nothing()
    {
        $other = User::factory()->create();
        $chatMessage = $this->createMessageFor($other);

        $this->assertSame(0, $this->service->markAllAsRead($chatMessage->chat, null));
        $this->assertFalse($chatMessage->refresh()->is_read);
    }

    public function test_update_flags_pins_for_the_given_duration()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $message = $this->createMessageFor($user);

        $this->freezeTime();

        $this->service->updateFlags($message, ['is_pinned' => true, 'content' => 'ignored'], 24 * 7);

        $message->refresh();
        $this->assertTrue($message->is_pinned);
        $this->assertSame('Original content', $message->content);
        $this->assertTrue($message->pinned_until->equalTo(now()->addDays(7)));

        $this->service->updateFlags($message, ['is_pinned' => false]);

        $message->refresh();
        $this->assertFalse($message->is_pinned);
        $this->assertNull($message->pinned_until);
    }

    public function test_update_flags_without_duration_pins_until_unpinned()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $message = $this->createMessageFor($user);

        $this->service->updateFlags($message, ['is_pinned' => true]);

        $message->refresh();
        $this->assertTrue($message->is_pinned);
        $this->assertNull($message->pinned_until);
    }

    public function test_release_expired_pins_unpins_only_expired_messages()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $message = $this->createMessageFor($user);
        $chat = $message->chat;

        $active = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Active',
            'custom_creator_id' => $user->id,
            'custom_creator_type' => get_class($user),
        ]);

        $this->service->updateFlags($message, ['is_pinned' => true], 24);
        $this->service->updateFlags($active, ['is_pinned' => true], 24 * 7);

        $this->travel(25)->hours();

        $this->assertSame(1, $this->service->releaseExpiredPins($chat));

        $message->refresh();
        $this->assertFalse($message->is_pinned);
        $this->assertNull($message->pinned_at);
        $this->assertNull($message->pinned_until);
        $this->assertTrue($active->refresh()->is_pinned);

        $this->assertSame(0, $this->service->releaseExpiredPins($chat));
    }

    /**
     * Star the message for the given user.
     */
    protected function starFor(ChatMessage $message, User $user): void
    {
        $this->service->setStarred($message, $user, true);
    }

    public function test_starring_a_message_is_private_to_the_user()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user);

        $message = $this->createMessageFor($other);
        $updatedAt = $message->updated_at;

        $this->travel(1)->minutes();

        $response = $this->service->updateFlags($message, ['is_starred' => true], null, $user);

        $this->assertTrue($response->is_starred);
        $this->assertTrue($message->isStarredBy($user));
        $this->assertFalse($message->isStarredBy($other));

        // the message itself is not updated, so the other participant's clients see no change
        $this->assertEquals($updatedAt, $message->refresh()->updated_at);
    }

    public function test_unstarring_a_message_keeps_the_other_users_star()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user);

        $message = $this->createMessageFor($other);
        $this->starFor($message, $user);
        $this->starFor($message, $other);

        $response = $this->service->updateFlags($message, ['is_starred' => false], null, $user);

        $this->assertFalse($response->is_starred);
        $this->assertFalse($message->isStarredBy($user));
        $this->assertTrue($message->isStarredBy($other));
    }

    public function test_starring_twice_keeps_a_single_star()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $message = $this->createMessageFor($user);

        $this->assertTrue($this->service->setStarred($message, $user, true));
        $this->assertFalse($this->service->setStarred($message, $user, true));

        $this->assertSame(1, $message->stars()->count());
    }

    public function test_starring_without_a_user_does_nothing()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $message = $this->createMessageFor($user);

        $this->assertFalse($this->service->setStarred($message, null, true));
        $this->assertSame(0, $message->stars()->count());
    }

    public function test_unstar_all_unstars_only_the_messages_starred_by_the_user()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user);

        $chat = Chat::create([
            'chatable_id' => $user->id,
            'chatable_type' => get_class($user),
        ]);

        $create = fn (User $creator) => ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Message',
            'custom_creator_id' => $creator->id,
            'custom_creator_type' => get_class($creator),
        ]);

        $starred = [$create($user), $create($other)];
        $plain = $create($other);

        foreach ($starred as $message) {
            $this->starFor($message, $user);
            $this->starFor($message, $other);
        }

        $this->assertSame(2, $this->service->unstarAll($chat, $user));

        foreach ($starred as $message) {
            $this->assertFalse($message->isStarredBy($user));
            // the other participant's stars stay
            $this->assertTrue($message->isStarredBy($other));
        }

        $this->assertFalse($plain->isStarredBy($user));
        $this->assertSame(0, $chat->starredMessages($user)->count());
        $this->assertSame(2, $chat->starredMessages($other)->count());

        // nothing left to unstar
        $this->assertSame(0, $this->service->unstarAll($chat, $user));
    }

    public function test_unstar_all_leaves_other_chats_untouched()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $chat = Chat::create(['chatable_id' => $user->id, 'chatable_type' => get_class($user)]);
        $otherChat = Chat::create(['chatable_id' => $user->id, 'chatable_type' => get_class($user)]);

        $otherMessage = ChatMessage::create([
            'chat_id' => $otherChat->id,
            'content' => 'Message',
            'custom_creator_id' => $user->id,
            'custom_creator_type' => get_class($user),
        ]);
        $this->starFor($otherMessage, $user);

        $this->assertSame(0, $this->service->unstarAll($chat, $user));
        $this->assertTrue($otherMessage->isStarredBy($user));
    }

    public function test_unstar_all_without_a_user_does_nothing()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $message = $this->createMessageFor($user);
        $this->starFor($message, $user);

        $this->assertSame(0, $this->service->unstarAll($message->chat, null));
        $this->assertTrue($message->isStarredBy($user));
    }
}
