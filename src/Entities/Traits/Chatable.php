<?php

namespace Unusualify\Modularous\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Modules\SystemNotification\Events\UnreadChatMessage;
use Modules\SystemNotification\Notifications\ChatableMessageBroadcastNotification;
use Modules\SystemNotification\Notifications\ChatableUnreadNotification;
use Unusualify\Modularous\Entities\Chat;
use Unusualify\Modularous\Entities\ChatMessage;
use Unusualify\Modularous\Entities\CreatorRecord;
use Unusualify\Modularous\Entities\Scopes\ChatableScopes;

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
        // $noAppend = static::$noChatableAppends ?? false;

        // if (! $noAppend) {
        //     $this->setAppends(array_merge($this->getAppends(), ['chat_messages_count', 'unread_chat_messages_count', 'unread_chat_messages_for_you_count']));
        // }
    }

    public function chat(): MorphOne
    {
        return $this->morphOne(Chat::class, 'chatable');
    }

    public function chatMessages(): HasManyThrough
    {
        $chatTable = (new Chat)->getTable();

        return $this->hasManyThrough(ChatMessage::class, Chat::class, 'chatable_id', 'chat_id', 'id', 'id')
            ->where("{$chatTable}.chatable_type", static::class);
    }

    public function creatorChatMessages(): HasManyThrough
    {
        $creator = $this->creator;

        return $this->hasManyThrough(ChatMessage::class, Chat::class, 'chatable_id', 'chat_id', 'id', 'id')
            ->whereHas('creatorRecord', function ($query) use ($creator) {
                if ($creator) {
                    $query->where('creator_id', $creator->id)
                        ->where('creator_type', get_class($creator));
                }
            });
    }

    public function latestChatMessage(): HasOneThrough
    {
        $chatTable = (new Chat)->getTable();
        $chatMessageTable = (new ChatMessage)->getTable();

        return $this->hasOneThrough(ChatMessage::class, Chat::class, 'chatable_id', 'chat_id', 'id', 'id')
            ->where("{$chatTable}.chatable_type", static::class)
            ->whereRaw("{$chatMessageTable}.created_at = (select max(created_at) from {$chatMessageTable} where {$chatMessageTable}.chat_id = {$chatTable}.id)");
    }

    public function unreadChatMessages(): HasManyThrough
    {
        return $this->chatMessages()->where('is_read', false);
    }

    public function unreadChatMessagesForYou(): HasManyThrough
    {
        return $this->unreadChatMessages()->whereNot(fn ($query) => $query->authorized());
    }

    public function unreadChatMessagesFromClient(): HasManyThrough
    {
        return $this->unreadChatMessages()->where(fn ($query) => $query->fromClient());
    }

    public function unreadChatMessagesFromCreator(): HasManyThrough
    {
        $creatorRecordTable = (new CreatorRecord)->getTable();
        $chatMessageTable = (new ChatMessage)->getTable();
        $chatTable = (new Chat)->getTable();
        $currentClass = static::class;

        return $this->unreadChatMessages()->where(function ($messageQuery) use ($creatorRecordTable, $chatMessageTable, $chatTable, $currentClass) {
            $creatableTableAlias = 'creatable_creators';
            $chatableTableAlias = 'chatable_creators';

            $messageQuery->whereExists(function ($subQuery) use ($creatorRecordTable, $chatMessageTable, $chatTable, $creatableTableAlias, $chatableTableAlias, $currentClass) {
                $subQuery->select(DB::raw(1))
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

    public function truncateChat(): void
    {
        $this->chat()->delete();

        $this->chat()->create();
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
            // get: fn () => $this->unreadChatMessagesFromClient()->count(),
            get: fn () => ! $this->relationLoaded('unreadChatMessagesFromClient') && $this->relationLoaded('chatMessages')
                ? ($this->chatMessages->filter(fn ($message) => $message->creator->roles_meta->contains('name', 'client-manager') || $message->creator->roles_meta->contains('name', 'client-assistant'))->count() ?? 0)
                : $this->unreadChatMessagesFromClient->count()
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

    /**
     * Resolve who should receive unread-chat notifications for the latest message.
     * Override on chatable models to customize recipient routing.
     *
     * @return iterable<object>
     */
    public function resolveChatableNotificationRecipients(ChatMessage $latestChatMessage): iterable
    {
        $messageCreator = $latestChatMessage->creator;

        if (! $messageCreator) {
            return [];
        }

        $chatableCreator = null;
        if (in_array('Unusualify\Modularous\Entities\Traits\HasCreator', class_uses_recursive($this))) {
            $chatableCreator = $this->creator;
        }

        $chatableAuthorizedUser = in_array('Unusualify\Modularous\Entities\Traits\HasAuthorizable', class_uses_recursive($this))
            ? ($this->is_authorized ? $this->authorizedUser : null)
            : null;

        $recipients = [];

        if (
            $chatableCreator
            && in_array('Illuminate\Notifications\RoutesNotifications', class_uses_recursive($chatableCreator))
            && ! $chatableCreator->is($messageCreator)
        ) {
            $recipients[] = $chatableCreator;
        } elseif (
            $chatableAuthorizedUser
            && in_array('Illuminate\Notifications\RoutesNotifications', class_uses_recursive($chatableAuthorizedUser))
            && ! $chatableAuthorizedUser->is($messageCreator)
        ) {
            $recipients[] = $chatableAuthorizedUser;
        }

        return $recipients;
    }

    /**
     * Immediately broadcast a retainable toast to other parties when a message is created.
     * Does not stamp notified_at — interval {@see handleChatableNotification} stays independent.
     */
    public function notifyChatableMessageBroadcast(ChatMessage $message): void
    {
        $chat = $this->relationLoaded('chat') ? $this->chat : $this->chat()->first();

        if (! $chat) {
            return;
        }

        foreach ($this->resolveChatableNotificationRecipients($message) as $notifiable) {
            $notifiable->notifyNow(new ChatableMessageBroadcastNotification($chat));
        }
    }

    public function handleChatableNotification(): void
    {
        $latestChatMessage = $this->latestChatMessage()->first();

        if ($latestChatMessage
            && $this->shouldSendChatableNotification($latestChatMessage)
            && $latestChatMessage->created_at->diffInMinutes(now()) > static::getChatableNotificationInterval()
            && ! $latestChatMessage->notified_at
        ) {
            try {
                // Domain fan-out (public channel) — not user-targeted.
                UnreadChatMessage::dispatch($latestChatMessage);

                // User-targeted channels (database / mail / broadcast → users.{id}).
                foreach ($this->resolveChatableNotificationRecipients($latestChatMessage) as $notifiable) {
                    $notifiable->notifyNow(new ChatableUnreadNotification($this->chat));
                }
            } finally {
                // Always stamp so a thrown NotificationSent listener (e.g. Telescope)
                // cannot leave notified_at null and re-broadcast forever.
                $latestChatMessage->forceFill(['notified_at' => now()])->saveQuietly();
            }
        }
    }

    /**
     * Get the number of chat messages for this model
     */
    public function numberOfChatMessages(): int
    {
        return $this->chatMessages->count();
    }

    /**
     * Get the number of unread chat messages for this model
     */
    public function numberOfUnreadChatMessages(): int
    {
        return $this->unreadChatMessages->count();
    }

    /**
     * Get the number of unread chat messages for you
     */
    public function numberOfUnreadChatMessagesForYou(): int
    {
        return $this->unreadChatMessagesForYou->count();
    }

    /**
     * Get the number of unread chat messages from creator
     */
    public function numberOfUnreadChatMessagesFromCreator(): int
    {
        return $this->unreadChatMessagesFromCreator->count();
    }

    /**
     * Get the number of unread chat messages from client
     */
    public function numberOfUnreadChatMessagesFromClient(): int
    {
        return $this->unreadChatMessagesFromClient->count();
    }

    /**
     * Get the number of unanswered creator chat messages
     */
    public function numberOfUnansweredCreatorChatMessages(): int
    {
        $latestMessage = $this->latestChatMessage;

        if (! $latestMessage) {
            return 0;
        }

        // Check if the latest message is from a creator and is unread
        return $this->hasUnansweredChatMessageFromCreator()->exists() ? 1 : 0;
    }
}
