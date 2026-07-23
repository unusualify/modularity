<?php

namespace Unusualify\Modularous\Http\Controllers\Utility;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SystemNotification\Events\ChatableMessageSynced;
use Unusualify\Modularous\Entities\Chat;
use Unusualify\Modularous\Entities\ChatMessage;
use Unusualify\Modularous\Facades\Filepond;

class ChatController extends Controller
{
    public function index(Request $request, Chat $chat)
    {
        $from = $request->get('from', null);

        if ($from) {

            $user_id = $request->get('user_id', null);
            $query = $chat->messages();

            if ($user_id) {
                // $query = $query->whereHas('user', function($query) use ($user_id) {
                //     $userTable = $query->getModel()->getTable();
                //     $query->where("{$userTable}.id", '!=', $user_id);
                // });
            }

            // $query->where('created_at', '>', Carbon::parse($from)->toDateTimeString());
            $query->where('created_at', '>', $from);
            $messages = $query->get();

            if ($user_id) {
                $messages = $messages->filter(function ($message) use ($user_id) {
                    return $message->creator_id !== $user_id;
                });
            }
        } else {

            $page = $request->get('page', 1);
            $perPage = $request->get('perPage', -1);

            if ($perPage === -1) {
                $messages = $chat->messages;
            } else {
                $messages = $chat->messages()
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);
            }
        }

        return response()->json($messages);
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

    public function update(Request $request, $id)
    {
        $message = ChatMessage::find($id);

        $message->update($request->all());

        $message->chat->chatable->touch();

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

    public function destroy(Request $request, ChatMessage $message)
    {
        $message->loadMissing(['creator']);
        $snapshot = $message->toArray();
        $chatId = $message->chat_id;
        $chat = $message->chat;

        $message->delete();

        if ($chat && $chat->chatable) {
            $chat->chatable->touch();
        }

        event(new ChatableMessageSynced(
            ChatableMessageSynced::ACTION_DELETED,
            $chatId,
            $snapshot,
        ));

        return response()->json($message);
    }

    public function show(Request $request, ChatMessage $chat_message)
    {
        return response()->json($chat_message);
    }
}
