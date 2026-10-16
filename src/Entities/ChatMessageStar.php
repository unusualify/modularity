<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A star a user puts on a chat message.
 *
 * Stars are private: each user sees only the messages they starred.
 */
class ChatMessageStar extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chat_message_id',
        'user_type',
        'user_id',
    ];

    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class);
    }

    /**
     * The user who starred the message.
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTable()
    {
        return modularousConfig('tables.chat_message_stars', 'um_chat_message_stars');
    }
}
