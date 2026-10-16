<?php

namespace Unusualify\Modularous\Http\Controllers\Utility;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\SystemNotification\Events\ChatableMessageSynced;
use Unusualify\Modularous\Entities\Chat;
use Unusualify\Modularous\Entities\ChatMessage;
use Unusualify\Modularous\Facades\Filepond;
use Unusualify\Modularous\Services\ChatMessageService;

class ChatController extends Controller
{
    public function index(Request $request, Chat $chat, ChatMessageService $service)
    {
        $service->releaseExpiredPins($chat);

        $from = $request->get('from', null);

        if ($from) {

            $user_id = $request->get('user_id', null);
            // trashed messages are included so that clients can drop messages deleted since the last sync
            $query = $chat->messages()->withTrashed()->withStarredFor($request->user());

            if ($user_id) {
                // $query = $query->whereHas('user', function($query) use ($user_id) {
                //     $userTable = $query->getModel()->getTable();
                //     $query->where("{$userTable}.id", '!=', $user_id);
                // });
            }

            // $query->where('created_at', '>', Carbon::parse($from)->toDateTimeString());
            $updatedFrom = $request->get('updated_from', null);

            // also return already-loaded messages changed since the last sync (edits, read status, deletions, etc.)
            $query->where(function ($query) use ($from, $updatedFrom) {
                $query->where(function ($query) use ($from) {
                    $query->where('created_at', '>', $from)->whereNull('deleted_at');
                });

                if ($updatedFrom) {
                    $query->orWhere('updated_at', '>=', $updatedFrom);
                }
            });
            $messages = $query->get();

            if ($user_id) {
                $messages = $messages->filter(function ($message) use ($user_id) {
                    return $message->creator_id !== $user_id;
                })->values();
            }
        } else {

            $page = $request->get('page', 1);
            $perPage = $request->get('perPage', -1);

            if ($perPage === -1) {
                $messages = $chat->messages()->withStarredFor($request->user())->get();
            } else {
                $messages = $chat->messages()
                    ->withStarredFor($request->user())
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);
            }
        }

