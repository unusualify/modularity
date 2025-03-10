<?php

namespace Unusualify\Modularity\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Unusualify\Modularity\Entities\Chat;
use Unusualify\Modularity\Entities\ChatMessage;
use Unusualify\Modularity\Facades\Filepond;

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

        return response()->json($chatMessage);
    }

    public function attachments(Request $request, Chat $chat)
    {
        return response()->json($chat->attachments);
    }

    public function update(Request $request, ChatMessage $message)
    {
        $message->update($request->all());

        return response()->json($message);
    }

    public function show(Request $request, ChatMessage $message)
    {
        return response()->json($message);
    }

    public function destroy(Request $request, ChatMessage $message)
    {
        $message->delete();

        return response()->json($message);
    }
}
