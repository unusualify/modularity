<?php

namespace Unusualify\Modularous\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Unusualify\Modularous\Entities\Chat;
use Unusualify\Modularous\Entities\ChatMessage;
use Unusualify\Modularous\Entities\ChatMessageStar;
use Unusualify\Modularous\Facades\Filepond;

class ChatMessageService
{
    /**
     * Attributes that can be toggled on a message by any chat participant.
     *
     * is_starred is private to each user and stored per user, not on the message.
     *
     * @var array<int, string>
     */
    public const UPDATABLE_FLAGS = [
        'is_read',
        'is_starred',
        'is_pinned',
        'is_sent',
        'is_received',
    ];

    /**
     * Update the flags of a chat message.
     *
     * When pinning, the optional pin duration (in hours) sets when the pin expires;
     * without it the message stays pinned until it is unpinned.
     *
     * Starring is private: it only changes the stars of the given user, and the
     * message itself is not updated, so the other participants see no change.
     *
     * @param array<string, mixed> $flags subset of UPDATABLE_FLAGS
     */
    public function updateFlags(ChatMessage $message, array $flags, ?int $pinDuration = null, ?Authenticatable $user = null): ChatMessage
    {
        $attributes = array_intersect_key($flags, array_flip(self::UPDATABLE_FLAGS));

        if (array_key_exists('is_starred', $attributes)) {
            $this->setStarred($message, $user, filter_var($attributes['is_starred'], FILTER_VALIDATE_BOOLEAN));

            unset($attributes['is_starred']);
        }

        if (! empty($attributes['is_pinned'])) {
            $attributes['pinned_until'] = $pinDuration ? now()->addHours($pinDuration) : null;
        }

        if (! empty($attributes)) {
            $message->update($attributes);

            $message->chat->chatable?->touch();
        }

        // is_starred of the response reflects the given user's star
        $message->loadExists(['stars as is_starred' => fn ($query) => $this->whereUser($query, $user)]);

        return $message;
    }

    /**
     * Star or unstar a message for the given user only.
     *
     * @return bool whether the user's star changed
     */
    public function setStarred(ChatMessage $message, ?Authenticatable $user, bool $starred): bool
    {
        if (! $user instanceof Model) {
            return false;
        }

        $attributes = [
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->getKey(),
        ];

        if ($starred) {
            return $message->stars()->firstOrCreate($attributes)->wasRecentlyCreated;
        }

        return $message->stars()->where($attributes)->delete() > 0;
    }

    /**
     * Unpin the messages of the chat whose pin duration has ended.
     *
     * A mass update is used so that no model events fire; updated_at is bumped,
     * so polling clients pick up the new pin status.
     *
     * @return int the number of messages unpinned
     */
    public function releaseExpiredPins(Chat $chat): int
    {
        return $chat->messages()->expiredPins()->update([
            'is_pinned' => false,
            'pinned_at' => null,
            'pinned_until' => null,
        ]);
    }

    /**
     * Edit the content and attachments of a chat message.
     *
     * The message row is locked during the check so that a concurrent
     * "mark as read" cannot slip in between the check and the update.
     *
     * @param array{content?: ?string, attachments?: ?array} $data
     *
     * @throws AuthorizationException when the message is no longer editable by the user
     */
    public function edit(ChatMessage $message, ?Authenticatable $user, array $data): ChatMessage
    {
        return DB::transaction(function () use ($message, $user, $data) {
            /** @var ChatMessage $locked */
            $locked = ChatMessage::query()->lockForUpdate()->findOrFail($message->getKey());

            if (! $locked->canBeEditedBy($user)) {
                throw new AuthorizationException(__('This message can no longer be edited.'));
            }

            // nothing changed: keep the message as is, it must not be marked as edited
            if (! $this->hasChanges($locked, $data)) {
                return $locked;
            }

            $locked->forceFill([
                'content' => $data['content'] ?? null,
                'edited_at' => now(),
            ])->save();

            if (array_key_exists('attachments', $data)) {
                Filepond::saveFile($locked, $data['attachments'] ?? [], 'attachments');
            }

            // touch through a fresh query so the chat/chatable relations are not attached to the response
            $locked->chat()->first()?->chatable?->touch();

            return $locked->refresh();
        });
    }

    /**
     * Delete a chat message together with its attachments.
     *
     * Follows the editing rules; the row is locked during the check so that
     * a concurrent "mark as read" cannot slip in between the check and the delete.
     *
     * @throws AuthorizationException when the message can no longer be deleted by the user
     */
    public function delete(ChatMessage $message, ?Authenticatable $user): ChatMessage
    {
        return DB::transaction(function () use ($message, $user) {
            /** @var ChatMessage $locked */
            $locked = ChatMessage::query()->lockForUpdate()->findOrFail($message->getKey());

            if (! $locked->canBeDeletedBy($user)) {
                throw new AuthorizationException(__('This message can no longer be deleted.'));
            }

            Filepond::saveFile($locked, [], 'attachments');

            // soft delete: deleted_at and updated_at are set, so polling clients can drop the message
            $locked->delete();

            $locked->chat()->first()?->chatable?->touch();

            return $locked;
        });
    }

    /**
     * Mark every message of the chat sent by the other party as read for the given user.
     *
     * The user's own messages (and their company peers', per the creator access rules)
     * are left untouched, so their read status keeps reflecting the other party.
     * updated_at is bumped, so polling clients pick up the new read status.
     *
     * @return int the number of messages marked as read
     */
    public function markAllAsRead(Chat $chat, ?Authenticatable $user): int
    {
        if (! $user) {
            return 0;
        }

        $ids = $chat->messages()
            ->where('is_read', false)
            ->whereNot(fn ($query) => $query->hasAccessToCreation($user))
            ->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        $count = ChatMessage::query()->whereKey($ids)->update(['is_read' => true]);

        $chat->chatable?->touch();

        return $count;
    }

    /**
     * Unstar every message of the chat starred by the given user.
     *
     * Stars are private, so the other participants' stars are left untouched.
     *
     * @return int the number of messages unstarred
     */
    public function unstarAll(Chat $chat, ?Authenticatable $user): int
    {
        if (! $user instanceof Model) {
            return 0;
        }

        return $this->whereUser(
            // without global scopes, the subquery selects only the id (and trashed messages are unstarred too)
            ChatMessageStar::query()->whereIn('chat_message_id', $chat->messages()->withoutGlobalScopes()->select('id')),
            $user
        )->delete();
    }

    /**
     * Limit a stars query to the stars of the given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation
     */
    protected function whereUser($query, ?Authenticatable $user)
    {
        if (! $user instanceof Model) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getKey());
    }

    /**
     * Whether the given edit data differs from the current message.
     *
     * Content is compared ignoring surrounding whitespace, attachments by their uuids.
     *
     * @param array{content?: ?string, attachments?: ?array} $data
     */
    public function hasChanges(ChatMessage $message, array $data): bool
    {
        if (trim((string) ($data['content'] ?? '')) !== trim((string) $message->content)) {
            return true;
        }

        if (! array_key_exists('attachments', $data)) {
            return false;
        }

        $currentUuids = $message->fileponds()
            ->where('role', 'attachments')
            ->pluck('uuid')
            ->sort()
            ->values()
            ->all();

        $newUuids = collect($data['attachments'] ?? [])
            ->pluck('uuid')
            ->filter()
            ->sort()
            ->values()
            ->all();

        return $currentUuids !== $newUuids;
    }
}
