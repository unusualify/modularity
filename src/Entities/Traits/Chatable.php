<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\SystemNotification\Notifications\ChatableUnreadNotification;
use Unusualify\Modularity\Entities\Chat;
use Unusualify\Modularity\Entities\ChatMessage;
use Unusualify\Modularity\Entities\CreatorRecord;
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

        if (! $noAppend) {
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

    public function creatorChatMessages(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        $creator = $this->creator;

        return $this->whereHas('chatMessages', function ($query) use ($creator) {
            $query->isCreator($creator->id, $creator->guard_name);
        });
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

    public function unreadChatMessagesFromClient(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->unreadChatMessages()->where(fn ($query) => $query->fromClient());
    }

    public function unreadChatMessagesFromCreator(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        $creatorRecordTable = (new CreatorRecord)->getTable();
        $chatMessageTable = (new ChatMessage)->getTable();
        $chatTable = (new Chat)->getTable();
        $currentClass = static::class;

        return $this->unreadChatMessages()->where(function ($messageQuery) use ($creatorRecordTable, $chatMessageTable, $chatTable, $currentClass) {
            $creatableTableAlias = 'creatable_creators';
            $chatableTableAlias = 'chatable_creators';

            $messageQuery->whereExists(function ($subQuery) use ($creatorRecordTable, $chatMessageTable, $chatTable, $creatableTableAlias, $chatableTableAlias, $currentClass) {
                $subQuery->select(\DB::raw(1))
                    ->from($creatorRecordTable . ' as ' . $creatableTableAlias)
                    ->join($creatorRecordTable . ' as ' . $chatableTableAlias, function ($join) use ($creatableTableAlias, $chatableTableAlias) {
                        $join->on($creatableTableAlias . '.creator_id', '=', $chatableTableAlias . '.creator_id')
                            ->on($creatableTableAlias . '.guard_name', '=', $chatableTableAlias . '.guard_name');
                    })
                    ->whereColumn($creatableTableAlias . '.creatable_id', $chatTable . '.chatable_id')
                    ->where($creatableTableAlias . '.creatable_type', $currentClass)
                    ->whereColumn($chatableTableAlias . '.creatable_id', $chatMessageTable . '.id')
                    ->where($chatableTableAlias . '.creatable_type', ChatMessage::class);
            });
        });
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

    protected function unreadChatMessagesFromCreatorCount(): Attribute
    {
        return new Attribute(
            get: fn () => $this->numberOfUnreadChatMessagesFromCreator(),
        );
    }

    protected function unreadChatMessagesFromClientCount(): Attribute
    {
        return new Attribute(
            get: fn () => $this->numberOfUnreadChatMessagesFromClient(),
        );
    }

    public function isUnanswered(): Attribute
    {
        return new Attribute(
            get: fn () => $this->numberOfUnansweredCreatorChatMessages(),
        );
    }

    /**
     * Get the interval in minutes
     */
    protected static function getChatableNotificationInterval(): int
    {
        return static::$chatableNotificationInterval ?? 60;
    }

    public function shouldSendChatableNotification(ChatMessage $latestChatMessage): bool
    {
        return ! $latestChatMessage->is_read;
    }

    public function handleChatableNotification(): void
    {
        $latestChatMessage = $this->latestChatMessage()->first();

        if ($latestChatMessage
            && $this->shouldSendChatableNotification($latestChatMessage)
            && $latestChatMessage->created_at->diffInMinutes(now()) > static::getChatableNotificationInterval()
            && ! $latestChatMessage->notified_at
        ) {
            $chatableCreator = null;
            if (in_array('Unusualify\Modularity\Entities\Traits\HasCreator', class_uses_recursive($this))) {
                $chatableCreator = $this->creator;
            }

            $messageCreator = $latestChatMessage->creator;

            $chatableAuthorizedUser = in_array('Unusualify\Modularity\Entities\Traits\HasAuthorizable', class_uses_recursive($this))
                ? ($this->is_authorized ? $this->authorizedUser : null)
                : null;

            if ($messageCreator) {
                if ($chatableCreator && in_array('Illuminate\Notifications\RoutesNotifications', class_uses_recursive($chatableCreator)) && ! $chatableCreator->is($messageCreator)) {
                    $chatableCreator->notifyNow(new ChatableUnreadNotification($this));
                } elseif ($chatableAuthorizedUser && in_array('Illuminate\Notifications\RoutesNotifications', class_uses_recursive($chatableAuthorizedUser)) && ! $chatableAuthorizedUser->is($messageCreator)) {
                    $chatableAuthorizedUser->notifyNow(new ChatableUnreadNotification($this));
                }
            }
        }
    }
}
