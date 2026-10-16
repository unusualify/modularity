<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chatable_id',
        'chatable_type',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['attachments'];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Latest message for this chat (one row per chat_id), using Laravel's one-of-many join.
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany('created_at');
    }

    public function fileponds(): HasManyThrough
    {
        return $this->hasManyThrough(Filepond::class, ChatMessage::class, 'chat_id', 'filepondable_id', 'id');
    }

    public function attachments(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds->where('role', 'attachments')->map(function ($filepond) {
                return $filepond->mediableFormat();
            }),
        );
    }

    public function chatable()
    {
        return $this->morphTo();
    }

    /**
     * Pinned messages of the chat whose pin has not expired, the most recently pinned first.
     */
    public function pinnedMessages(): HasMany
    {
        return $this->messages()
            ->activelyPinned()
            ->orderByDesc('pinned_at')
            ->orderByDesc('id');
    }

    /**
     * Messages of the chat starred by the given user (the current user by default),
     * ordered by the message date, the newest first. Stars are private to each user.
     */
    public function starredMessages(?Authenticatable $user = null): HasMany
    {
        return $this->messages()
            ->starredBy($user)
            ->withStarredFor($user)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * The most recently pinned message.
     */
    public function pinnedMessage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->pinnedMessages()->first(),
        );
    }

    public function getTable()
    {
        return modularousConfig('tables.chats', parent::getTable());
    }
}
