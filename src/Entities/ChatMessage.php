<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularous\Entities\Scopes\ChatMessageScopes;
use Unusualify\Modularous\Entities\Traits\HasCreator;
use Unusualify\Modularous\Entities\Traits\HasFileponds;

class ChatMessage extends Model
{
    use HasCreator,
        HasFileponds,
        ChatMessageScopes;

    protected static $abortCreatorRoleExceptions = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chat_id',
        'content',
        'is_read',
        'is_pinned',
        'is_sent',
        'is_received',
        'edited_at',
        'notified_at',
        'pinned_until',
    ];

    protected $with = ['creator'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['user_profile', 'attachments', 'is_editable', 'editable_until', 'is_deletable', 'is_starred'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_read' => 'boolean',
        'is_pinned' => 'boolean',
        'is_sent' => 'boolean',
        'is_received' => 'boolean',
        'edited_at' => 'datetime',
        'notified_at' => 'datetime',
        'pinned_at' => 'datetime',
        'pinned_until' => 'datetime',
    ];

    /**
     * Perform any actions when booting the trait
     */
    public static function booted(): void
    {
        static::saving(function (self $model) {
            if ($model->isDirty('is_pinned')) {
                $model->pinned_at = $model->is_pinned ? now() : null;

                if (! $model->is_pinned) {
                    $model->pinned_until = null;
                }
            }
        });

        static::saved(function (self $model) {
            if ($model->isDirty('is_pinned') && $model->is_pinned) {
                $model->unpinOverflowingMessages();
            }
        });
    }

    /**
     * Maximum number of messages that can be pinned in a chat at the same time.
     */
    public static function getMaxPinnedMessages(): int
    {
        return max(1, (int) modularousConfig('chat.max_pinned_messages', 4));
    }

    /**
     * Durations (in hours) a message can be pinned for.
     *
     * @return array<int, int>
     */
    public static function getPinDurations(): array
    {
        return collect(modularousConfig('chat.pin_durations', [24, 24 * 7, 24 * 30]))
            ->map(fn ($hours) => (int) $hours)
            ->filter(fn (int $hours) => $hours > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Keep the chat within the pin limit by unpinning the oldest pins.
     *
     * This message (the one just pinned) always keeps its pin. A mass update is used so
     * that no model events fire; updated_at is bumped, so polling clients pick up the change.
     */
    protected function unpinOverflowingMessages(): void
    {
        $overflowIds = static::query()
            ->where('chat_id', $this->chat_id)
            ->activelyPinned()
            ->whereKeyNot($this->getKey())
            ->orderByDesc('pinned_at')
            ->orderByDesc('id')
            ->pluck('id')
            ->slice(static::getMaxPinnedMessages() - 1);

        if ($overflowIds->isEmpty()) {
            return;
        }

        static::query()->whereKey($overflowIds->values())->update([
            'is_pinned' => false,
            'pinned_at' => null,
            'pinned_until' => null,
        ]);
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Stars put on the message, one per user who starred it.
     */
    public function stars(): HasMany
    {
        return $this->hasMany(ChatMessageStar::class);
    }

    /**
     * Whether the given user starred the message.
     */
    public function isStarredBy(?Authenticatable $user): bool
    {
        if (! $user instanceof \Illuminate\Database\Eloquent\Model) {
            return false;
        }

        return $this->stars()
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getKey())
            ->exists();
    }

    /**
     * Whether the current user starred the message; stars are private to each user.
     *
     * Read from the `withStarredFor` scope when the query used it, otherwise looked up.
     */
    protected function isStarred(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? (bool) $value : $this->isStarredBy(Auth::user()),
        );
    }

    /**
     * Stored with microseconds so the pin expires exactly after the given duration;
     * the default date format would truncate them.
     */
    protected function pinnedUntil(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value === null ? null : Carbon::parse($value)->format('Y-m-d H:i:s.u'),
        );
    }

    protected function userProfile(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->creator ? get_user_profile($this->creator) : null,
        );
    }

    protected function attachments(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds->where('role', 'attachments')->map(function ($filepond) {
                return $filepond->mediableFormat();
            }),
        );
    }

    /**
     * Number of minutes after creation during which a message can be edited.
     */
    public static function getEditWindow(): int
    {
        return (int) modularousConfig('chat.edit_window', 15);
    }

    /**
     * Whether the message has been edited before.
     */
    public function isEdited(): bool
    {
        return ! is_null($this->edited_at);
    }

    /**
     * Whether the message can still be edited, regardless of who edits it.
     *
     * A message is editable only once, only while it has not been read
     * by the other party and only within the configured edit window.
     */
    public function canBeEdited(): bool
    {
        $editableUntil = $this->getEditableUntil();

        return ! $this->isEdited()
            && ! $this->is_read
            && $editableUntil !== null
            && $editableUntil->isFuture();
    }

    /**
     * Whether the given user can edit the message (must be its creator).
     */
    public function canBeEditedBy(?Authenticatable $user): bool
    {
        if (! $user || ! $this->canBeEdited()) {
            return false;
        }

        return $user instanceof \Illuminate\Database\Eloquent\Model
            && $this->creator?->is($user) === true;
    }

    /**
     * Whether the message can still be deleted, regardless of who deletes it.
     *
     * Deleting follows the same rules as editing: only once, only while
     * unread by the other party and only within the edit window.
     */
    public function canBeDeleted(): bool
    {
        return ! $this->trashed() && $this->canBeEdited();
    }

    /**
     * Whether the given user can delete the message (must be its creator).
     */
    public function canBeDeletedBy(?Authenticatable $user): bool
    {
        return $this->canBeDeleted() && $this->canBeEditedBy($user);
    }

    /**
     * The moment after which the message can no longer be edited.
     */
    public function getEditableUntil(): ?Carbon
    {
        return $this->created_at
            ? Carbon::parse($this->created_at)->addMinutes(static::getEditWindow())
            : null;
    }

    protected function isEditable(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->canBeEdited(),
        );
    }

    protected function isDeletable(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->canBeDeleted(),
        );
    }

    protected function editableUntil(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->canBeEdited() ? $this->getEditableUntil()?->toJSON() : null,
        );
    }

    public function getTable()
    {
        return modularousConfig('tables.chat_messages', parent::getTable());
    }
}
