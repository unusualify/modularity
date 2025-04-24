<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Chat;
use Unusualify\Modularity\Entities\ChatMessage;
use Unusualify\Modularity\Entities\Scopes\ChatableScopes;

trait Chatable
{
    use ChatableScopes;

    /**
     * Perform any actions when booting the trait
     */
    public static function bootChatable(): void
    {
        static::retrieved(function (Model $model) {
            if ($model->chat) {
                $model->setAttribute('_chat_id', $model->chat->id);
            } elseif ($model->{$model->getKeyName()}) {
                $chat = $model->chat()->create();
                $model->setAttribute('_chat_id', $chat->id);
            }
        });

        static::created(function (Model $model) {
            $model->chat()->create();
        });

        static::saving(function (Model $model) {
            $model->offsetUnset('_chat_id');
        });
    }


    /**
     * Laravel hook to initialize the trait
     */
    public function initializeChatable(): void
    {
        $noAppend = static::$noChatableAppends ?? false;

        if(!$noAppend) {
            $this->setAppends(array_merge($this->getAppends(), ['chat_messages_count', 'unread_chat_messages_count', 'unread_chat_messages_for_you_count']));
        }
    }

    public function chat(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Chat::class, 'chatable');
    }

    public function chatMessages(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(ChatMessage::class, Chat::class, 'chatable_id', 'chat_id', 'id', 'id');
    }

    public function latestChatMessage(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        $chatTable = (new Chat)->getTable();
        $chatMessageTable = (new ChatMessage)->getTable();

        return $this->hasOneThrough(ChatMessage::class, Chat::class, 'chatable_id', 'chat_id', 'id', 'id')
            ->whereRaw("{$chatMessageTable}.created_at = (select max(created_at) from {$chatMessageTable} where {$chatMessageTable}.chat_id = {$chatTable}.id)");
    }

    public function unreadChatMessages(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->chatMessages()->where('is_read', false);
    }

    public function unreadChatMessagesForYou(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->unreadChatMessages()->whereNot(fn ($query) => $query->authorized());
    }

    protected function chatMessagesCount(): Attribute
    {
        return new Attribute(
            get: fn () => $this->numberOfChatMessages(),
        );
    }

    protected function unreadChatMessagesCount(): Attribute
    {
        return new Attribute(
            get: fn () => $this->numberOfUnreadChatMessages(),
        );
    }

    protected function unreadChatMessagesForYouCount(): Attribute
    {
        return new Attribute(
            get: fn () => $this->numberOfUnreadChatMessagesForYou(),
        );
    }
}
