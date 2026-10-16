<?php

namespace Unusualify\Modularous\Tests\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Unusualify\Modularous\Entities\Chat;
use Unusualify\Modularous\Entities\ChatMessage;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Http\Controllers\Utility\ChatController;
use Unusualify\Modularous\Services\ChatMessageService;
use Unusualify\Modularous\Tests\ModelTestCase;

class ChatControllerTest extends ModelTestCase
{
    use RefreshDatabase;

    protected ChatController $controller;

    protected User $user;

    protected Chat $chat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = $this->app->make(ChatController::class);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->chat = $this->createChat();
    }

    /**
     * Create a chat owned by the acting user.
     */
    protected function createChat(): Chat
    {
        return Chat::create([
            'chatable_id' => $this->user->id,
            'chatable_type' => get_class($this->user),
        ]);
    }

    /**
     * Create a message in the given chat, created at the given time.
     *
     * `is_starred` stars the message for the acting user.
     */
    protected function createMessage(Chat $chat, array $attributes = [], ?Carbon $createdAt = null): ChatMessage
    {
        $starred = (bool) ($attributes['is_starred'] ?? false);
        unset($attributes['is_starred']);

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'content' => 'Message',
            'custom_creator_id' => $this->user->id,
            'custom_creator_type' => get_class($this->user),
            ...$attributes,
        ]);

        if ($createdAt) {
            $message->forceFill(['created_at' => $createdAt])->saveQuietly();
        }

        if ($starred) {
            $this->star($message, $this->user);
        }

        return $message->refresh();
    }

    /**
     * Star the message for the given user.
     */
    protected function star(ChatMessage $message, User $user): void
    {
        $message->stars()->create([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
        ]);
    }

    /**
     * Call the starred messages endpoint with the given query parameters.
     */
    protected function starredMessages(array $query = [], ?Chat $chat = null): JsonResponse
    {
        $request = Request::create('/', 'GET', $query);
        $request->setUserResolver(fn () => auth()->user());

        return $this->controller->starredMessages($request, $chat ?? $this->chat);
    }

    public function test_it_returns_only_starred_messages_of_the_chat_wrapped_in_resource()
    {
        $starred = $this->createMessage($this->chat, ['content' => 'Starred', 'is_starred' => true]);
        $this->createMessage($this->chat, ['content' => 'Not starred', 'is_starred' => false]);

        $otherChat = $this->createChat();
        $this->createMessage($otherChat, ['content' => 'Other chat', 'is_starred' => true]);

        $response = $this->starredMessages();

        $this->assertSame(200, $response->getStatusCode());

        $resource = $response->getData(true)['resource'];

        $this->assertSame([$starred->id], array_column($resource['data'], 'id'));
        $this->assertSame(1, $resource['total']);
        $this->assertSame('1', $response->headers->get('X-Chat-Starred-Count'));
    }

    public function test_it_orders_starred_messages_newest_first()
    {
        $oldest = $this->createMessage($this->chat, ['is_starred' => true], now()->subDays(2));
        $newest = $this->createMessage($this->chat, ['is_starred' => true], now());
        $middle = $this->createMessage($this->chat, ['is_starred' => true], now()->subDay());

        $data = $this->starredMessages()->getData(true)['resource']['data'];

        $this->assertSame([$newest->id, $middle->id, $oldest->id], array_column($data, 'id'));
    }

    public function test_it_paginates_twenty_messages_per_page_by_default()
    {
        foreach (range(1, 25) as $day) {
            $this->createMessage($this->chat, ['is_starred' => true], now()->subDays($day));
        }

        $firstPage = $this->starredMessages()->getData(true)['resource'];

        $this->assertCount(20, $firstPage['data']);
        $this->assertSame(20, $firstPage['per_page']);
        $this->assertSame(2, $firstPage['last_page']);

        $secondPage = $this->starredMessages(['page' => 2])->getData(true)['resource'];

        $this->assertCount(5, $secondPage['data']);
        $this->assertEmpty(array_intersect(
            array_column($firstPage['data'], 'id'),
            array_column($secondPage['data'], 'id')
        ));
    }

    public function test_it_respects_the_per_page_parameter()
    {
        foreach (range(1, 3) as $day) {
            $this->createMessage($this->chat, ['is_starred' => true], now()->subDays($day));
        }

        $resource = $this->starredMessages(['perPage' => 2])->getData(true)['resource'];

        $this->assertCount(2, $resource['data']);
        $this->assertSame(2, $resource['last_page']);
    }

    public function test_it_filters_by_search_while_the_count_header_keeps_the_total()
    {
        $match = $this->createMessage($this->chat, ['content' => 'Invoice for October', 'is_starred' => true]);
        $this->createMessage($this->chat, ['content' => 'Meeting notes', 'is_starred' => true]);

        $response = $this->starredMessages(['search' => '  invoice  ']);

        $this->assertSame([$match->id], array_column($response->getData(true)['resource']['data'], 'id'));
        $this->assertSame('2', $response->headers->get('X-Chat-Starred-Count'));
    }

    public function test_it_treats_like_wildcards_in_search_literally()
    {
        $match = $this->createMessage($this->chat, ['content' => '100% done', 'is_starred' => true]);
        $this->createMessage($this->chat, ['content' => '100 done', 'is_starred' => true]);

        $data = $this->starredMessages(['search' => '100%'])->getData(true)['resource']['data'];

        $this->assertSame([$match->id], array_column($data, 'id'));
    }

    public function test_it_includes_the_edited_at_and_attachments_of_the_messages()
    {
        $this->createMessage($this->chat, ['is_starred' => true, 'edited_at' => now()]);

        $message = $this->starredMessages()->getData(true)['resource']['data'][0];

        $this->assertNotNull($message['edited_at']);
        $this->assertArrayHasKey('attachments', $message);
        $this->assertSame([], $message['attachments']);
    }

    public function test_it_returns_an_empty_page_when_nothing_is_starred()
    {
        $this->createMessage($this->chat, ['is_starred' => false]);

        $response = $this->starredMessages();

        $this->assertSame([], $response->getData(true)['resource']['data']);
        $this->assertSame('0', $response->headers->get('X-Chat-Starred-Count'));
    }

    public function test_it_lists_only_the_messages_starred_by_the_current_user()
    {
        $other = User::factory()->create();

        $mine = $this->createMessage($this->chat, ['content' => 'Mine', 'is_starred' => true]);
        $theirs = $this->createMessage($this->chat, ['content' => 'Theirs']);
        $this->star($theirs, $other);

        $response = $this->starredMessages();

        $data = $response->getData(true)['resource']['data'];

        $this->assertSame([$mine->id], array_column($data, 'id'));
        $this->assertTrue($data[0]['is_starred']);
        $this->assertSame('1', $response->headers->get('X-Chat-Starred-Count'));

        // the other participant sees only their own star
        $this->actingAs($other);

        $response = $this->starredMessages();

        $this->assertSame([$theirs->id], array_column($response->getData(true)['resource']['data'], 'id'));
        $this->assertSame('1', $response->headers->get('X-Chat-Starred-Count'));
    }

    public function test_starring_a_message_does_not_change_the_other_users_starred_messages()
    {
        $other = User::factory()->create();
        $message = $this->createMessage($this->chat);

        $request = Request::create('/', 'PUT', ['is_starred' => true]);
        $request->setUserResolver(fn () => $this->user);

        $response = $this->controller->update($request, $message->id, $this->app->make(ChatMessageService::class));

        $this->assertTrue($response->getData(true)['is_starred']);
        $this->assertSame('1', $this->starredMessages()->headers->get('X-Chat-Starred-Count'));

        $this->actingAs($other);

        $response = $this->starredMessages();

        $this->assertSame([], $response->getData(true)['resource']['data']);
        $this->assertSame('0', $response->headers->get('X-Chat-Starred-Count'));
    }

    public function test_it_rejects_a_per_page_over_one_hundred()
    {
        $this->expectException(ValidationException::class);

        $this->starredMessages(['perPage' => 101]);
    }

    public function test_it_rejects_a_page_below_one()
    {
        $this->expectException(ValidationException::class);

        $this->starredMessages(['page' => 0]);
    }
}