        return response()->json($messages)
            ->header('X-Chat-Synced-At', now()->format('Y-m-d H:i:s'))
            ->header('X-Chat-Unread-Count', (string) $chat->messages()->unreadForYou()->count())
            // lets polling clients notice pin changes, also on their own messages which are not polled
            ->header('X-Chat-Pinned-Ids', $chat->pinnedMessages()->pluck('id')->implode(','))
            // lets polling clients keep the starred messages badge in sync; stars are private to each user
            ->header('X-Chat-Starred-Count', (string) $chat->starredMessages($request->user())->count());
    }

    public function store(Request $request, Chat $chat)
    {
        $attachments = $request->get('attachments', []);

        $chatMessage = $chat->messages()->create($request->only('content'));

        if ($attachments) {
            Filepond::saveFile($chatMessage, $attachments, 'attachments');
        }

        $chat->chatable->touch();

        $chatMessage->refresh();
        $chatMessage->loadMissing(['creator']);

        event(new ChatableMessageSynced(
            ChatableMessageSynced::ACTION_CREATED,
            $chat->id,
            $chatMessage->toArray(),
        ));

        // Immediate retainable toast for other parties (does not stamp notified_at).
        $chatable = $chat->chatable;
        if ($chatable && method_exists($chatable, 'notifyChatableMessageBroadcast')) {
            $chatable->notifyChatableMessageBroadcast($chatMessage);
        }

        return response()->json($chatMessage);
    }

    public function attachments(Request $request, Chat $chat)
    {
        return response()->json($chat->attachments);
    }

    /**
     * Update the flags of a message (read, starred, pinned, etc.).
     *
     * When pinning, `pin_duration` (in hours, one of the configured durations)
     * sets how long the message stays pinned.
     */
    public function update(Request $request, $id, ChatMessageService $service)
    {
        $message = ChatMessage::findOrFail($id);

        $validated = $request->validate([
            'pin_duration' => ['nullable', 'integer', Rule::in(ChatMessage::getPinDurations())],
        ]);

        // content can only be changed through the edit endpoint
        $message = $service->updateFlags(
            $message,
            $request->only(ChatMessageService::UPDATABLE_FLAGS),
            $validated['pin_duration'] ?? null,
            $request->user()
        );

        return response()->json($message);
    }

    /**
     * Edit the content and attachments of a message.
     *
     * Allowed only for the sender, once, within the configured edit window
     * and while the message has not been read by the other party.
     */
    public function edit(Request $request, ChatMessage $chatMessage, ChatMessageService $service)
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.uuid' => ['required', 'string'],
        ]);

        $hasContent = filled($validated['content'] ?? null);
        $hasAttachments = ! empty($validated['attachments'] ?? []);

        if (! $hasContent && ! $hasAttachments) {
            return response()->json([
                'message' => __('A message must have content or attachments.'),
            ], 422);
        }

        $message = $service->edit($chatMessage, $request->user(), [
            'content' => $validated['content'] ?? null,
            'attachments' => $validated['attachments'] ?? [],
        ]);

        $message->refresh();
        $message->loadMissing(['creator']);

        event(new ChatableMessageSynced(
            ChatableMessageSynced::ACTION_UPDATED,
            $message->chat_id,
            $message->toArray(),
        ));

        return response()->json($message);
    }

    public function pinnedMessage(Request $request, $id)
    {
        $chat = Chat::find($id);

        return response()->json($chat->pinnedMessage);
    }

    /**
     * Pinned messages of the chat (up to the configured limit), the most recently pinned first.
     */
    public function pinnedMessages(Request $request, Chat $chat, ChatMessageService $service)
    {
        $service->releaseExpiredPins($chat);

        return response()->json($chat->pinnedMessages()->withStarredFor($request->user())->get());
    }

    /**
     * Messages of the chat starred by the current user, ordered by the message date, the newest first.
     *
     * Stars are private: the other participants' stars are not listed.
     *
     * Paginated, and optionally filtered by a search term on the message content.
     * Wrapped in `resource` as the browser input (v-input-browser) expects.
     */
    public function starredMessages(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = trim($validated['search'] ?? '');

        $messages = $chat->starredMessages($request->user())
            ->with('fileponds')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('content', 'like', '%' . addcslashes($search, '%_\\') . '%');
            })
            ->paginate($validated['perPage'] ?? 20, ['*'], 'page', $validated['page'] ?? 1);

        return response()->json(['resource' => $messages])
            ->header('X-Chat-Starred-Count', (string) $chat->starredMessages($request->user())->count());
    }

    /**
     * Delete a message.
     *
     * Follows the editing rules: only the sender, once, within the configured
     * edit window and while the message has not been read by the other party.
     */
    public function destroy(Request $request, ChatMessage $chatMessage, ChatMessageService $service)
    {
        $message = $service->delete($chatMessage, $request->user());
        $message->loadMissing(['creator']);

        event(new ChatableMessageSynced(
            ChatableMessageSynced::ACTION_DELETED,
            $message->chat_id,
            $message->toArray(),
        ));

        return response()->json($message);
    }

    public function show(Request $request, ChatMessage $chat_message)
    {
        return response()->json($chat_message);
    }

    /**
     * Mark all messages of the chat sent by the other party as read.
     */
    public function markAllAsRead(Request $request, Chat $chat, ChatMessageService $service)
    {
        $count = $service->markAllAsRead($chat, $request->user());

        return response()->json(['count' => $count]);
    }

    /**
     * Unstar all messages of the chat starred by the current user; the other participants' stars stay.
     */
    public function unstarAll(Request $request, Chat $chat, ChatMessageService $service)
    {
        $count = $service->unstarAll($chat, $request->user());

        return response()->json(['count' => $count]);
    }
}
